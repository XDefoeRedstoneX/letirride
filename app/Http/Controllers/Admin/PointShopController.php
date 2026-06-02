<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountType;
use App\Models\PointShopItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PointShopController extends Controller
{
    public function index()
    {
        $items = PointShopItem::with('discountType')
            ->withCount('purchases')
            ->orderBy('point_cost')
            ->get();

        return view('admin.point-shop', [
            'items' => $items,
            'discounts' => DiscountType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateItem($request);

        PointShopItem::create($data);

        return back()->with('success', 'Point-shop item created.');
    }

    public function update(Request $request, PointShopItem $pointShopItem): RedirectResponse
    {
        $data = $this->validateItem($request, $pointShopItem);

        $pointShopItem->update($data);

        return back()->with('success', 'Point-shop item updated.');
    }

    public function destroy(PointShopItem $pointShopItem): RedirectResponse
    {
        $pointShopItem->delete();

        return back()->with('success', 'Point-shop item removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateItem(Request $request, ?PointShopItem $existing = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'point_cost' => 'required|integer|min:1',
            'reward_type' => ['required', Rule::in(PointShopItem::REWARD_TYPES)],
            'discount_type_id' => 'nullable|required_if:reward_type,discount|exists:discount_types,id',
            'points_amount' => 'nullable|required_if:reward_type,cashback|integer|min:1',
            'image_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
            'is_active' => 'nullable|boolean',
        ], [
            'discount_type_id.required_if' => 'Choose which discount this voucher grants.',
            'points_amount.required_if' => 'Set how many points the cashback grants.',
        ]);

        // Store a bare filename under public/point-shop-assets/ to match the
        // path the customer page already builds ('/point-shop-assets/'.$img).
        $img = $existing?->img;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $name = Str::slug(Str::limit($validated['name'], 30, '')).'-'.Str::random(6).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('point-shop-assets'), $name);
            $img = $name;
        }

        $isDiscount = $validated['reward_type'] === 'discount';

        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'point_cost' => $validated['point_cost'],
            'reward_type' => $validated['reward_type'],
            'discount_type_id' => $isDiscount ? $validated['discount_type_id'] : null,
            'points_amount' => $isDiscount ? null : $validated['points_amount'],
            'img' => $img,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
