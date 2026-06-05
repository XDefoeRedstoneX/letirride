<?php

use App\Models\Referral;
use App\Models\User;
use App\Support\ReferralLink;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \App\Models\ReferralConfig::create([
        'id' => 1,
        'referee_welcome_points' => 100,
        'referrer_first_purchase_pts' => 500,
        'commission_percent' => 0,
        'commission_per_order_cap' => 5000,
        'commission_lifetime_cap' => 50000,
        'referrer_min_account_age_h' => 0,
    ]);
});

it('instantly redeems the code when a logged-in user opens a share link', function () {
    $referrer = User::factory()->create(['referral_code' => 'ALICECODE', 'points_balance' => 0]);
    $referee = User::factory()->create(['points_balance' => 0]);

    $response = $this->actingAs($referee)->get('/?ref=ALICECODE');

    // Redirects to the clean URL (no ?ref) so a refresh can't re-claim.
    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain('ref=');

    expect($referee->fresh()->referred_by)->toBe($referrer->id);
    expect($referee->fresh()->points_balance)->toBe(100);
    expect(Referral::where('referrer_id', $referrer->id)
        ->where('referred_user_id', $referee->id)->count())->toBe(1);
});

it('does not double-claim when a logged-in user reopens the same link', function () {
    $referrer = User::factory()->create(['referral_code' => 'ALICECODE', 'points_balance' => 0]);
    $referee = User::factory()->create(['points_balance' => 0]);

    $this->actingAs($referee)->get('/?ref=ALICECODE');
    $this->actingAs($referee)->get('/?ref=ALICECODE');

    expect($referee->fresh()->points_balance)->toBe(100);
    expect(Referral::where('referred_user_id', $referee->id)->count())->toBe(1);
});

it('stashes the code for a guest and redeems it on login', function () {
    $referrer = User::factory()->create(['referral_code' => 'ALICECODE', 'points_balance' => 0]);
    $referee = User::factory()->create([
        'points_balance' => 0,
        'password' => bcrypt('secret123'),
    ]);

    // Guest opens the link: code is stashed, no claim yet.
    $this->get('/?ref=ALICECODE')->assertOk();
    $this->assertEquals('ALICECODE', session(ReferralLink::SESSION_KEY));
    expect($referee->fresh()->referred_by)->toBeNull();

    // They log in → the pending code is claimed automatically.
    $this->postJson(route('logAuth'), [
        'email' => $referee->email,
        'password' => 'secret123',
    ])->assertOk();

    expect($referee->fresh()->referred_by)->toBe($referrer->id);
    expect($referee->fresh()->points_balance)->toBe(100);
    expect(session(ReferralLink::SESSION_KEY))->toBeNull();
});

it('redeems a stashed code when a guest registers without typing it', function () {
    $referrer = User::factory()->create(['referral_code' => 'ALICECODE', 'points_balance' => 0]);

    $this->get('/?ref=ALICECODE');

    $this->postJson(route('regAuth'), [
        'name' => 'New Friend',
        'email' => 'newfriend@gmail.com',
        'password' => 'secret123',
    ])->assertOk();

    $referee = User::where('email', 'newfriend@gmail.com')->firstOrFail();
    expect($referee->referred_by)->toBe($referrer->id);
    expect($referee->points_balance)->toBe(100);
});

it('does not let a user claim their own share link', function () {
    $user = User::factory()->create(['referral_code' => 'MINE', 'points_balance' => 0]);

    $this->actingAs($user)->get('/?ref=MINE');

    expect($user->fresh()->referred_by)->toBeNull();
    expect($user->fresh()->points_balance)->toBe(0);
});
