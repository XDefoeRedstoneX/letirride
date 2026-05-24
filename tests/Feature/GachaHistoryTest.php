<?php

use App\Models\GachaHistory;
use App\Models\GachaPool;
use App\Models\User;
use App\Models\UserGachaState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedHistoryPool(): GachaPool
{
    return GachaPool::create([
        'id' => 1,
        'prize_name' => 'Test Prize',
        'rarity_item' => 'rare',
        'reward_type' => 'points',
        'discount_type_id' => null,
        'points_amount' => 100,
        'image_path' => null,
        'base_win_chance' => 100.0,
    ]);
}

it('requires authentication to view gacha history', function () {
    $this->get(route('gacha.history'))
        ->assertRedirect();
});

it('shows the empty state when the user has no spins', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('gacha.history'));

    $response->assertOk();
    $response->assertSee('NO SPINS YET', false);
    $response->assertSee('GO TO GACHA', false);
});

it('lists the authenticated user\'s spins ordered newest-first', function () {
    $pool = seedHistoryPool();
    $user = User::factory()->create();

    // Older spin first (so the ordering test means something)
    GachaHistory::create([
        'user_id' => $user->id,
        'gacha_pool_id' => $pool->id,
        'points_spent' => 200,
        'cost_type' => 'points',
        'pity_triggered' => null,
        'reward_type' => 'points',
        'image_path' => '/custom/old.png',
        'created_at' => now()->subDays(2),
    ]);
    $newer = GachaHistory::create([
        'user_id' => $user->id,
        'gacha_pool_id' => $pool->id,
        'points_spent' => 200,
        'cost_type' => 'money',
        'pity_triggered' => 'hard',
        'reward_type' => 'points',
        'image_path' => '/custom/new.png',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('gacha.history'));

    $response->assertOk();
    $rows = $response->viewData('rows');
    expect($rows->total())->toBe(2);
    // Newest first
    expect($rows->items()[0]->id)->toBe($newer->id);
});

it('does not show other users\' history', function () {
    $pool = seedHistoryPool();
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    GachaHistory::create([
        'user_id' => $alice->id,
        'gacha_pool_id' => $pool->id,
        'points_spent' => 200,
        'cost_type' => 'points',
        'reward_type' => 'points',
    ]);

    $response = $this->actingAs($bob)->get(route('gacha.history'));

    $response->assertOk();
    $rows = $response->viewData('rows');
    expect($rows->total())->toBe(0);
});

it('exposes the pity snapshot to the view', function () {
    $user = User::factory()->create();
    UserGachaState::create([
        'user_id' => $user->id,
        'pity_counter' => 23,
        'mini_pity_counter' => 4,
        'total_spins' => 27,
        'free_spins' => 2,
    ]);

    $response = $this->actingAs($user)->get(route('gacha.history'));

    $response->assertOk();
    $pity = $response->viewData('pity');
    expect($pity['pity_counter'])->toBe(23);
    expect($pity['mini_pity_counter'])->toBe(4);
    expect($pity['free_spins'])->toBe(2);
    expect($pity['total_spins'])->toBe(27);
});

it('renders pity chip + history link on the main /gacha page', function () {
    $user = User::factory()->create();
    UserGachaState::create([
        'user_id' => $user->id,
        'pity_counter' => 23,
        'mini_pity_counter' => 4,
    ]);

    $response = $this->actingAs($user)->get(route('gacha'));

    $response->assertOk();
    $response->assertSee('PITY 23/50', false);
    $response->assertSee('MINI 4/10', false);
    $response->assertSee(route('gacha.history'), false);
});

it('paginates at 20 rows per page', function () {
    $pool = seedHistoryPool();
    $user = User::factory()->create();

    foreach (range(1, 25) as $i) {
        GachaHistory::create([
            'user_id' => $user->id,
            'gacha_pool_id' => $pool->id,
            'points_spent' => 200,
            'cost_type' => 'points',
            'reward_type' => 'points',
        ]);
    }

    $response = $this->actingAs($user)->get(route('gacha.history'));
    $response->assertOk();
    $rows = $response->viewData('rows');
    expect($rows->total())->toBe(25);
    expect($rows->perPage())->toBe(20);
    expect(count($rows->items()))->toBe(20);

    $response = $this->actingAs($user)->get(route('gacha.history').'?page=2');
    $rows = $response->viewData('rows');
    expect(count($rows->items()))->toBe(5);
});
