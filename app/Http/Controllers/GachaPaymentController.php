<?php

namespace App\Http\Controllers;

use App\Models\GachaHistory;
use App\Models\GachaPayment;
use App\Models\GachaPool;
use App\Models\UserDiscount;
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

    public function __construct()
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
                    'email' => $user->email,
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
                    'image' => $this->imageForRarity($history->gachaPool->rarity_item),
                ];
            }
        }

        return response()->json([
            'status' => $payment->status,
            'prize' => $prize,
        ]);
    }

    /**
     * Mark payment paid, run weighted RNG spin, issue voucher.
     */
    private function fulfill(GachaPayment $payment): void
    {
        $payment->update(['status' => 'paid']);

        $prizes = GachaPool::all();

        if ($prizes->isEmpty()) {
            return;
        }

        $totalWeight = $prizes->sum('base_win_chance');
        $random = mt_rand(0, (int) ($totalWeight * 100)) / 100;
        $cumulative = 0;
        $wonPrize = null;

        foreach ($prizes as $prize) {
            $cumulative += $prize->base_win_chance;
            if ($random <= $cumulative) {
                $wonPrize = $prize;
                break;
            }
        }

        if (! $wonPrize) {
            $wonPrize = $prizes->last();
        }

        GachaHistory::create([
            'user_id' => $payment->user_id,
            'gacha_pool_id' => $wonPrize->id,
            'points_spent' => 0,
            'gacha_payment_id' => $payment->id,
        ]);

        if ($wonPrize->discount_type_id) {
            UserDiscount::create([
                'user_id' => $payment->user_id,
                'discount_type_id' => $wonPrize->discount_type_id,
                'is_used' => false,
                'obtained_from' => 'gacha',
                'expires_at' => now()->addDays(14),
            ]);
        }
    }

    private function imageForRarity(string $rarity): string
    {
        return match ($rarity) {
            'legendary', 'grand_prize' => '/gacha-assets/jackpot.png',
            'epic', 'rare' => '/gacha-assets/voucher.png',
            default => '/gacha-assets/points.png',
        };
    }
}
