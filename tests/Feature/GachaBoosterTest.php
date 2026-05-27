<?php

use App\Models\GachaBooster;
use App\Models\GachaPool;
use App\Models\GachaRarityChance;
use App\Models\User;
use App\Models\UserActiveBooster;
use App\Services\GachaRollService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeBooster(array $overrides = []): GachaBooster
{
    return GachaBooster::create(array_merge([
        'key' => 'lucky_charm',
        'name' => 'Lucky Charm',
        'description' => '+5% Rare+ for 5 rolls.',
        'point_cost' => 500,
        'rarity_floor' => 'rare',
        'bonus_percent' => 5.00,
        'rolls_granted' => 5,
        'is_active' => true,
    ], $overrides));
}

function seedBoosterTestRarities(): void
{
    GachaRarityChance::query()->insert([
        ['rarity' => 'common', 'base_chance' => 50.00, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
        ['rarity' => 'rare',   'base_chance' => 50.00, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
    ]);
}

it('activates a booster, deducts points, creates an active row with rolls_remaining', function () {
    $booster = makeBooster(['rolls_granted' => 5]);
    $user = User::factory()->create(['points_balance' => 1000]);

    $response = $this->actingAs($user)
        ->postJson(route('gacha.boosters.activate', $booster));

    $response->assertOk()
        ->assertJsonStructure(['message', 'new_balance', 'active_booster' => ['id', 'booster_id', 'rolls_remaining']]);
    expect($user->fresh()->points_balance)->toBe(500);
    expect(UserActiveBooster::where('user_id', $user->id)->count())->toBe(1);
    expect(UserActiveBooster::first()->rolls_remaining)->toBe(5);
});

it('rejects activation when the booster is inactive', function () {
    $booster = makeBooster(['is_active' => false]);
    $user = User::factory()->create(['points_balance' => 1000]);

    $this->actingAs($user)
        ->postJson(route('gacha.boosters.activate', $booster))
        ->assertStatus(422);
    expect($user->fresh()->points_balance)->toBe(1000);
});

it('rejects activation when the user lacks points', function () {
    $booster = makeBooster();
    $user = User::factory()->create(['points_balance' => 100]);

    $this->actingAs($user)
        ->postJson(route('gacha.boosters.activate', $booster))
        ->assertStatus(422);
    expect(UserActiveBooster::count())->toBe(0);
});

it('adds rolls when re-activating an already-active booster', function () {
    $booster = makeBooster(['rolls_granted' => 5]);
    $user = User::factory()->create(['points_balance' => 2000]);

    $this->actingAs($user)
        ->postJson(route('gacha.boosters.activate', $booster))
        ->assertOk();
    $first = UserActiveBooster::first();
    expect($first->rolls_remaining)->toBe(5);

    $this->actingAs($user)
        ->postJson(route('gacha.boosters.activate', $booster))
        ->assertOk();

    expect(UserActiveBooster::count())->toBe(1);
    expect(UserActiveBooster::first()->rolls_remaining)->toBe(10);
    expect($user->fresh()->points_balance)->toBe(1000);
});

it('shifts weight into bonus-tier rarities when a booster is active', function () {
    seedBoosterTestRarities();
    GachaPool::query()->insert([
        ['id' => 1, 'prize_name' => 'Common', 'rarity_item' => 'common', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 1, 'image_path' => null],
        ['id' => 2, 'prize_name' => 'Rare', 'rarity_item' => 'rare', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 1, 'image_path' => null],
    ]);
    $booster = makeBooster(['rarity_floor' => 'rare', 'bonus_percent' => 20.0, 'rolls_granted' => 1000]);
    $user = User::factory()->create(['points_balance' => 0]);
    UserActiveBooster::create([
        'user_id' => $user->id,
        'gacha_booster_id' => $booster->id,
        'rolls_remaining' => 1000,
    ]);

    $rareWins = 0;
    for ($i = 0; $i < 200; $i++) {
        $user->gachaState()->delete();
        $service = app(GachaRollService::class);
        $outcome = $service->roll($user->fresh());
        if ($outcome['prize']->rarity_item === 'rare') {
            $rareWins++;
        }
    }

    // With 50/50 base + 20% shift toward rare, rare should win ~70%. Wide band.
    expect($rareWins)->toBeGreaterThan(120);
});

it('decrements rolls_remaining by 1 per roll and deletes the row at zero', function () {
    seedBoosterTestRarities();
    GachaPool::query()->insert([
        ['id' => 1, 'prize_name' => 'Common', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null],
    ]);
    $booster = makeBooster(['rolls_granted' => 2]);
    $user = User::factory()->create(['points_balance' => 0]);
    $active = UserActiveBooster::create([
        'user_id' => $user->id,
        'gacha_booster_id' => $booster->id,
        'rolls_remaining' => 2,
    ]);

    $service = app(GachaRollService::class);

    $service->roll($user->fresh());
    expect($active->fresh()->rolls_remaining)->toBe(1);

    $service->roll($user->fresh());
    expect(UserActiveBooster::find($active->id))->toBeNull();
});

it('ignores boosters with zero rolls remaining in the snapshot', function () {
    $booster = makeBooster();
    $user = User::factory()->create(['points_balance' => 0]);
    UserActiveBooster::create([
        'user_id' => $user->id,
        'gacha_booster_id' => $booster->id,
        'rolls_remaining' => 0,
    ]);

    $snapshot = app(GachaRollService::class)->snapshotFor($user);
    expect($snapshot['active_boosters'])->toBe([]);
});

it('exposes active boosters in the snapshot with rolls_remaining', function () {
    $booster = makeBooster();
    $user = User::factory()->create(['points_balance' => 0]);
    UserActiveBooster::create([
        'user_id' => $user->id,
        'gacha_booster_id' => $booster->id,
        'rolls_remaining' => 4,
    ]);

    $snapshot = app(GachaRollService::class)->snapshotFor($user);
    expect($snapshot['active_boosters'])->toHaveCount(1);
    expect($snapshot['active_boosters'][0]['booster_id'])->toBe($booster->id);
    expect($snapshot['active_boosters'][0]['rarity_floor'])->toBe('rare');
    expect($snapshot['active_boosters'][0]['rolls_remaining'])->toBe(4);
});

it('admin can create, update, and delete a booster', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.gacha-boosters.store'), [
            'key' => 'starlight',
            'name' => 'Starlight Boost',
            'description' => 'test',
            'point_cost' => 1000,
            'rarity_floor' => 'epic',
            'bonus_percent' => 7.5,
            'rolls_granted' => 10,
            'is_active' => true,
        ])
        ->assertRedirect();
    $b = GachaBooster::where('key', 'starlight')->first();
    expect($b)->not->toBeNull();
    expect($b->rolls_granted)->toBe(10);

    $this->actingAs($admin)
        ->patch(route('admin.gacha-boosters.update', $b), [
            'key' => 'starlight',
            'name' => 'Starlight Boost v2',
            'point_cost' => 1500,
            'rarity_floor' => 'epic',
            'bonus_percent' => 8.0,
            'rolls_granted' => 15,
            'is_active' => true,
        ])
        ->assertRedirect();
    expect($b->fresh()->name)->toBe('Starlight Boost v2');
    expect($b->fresh()->rolls_granted)->toBe(15);

    $this->actingAs($admin)
        ->delete(route('admin.gacha-boosters.destroy', $b))
        ->assertRedirect();
    expect(GachaBooster::where('key', 'starlight')->exists())->toBeFalse();
});
