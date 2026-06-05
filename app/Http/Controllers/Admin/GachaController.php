<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GachaIcon;
use App\Models\GachaPool;
use App\Models\GachaRarityChance;
use App\Support\GachaIconCatalog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class GachaController extends Controller
{
    private const RARITY_ORDER = ['grand_prize', 'legendary', 'epic', 'rare', 'uncommon', 'common'];

    public function index()
    {
        $rarityRank = array_flip(self::RARITY_ORDER);

        $allPools = GachaPool::with('discountType')->get();
        $sortedPools = $allPools->sortBy(fn (GachaPool $p) => $rarityRank[$p->rarity_item] ?? 99)->values();

        $rarityChances = GachaRarityChance::all()->keyBy('rarity');
        $prizeCounts = $sortedPools->groupBy('rarity_item')->map->count();

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

        $page = Paginator::resolveCurrentPage('page');
        $perPage = 20;
        $pools = new LengthAwarePaginator(
            $sortedPools->forPage($page, $perPage),
            $sortedPools->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        $icons = GachaIcon::orderBy('sort_order')->orderBy('label')->get();

        return view('admin.gacha', [
            'pools' => $pools,
            'rarityBreakdown' => $rarityBreakdown,
            'rarityTotal' => (float) $rarityChances->sum('base_chance'),
            'icons' => $icons,
            'iconsByCategory' => $icons->groupBy('category'),
            'iconCategories' => GachaIconCatalog::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePrize($request);

        GachaPool::create($data);

        return back()->with('success', 'Gacha prize added.');
    }

    public function update(Request $request, GachaPool $pool)
    {
        $data = $this->validatePrize($request);

        $pool->update($data);

        return back()->with('success', 'Gacha prize updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePrize(Request $request): array
    {
        $validated = $request->validate([
            'prize_name' => 'required|string|max:255',
            'discount_type_id' => 'nullable|exists:discount_types,id',
            'rarity_item' => 'required|in:common,uncommon,rare,epic,grand_prize,legendary',
            'icon_key' => 'nullable|exists:gacha_icons,key',
            'image_path' => 'nullable|string|max:1024',
        ]);

        // Normalize blanks/absent fields to nulls so the resolver falls through
        // cleanly (auto-pick by reward type / subcategory).
        $validated['icon_key'] = ($validated['icon_key'] ?? null) ?: null;
        $validated['image_path'] = ($validated['image_path'] ?? null) ?: null;

        return $validated;
    }

    public function destroy(GachaPool $pool)
    {
        $pool->delete();

        return back()->with('success', 'Gacha prize removed.');
    }
}
