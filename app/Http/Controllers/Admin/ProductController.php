<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductKey;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'subcategory', 'discount'])
            ->withCount('productKeys')
            ->orderByDesc('id')
            ->paginate(20);

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

        // Handle discount upsert / deactivation
        $this->syncDiscount($request, $product);

        return back()->with('success', 'Product updated successfully.');
    }

    private function syncDiscount(Request $request, Product $product): void
    {
        $enabled = $request->boolean('discount_enabled', false);

        if (! $enabled) {
            // Deactivate any existing discount — no hard delete
            $product->discount()?->update(['is_active' => false]);
            return;
        }

        $request->validate([
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01|max:9999999',
            'discount_label' => 'nullable|string|max:40',
            'discount_ends'  => 'nullable|date|after:now',
        ]);

        if ($request->discount_type === 'percentage' && $request->discount_value > 95) {
            abort(422, 'Percentage discount cannot exceed 95%.');
        }

        ProductDiscount::updateOrCreate(
            ['product_id' => $product->id],
            [
                'type'      => $request->discount_type,
                'value'     => $request->discount_value,
                'label'     => $request->discount_label ?: 'SALE',
                'ends_at'   => $request->discount_ends ?: null,
                'is_active' => true,
            ]
        );
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
