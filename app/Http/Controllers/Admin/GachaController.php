<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GachaPool;
use Illuminate\Http\Request;

class GachaController extends Controller
{
    public function index()
    {
        $pools = GachaPool::with('discountType')
            ->orderByDesc('base_win_chance')
            ->get();

        return view('admin.gacha', ['pools' => $pools]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'prize_name' => 'required|string|max:255',
            'discount_type_id' => 'required|exists:discount_types,id',
            'rarity_item' => 'required|in:common,uncommon,rare,epic,grand_prize,legendary',
            'base_win_chance' => 'required|numeric|min:0|max:100',
        ]);

        GachaPool::create($request->only(['prize_name', 'discount_type_id', 'rarity_item', 'base_win_chance']));

        return back()->with('success', 'Gacha prize added.');
    }

    public function update(Request $request, GachaPool $pool)
    {
        $request->validate([
            'prize_name' => 'required|string|max:255',
            'discount_type_id' => 'required|exists:discount_types,id',
            'rarity_item' => 'required|in:common,uncommon,rare,epic,grand_prize,legendary',
            'base_win_chance' => 'required|numeric|min:0|max:100',
        ]);

        $pool->update($request->only(['prize_name', 'discount_type_id', 'rarity_item', 'base_win_chance']));

        return back()->with('success', 'Gacha prize updated.');
    }

    public function destroy(GachaPool $pool)
    {
        $pool->delete();

        return back()->with('success', 'Gacha prize removed.');
    }
}
