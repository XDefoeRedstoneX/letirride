<?php

namespace App\Services;

use App\Models\GachaPool;
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
     * active luck boosters, runs weighted RNG against the eligible pool, and
     * mutates the user's gacha state. Does NOT deduct the spin cost or grant the
     * reward — callers handle those.
     *
     * @return array{prize: GachaPool, pity_triggered: ?string, boosters_applied: array<int, int>}
     */
    public function roll(User $user): array
    {
        $state = $this->ensureState($user);

        $pityTriggered = $this->resolvePityTrigger($state);
        $pool = $this->eligiblePool($pityTriggered);

        if ($pool->isEmpty()) {
            throw new RuntimeException('Gacha pool is empty for the resolved roll context.');
        }

        $boosters = $this->activeBoostersFor($user);
        $weighted = $this->applyBoosters($pool, $boosters);

        $prize = $this->pickWeighted($weighted);

        $this->updateStateAfterRoll($state, $prize->rarity_item);

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
     * @return array{pity_counter: int, mini_pity_counter: int, free_spins: int, total_spins: int}
     */
    public function snapshotFor(User $user): array
    {
        $state = $this->ensureState($user);

        return [
            'pity_counter' => $state->pity_counter,
            'mini_pity_counter' => $state->mini_pity_counter,
            'free_spins' => $state->free_spins,
            'total_spins' => $state->total_spins,
            'active_boosters' => $this->activeBoostersFor($user)
                ->map(fn (UserActiveBooster $ab) => [
                    'id' => $ab->id,
                    'booster_id' => $ab->gacha_booster_id,
                    'key' => $ab->booster->key,
                    'name' => $ab->booster->name,
                    'rarity_floor' => $ab->booster->rarity_floor,
                    'bonus_percent' => (float) $ab->booster->bonus_percent,
                    'expires_at' => $ab->expires_at->toIso8601String(),
                ])->values()->all(),
        ];
    }

    /**
     * @return Collection<int, UserActiveBooster>
     */
    private function activeBoostersFor(User $user): Collection
    {
        return UserActiveBooster::with('booster')
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->get()
            ->filter(fn (UserActiveBooster $b) => $b->booster && $b->booster->is_active)
            ->values();
    }

    /**
     * Shift weight into bonus-tier prizes per each active booster. Total weight is
     * preserved. Effect is clamped if non-bonus weight is insufficient.
     *
     * @param  Collection<int, GachaPool>  $pool
     * @param  Collection<int, UserActiveBooster>  $boosters
     * @return Collection<int, GachaPool>
     */
    private function applyBoosters(Collection $pool, Collection $boosters): Collection
    {
        if ($boosters->isEmpty()) {
            return $pool;
        }

        foreach ($boosters as $active) {
            $booster = $active->booster;
            $floorRank = self::RARITY_RANK[$booster->rarity_floor] ?? 0;
            $bonus = (float) $booster->bonus_percent;

            [$bonusTier, $nonBonus] = $pool->partition(function (GachaPool $prize) use ($floorRank) {
                return ($prize->reward_type !== 'nothing')
                    && ((self::RARITY_RANK[$prize->rarity_item] ?? 0) >= $floorRank);
            });

            $bonusSum = (float) $bonusTier->sum('base_win_chance');
            $nonBonusSum = (float) $nonBonus->sum('base_win_chance');

            if ($bonusSum <= 0 || $nonBonusSum <= 0) {
                continue;
            }

            $shift = min($bonus, $nonBonusSum);

            $bonusScale = ($bonusSum + $shift) / $bonusSum;
            $nonBonusScale = ($nonBonusSum - $shift) / $nonBonusSum;

            foreach ($bonusTier as $prize) {
                $prize->base_win_chance = (float) $prize->base_win_chance * $bonusScale;
            }

            foreach ($nonBonus as $prize) {
                $prize->base_win_chance = (float) $prize->base_win_chance * $nonBonusScale;
            }
        }

        return $pool;
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
     * @param  Collection<int, GachaPool>  $pool
     */
    private function pickWeighted(Collection $pool): GachaPool
    {
        $totalWeight = (float) $pool->sum('base_win_chance');

        if ($totalWeight <= 0) {
            return $pool->first();
        }

        // Two decimal places of precision in base_win_chance — scale to integers for mt_rand.
        $random = mt_rand(1, (int) round($totalWeight * 100)) / 100;
        $cumulative = 0.0;

        foreach ($pool as $prize) {
            $cumulative += (float) $prize->base_win_chance;
            if ($random <= $cumulative) {
                return $prize;
            }
        }

        return $pool->last();
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
