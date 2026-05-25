<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductKey;
use App\Models\TopupCredential;
use App\Models\UserDiscount;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Show the user's purchased product keys, direct top-up items, and active discount vouchers.
     */
    public function index()
    {
        $user = Auth::user();

        $this->settleProcessingTopups($user->id);

        // Voucher code product keys from paid orders
        $productKeys = ProductKey::with(['product', 'order'])
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', 'paid'))
            ->orderByDesc('order_id')
            ->get()
            ->map(fn (ProductKey $key) => [
                'name' => $key->product->name,
                'code' => $key->key_code,
                'item_type' => 'voucher_key',
                'type' => 'Product',
                'date' => $key->order?->created_at?->format('Y-m-d') ?? '-',
                'image' => '/products/'.ltrim($key->product->image ?: 'soundcloud.png', '/'),
                'topup_status' => null,
                'player_id' => null,
                'zone_id' => null,
                'server_id' => null,
            ]);

        // Direct top-up order details from paid orders
        $topupItems = OrderDetail::with(['product', 'order', 'topupCredential'])
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', 'paid'))
            ->whereHas('product', fn ($q) => $q->where('type', 'direct_topup'))
            ->orderByDesc('order_id')
            ->get()
            ->flatMap(function (OrderDetail $detail) {
                $items = [];
                for ($i = 0; $i < $detail->quantity; $i++) {
                    $items[] = [
                        'name' => $detail->product->name,
                        'code' => null,
                        'item_type' => 'direct_topup',
                        'type' => 'Product',
                        'date' => $detail->order?->created_at?->format('Y-m-d') ?? '-',
                        'image' => '/products/'.ltrim($detail->product->image ?: 'soundcloud.png', '/'),
                        'topup_status' => $detail->topupCredential?->topup_status ?? 'pending',
                        'player_id' => $detail->topupCredential?->player_id,
                        'zone_id' => $detail->topupCredential?->zone_id,
                        'server_id' => $detail->topupCredential?->server_id,
                    ];
                }

                return $items;
            });

        // Active discount vouchers
        $vouchers = UserDiscount::with('discountType')
            ->where('user_id', $user->id)
            ->where('is_used', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn (UserDiscount $ud) => [
                'name' => $ud->discountType->name,
                'code' => 'DISC-'.str_pad($ud->id, 4, '0', STR_PAD_LEFT),
                'item_type' => 'discount',
                'type' => 'Voucher',
                'date' => $ud->expires_at?->format('Y-m-d') ?? 'No Expiry',
                'image' => '/gacha-assets/voucher.png',
                'topup_status' => null,
                'player_id' => null,
                'zone_id' => null,
                'server_id' => null,
            ]);

        // Redeemed (consumed) vouchers — audit history of past usage.
        $redeemedVouchers = UserDiscount::with(['discountType', 'order'])
            ->where('user_id', $user->id)
            ->where('is_used', true)
            ->orderByDesc('used_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (UserDiscount $ud) => [
                'name' => $ud->discountType->name,
                'code' => 'DISC-'.str_pad($ud->id, 4, '0', STR_PAD_LEFT),
                'item_type' => 'discount_redeemed',
                'type' => 'Voucher',
                'date' => $ud->used_at?->format('Y-m-d') ?? '—',
                'image' => '/gacha-assets/voucher.png',
                'topup_status' => null,
                'player_id' => null,
                'zone_id' => null,
                'server_id' => null,
                'redeemed_order' => $ud->order?->display_noinv,
            ]);

        $items = collect($productKeys)
            ->merge($topupItems)
            ->merge($vouchers)
            ->merge($redeemedVouchers)
            ->values();

        return view('pages.inventory', [
            'items' => $items,
        ]);
    }

    /**
     * Fake-bot delivery: any 'processing' topup whose fulfilled_at is older
     * than TopupCredential::SETTLEMENT_SECONDS gets flipped to 'sent'. Runs
     * lazily on each inventory page load so no queue or cron is required.
     */
    private function settleProcessingTopups(int $userId): void
    {
        $cutoff = now()->subSeconds(TopupCredential::SETTLEMENT_SECONDS);

        $settledIds = TopupCredential::where('topup_status', 'processing')
            ->where('fulfilled_at', '<=', $cutoff)
            ->whereIn(
                'order_detail_id',
                OrderDetail::whereIn(
                    'order_id',
                    Order::where('user_id', $userId)->where('status', 'paid')->pluck('id')
                )->pluck('id')
            )
            ->pluck('id');

        if ($settledIds->isNotEmpty()) {
            TopupCredential::whereIn('id', $settledIds)->update(['topup_status' => 'sent']);
        }
    }
}
