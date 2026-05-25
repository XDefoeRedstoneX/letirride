<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'subcategory'])
            ->withCount('productKeys')
            ->orderByDesc('id')
            ->get();

        return view('admin.products', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'subcategories' => Subcategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        Product::create($data + ['is_active' => true]);

        return back()->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, updating: true);
        $data['is_active'] = $request->boolean('is_active', true);

        $product->update($data);

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);

        return back()->with('success', 'Product deactivated.');
    }

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

    private function validateProduct(Request $request, bool $updating = false): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'type' => 'nullable|in:voucher,direct_topup',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'point_multiplier' => 'nullable|numeric|min:0',
            'image' => 'nullable|string|max:255',
        ]);

        // Cross-check: chosen subcategory must belong to chosen category.
        if (! empty($validated['subcategory_id'])) {
            $belongs = Subcategory::where('id', $validated['subcategory_id'])
                ->where('category_id', $validated['category_id'])
                ->exists();
            if (! $belongs) {
                abort(422, 'Subcategory does not belong to the selected category.');
            }
        }

        return [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'] ?? null,
            'type' => $validated['type'] ?? 'voucher',
            'price' => $validated['price'],
            'description' => $validated['description'] ?? '',
            'point_multiplier' => $validated['point_multiplier'] ?? 1.0,
            'image' => $validated['image'] ?? null,
        ];
    }
}
