<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use App\Models\ProductKey;
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
            ->map(fn (OrderDetail $detail) => [
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
            ]);

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

        $items = collect($productKeys)->merge($topupItems)->merge($vouchers)->values();

        return view('pages.inventory', [
            'items' => $items,
        ]);
    }
}
