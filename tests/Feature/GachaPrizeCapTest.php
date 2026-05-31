<?php

use App\Models\GachaHistory;
use App\Models\GachaPool;
use App\Models\GachaRarityChance;
use App\Models\User;
use App\Services\GachaRollService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function cappedPoolPair(): array
{
    GachaRarityChance::create(['rarity' => 'uncommon', 'base_chance' => 100.00, 'sort_order' => 0]);

    $capped = GachaPool::create([
        'prize_name' => 'Welcome Bonus', 'rarity_item' => 'uncommon', 'reward_type' => 'points',
        'points_amount' => 50, 'max_per_user' => 1,
    ]);
    $other = GachaPool::create([
        'prize_name' => 'Regular', 'rarity_item' => 'uncommon', 'reward_type' => 'points',
        'points_amount' => 50,
    ]);

    return [$capped, $other];
}

it('keeps a capped prize eligible until the cap is reached', function () {
    [$capped] = cappedPoolPair();
    $user = User::factory()->create();

    // No prior wins → capped prize is still in the eligible pool.
    GachaPool::where('id', '!=', $capped->id)->delete(); // only the capped prize remains
    $result = app(GachaRollService::class)->roll($user);

    expect($result['prize']->id)->toBe($capped->id);
});

it('never re-awards a prize once its max_per_user cap is hit', function () {
    [$capped, $other] = cappedPoolPair();
    $user = User::factory()->create();

    // Simulate the user already winning the capped prize once.
    GachaHistory::create([
        'user_id' => $user->id, 'gacha_pool_id' => $capped->id,
        'points_spent' => 200, 'cost_type' => 'points', 'reward_type' => 'points',
    ]);

    $service = app(GachaRollService::class);
    for ($i = 0; $i < 30; $i++) {
        expect($service->roll($user)['prize']->id)->toBe($other->id);
    }
});

it('treats a null max_per_user as unlimited', function () {
    GachaRarityChance::create(['rarity' => 'common', 'base_chance' => 100.00, 'sort_order' => 0]);
    $prize = GachaPool::create([
        'prize_name' => 'Endless', 'rarity_item' => 'common', 'reward_type' => 'points',
        'points_amount' => 25, 'max_per_user' => null,
    ]);
    $user = User::factory()->create();

    // Many prior wins don't exclude an uncapped prize.
    foreach (range(1, 5) as $i) {
        GachaHistory::create([
            'user_id' => $user->id, 'gacha_pool_id' => $prize->id,
            'points_spent' => 200, 'cost_type' => 'points', 'reward_type' => 'points',
        ]);
    }

    expect(app(GachaRollService::class)->roll($user)['prize']->id)->toBe($prize->id);
});
