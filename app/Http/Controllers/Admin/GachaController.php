<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GachaPool;
use App\Models\GachaRarityChance;
use Illuminate\Http\Request;

class GachaController extends Controller
{
    private const RARITY_ORDER = ['grand_prize', 'legendary', 'epic', 'rare', 'uncommon', 'common'];

    public function index()
    {
        $rarityRank = array_flip(self::RARITY_ORDER);

        $pools = GachaPool::with('discountType')
            ->get()
            ->sortBy(fn (GachaPool $p) => $rarityRank[$p->rarity_item] ?? 99)
            ->values();

        $rarityChances = GachaRarityChance::all()->keyBy('rarity');
        $prizeCounts = $pools->groupBy('rarity_item')->map->count();

        $rarityBreakdown = collect(self::RARITY_ORDER)->map(function (string $rarity) use ($rarityChances, $prizeCounts) {
            $count = (int) ($prizeCounts[$rarity] ?? 0);
            $chance = (float) ($rarityChances[$rarity]->base_chance ?? 0);

            return [
                'rarity' => $rarity,
                'base_chance' => $chance,
                'prize_count' => $count,
                'per_prize_chance' => $count > 0 ? round($chance / $count, 4) : 0.0,
            ];
        })->values();

        return view('admin.gacha', [
            'pools' => $pools,
            'rarityBreakdown' => $rarityBreakdown,
            'rarityTotal' => (float) $rarityChances->sum('base_chance'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'prize_name' => 'required|string|max:255',
            'discount_type_id' => 'nullable|exists:discount_types,id',
            'rarity_item' => 'required|in:common,uncommon,rare,epic,grand_prize,legendary',
        ]);

        GachaPool::create($request->only(['prize_name', 'discount_type_id', 'rarity_item']));

        return back()->with('success', 'Gacha prize added.');
    }

    public function update(Request $request, GachaPool $pool)
    {
        $request->validate([
            'prize_name' => 'required|string|max:255',
            'discount_type_id' => 'nullable|exists:discount_types,id',
            'rarity_item' => 'required|in:common,uncommon,rare,epic,grand_prize,legendary',
        ]);

        $pool->update($request->only(['prize_name', 'discount_type_id', 'rarity_item']));

        return back()->with('success', 'Gacha prize updated.');
    }

    public function destroy(GachaPool $pool)
    {
        $pool->delete();

        return back()->with('success', 'Gacha prize removed.');
    }
}
