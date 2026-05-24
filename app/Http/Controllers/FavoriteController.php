<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function store(Request $request, int $productId)
    {
        $productExists = Product::where('id', $productId)
            ->where('is_active', true)
            ->exists();

        if (! $productExists) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        Favorite::firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $productId,
        ]);

        return response()->json([
            'message' => 'Added to favorites.',
            'favorited' => true,
            'product_id' => $productId,
        ]);
    }

    public function destroy(Request $request, int $productId)
    {
        Favorite::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();

        return response()->json([
            'message' => 'Removed from favorites.',
            'favorited' => false,
            'product_id' => $productId,
        ]);
    }

    public function showFavorites()
    {
        if (! Auth::check()) {
            return view('pages.favorites', ['favorites' => collect()]);
        }

        $favorites = Favorite::with(['product.category'])
            ->where('user_id', Auth::id())
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Favorite $favorite) {
                return [
                    'id' => $favorite->product->id,
                    'name' => $favorite->product->name,
                    'price' => (float) $favorite->product->price,
                    'category' => $favorite->product->category?->name ?: 'Other',
                    'image' => '/products/'.ltrim($favorite->product->image ?: 'steam-wallet.png', '/'),
                ];
            })
            ->values();

        return view('pages.favorites', ['favorites' => $favorites]);
    }
}
