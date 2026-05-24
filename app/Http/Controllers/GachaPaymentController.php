<?php

namespace App\Http\Controllers;

use App\Models\GachaHistory;
use App\Models\GachaPayment;
use App\Models\User;
use App\Services\GachaRollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Transaction;

class GachaPaymentController extends Controller
{
    public const SPIN_PRICE = 15000;

    public function __construct(private readonly GachaRollService $roller)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        if (! config('midtrans.is_production')) {
            Config::$curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER => [],
            ];
        }
    }

    /**
     * Create a pending gacha payment and return a Midtrans Snap token.
     */
    public function store(): JsonResponse
    {
        $user = Auth::user();
        $orderId = 'GACHA-'.date('Ymd').'-'.strtoupper(Str::random(6)).'::'.time();

        DB::beginTransaction();

        try {
            $payment = GachaPayment::create([
                'user_id' => $user->id,
                'amount' => self::SPIN_PRICE,
                'status' => 'pending',
                'midtrans_order_id' => $orderId,
            ]);

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => self::SPIN_PRICE,
                ],
                'item_details' => [[
                    'id' => 'GACHA-SPIN',
                    'price' => self::SPIN_PRICE,
                    'quantity' => 1,
                    'name' => 'Gacha Spin (1x)',
                ]],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $this->midtransSafeEmail($user),
                ],
            ];

            $snapToken = Snap::getSnapToken($params);
            $payment->update(['snap_token' => $snapToken]);

            DB::commit();

            return response()->json([
                'snap_token' => $snapToken,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gacha payment create failed: '.$e->getMessage());

            return response()->json(['message' => 'Failed to create payment. Please try again.'], 500);
        }
    }

    /**
     * Midtrans server-to-server webhook — fulfill on settlement/capture.
     */
    public function callback(): JsonResponse
    {
        try {
            $notification = new Notification;
        } catch (\Exception $e) {
            Log::error('Gacha Midtrans notification error: '.$e->getMessage());

            return response()->json(['message' => 'Invalid notification.'], 400);
        }

        $midtransOrderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? 'accept';

        $payment = GachaPayment::where('midtrans_order_id', $midtransOrderId)->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        if (in_array($payment->status, ['paid', 'failed'])) {
            return response()->json(['message' => 'Already processed.']);
        }

        if (in_array($transactionStatus, ['capture', 'settlement']) && $fraudStatus === 'accept') {
            $this->fulfill($payment);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Frontend polling — check payment status and return prize when paid.
     */
    public function verify(GachaPayment $payment): JsonResponse
    {
        if ($payment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($payment->status === 'pending') {
            try {
                $midtransStatus = Transaction::status($payment->midtrans_order_id);
                $txStatus = $midtransStatus->transaction_status ?? null;
                $fraudStatus = $midtransStatus->fraud_status ?? 'accept';

                if (in_array($txStatus, ['capture', 'settlement']) && $fraudStatus === 'accept') {
                    $this->fulfill($payment);
                } elseif (in_array($txStatus, ['cancel', 'deny', 'expire'])) {
                    $payment->update(['status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::warning('Gacha payment verify failed: '.$e->getMessage());
            }

            $payment->refresh();
        }

        $prize = null;

        if ($payment->status === 'paid') {
            $history = GachaHistory::with('gachaPool')
                ->where('gacha_payment_id', $payment->id)
                ->first();

            if ($history) {
                $prize = [
                    'id' => $history->gachaPool->id,
                    'name' => $history->gachaPool->prize_name,
                    'rarity' => $history->gachaPool->rarity_item,
                    'reward_type' => $history->gachaPool->reward_type,
                    'points_amount' => $history->gachaPool->points_amount,
                    'image' => GachaController::resolveImageFor($history->gachaPool),
                    'discount_name' => $history->gachaPool->discountType?->name ?? '',
                ];
            }
        }

        return response()->json([
            'status' => $payment->status,
            'prize' => $prize,
        ]);
    }

    /**
     * Midtrans rejects emails whose TLD is shorter than 2 chars (e.g. a@a.a from the
     * dev seeder). Swap in a deterministic placeholder when the user's stored email
     * would fail Midtrans validation so the spin can still proceed end-to-end.
     */
    private function midtransSafeEmail(User $user): string
    {
        if (is_string($user->email) && preg_match('/@[^@\s]+\.[a-zA-Z]{2,}$/', $user->email)) {
            return $user->email;
        }

        return 'user-'.$user->id.'@noreply.ridly.example';
    }

    /**
     * Mark payment paid, run a roll through GachaRollService (pity + boosters apply),
     * dispatch the reward, and link the resulting history to this payment.
     *
     * Idempotent: if this payment already has a linked history row, skip.
     */
    public function fulfill(GachaPayment $payment): void
    {
        if (GachaHistory::where('gacha_payment_id', $payment->id)->exists()) {
            $payment->update(['status' => 'paid']);

            return;
        }

        $user = User::find($payment->user_id);
        if (! $user) {
            $payment->update(['status' => 'failed']);

            return;
        }

        DB::transaction(function () use ($payment, $user) {
            $payment->update(['status' => 'paid']);

            $outcome = $this->roller->roll($user);
            $prize = $outcome['prize'];

            $this->roller->dispatchReward($user, $prize);

            GachaHistory::create([
                'user_id' => $user->id,
                'gacha_pool_id' => $prize->id,
                'points_spent' => 0,
                'gacha_payment_id' => $payment->id,
                'cost_type' => 'money',
                'pity_triggered' => $outcome['pity_triggered'],
                'reward_type' => $prize->reward_type,
                'image_path' => GachaController::resolveImageFor($prize),
            ]);
        });
    }
}
