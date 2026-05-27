<?php

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

it('lets an admin view the referrals page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.referrals'))
        ->assertOk();
});

it('rejects non-admins from the admin referrals page', function () {
    $user = User::factory()->create(['role' => 'buyer']);

    $this->actingAs($user)
        ->get(route('admin.referrals'))
        ->assertForbidden();
});

it('lets an admin update the referral config', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.referrals.config'), [
            'referee_welcome_points' => 250,
            'referrer_first_purchase_pts' => 1000,
            'commission_percent' => 2.5,
            'commission_per_order_cap' => 10000,
            'commission_lifetime_cap' => 100000,
            'referrer_min_account_age_h' => 12,
        ])
        ->assertRedirect();

    $cfg = ReferralConfig::current()->fresh();
    expect($cfg->referee_welcome_points)->toBe(250);
    expect($cfg->referrer_first_purchase_pts)->toBe(1000);
    expect((float) $cfg->commission_percent)->toBe(2.5);
    expect($cfg->referrer_min_account_age_h)->toBe(12);
});

it('lets an admin void a referral', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $referrer = User::factory()->create(['referral_code' => 'R1']);
    $referee = User::factory()->create(['referred_by' => $referrer->id]);
    $referral = Referral::create([
        'referrer_id' => $referrer->id,
        'referred_user_id' => $referee->id,
        'status' => Referral::STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.referrals.void', $referral))
        ->assertRedirect();

    expect($referral->fresh()->status)->toBe(Referral::STATUS_VOID);
});
