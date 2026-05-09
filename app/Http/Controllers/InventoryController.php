<?php

namespace App\Http\Controllers;

use App\Models\ProductKey;
use App\Models\UserDiscount;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Show the user's purchased product keys and active discount vouchers.
     */
    public function index()
    {
        $user = Auth::user();

        // Product keys from paid orders
        $productKeys = ProductKey::with(['product', 'order'])
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', 'paid'))
            ->orderByDesc('order_id')
            ->get()
            ->map(fn (ProductKey $key) => [
                'name' => $key->product->name,
                'code' => $key->key_code,
                'type' => 'Product',
                'date' => $key->order?->created_at?->format('Y-m-d') ?? '-',
                'image' => '/products/'.ltrim($key->product->image ?: 'soundcloud.svg', '/'),
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
                'type' => 'Voucher',
                'date' => $ud->expires_at?->format('Y-m-d') ?? 'No Expiry',
                'image' => '/gacha/voucher.svg',
            ]);

        $items = $productKeys->merge($vouchers)->values();

        return view('pages.inventory', [
            'items' => $items,
        ]);
    }
}
