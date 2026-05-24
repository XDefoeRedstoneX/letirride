<?php

use App\Models\DiscountType;
use App\Models\GachaHistory;
use App\Models\GachaPool;
use App\Models\User;
use App\Models\UserDiscount;
use App\Models\UserGachaState;
use App\Services\GachaRollService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    DiscountType::query()->insert([
        ['id' => 1, 'name' => '10% Off All', 'type' => 'percent', 'value' => 10.00, 'target_category_id' => null],
    ]);
});

/**
 * Seed a deterministic mini-pool so weight-only behavior is predictable.
 */
function seedDeterministicPool(): void
{
    GachaPool::query()->insert([
        ['id' => 1, 'prize_name' => 'TestGrand', 'rarity_item' => 'grand_prize', 'reward_type' => 'discount', 'discount_type_id' => 1, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 1.0],
        ['id' => 2, 'prize_name' => 'TestEpic', 'rarity_item' => 'epic', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 500, 'image_path' => null, 'base_win_chance' => 5.0],
        ['id' => 3, 'prize_name' => 'TestUncommon', 'rarity_item' => 'uncommon', 'reward_type' => 'discount', 'discount_type_id' => 1, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 24.0],
        ['id' => 4, 'prize_name' => 'TestCommon', 'rarity_item' => 'common', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 25, 'image_path' => null, 'base_win_chance' => 50.0],
        ['id' => 5, 'prize_name' => 'TestFreeSpin', 'rarity_item' => 'rare', 'reward_type' => 'free_spin', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 0.0],
        ['id' => 6, 'prize_name' => 'TestNothing', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 20.0],
    ]);
}

it('deducts points and records history on a points spin', function () {
    GachaPool::query()->insert([
        ['id' => 700, 'prize_name' => 'OnlyNothing', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 100.0],
    ]);
    $user = User::factory()->create(['points_balance' => 500]);

    $response = $this->actingAs($user)->postJson(route('gacha.roll'));

    $response->assertOk()
        ->assertJsonStructure(['prize' => ['id', 'name', 'rarity', 'reward_type', 'image'], 'new_balance', 'pity']);
    expect($user->fresh()->points_balance)->toBe(300);
    expect(GachaHistory::where('user_id', $user->id)->count())->toBe(1);
    $hist = GachaHistory::first();
    expect($hist->points_spent)->toBe(200);
    expect($hist->cost_type)->toBe('points');
    expect($hist->reward_type)->toBe('nothing');
});

it('rejects a points spin when balance is insufficient', function () {
    seedDeterministicPool();
    $user = User::factory()->create(['points_balance' => 100]);

    $response = $this->actingAs($user)->postJson(route('gacha.roll'));

    $response->assertStatus(422);
    expect(GachaHistory::count())->toBe(0);
    expect($user->fresh()->points_balance)->toBe(100);
});

it('rejects cost_type=money (money spins go through gacha.pay)', function () {
    seedDeterministicPool();
    $user = User::factory()->create(['points_balance' => 500]);

    $this->actingAs($user)->postJson(route('gacha.roll'), ['cost_type' => 'money'])
        ->assertStatus(422);
});

it('consumes a free spin token on a free_spin cost spin', function () {
    GachaPool::query()->insert([
        ['id' => 800, 'prize_name' => 'OnlyNothing', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 100.0],
    ]);
    $user = User::factory()->create(['points_balance' => 0]);
    UserGachaState::create(['user_id' => $user->id, 'free_spins' => 1]);

    $this->actingAs($user)->postJson(route('gacha.roll'), ['cost_type' => 'free_spin'])
        ->assertOk();

    expect($user->fresh()->points_balance)->toBe(0);
    expect($user->gachaState()->first()->free_spins)->toBe(0);
    expect(GachaHistory::first()->points_spent)->toBe(0);
});

it('rejects a free_spin cost spin when no tokens are available', function () {
    seedDeterministicPool();
    $user = User::factory()->create(['points_balance' => 500]);

    $this->actingAs($user)->postJson(route('gacha.roll'), ['cost_type' => 'free_spin'])
        ->assertStatus(422);
});

it('hard pity at 50 restricts the pool to Epic+', function () {
    seedDeterministicPool();
    $user = User::factory()->create(['points_balance' => 500]);
    UserGachaState::create(['user_id' => $user->id, 'pity_counter' => 50, 'mini_pity_counter' => 50]);

    $response = $this->actingAs($user)->postJson(route('gacha.roll'));

    $response->assertOk()->assertJson(['pity_triggered' => 'hard']);
    $prize = $response->json('prize');
    expect($prize['rarity'])->toBeIn(['epic', 'legendary', 'grand_prize']);
});

it('mini pity at 10 restricts the pool to Uncommon+', function () {
    seedDeterministicPool();
    $user = User::factory()->create(['points_balance' => 500]);
    UserGachaState::create(['user_id' => $user->id, 'pity_counter' => 5, 'mini_pity_counter' => 10]);

    $response = $this->actingAs($user)->postJson(route('gacha.roll'));

    $response->assertOk()->assertJson(['pity_triggered' => 'mini']);
    $prize = $response->json('prize');
    expect($prize['rarity'])->toBeIn(['uncommon', 'rare', 'epic', 'legendary', 'grand_prize']);
});

it('resets pity counters on an Epic+ win', function () {
    seedDeterministicPool();
    $user = User::factory()->create(['points_balance' => 500]);
    UserGachaState::create(['user_id' => $user->id, 'pity_counter' => 50, 'mini_pity_counter' => 10]);

    $this->actingAs($user)->postJson(route('gacha.roll'))->assertOk();

    $state = $user->gachaState()->first();
    expect($state->pity_counter)->toBe(0);
    expect($state->mini_pity_counter)->toBe(0);
});

it('increments both pity counters on a Common loss', function () {
    GachaPool::query()->insert([
        ['id' => 100, 'prize_name' => 'OnlyCommon', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 100.0],
    ]);
    $user = User::factory()->create(['points_balance' => 500]);

    $this->actingAs($user)->postJson(route('gacha.roll'))->assertOk();

    $state = $user->gachaState()->first();
    expect($state->pity_counter)->toBe(1);
    expect($state->mini_pity_counter)->toBe(1);
    expect($state->total_spins)->toBe(1);
});

it('grants a UserDiscount when prize reward_type is discount', function () {
    GachaPool::query()->insert([
        ['id' => 200, 'prize_name' => 'OnlyDiscount', 'rarity_item' => 'rare', 'reward_type' => 'discount', 'discount_type_id' => 1, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 100.0],
    ]);
    $user = User::factory()->create(['points_balance' => 500]);

    $this->actingAs($user)->postJson(route('gacha.roll'))->assertOk();

    expect(UserDiscount::where('user_id', $user->id)->count())->toBe(1);
    expect(UserDiscount::first()->obtained_from)->toBe('gacha');
});

it('increments points balance when prize reward_type is points', function () {
    GachaPool::query()->insert([
        ['id' => 300, 'prize_name' => 'OnlyPoints', 'rarity_item' => 'common', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 100, 'image_path' => null, 'base_win_chance' => 100.0],
    ]);
    $user = User::factory()->create(['points_balance' => 500]);

    $this->actingAs($user)->postJson(route('gacha.roll'))->assertOk();

    // Started at 500, spent 200, won 100 = 400.
    expect($user->fresh()->points_balance)->toBe(400);
});

it('increments free_spins when prize reward_type is free_spin', function () {
    GachaPool::query()->insert([
        ['id' => 400, 'prize_name' => 'OnlyFreeSpin', 'rarity_item' => 'rare', 'reward_type' => 'free_spin', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 100.0],
    ]);
    $user = User::factory()->create(['points_balance' => 500]);

    $this->actingAs($user)->postJson(route('gacha.roll'))->assertOk();

    expect($user->gachaState()->first()->free_spins)->toBe(1);
});

it('grants no side effect when prize reward_type is nothing', function () {
    GachaPool::query()->insert([
        ['id' => 500, 'prize_name' => 'OnlyNothing', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 100.0],
    ]);
    $user = User::factory()->create(['points_balance' => 500]);

    $this->actingAs($user)->postJson(route('gacha.roll'))->assertOk();

    expect(UserDiscount::count())->toBe(0);
    // points_balance: 500 - 200 (cost) + 0 (reward) = 300
    expect($user->fresh()->points_balance)->toBe(300);
    expect($user->gachaState()->first()->free_spins)->toBe(0);
});

it('throttles rolls beyond 10 per minute', function () {
    seedDeterministicPool();
    $user = User::factory()->create(['points_balance' => 100000]);

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($user)->postJson(route('gacha.roll'))->assertOk();
    }

    $this->actingAs($user)->postJson(route('gacha.roll'))->assertStatus(429);
});

it('records image_path and reward_type snapshot in history', function () {
    GachaPool::query()->insert([
        ['id' => 600, 'prize_name' => 'Snapshot', 'rarity_item' => 'epic', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 500, 'image_path' => '/custom/snapshot.png', 'base_win_chance' => 100.0],
    ]);
    $user = User::factory()->create(['points_balance' => 500]);

    $this->actingAs($user)->postJson(route('gacha.roll'))->assertOk();

    $hist = GachaHistory::first();
    expect($hist->image_path)->toBe('/custom/snapshot.png');
    expect($hist->reward_type)->toBe('points');
});

it('exposes service-level pity thresholds as constants', function () {
    expect(GachaRollService::HARD_PITY_THRESHOLD)->toBe(50);
    expect(GachaRollService::MINI_PITY_THRESHOLD)->toBe(10);
});
