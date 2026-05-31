<?php

use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralConfig;
use App\Models\User;
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

it('renders the user\'s own referral code on the page', function () {
    $user = User::factory()->create(['referral_code' => 'MYCODE123']);

    $response = $this->actingAs($user)->get(route('referrals'));

    $response->assertOk();
    $response->assertSee('MYCODE123', false);
});

it('shows the how-it-works explainer with the configured reward values', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('referrals'));

    $response->assertOk();
    $response->assertSee('HOW IT WORKS', false);
    $response->assertSee('They join with your code', false);
    $response->assertSee('They make their first purchase', false);
    $response->assertSee('Climb the milestones', false);
    // Commission disabled by default → that step must NOT appear.
    $response->assertDontSee('Keep earning on every order', false);
});

it('includes the commission step only when commission is enabled', function () {
    ReferralConfig::current()->update(['commission_percent' => 2.5]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('referrals'));

    $response->assertOk();
    $response->assertSee('Keep earning on every order', false);
    $response->assertSee('2.5%', false);
});

it('shows the claim form when the user has no referrer and no paid orders', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('referrals'));

    $response->assertOk();
    $response->assertSee('GOT A CODE FROM A FRIEND', false);
});

it('hides the claim form once the user has been referred', function () {
    $referrer = User::factory()->create(['referral_code' => 'REF', 'name' => 'Alice']);
    $user = User::factory()->create(['referred_by' => $referrer->id]);

    $response = $this->actingAs($user)->get(route('referrals'));

    $response->assertOk();
    $response->assertDontSee('GOT A CODE FROM A FRIEND', false);
    $response->assertSee('Alice', false);
});

it('hides the claim form after the user has a paid order, even with no referrer', function () {
    $user = User::factory()->create();
    Order::create([
        'noinv' => 'INV-1', 'user_id' => $user->id,
        'subtotal' => 100000, 'discount_amount' => 0, 'total_price_after_discount' => 100000,
        'status' => 'paid',
    ]);

    $response = $this->actingAs($user)->get(route('referrals'));

    $response->assertOk();
    $response->assertDontSee('GOT A CODE FROM A FRIEND', false);
});

it('lists the referee names for the authenticated referrer only', function () {
    $alice = User::factory()->create(['referral_code' => 'ALICEC']);
    $bob = User::factory()->create(['referred_by' => $alice->id, 'name' => 'Bob']);
    $eve = User::factory()->create(['referral_code' => 'EVEC']);
    $mallory = User::factory()->create(['referred_by' => $eve->id, 'name' => 'Mallory']);

    Referral::create(['referrer_id' => $alice->id, 'referred_user_id' => $bob->id, 'status' => 'pending']);
    Referral::create(['referrer_id' => $eve->id, 'referred_user_id' => $mallory->id, 'status' => 'pending']);

    $response = $this->actingAs($alice)->get(route('referrals'));

    $response->assertOk();
    $response->assertSee('Bob', false);
    $response->assertDontSee('Mallory', false);
});

it('redirects guests away from the referrals page', function () {
    $this->get(route('referrals'))->assertRedirect();
});
