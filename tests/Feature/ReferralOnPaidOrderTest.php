<?php

use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralConfig;
use App\Models\ReferralReward;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    ReferralConfig::create([
        'id' => 1,
        'referee_welcome_points' => 100,
        'referrer_first_purchase_pts' => 500,
        'commission_percent' => 0,
        'commission_per_order_cap' => 5000,
        'commission_lifetime_cap' => 50000,
        'referrer_min_account_age_h' => 0,
    ]);
});

function setupPair(): array
{
    $referrer = User::factory()->create(['referral_code' => 'REF1', 'points_balance' => 0]);
    $referee = User::factory()->create(['referred_by' => $referrer->id, 'points_balance' => 0]);
    $referral = Referral::create([
        'referrer_id' => $referrer->id,
        'referred_user_id' => $referee->id,
        'status' => Referral::STATUS_PENDING,
    ]);

    return [$referrer, $referee, $referral];
}

function makePaidOrder(User $user, int $subtotal = 100000): Order
{
    return Order::create([
        'noinv' => 'INV-'.uniqid(),
        'user_id' => $user->id,
        'subtotal' => $subtotal,
        'discount_amount' => 0,
        'total_price_after_discount' => $subtotal,
        'status' => 'paid',
    ]);
}

it('pays the first-purchase bonus to the referrer on the referee\'s first paid order', function () {
    [$referrer, $referee, $referral] = setupPair();
    $order = makePaidOrder($referee);

    app(ReferralService::class)->onPaidOrder($order);

    expect($referrer->fresh()->points_balance)->toBe(500);
    expect($referral->fresh()->status)->toBe(Referral::STATUS_FIRST_PURCHASE_REWARDED);
    expect($referral->fresh()->first_purchase_order_id)->toBe($order->id);
    expect(ReferralReward::where('referral_id', $referral->id)->where('kind', 'first_purchase')->count())->toBe(1);
});

it('does not re-pay the first-purchase bonus on a second order', function () {
    [$referrer, $referee, $referral] = setupPair();
    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee));
    $balanceAfterFirst = $referrer->fresh()->points_balance;

    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee));

    // Commission disabled by default → second order pays nothing.
    expect($referrer->fresh()->points_balance)->toBe($balanceAfterFirst);
    expect(ReferralReward::where('kind', 'first_purchase')->count())->toBe(1);
});

it('is idempotent against a duplicate webhook for the same order', function () {
    [$referrer, $referee, $referral] = setupPair();
    $order = makePaidOrder($referee);

    app(ReferralService::class)->onPaidOrder($order);
    app(ReferralService::class)->onPaidOrder($order);
    app(ReferralService::class)->onPaidOrder($order);

    expect($referrer->fresh()->points_balance)->toBe(500);
    expect(ReferralReward::count())->toBe(1);
});

it('skips payout for a voided referral', function () {
    [$referrer, $referee, $referral] = setupPair();
    $referral->update(['status' => Referral::STATUS_VOID]);

    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee));

    expect($referrer->fresh()->points_balance)->toBe(0);
});

it('pays commission on second order when commission is enabled', function () {
    [$referrer, $referee, $referral] = setupPair();
    // First order — Tier A fires
    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee));

    ReferralConfig::current()->update(['commission_percent' => 5, 'commission_per_order_cap' => 100000]);

    // Second order — Tier B fires at 5% of 200_000 = 10_000 (no cap hit since 100k cap)
    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee, 200000));

    expect($referrer->fresh()->points_balance)->toBe(500 + 10000);
    expect(ReferralReward::where('kind', 'commission')->count())->toBe(1);
    expect($referral->fresh()->total_commission_paid)->toBe(10000);
});

it('respects per-order commission cap', function () {
    [$referrer, $referee, $referral] = setupPair();
    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee));

    ReferralConfig::current()->update(['commission_percent' => 10, 'commission_per_order_cap' => 200]);

    // 10% of 100_000 = 10_000, but per-order cap caps it at 200.
    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee, 100000));

    expect($referrer->fresh()->points_balance)->toBe(500 + 200);
});

it('respects lifetime commission cap', function () {
    [$referrer, $referee, $referral] = setupPair();
    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee));
    ReferralConfig::current()->update(['commission_percent' => 10, 'commission_per_order_cap' => 100000, 'commission_lifetime_cap' => 1000]);

    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee, 50000));  // 5000 raw → clamped to 1000 (lifetime cap)
    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee, 50000));  // already at cap → 0

    expect($referral->fresh()->total_commission_paid)->toBe(1000);
    expect($referrer->fresh()->points_balance)->toBe(500 + 1000);
});

it('does nothing for an order whose user has no referrer', function () {
    $alone = User::factory()->create(['points_balance' => 0]);

    app(ReferralService::class)->onPaidOrder(makePaidOrder($alone));

    expect(ReferralReward::count())->toBe(0);
});

it('blocks payout when the referrer account is too new', function () {
    [$referrer, $referee, $referral] = setupPair();
    $referrer->forceFill(['created_at' => now()->subMinutes(30)])->save();
    ReferralConfig::current()->update(['referrer_min_account_age_h' => 24]);

    app(ReferralService::class)->onPaidOrder(makePaidOrder($referee));

    expect($referrer->fresh()->points_balance)->toBe(0);
    expect($referral->fresh()->status)->toBe(Referral::STATUS_PENDING);
});
