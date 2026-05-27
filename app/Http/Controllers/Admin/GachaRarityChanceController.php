<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GachaPool;
use App\Models\GachaRarityChance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GachaRarityChanceController extends Controller
{
    public const RARITY_ORDER = ['grand_prize', 'legendary', 'epic', 'rare', 'uncommon', 'common'];

    public function index(): View
    {
        $rarityRank = array_flip(self::RARITY_ORDER);
        $rows = GachaRarityChance::all()->keyBy('rarity');
        $prizeCounts = GachaPool::query()
            ->selectRaw('rarity_item, COUNT(*) as c')
            ->groupBy('rarity_item')
            ->pluck('c', 'rarity_item');

        $rarities = collect(self::RARITY_ORDER)->map(function (string $rarity) use ($rows, $prizeCounts) {
            $chance = (float) ($rows[$rarity]->base_chance ?? 0);
            $count = (int) ($prizeCounts[$rarity] ?? 0);

            return [
                'rarity' => $rarity,
                'base_chance' => $chance,
                'prize_count' => $count,
                'per_prize_chance' => $count > 0 ? round($chance / $count, 4) : 0.0,
            ];
        })->values();

        return view('admin.gacha-rarities', [
            'rarities' => $rarities,
            'total' => round($rarities->sum('base_chance'), 2),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'chances' => 'required|array',
            'chances.*' => 'required|numeric|min:0|max:100',
        ]);

        $chances = $validated['chances'];
        $allowed = array_flip(self::RARITY_ORDER);
        $chances = array_intersect_key($chances, $allowed);

        $total = round(array_sum($chances), 2);
        if (abs($total - 100.0) > 0.01) {
            return back()->withErrors([
                'chances' => 'Rarity chances must sum to exactly 100% (currently '.$total.'%).',
            ])->withInput();
        }

        DB::transaction(function () use ($chances) {
            foreach (self::RARITY_ORDER as $i => $rarity) {
                $value = (float) ($chances[$rarity] ?? 0);

                GachaRarityChance::updateOrCreate(
                    ['rarity' => $rarity],
                    ['base_chance' => $value, 'sort_order' => $i],
                );
            }
        });

        return back()->with('success', 'Rarity chances updated.');
    }
}
