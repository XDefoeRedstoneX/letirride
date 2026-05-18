<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductKey;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->withCount('productKeys')
            ->orderByDesc('id')
            ->get();

        return view('admin.products', ['products' => $products]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'point_reward' => 'nullable|integer|min:0',
            'image' => 'nullable|string|max:255',
        ]);

        Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'description' => $request->description ?? '',
            'point_reward' => $request->point_reward ?? 0,
            'image' => $request->image,
            'is_active' => true,
        ]);

        return back()->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'point_reward' => 'nullable|integer|min:0',
            'image' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'description' => $request->description ?? '',
            'point_reward' => $request->point_reward ?? 0,
            'image' => $request->image,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);

        return back()->with('success', 'Product deactivated.');
    }

    /**
     * Add product keys via form.
     */
    public function addKeys(Request $request, Product $product)
    {
        $request->validate([
            'keys' => 'required|string',
        ]);

        $keys = array_filter(array_map('trim', explode("\n", $request->keys)));

        foreach ($keys as $keyCode) {
            ProductKey::create([
                'product_id' => $product->id,
                'key_code' => $keyCode,
                'status' => 'available',
            ]);
        }

        return back()->with('success', count($keys).' key(s) added.');
    }
}
