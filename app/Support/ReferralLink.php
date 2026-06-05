<?php

namespace App\Support;

use App\Models\User;
use App\Services\ReferralService;

/**
 * Shared helpers for the "open a share link → auto-redeem" flow. Used by both
 * the HandleReferralLink middleware (logged-in users claim instantly) and the
 * auth flows in AuthController (guests claim right after they authenticate).
 */
class ReferralLink
{
    /** Session key holding a code a guest opened before authenticating. */
    public const SESSION_KEY = 'pending_referral_code';

    /**
     * Map a ReferralService::claimCode() result to a [flashKey, message] pair
     * suitable for a session flash → toast.
     *
     * @param  array{status: string, points_awarded?: int}  $result
     * @return array{0: string, 1: string}
     */
    public static function feedbackFor(array $result): array
    {
        return match ($result['status']) {
            ReferralService::CLAIM_OK => ['success', 'Referral applied! You received '.number_format($result['points_awarded'] ?? 0).' bonus points.'],
            ReferralService::CLAIM_SELF => ['error', "You can't use your own referral link."],
            ReferralService::CLAIM_ALREADY_HAS_REFERRER => ['info', "You've already used a referral code."],
            ReferralService::CLAIM_HAS_PAID_ORDER => ['info', 'Referral links can only be claimed before your first purchase.'],
            ReferralService::CLAIM_NOT_FOUND => ['error', 'That referral link is invalid.'],
            default => ['error', 'Could not apply the referral link.'],
        };
    }

    /**
     * Claim a code stashed in the session (set when a guest opened a share link
     * before signing in) and clear it. Returns a [flashKey, message] pair to
     * flash, or null when there was nothing pending.
     *
     * @return array{0: string, 1: string}|null
     */
    public static function consumePending(ReferralService $referrals, User $user): ?array
    {
        $code = trim((string) session(self::SESSION_KEY, ''));
        if ($code === '') {
            return null;
        }

        session()->forget(self::SESSION_KEY);

        return self::feedbackFor($referrals->claimCode($user, $code));
    }
}
