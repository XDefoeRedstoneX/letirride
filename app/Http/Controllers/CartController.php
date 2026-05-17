<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display the user's cart page.
     */
    public function index()
    {
        $cartItems = CartItem::with(['product.category'])
            ->where('user_id', Auth::id())
            ->get()
            ->map(function (CartItem $item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => (float) $item->product->price,
                    'category' => $item->product->category?->name ?? 'Other',
                    'category_id' => $item->product->category_id,
                    'product_type' => $item->product->type ?? 'voucher',
                    'image' => '/products/'.ltrim($item->product->image ?: 'soundcloud.png', '/'),
                    'quantity' => $item->quantity,
                    'topup_meta' => $item->topup_meta,
                ];
            });

        $userDiscounts = Auth::user()
            ->userDiscounts()
            ->with('discountType')
            ->where('is_used', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get()
            ->map(fn ($ud) => [
                'id' => $ud->id,
                'name' => $ud->discountType->name,
                'type' => $ud->discountType->type,
                'value' => (float) $ud->discountType->value,
                'target_category_id' => $ud->discountType->target_category_id,
                'target_category_name' => $ud->discountType->targetCategory?->name,
            ]);

        $pendingOrder = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->latest()
            ->first(['id', 'noinv']);

        return view('pages.cart', [
            'cartItems' => $cartItems,
            'userDiscounts' => $userDiscounts,
            'midtransClientKey' => config('midtrans.client_key'),
            'pendingOrder' => $pendingOrder,
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        if (! $product->is_active) {
            return response()->json(['message' => 'Product is not available.'], 404);
        }

        // Bug 5: Validate topup_meta for direct_topup products
        if (($product->type ?? 'voucher') === 'direct_topup') {
            $request->validate([
                'player_id' => 'required|string|max:100',
                'zone_id' => 'nullable|string|max:50',
                'server_id' => 'nullable|string|max:50',
            ]);
        }

        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        $topupMeta = null;
        if (($product->type ?? 'voucher') === 'direct_topup') {
            $topupMeta = [
                'player_id' => $request->input('player_id'),
                'zone_id' => $request->input('zone_id'),
                'server_id' => $request->input('server_id'),
            ];
        }

        if ($cartItem) {
            $cartItem->increment('quantity');
            // Update topup_meta if provided (user may change UID)
            if ($topupMeta) {
                $cartItem->update(['topup_meta' => $topupMeta]);
            }
        } else {
            CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => 1,
                'topup_meta' => $topupMeta,
            ]);
        }

        $count = CartItem::where('user_id', Auth::id())->sum('quantity');

        return response()->json([
            'message' => $product->name.' added to cart.',
            'cart_count' => (int) $count,
        ]);
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $cartItem->update(['quantity' => $request->quantity]);

        $count = CartItem::where('user_id', Auth::id())->sum('quantity');

        return response()->json([
            'message' => 'Cart updated.',
            'cart_count' => (int) $count,
            'item_total' => (float) $cartItem->product->price * $cartItem->quantity,
        ]);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $cartItem->delete();

        $count = CartItem::where('user_id', Auth::id())->sum('quantity');

        return response()->json([
            'message' => 'Item removed from cart.',
            'cart_count' => (int) $count,
        ]);
    }

    /**
     * Return the current cart item count (for navbar badge via AJAX).
     */
    public function count(): JsonResponse
    {
        $count = CartItem::where('user_id', Auth::id())->sum('quantity');

        return response()->json(['count' => (int) $count]);
    }
}
