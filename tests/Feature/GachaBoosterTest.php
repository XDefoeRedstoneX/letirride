<?php

use App\Models\GachaBooster;
use App\Models\GachaPool;
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
        'description' => '+5% Rare+ for 30 min.',
        'point_cost' => 500,
        'rarity_floor' => 'rare',
        'bonus_percent' => 5.00,
        'duration_minutes' => 30,
        'is_active' => true,
    ], $overrides));
}

it('activates a booster, deducts points, creates an active row', function () {
    $booster = makeBooster();
    $user = User::factory()->create(['points_balance' => 1000]);

    $response = $this->actingAs($user)
        ->postJson(route('gacha.boosters.activate', $booster));

    $response->assertOk()
        ->assertJsonStructure(['message', 'new_balance', 'active_booster' => ['id', 'booster_id', 'expires_at']]);
    expect($user->fresh()->points_balance)->toBe(500);
    expect(UserActiveBooster::where('user_id', $user->id)->count())->toBe(1);
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

it('extends expires_at when re-activating an already-active booster', function () {
    $booster = makeBooster(['duration_minutes' => 30]);
    $user = User::factory()->create(['points_balance' => 2000]);

    $this->actingAs($user)
        ->postJson(route('gacha.boosters.activate', $booster))
        ->assertOk();
    $first = UserActiveBooster::first();
    $firstExpiry = $first->expires_at;

    $this->actingAs($user)
        ->postJson(route('gacha.boosters.activate', $booster))
        ->assertOk();

    expect(UserActiveBooster::count())->toBe(1);
    $extended = UserActiveBooster::first();
    expect($extended->expires_at->greaterThan($firstExpiry))->toBeTrue();
    expect($user->fresh()->points_balance)->toBe(1000);
});

it('shifts weight into bonus-tier prizes when a booster is active', function () {
    GachaPool::query()->insert([
        ['id' => 1, 'prize_name' => 'Common', 'rarity_item' => 'common', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 1, 'image_path' => null, 'base_win_chance' => 50.0],
        ['id' => 2, 'prize_name' => 'Rare', 'rarity_item' => 'rare', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 1, 'image_path' => null, 'base_win_chance' => 50.0],
    ]);
    $booster = makeBooster(['rarity_floor' => 'rare', 'bonus_percent' => 20.0]);
    $user = User::factory()->create(['points_balance' => 0]);
    UserActiveBooster::create([
        'user_id' => $user->id,
        'gacha_booster_id' => $booster->id,
        'activated_at' => now()->subMinutes(5),
        'expires_at' => now()->addMinutes(25),
    ]);

    $rareWins = 0;
    for ($i = 0; $i < 200; $i++) {
        // Reset pity state each iteration so it never triggers.
        $user->gachaState()->delete();
        $service = app(GachaRollService::class);
        $outcome = $service->roll($user->fresh());
        if ($outcome['prize']->rarity_item === 'rare') {
            $rareWins++;
        }
    }

    // With 50/50 base + 20% shift, rare should win ~70%. Wide band to avoid flake.
    expect($rareWins)->toBeGreaterThan(120);
});

it('ignores expired boosters in the snapshot', function () {
    $booster = makeBooster();
    $user = User::factory()->create(['points_balance' => 0]);
    UserActiveBooster::create([
        'user_id' => $user->id,
        'gacha_booster_id' => $booster->id,
        'activated_at' => now()->subHours(2),
        'expires_at' => now()->subMinutes(5),
    ]);

    $snapshot = app(GachaRollService::class)->snapshotFor($user);
    expect($snapshot['active_boosters'])->toBe([]);
});

it('exposes active boosters in the snapshot', function () {
    $booster = makeBooster();
    $user = User::factory()->create(['points_balance' => 0]);
    UserActiveBooster::create([
        'user_id' => $user->id,
        'gacha_booster_id' => $booster->id,
        'activated_at' => now()->subMinutes(1),
        'expires_at' => now()->addMinutes(29),
    ]);

    $snapshot = app(GachaRollService::class)->snapshotFor($user);
    expect($snapshot['active_boosters'])->toHaveCount(1);
    expect($snapshot['active_boosters'][0]['booster_id'])->toBe($booster->id);
    expect($snapshot['active_boosters'][0]['rarity_floor'])->toBe('rare');
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
            'duration_minutes' => 20,
            'is_active' => true,
        ])
        ->assertRedirect();
    $b = GachaBooster::where('key', 'starlight')->first();
    expect($b)->not->toBeNull();

    $this->actingAs($admin)
        ->patch(route('admin.gacha-boosters.update', $b), [
            'key' => 'starlight',
            'name' => 'Starlight Boost v2',
            'point_cost' => 1500,
            'rarity_floor' => 'epic',
            'bonus_percent' => 8.0,
            'duration_minutes' => 25,
            'is_active' => true,
        ])
        ->assertRedirect();
    expect($b->fresh()->name)->toBe('Starlight Boost v2');

    $this->actingAs($admin)
        ->delete(route('admin.gacha-boosters.destroy', $b))
        ->assertRedirect();
    expect(GachaBooster::where('key', 'starlight')->exists())->toBeFalse();
});
