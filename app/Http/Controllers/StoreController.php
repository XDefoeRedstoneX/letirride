<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function showStore()
    {
        $availableProductImages = collect(glob(public_path('products/*.png')))
            ->map(fn (string $path) => basename($path))
            ->all();

        $products = Product::query()
            ->with(['category', 'subcategory'])
            ->withCount(['productKeys as available_keys_count' => function ($query) {
                $query->where('status', 'available');
            }])
            ->where('is_active', true)
            ->get()
            ->map(function (Product $product) use ($availableProductImages) {
                $fileName = $product->image ?: 'steam-wallet.png';

                if (! in_array($fileName, $availableProductImages, true)) {
                    $fileName = 'steam-wallet.png';
                }

                $type = $product->type ?? 'voucher';
                $stock = (int) ($product->available_keys_count ?? 0);
                // direct_topup products don't consume keys, so they're always in stock
                $inStock = $type === 'direct_topup' ? true : $stock > 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'category' => $product->category?->name ?? 'Other',
                    'subcategory' => $product->subcategory?->name ?? 'Other',
                    'product_type' => $type,
                    'stock' => $stock,
                    'in_stock' => $inStock,
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
