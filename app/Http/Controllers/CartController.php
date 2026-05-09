<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
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
                    'image' => '/products/'.ltrim($item->product->image ?: 'soundcloud.svg', '/'),
                    'quantity' => $item->quantity,
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

        return view('pages.cart', [
            'cartItems' => $cartItems,
            'userDiscounts' => $userDiscounts,
            'midtransClientKey' => config('midtrans.client_key'),
        ]);
    }

    /**
     * Add a product to the cart (or increment quantity if it already exists).
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        if (! $product->is_active) {
            return response()->json(['message' => 'Product is not available.'], 404);
        }

        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => 1,
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
