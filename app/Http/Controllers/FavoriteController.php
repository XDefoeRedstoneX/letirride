<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function store(Request $request, int $productId)
    {
        $productExists = DB::table('products')
            ->where('id', $productId)
            ->where('is_active', true)
            ->exists();

        if (! $productExists) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        DB::table('favorites')->insertOrIgnore([
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Added to favorites.',
            'favorited' => true,
            'product_id' => $productId,
        ]);
    }

    public function destroy(Request $request, int $productId)
    {
        DB::table('favorites')
            ->where('user_id', Auth::id())
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
        $favorites = DB::table('favorites')
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('favorites.user_id', Auth::id())
            ->where('products.is_active', true)
            ->orderByDesc('favorites.created_at')
            ->get([
                'products.id',
                'products.name',
                'products.price',
                'products.image',
                'categories.name as category',
            ])
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'category' => $product->category ?: 'Other',
                    'image' => '/products/' . ltrim($product->image ?: 'soundcloud.svg', '/'),
                ];
            })
            ->values();

        return view('pages.favorites', ['favorites' => $favorites]);
    }
}
