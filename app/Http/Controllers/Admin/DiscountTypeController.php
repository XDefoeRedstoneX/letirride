<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiscountType;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscountTypeController extends Controller
{
    public function index()
    {
        $discounts = DiscountType::with(['targetCategory', 'targetSubcategory'])
            ->withCount(['gachaPools', 'pointShopItems'])
            ->orderBy('id')
            ->get();

        return view('admin.discount-types', [
            'discounts' => $discounts,
            'categories' => Category::orderBy('name')->get(),
            'subcategories' => Subcategory::with('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateDiscount($request);

        DiscountType::create($data);

        return back()->with('success', 'Discount type created.');
    }

    public function update(Request $request, DiscountType $discountType): RedirectResponse
    {
        $data = $this->validateDiscount($request);

        $discountType->update($data);

        return back()->with('success', 'Discount type updated.');
    }

    public function destroy(DiscountType $discountType): RedirectResponse
    {
        $usage = $discountType->usageCount();
        if ($usage > 0) {
            return back()->with('error', "Can't delete \"{$discountType->name}\" — it's used by {$usage} gacha prize/point-shop item(s). Reassign or remove those first.");
        }

        $discountType->delete();

        return back()->with('success', 'Discount type deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDiscount(Request $request): array
    {
        // Percent discounts are capped at 100; fixed (Rp) discounts are not.
        $valueRules = ['required', 'numeric', 'min:0'];
        if ($request->input('type') === 'percent') {
            $valueRules[] = 'max:100';
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(DiscountType::TYPES)],
            'value' => $valueRules,
            'target_scope' => 'required|in:storewide,category,subcategory',
            'target_category_id' => 'nullable|required_if:target_scope,category|exists:categories,id',
            'target_subcategory_id' => 'nullable|required_if:target_scope,subcategory|exists:subcategories,id',
        ], [
            'value.max' => 'Percentage discounts cannot exceed 100%.',
            'target_category_id.required_if' => 'Pick a category for a category-scoped discount.',
            'target_subcategory_id.required_if' => 'Pick a subcategory for a brand-scoped discount.',
        ]);

        // Normalise targets so only one scope is ever persisted.
        return [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'target_category_id' => $validated['target_scope'] === 'category' ? $validated['target_category_id'] : null,
            'target_subcategory_id' => $validated['target_scope'] === 'subcategory' ? $validated['target_subcategory_id'] : null,
        ];
    }
}
