<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GachaIcon;
use App\Support\GachaIconCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GachaIconController extends Controller
{
    public function index()
    {
        $icons = GachaIcon::orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        return view('admin.gacha-icons', [
            'icons' => $icons,
            'iconsByCategory' => $icons->groupBy('category'),
            'categories' => GachaIconCatalog::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateIcon($request, null);

        GachaIcon::create($data);
        GachaIcon::flushCache();

        return back()->with('success', 'Coin icon added.');
    }

    public function update(Request $request, GachaIcon $gachaIcon)
    {
        $data = $this->validateIcon($request, $gachaIcon);

        $gachaIcon->update($data);
        GachaIcon::flushCache();

        return back()->with('success', 'Coin icon updated.');
    }

    public function destroy(GachaIcon $gachaIcon)
    {
        // Prizes referencing this key fall back to their reward-type auto-pick.
        $gachaIcon->delete();
        GachaIcon::flushCache();

        return back()->with('success', 'Coin icon removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateIcon(Request $request, ?GachaIcon $existing): array
    {
        $validated = $request->validate([
            'key' => [
                'required', 'string', 'max:64', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('gacha_icons', 'key')->ignore($existing?->id),
            ],
            'label' => 'required|string|max:255',
            'category' => ['required', Rule::in(array_keys(GachaIconCatalog::CATEGORIES))],
            'image_url' => 'nullable|string|max:1024',
            'image_file' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:512',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = $existing?->image_path;

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $name = Str::slug($validated['key']).'-'.Str::random(6).'.'.$file->getClientOriginalExtension();
            $file->storeAs('gacha-icons/uploads', $name, 'public');
            $imagePath = '/storage/gacha-icons/uploads/'.$name;
        } elseif (! empty($validated['image_url'])) {
            $imagePath = $validated['image_url'];
        }

        // New icons must end up with a path; fall back to the conventional
        // catalog path derived from the key if nothing was provided.
        if (empty($imagePath)) {
            $imagePath = GachaIconCatalog::pathFor($validated['key']);
        }

        return [
            'key' => $validated['key'],
            'label' => $validated['label'],
            'category' => $validated['category'],
            'image_path' => $imagePath,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
