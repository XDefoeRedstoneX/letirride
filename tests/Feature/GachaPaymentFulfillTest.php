<?php

use App\Http\Controllers\GachaPaymentController;
use App\Models\GachaHistory;
use App\Models\GachaPayment;
use App\Models\GachaPool;
use App\Models\GachaRarityChance;
use App\Models\User;
use App\Models\UserGachaState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * fulfill() is the pure logic path of the money-spin flow — it has no Midtrans
 * dependency, so we exercise it directly without mocking. The Snap-token issuance
 * and webhook validation paths are integration concerns tested manually in browser.
 */
function makePendingPayment(User $user): GachaPayment
{
    return GachaPayment::create([
        'user_id' => $user->id,
        'amount' => GachaPaymentController::SPIN_PRICE,
        'status' => 'pending',
        'midtrans_order_id' => 'GACHA-TEST-'.uniqid().'::'.time(),
    ]);
}

it('fulfills a payment: marks paid, records history with cost_type=money, ticks pity', function () {
    GachaRarityChance::create(['rarity' => 'common', 'base_chance' => 100.00, 'sort_order' => 0]);
    GachaPool::query()->insert([
        ['id' => 1, 'prize_name' => 'OnlyNothing', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null],
    ]);
    $user = User::factory()->create(['points_balance' => 0]);
    $payment = makePendingPayment($user);

    app(GachaPaymentController::class)->fulfill($payment);

    $payment->refresh();
    expect($payment->status)->toBe('paid');

    $hist = GachaHistory::where('gacha_payment_id', $payment->id)->first();
    expect($hist)->not->toBeNull();
    expect($hist->cost_type)->toBe('money');
    expect($hist->points_spent)->toBe(0);
    expect($hist->reward_type)->toBe('nothing');

    // Pity counter ticked because we won a 'nothing' (treated as below uncommon).
    $state = $user->gachaState()->first();
    expect($state->pity_counter)->toBe(1);
    expect($state->mini_pity_counter)->toBe(1);
});

it('grants the reward on a money fulfillment (points type increments balance)', function () {
    GachaRarityChance::create(['rarity' => 'common', 'base_chance' => 100.00, 'sort_order' => 0]);
    GachaPool::query()->insert([
        ['id' => 1, 'prize_name' => 'OnlyPoints', 'rarity_item' => 'common', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 250, 'image_path' => null],
    ]);
    $user = User::factory()->create(['points_balance' => 100]);
    $payment = makePendingPayment($user);

    app(GachaPaymentController::class)->fulfill($payment);

    expect($user->fresh()->points_balance)->toBe(350);
});

it('respects hard pity on money spins (Epic+ guaranteed at counter=50)', function () {
    GachaRarityChance::create(['rarity' => 'epic', 'base_chance' => 5.00, 'sort_order' => 0]);
    GachaRarityChance::create(['rarity' => 'common', 'base_chance' => 95.00, 'sort_order' => 1]);
    GachaPool::query()->insert([
        ['id' => 1, 'prize_name' => 'TestEpic', 'rarity_item' => 'epic', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 500, 'image_path' => null],
        ['id' => 2, 'prize_name' => 'TestCommon', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null],
    ]);
    $user = User::factory()->create(['points_balance' => 0]);
    UserGachaState::create(['user_id' => $user->id, 'pity_counter' => 50, 'mini_pity_counter' => 50]);
    $payment = makePendingPayment($user);

    app(GachaPaymentController::class)->fulfill($payment);

    $hist = GachaHistory::where('gacha_payment_id', $payment->id)->first();
    expect($hist->pity_triggered)->toBe('hard');
    expect(GachaPool::find($hist->gacha_pool_id)->rarity_item)
        ->toBeIn(['epic', 'legendary', 'grand_prize']);
});

it('is idempotent: fulfilling the same payment twice produces only one history row', function () {
    GachaRarityChance::create(['rarity' => 'common', 'base_chance' => 100.00, 'sort_order' => 0]);
    GachaPool::query()->insert([
        ['id' => 1, 'prize_name' => 'OnlyNothing', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null],
    ]);
    $user = User::factory()->create(['points_balance' => 0]);
    $payment = makePendingPayment($user);

    app(GachaPaymentController::class)->fulfill($payment);
    app(GachaPaymentController::class)->fulfill($payment);

    expect(GachaHistory::where('gacha_payment_id', $payment->id)->count())->toBe(1);
    expect($payment->fresh()->status)->toBe('paid');
});
