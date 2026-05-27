<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GachaBooster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GachaBoosterController extends Controller
{
    public function index(): View
    {
        $boosters = GachaBooster::orderBy('point_cost')->get();

        return view('admin.gacha-boosters', ['boosters' => $boosters]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['is_active'] = $request->boolean('is_active', true);

        GachaBooster::create($data);

        return back()->with('success', 'Booster created.');
    }

    public function update(Request $request, GachaBooster $booster): RedirectResponse
    {
        $data = $this->validatedData($request, $booster->id);
        $data['is_active'] = $request->boolean('is_active', true);

        $booster->update($data);

        return back()->with('success', 'Booster updated.');
    }

    public function destroy(GachaBooster $booster): RedirectResponse
    {
        $booster->delete();

        return back()->with('success', 'Booster deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = $ignoreId !== null
            ? 'unique:gacha_boosters,key,'.$ignoreId
            : 'unique:gacha_boosters,key';

        return $request->validate([
            'key' => 'required|string|max:64|'.$uniqueRule,
            'name' => 'required|string|max:128',
            'description' => 'nullable|string|max:255',
            'point_cost' => 'required|integer|min:0',
            'rarity_floor' => 'required|in:uncommon,rare,epic,legendary,grand_prize',
            'bonus_percent' => 'required|numeric|min:0|max:100',
            'rolls_granted' => 'required|integer|min:1|max:200',
        ]);
    }
}
