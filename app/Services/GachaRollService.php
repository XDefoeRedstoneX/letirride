<?php

namespace App\Services;

use App\Models\GachaHistory;
use App\Models\GachaPool;
use App\Models\GachaRarityChance;
use App\Models\User;
use App\Models\UserActiveBooster;
use App\Models\UserDiscount;
use App\Models\UserGachaState;
use Illuminate\Support\Collection;
use RuntimeException;

class GachaRollService
{
    public const HARD_PITY_THRESHOLD = 50;

    public const MINI_PITY_THRESHOLD = 10;

    /**
     * @var array<string, int>
     */
    private const RARITY_RANK = [
        'common' => 1,
        'uncommon' => 2,
        'rare' => 3,
        'epic' => 4,
        'legendary' => 5,
        'grand_prize' => 6,
    ];

    private const UNCOMMON_PLUS = ['uncommon', 'rare', 'epic', 'legendary', 'grand_prize'];

    private const EPIC_PLUS = ['epic', 'legendary', 'grand_prize'];

    /**
     * Resolve a single roll outcome for the given user: applies pity, applies any
     * active luck boosters, picks a rarity by weighted RNG, then picks a prize
     * uniformly from that rarity's eligible prizes. Consumes one charge from each
     * active booster (deleting rows that hit zero). Does NOT deduct the spin cost
     * or grant the reward — callers handle those.
     *
     * @return array{prize: GachaPool, pity_triggered: ?string, boosters_applied: array<int, int>}
     */
    public function roll(User $user): array
    {
        $state = $this->ensureState($user);

        $pityTriggered = $this->resolvePityTrigger($state);
        $pool = $this->eligiblePool($pityTriggered);
        $pool = $this->removeCappedPrizes($pool, $user);

        if ($pool->isEmpty()) {
            throw new RuntimeException('Gacha pool is empty for the resolved roll context.');
        }

        $boosters = $this->activeBoostersFor($user);
        $rarityWeights = $this->effectiveRarityWeights($pool, $boosters);
        $rarity = $this->pickRarity($rarityWeights);
        $prize = $this->pickPrizeInRarity($pool, $rarity);

        $this->updateStateAfterRoll($state, $prize->rarity_item);
        $this->consumeBoosterCharges($boosters);

        return [
            'prize' => $prize,
            'pity_triggered' => $pityTriggered,
            'boosters_applied' => $boosters->pluck('gacha_booster_id')->all(),
        ];
    }

    /**
     * Apply the prize's reward to the user. Idempotency is the caller's job.
     */
    public function dispatchReward(User $user, GachaPool $prize): void
    {
        match ($prize->reward_type) {
            'discount' => $this->grantDiscount($user, $prize),
            'points' => $user->increment('points_balance', (int) ($prize->points_amount ?? 0)),
            'free_spin' => UserGachaState::where('user_id', $user->id)->increment('free_spins'),
            'nothing' => null,
            default => null,
        };
    }

    /**
     * Pity counters BEFORE the next spin. Returns the current effective state for UI.
     *
     * @return array{pity_counter: int, mini_pity_counter: int, free_spins: int, total_spins: int, active_boosters: array<int, array<string, mixed>>, effective_rarity_chances: array<string, float>, base_rarity_chances: array<string, float>}
     */
    public function snapshotFor(User $user): array
    {
        $state = $this->ensureState($user);
        $boosters = $this->activeBoostersFor($user);
        $pool = GachaPool::all();

        return [
            'pity_counter' => $state->pity_counter,
            'mini_pity_counter' => $state->mini_pity_counter,
            'free_spins' => $state->free_spins,
            'total_spins' => $state->total_spins,
            'active_boosters' => $boosters
                ->map(fn (UserActiveBooster $ab) => [
                    'id' => $ab->id,
                    'booster_id' => $ab->gacha_booster_id,
                    'key' => $ab->booster->key,
                    'name' => $ab->booster->name,
                    'rarity_floor' => $ab->booster->rarity_floor,
                    'bonus_percent' => (float) $ab->booster->bonus_percent,
                    'rolls_remaining' => (int) $ab->rolls_remaining,
                ])->values()->all(),
            'base_rarity_chances' => $this->baseRarityChances($pool),
            'effective_rarity_chances' => $this->effectiveRarityWeights($pool, $boosters),
        ];
    }

    /**
     * Public helper for the UI: effective rarity weights for the user's
     * current active-booster set against the live pool.
     *
     * @return array<string, float>
     */
    public function effectiveRarityWeightsFor(User $user): array
    {
        return $this->effectiveRarityWeights(GachaPool::all(), $this->activeBoostersFor($user));
    }

    /**
     * @return Collection<int, UserActiveBooster>
     */
    private function activeBoostersFor(User $user): Collection
    {
        return UserActiveBooster::with('booster')
            ->where('user_id', $user->id)
            ->where('rolls_remaining', '>', 0)
            ->get()
            ->filter(fn (UserActiveBooster $b) => $b->booster && $b->booster->is_active)
            ->values();
    }

    /**
     * Decrement one charge from every active booster; delete rows that hit zero.
     *
     * @param  Collection<int, UserActiveBooster>  $boosters
     */
    private function consumeBoosterCharges(Collection $boosters): void
    {
        foreach ($boosters as $ab) {
            $remaining = max(0, ((int) $ab->rolls_remaining) - 1);
            if ($remaining === 0) {
                $ab->delete();
            } else {
                $ab->rolls_remaining = $remaining;
                $ab->save();
            }
        }
    }

    /**
     * Rarity chances after each active booster shifts weight into its floor-and-above
     * rarities. Operates on rarities present in the supplied pool. Output sums to ~100.
     *
     * @param  Collection<int, GachaPool>  $pool
     * @param  Collection<int, UserActiveBooster>  $boosters
     * @return array<string, float>
     */
    private function effectiveRarityWeights(Collection $pool, Collection $boosters): array
    {
        $weights = $this->baseRarityChances($pool);

        if (empty($weights)) {
            return [];
        }

        foreach ($boosters as $active) {
            $booster = $active->booster;
            $floorRank = self::RARITY_RANK[$booster->rarity_floor] ?? 0;
            $bonus = (float) $booster->bonus_percent;

            $bonusKeys = array_keys(array_filter(
                $weights,
                fn ($_, $rarity) => (self::RARITY_RANK[$rarity] ?? 0) >= $floorRank,
                ARRAY_FILTER_USE_BOTH
            ));
            $nonBonusKeys = array_diff(array_keys($weights), $bonusKeys);

            $bonusSum = array_sum(array_intersect_key($weights, array_flip($bonusKeys)));
            $nonBonusSum = array_sum(array_intersect_key($weights, array_flip($nonBonusKeys)));

            if ($bonusSum <= 0 || $nonBonusSum <= 0) {
                continue;
            }

            $shift = min($bonus, $nonBonusSum);
            $bonusScale = ($bonusSum + $shift) / $bonusSum;
            $nonBonusScale = ($nonBonusSum - $shift) / $nonBonusSum;

            foreach ($bonusKeys as $rarity) {
                $weights[$rarity] *= $bonusScale;
            }
            foreach ($nonBonusKeys as $rarity) {
                $weights[$rarity] *= $nonBonusScale;
            }
        }

        return array_map(fn ($w) => round($w, 4), $weights);
    }

    /**
     * Read the rarity chance table, restrict to rarities present in the pool,
     * and normalize to 100 across the survivors so pity-restricted pools still
     * give a valid distribution.
     *
     * @param  Collection<int, GachaPool>  $pool
     * @return array<string, float>
     */
    private function baseRarityChances(Collection $pool): array
    {
        $presentRarities = $pool->pluck('rarity_item')->unique()->values()->all();

        $rows = GachaRarityChance::whereIn('rarity', $presentRarities)->get();

        $weights = [];
        foreach ($rows as $row) {
            $chance = (float) $row->base_chance;
            if ($chance > 0) {
                $weights[$row->rarity] = $chance;
            }
        }

        $sum = array_sum($weights);
        if ($sum <= 0) {
            // Fallback: uniform across present rarities so a roll can always succeed.
            $uniform = count($presentRarities) > 0 ? 100 / count($presentRarities) : 0;

            return array_fill_keys($presentRarities, $uniform);
        }

        $scale = 100 / $sum;

        return array_map(fn ($w) => $w * $scale, $weights);
    }

    private function ensureState(User $user): UserGachaState
    {
        return UserGachaState::firstOrCreate(['user_id' => $user->id]);
    }

    private function resolvePityTrigger(UserGachaState $state): ?string
    {
        if ($state->pity_counter >= self::HARD_PITY_THRESHOLD) {
            return 'hard';
        }

        if ($state->mini_pity_counter >= self::MINI_PITY_THRESHOLD) {
            return 'mini';
        }

        return null;
    }

    /**
     * @return Collection<int, GachaPool>
     */
    private function eligiblePool(?string $pityTriggered): Collection
    {
        $query = GachaPool::query();

        if ($pityTriggered === 'hard') {
            $query->whereIn('rarity_item', self::EPIC_PLUS)
                ->where('reward_type', '!=', 'nothing');
        } elseif ($pityTriggered === 'mini') {
            $query->whereIn('rarity_item', self::UNCOMMON_PLUS)
                ->where('reward_type', '!=', 'nothing');
        }

        $pool = $query->get();

        // Defensive: if pity-restricted query returned nothing (admin misconfiguration),
        // fall back to the full pool so the user still gets a roll.
        if ($pool->isEmpty() && $pityTriggered !== null) {
            return GachaPool::all();
        }

        return $pool;
    }

    /**
     * Drop prizes the user has already won up to their per-prize `max_per_user`
     * cap (e.g. the one-time Welcome Bonus). Counts prior wins from history.
     * Falls back to the unfiltered pool if every prize ends up capped, so a roll
     * can always resolve.
     *
     * @param  Collection<int, GachaPool>  $pool
     * @return Collection<int, GachaPool>
     */
    private function removeCappedPrizes(Collection $pool, User $user): Collection
    {
        $capped = $pool->filter(fn (GachaPool $p) => $p->max_per_user !== null);

        if ($capped->isEmpty()) {
            return $pool;
        }

        $wins = GachaHistory::where('user_id', $user->id)
            ->whereIn('gacha_pool_id', $capped->pluck('id'))
            ->selectRaw('gacha_pool_id, COUNT(*) as c')
            ->groupBy('gacha_pool_id')
            ->pluck('c', 'gacha_pool_id');

        $filtered = $pool->reject(function (GachaPool $p) use ($wins) {
            return $p->max_per_user !== null
                && (int) ($wins[$p->id] ?? 0) >= $p->max_per_user;
        })->values();

        return $filtered->isEmpty() ? $pool : $filtered;
    }

    /**
     * @param  array<string, float>  $weights
     */
    private function pickRarity(array $weights): string
    {
        $total = array_sum($weights);
        if ($total <= 0) {
            return (string) array_key_first($weights);
        }

        $random = mt_rand(1, (int) round($total * 100)) / 100;
        $cumulative = 0.0;

        foreach ($weights as $rarity => $w) {
            $cumulative += (float) $w;
            if ($random <= $cumulative) {
                return $rarity;
            }
        }

        return (string) array_key_last($weights);
    }

    /**
     * Uniform pick among prizes of the chosen rarity. Falls back to any prize if
     * the rarity bucket is unexpectedly empty (race against an admin edit).
     *
     * @param  Collection<int, GachaPool>  $pool
     */
    private function pickPrizeInRarity(Collection $pool, string $rarity): GachaPool
    {
        $bucket = $pool->where('rarity_item', $rarity)->values();
        if ($bucket->isEmpty()) {
            return $pool->first();
        }

        return $bucket->get(random_int(0, $bucket->count() - 1));
    }

    private function updateStateAfterRoll(UserGachaState $state, string $wonRarity): void
    {
        $wonRank = self::RARITY_RANK[$wonRarity] ?? 0;

        $state->total_spins++;

        if ($wonRank >= self::RARITY_RANK['epic']) {
            $state->pity_counter = 0;
            $state->mini_pity_counter = 0;

            if ($wonRank >= self::RARITY_RANK['legendary']) {
                $state->last_legendary_at = now();
            }
        } elseif ($wonRank >= self::RARITY_RANK['uncommon']) {
            $state->mini_pity_counter = 0;
            $state->pity_counter++;
        } else {
            $state->mini_pity_counter++;
            $state->pity_counter++;
        }

        $state->save();
    }

    private function grantDiscount(User $user, GachaPool $prize): void
    {
        if (! $prize->discount_type_id) {
            return;
        }

        UserDiscount::create([
            'user_id' => $user->id,
            'discount_type_id' => $prize->discount_type_id,
            'is_used' => false,
            'obtained_from' => 'gacha',
            'expires_at' => now()->addDays(14),
        ]);
    }
}
