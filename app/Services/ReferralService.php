<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralConfig;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReferralService
{
    /**
     * Result codes returned by claimCode() so callers (sign-up vs. profile claim)
     * can present the right UX without parsing free-form messages.
     */
    public const CLAIM_OK = 'ok';

    public const CLAIM_NOT_FOUND = 'not_found';

    public const CLAIM_SELF = 'self_referral';

    public const CLAIM_ALREADY_HAS_REFERRER = 'already_has_referrer';

    public const CLAIM_HAS_PAID_ORDER = 'has_paid_order';

    /**
     * Attempt to bind `$newUser` to the referrer identified by `$code`. Grants the
     * referee welcome bonus and creates a pending Referral. Idempotent against
     * race conditions via the (referrer_id, referred_user_id) unique index.
     *
     * @return array{status: string, referral?: Referral, points_awarded?: int}
     */
    public function claimCode(User $newUser, string $code): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return ['status' => self::CLAIM_NOT_FOUND];
        }

        $referrer = User::where('referral_code', $code)->first();
        if (! $referrer) {
            return ['status' => self::CLAIM_NOT_FOUND];
        }

        if ($referrer->id === $newUser->id) {
            return ['status' => self::CLAIM_SELF];
        }

        if ($newUser->referred_by) {
            return ['status' => self::CLAIM_ALREADY_HAS_REFERRER];
        }

        // Front-run guard: a user who has already paid for something cannot
        // retroactively credit a friend (otherwise people self-buy first, then
        // claim a "friend's" code with a burner referee).
        if ($newUser->orders()->where('status', 'paid')->exists()) {
            return ['status' => self::CLAIM_HAS_PAID_ORDER];
        }

        $config = ReferralConfig::current();

        $result = DB::transaction(function () use ($newUser, $referrer, $config) {
            $newUser->forceFill(['referred_by' => $referrer->id])->save();

            try {
                $referral = Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_user_id' => $newUser->id,
                    'status' => Referral::STATUS_PENDING,
                ]);
            } catch (QueryException $e) {
                // Unique-pair collision: the row already exists. Reload it.
                $referral = Referral::where('referrer_id', $referrer->id)
                    ->where('referred_user_id', $newUser->id)
                    ->firstOrFail();
            }

            $welcome = (int) $config->referee_welcome_points;
            if ($welcome > 0) {
                $created = $this->recordReward($referral, $newUser, null, ReferralReward::KIND_REFEREE_WELCOME, $welcome);
                if ($created) {
                    $newUser->increment('points_balance', $welcome);
                }
            }

            return ['referral' => $referral, 'points_awarded' => $welcome];
        });

        return ['status' => self::CLAIM_OK] + $result;
    }

    /**
     * Hook called from CheckoutController when an order flips to `paid`. Fires
     * Tier A (first-purchase bonus) the first time and Tier B (commission) on
     * subsequent orders if enabled. Safe to call multiple times for the same
     * order — the unique index on referral_rewards makes it idempotent.
     */
    public function onPaidOrder(Order $order): void
    {
        $user = $order->user ?? User::find($order->user_id);
        if (! $user || ! $user->referred_by) {
            return;
        }

        $referral = Referral::where('referred_user_id', $user->id)->first();
        if (! $referral || $referral->status === Referral::STATUS_VOID) {
            return;
        }

        $referrer = $referral->referrer;
        if (! $referrer) {
            return;
        }

        $config = ReferralConfig::current();

        // Anti-fraud: don't pay out if the referrer's account is too new.
        if ($config->referrer_min_account_age_h > 0
            && $referrer->created_at
            && $referrer->created_at->diffInHours(now()) < $config->referrer_min_account_age_h) {
            return;
        }

        DB::transaction(function () use ($order, $referral, $referrer, $config) {
            $referral = $referral->fresh();

            if ($referral->status === Referral::STATUS_PENDING) {
                $this->payFirstPurchase($order, $referral, $referrer, $config);
            } elseif ($referral->status === Referral::STATUS_FIRST_PURCHASE_REWARDED) {
                $this->payCommission($order, $referral, $referrer, $config);
            }
        });
    }

    private function payFirstPurchase(Order $order, Referral $referral, User $referrer, ReferralConfig $config): void
    {
        $points = (int) $config->referrer_first_purchase_pts;
        if ($points <= 0) {
            $referral->forceFill([
                'status' => Referral::STATUS_FIRST_PURCHASE_REWARDED,
                'first_purchase_order_id' => $order->id,
                'first_purchase_rewarded_at' => now(),
            ])->save();

            return;
        }

        $created = $this->recordReward($referral, $referrer, $order, ReferralReward::KIND_FIRST_PURCHASE, $points);

        if (! $created) {
            return;
        }

        $referrer->increment('points_balance', $points);

        $referral->forceFill([
            'status' => Referral::STATUS_FIRST_PURCHASE_REWARDED,
            'first_purchase_order_id' => $order->id,
            'first_purchase_rewarded_at' => now(),
        ])->save();
    }

    private function payCommission(Order $order, Referral $referral, User $referrer, ReferralConfig $config): void
    {
        $rate = (float) $config->commission_percent;
        if ($rate <= 0) {
            return;
        }

        $subtotal = (float) ($order->subtotal ?? $order->total_price_after_discount ?? 0);
        if ($subtotal <= 0) {
            return;
        }

        $perOrderCap = (int) $config->commission_per_order_cap;
        $lifetimeCap = (int) $config->commission_lifetime_cap;

        $raw = (int) floor($subtotal * $rate / 100);
        $payout = $perOrderCap > 0 ? min($raw, $perOrderCap) : $raw;

        if ($lifetimeCap > 0) {
            $remaining = max(0, $lifetimeCap - (int) $referral->total_commission_paid);
            $payout = min($payout, $remaining);
        }

        if ($payout <= 0) {
            return;
        }

        $created = $this->recordReward($referral, $referrer, $order, ReferralReward::KIND_COMMISSION, $payout);

        if (! $created) {
            return;
        }

        $referrer->increment('points_balance', $payout);
        $referral->forceFill([
            'total_commission_paid' => (int) $referral->total_commission_paid + $payout,
        ])->save();
    }

    /**
     * Insert a reward row, returning true if a new row was created and false if
     * the unique index blocked a duplicate (already paid).
     */
    private function recordReward(Referral $referral, User $recipient, ?Order $order, string $kind, int $points): bool
    {
        if ($points <= 0) {
            throw new RuntimeException('Refusing to record a zero/negative reward.');
        }

        try {
            ReferralReward::create([
                'referral_id' => $referral->id,
                'recipient_id' => $recipient->id,
                'order_id' => $order?->id,
                'kind' => $kind,
                'points_amount' => $points,
            ]);

            return true;
        } catch (QueryException $e) {
            // 23000 = integrity constraint (unique). Duplicate kind/order pair → already paid.
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'UNIQUE')) {
                return false;
            }
            throw $e;
        }
    }
}
