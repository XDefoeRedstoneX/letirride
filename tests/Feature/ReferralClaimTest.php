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

function makeReferrer(string $code = 'ALICECODE'): User
{
    return User::factory()->create(['referral_code' => $code, 'points_balance' => 0]);
}

it('claims a valid code: sets referred_by, creates referral, grants welcome points', function () {
    $referrer = makeReferrer();
    $newUser = User::factory()->create(['points_balance' => 0]);

    $response = $this->actingAs($newUser)
        ->postJson(route('referrals.claim'), ['code' => 'ALICECODE']);

    $response->assertOk()->assertJsonStructure(['message', 'points_awarded', 'new_balance']);
    expect($newUser->fresh()->referred_by)->toBe($referrer->id);
    expect($newUser->fresh()->points_balance)->toBe(100);
    expect(Referral::where('referrer_id', $referrer->id)->where('referred_user_id', $newUser->id)->count())->toBe(1);
    expect(ReferralReward::where('recipient_id', $newUser->id)->where('kind', 'referee_welcome')->count())->toBe(1);
});

it('rejects a self-referral attempt', function () {
    $user = User::factory()->create(['referral_code' => 'MINE', 'points_balance' => 0]);

    $this->actingAs($user)
        ->postJson(route('referrals.claim'), ['code' => 'MINE'])
        ->assertStatus(422);
    expect($user->fresh()->referred_by)->toBeNull();
    expect($user->fresh()->points_balance)->toBe(0);
});

it('rejects a claim when the user already has a referrer', function () {
    $first = makeReferrer('FIRST');
    $second = makeReferrer('SECOND');
    $user = User::factory()->create(['referred_by' => $first->id, 'points_balance' => 0]);

    $this->actingAs($user)
        ->postJson(route('referrals.claim'), ['code' => 'SECOND'])
        ->assertStatus(422);
    expect($user->fresh()->referred_by)->toBe($first->id);
});

it('rejects a claim after the user has a paid order', function () {
    $referrer = makeReferrer();
    $user = User::factory()->create(['points_balance' => 0]);
    Order::create([
        'noinv' => 'INV-TEST-1', 'user_id' => $user->id,
        'subtotal' => 100000, 'discount_amount' => 0, 'total_price_after_discount' => 100000,
        'status' => 'paid',
    ]);

    $this->actingAs($user)
        ->postJson(route('referrals.claim'), ['code' => 'ALICECODE'])
        ->assertStatus(422);
    expect($user->fresh()->referred_by)->toBeNull();
});

it('rejects an unknown code', function () {
    $user = User::factory()->create(['points_balance' => 0]);

    $this->actingAs($user)
        ->postJson(route('referrals.claim'), ['code' => 'NOPE'])
        ->assertStatus(422);
});

it('matches codes case-insensitively', function () {
    $referrer = makeReferrer('UPPERCODE');
    $user = User::factory()->create(['points_balance' => 0]);

    $this->actingAs($user)
        ->postJson(route('referrals.claim'), ['code' => 'uppercode'])
        ->assertOk();
    expect($user->fresh()->referred_by)->toBe($referrer->id);
});

it('throttles the claim endpoint beyond 10 per minute', function () {
    $referrer = makeReferrer();
    $user = User::factory()->create(['points_balance' => 0]);

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($user)->postJson(route('referrals.claim'), ['code' => 'NOPE'.$i]);
    }
    $this->actingAs($user)
        ->postJson(route('referrals.claim'), ['code' => 'NOPE'])
        ->assertStatus(429);
});

it('returns CLAIM_OK status from the service', function () {
    $referrer = makeReferrer('SVC');
    $newUser = User::factory()->create(['points_balance' => 0]);

    $result = app(ReferralService::class)->claimCode($newUser, 'SVC');
    expect($result['status'])->toBe(ReferralService::CLAIM_OK);
});
