<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function showStore()
    {
        $availableProductImages = collect(glob(public_path('products/*.svg')))
            ->map(fn (string $path) => basename($path))
            ->all();

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->get()
            ->map(function (Product $product) use ($availableProductImages) {
                $fileName = $product->image ?: 'soundcloud.svg';

                if (! in_array($fileName, $availableProductImages, true)) {
                    $fileName = 'soundcloud.svg';
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'category' => $product->category?->name ?? 'Other',
                    'image' => '/products/'.ltrim($fileName, '/'),
                ];
            })
            ->values();

        $favoriteIds = Auth::check()
            ? Favorite::where('user_id', Auth::id())
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->values()
            : collect();

        return view('pages.products', [
            'products' => $products,
            'favoriteIds' => $favoriteIds,
        ]);
    }
}
