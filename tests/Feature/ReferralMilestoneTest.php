<?php

use App\Models\DiscountType;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralConfig;
use App\Models\ReferralReward;
use App\Models\ReferralTier;
use App\Models\User;
use App\Models\UserDiscount;
use App\Models\UserGachaState;
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

    DiscountType::create([
        'id' => 1,
        'name' => 'Test Discount',
        'type' => 'percent',
        'value' => 10.00,
    ]);
});

function makeReferrerUser(): User
{
    return User::factory()->create(['referral_code' => 'OWN'.uniqid(), 'points_balance' => 0]);
}

function makePaidReferral(User $referrer): User
{
    $referee = User::factory()->create(['referred_by' => $referrer->id]);
    Referral::create([
        'referrer_id' => $referrer->id,
        'referred_user_id' => $referee->id,
        'status' => Referral::STATUS_FIRST_PURCHASE_REWARDED,
        'first_purchase_rewarded_at' => now(),
    ]);

    return $referee;
}

function makeTier(array $attrs): ReferralTier
{
    return ReferralTier::create(array_merge([
        'title' => 'Test Tier',
        'description' => null,
        'points_reward' => 0,
        'free_spins_reward' => 0,
        'sort_order' => 0,
        'is_active' => true,
    ], $attrs));
}

it('grants a one-friend tier when the first paid referral lands', function () {
    $tier = makeTier(['threshold' => 1, 'points_reward' => 250]);
    $referrer = makeReferrerUser();
    makePaidReferral($referrer);

    $granted = app(ReferralService::class)->checkMilestonesForReferrer($referrer);

    expect($granted)->toHaveCount(1);
    expect($referrer->fresh()->points_balance)->toBe(250);
    expect(ReferralReward::where('recipient_id', $referrer->id)->where('kind', 'milestone')->count())->toBe(1);
});

it('grants all crossed tiers at once when multiple thresholds are below the current count', function () {
    makeTier(['threshold' => 1, 'points_reward' => 100]);
    makeTier(['threshold' => 3, 'points_reward' => 500]);
    makeTier(['threshold' => 5, 'points_reward' => 1000]);
    $referrer = makeReferrerUser();
    for ($i = 0; $i < 5; $i++) {
        makePaidReferral($referrer);
    }

    $granted = app(ReferralService::class)->checkMilestonesForReferrer($referrer);

    expect($granted)->toHaveCount(3);
    expect($referrer->fresh()->points_balance)->toBe(1600);
});

it('is idempotent: running checkMilestones twice doesn\'t re-grant', function () {
    makeTier(['threshold' => 1, 'points_reward' => 200]);
    $referrer = makeReferrerUser();
    makePaidReferral($referrer);

    $svc = app(ReferralService::class);
    $svc->checkMilestonesForReferrer($referrer);
    $svc->checkMilestonesForReferrer($referrer);
    $svc->checkMilestonesForReferrer($referrer);

    expect($referrer->fresh()->points_balance)->toBe(200);
    expect(ReferralReward::where('kind', 'milestone')->count())->toBe(1);
});

it('skips inactive tiers', function () {
    makeTier(['threshold' => 1, 'points_reward' => 100, 'is_active' => false]);
    $referrer = makeReferrerUser();
    makePaidReferral($referrer);

    $granted = app(ReferralService::class)->checkMilestonesForReferrer($referrer);

    expect($granted)->toBeEmpty();
    expect($referrer->fresh()->points_balance)->toBe(0);
});

it('issues a discount voucher when the tier carries one', function () {
    makeTier(['threshold' => 1, 'points_reward' => 0, 'discount_type_id' => 1]);
    $referrer = makeReferrerUser();
    makePaidReferral($referrer);

    app(ReferralService::class)->checkMilestonesForReferrer($referrer);

    expect(UserDiscount::where('user_id', $referrer->id)->where('obtained_from', 'referral_milestone')->count())->toBe(1);
});

it('adds free spin tokens when the tier carries them', function () {
    makeTier(['threshold' => 1, 'points_reward' => 0, 'free_spins_reward' => 3]);
    $referrer = makeReferrerUser();
    makePaidReferral($referrer);

    app(ReferralService::class)->checkMilestonesForReferrer($referrer);

    expect(UserGachaState::where('user_id', $referrer->id)->first()->free_spins)->toBe(3);
});

it('does not count void referrals toward progression', function () {
    makeTier(['threshold' => 2, 'points_reward' => 500]);
    $referrer = makeReferrerUser();
    makePaidReferral($referrer);

    // Second referral is voided — should NOT push count to 2.
    $referee = User::factory()->create(['referred_by' => $referrer->id]);
    Referral::create([
        'referrer_id' => $referrer->id,
        'referred_user_id' => $referee->id,
        'status' => Referral::STATUS_VOID,
    ]);

    app(ReferralService::class)->checkMilestonesForReferrer($referrer);

    expect($referrer->fresh()->points_balance)->toBe(0);
    expect(ReferralReward::where('kind', 'milestone')->count())->toBe(0);
});

it('fires from onPaidOrder when the first-purchase reward flips the referral status', function () {
    $tier = makeTier(['threshold' => 1, 'points_reward' => 300]);
    $referrer = makeReferrerUser();
    $referee = User::factory()->create(['referred_by' => $referrer->id, 'points_balance' => 0]);
    Referral::create([
        'referrer_id' => $referrer->id,
        'referred_user_id' => $referee->id,
        'status' => Referral::STATUS_PENDING,
    ]);

    $order = Order::create([
        'noinv' => 'INV-TEST-MILESTONE',
        'user_id' => $referee->id,
        'subtotal' => 100000,
        'discount_amount' => 0,
        'total_price_after_discount' => 100000,
        'status' => 'paid',
    ]);

    app(ReferralService::class)->onPaidOrder($order);

    // Referrer should have first-purchase bonus (500) + tier reward (300) = 800.
    expect($referrer->fresh()->points_balance)->toBe(800);
    expect(ReferralReward::where('recipient_id', $referrer->id)->where('kind', 'milestone')->count())->toBe(1);
});

it('backfills milestones for existing qualifying users when a new tier is created', function () {
    $referrer = makeReferrerUser();
    makePaidReferral($referrer);
    makePaidReferral($referrer);
    makePaidReferral($referrer);

    // No tier yet → no payout.
    expect($referrer->fresh()->points_balance)->toBe(0);

    makeTier(['threshold' => 2, 'points_reward' => 400]);

    $count = app(ReferralService::class)->backfillMilestonesForAll();

    expect($count)->toBe(1);
    expect($referrer->fresh()->points_balance)->toBe(400);
});

it('counts a milestone toward earned_points on the referrals page', function () {
    makeTier(['threshold' => 1, 'points_reward' => 250]);
    $referrer = makeReferrerUser();
    makePaidReferral($referrer);
    app(ReferralService::class)->checkMilestonesForReferrer($referrer);

    $response = $this->actingAs($referrer)->get(route('referrals'));

    $response->assertOk();
    $response->assertSee('REWARD MILESTONES', false);
    $response->assertSee('UNLOCKED', false);
});
