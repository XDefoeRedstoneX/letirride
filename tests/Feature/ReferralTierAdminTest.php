<?php

use App\Models\Referral;
use App\Models\ReferralConfig;
use App\Models\ReferralReward;
use App\Models\ReferralTier;
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

it('lets an admin view the tier page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.referral-tiers'))
        ->assertOk()
        ->assertSee('Tier Rewards', false)->assertSee('Tiers', false);
});

it('rejects non-admins from the tier page', function () {
    $user = User::factory()->create(['role' => 'buyer']);

    $this->actingAs($user)
        ->get(route('admin.referral-tiers'))
        ->assertForbidden();
});

it('lets an admin create a tier and backfills qualifying users immediately', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Two referrers who already qualify for a threshold=2 tier.
    $referrer = User::factory()->create(['points_balance' => 0]);
    foreach (range(1, 2) as $i) {
        $referee = User::factory()->create(['referred_by' => $referrer->id]);
        Referral::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referee->id,
            'status' => Referral::STATUS_FIRST_PURCHASE_REWARDED,
            'first_purchase_rewarded_at' => now(),
        ]);
    }

    $this->actingAs($admin)
        ->post(route('admin.referral-tiers.store'), [
            'threshold' => 2,
            'title' => 'Pair Up',
            'description' => 'Two friends in',
            'points_reward' => 350,
            'discount_type_id' => null,
            'free_spins_reward' => 0,
            'icon' => null,
            'sort_order' => 0,
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(ReferralTier::where('threshold', 2)->exists())->toBeTrue();
    // Backfill should have already paid out.
    expect($referrer->fresh()->points_balance)->toBe(350);
    expect(ReferralReward::where('recipient_id', $referrer->id)->where('kind', 'milestone')->count())->toBe(1);
});

it('lets an admin update a tier', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $tier = ReferralTier::create([
        'threshold' => 5,
        'title' => 'Old Title',
        'points_reward' => 100,
        'free_spins_reward' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.referral-tiers.update', $tier), [
            'threshold' => 5,
            'title' => 'New Title',
            'description' => 'Updated',
            'points_reward' => 999,
            'discount_type_id' => null,
            'free_spins_reward' => 2,
            'icon' => 'star',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect();

    expect($tier->fresh()->title)->toBe('New Title');
    expect($tier->fresh()->points_reward)->toBe(999);
});

it('rejects two tiers at the same threshold', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ReferralTier::create([
        'threshold' => 3,
        'title' => 'Existing',
        'points_reward' => 100,
        'free_spins_reward' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.referral-tiers.store'), [
            'threshold' => 3,
            'title' => 'Duplicate',
            'points_reward' => 100,
            'discount_type_id' => null,
            'free_spins_reward' => 0,
            'sort_order' => 0,
            'is_active' => true,
        ])
        ->assertSessionHasErrors('threshold');
});

it('lets an admin delete a tier without clawing back paid rewards', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $recipient = User::factory()->create(['points_balance' => 500]);
    $tier = ReferralTier::create([
        'threshold' => 1,
        'title' => 'About to delete',
        'points_reward' => 500,
        'free_spins_reward' => 0,
        'is_active' => true,
    ]);
    ReferralReward::create([
        'recipient_id' => $recipient->id,
        'tier_id' => $tier->id,
        'kind' => 'milestone',
        'points_amount' => 500,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.referral-tiers.destroy', $tier))
        ->assertRedirect();

    expect(ReferralTier::find($tier->id))->toBeNull();
    // Reward row stays, tier_id is now null (nullOnDelete).
    expect(ReferralReward::where('recipient_id', $recipient->id)->where('kind', 'milestone')->count())->toBe(1);
    expect($recipient->fresh()->points_balance)->toBe(500);
});

it('lets an admin trigger a manual backfill', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $referrer = User::factory()->create(['points_balance' => 0]);
    $referee = User::factory()->create(['referred_by' => $referrer->id]);
    Referral::create([
        'referrer_id' => $referrer->id,
        'referred_user_id' => $referee->id,
        'status' => Referral::STATUS_FIRST_PURCHASE_REWARDED,
        'first_purchase_rewarded_at' => now(),
    ]);
    ReferralTier::create([
        'threshold' => 1,
        'title' => 'Manual',
        'points_reward' => 750,
        'free_spins_reward' => 0,
        'is_active' => true,
    ]);

    // Don't auto-backfill: manually call the backfill endpoint instead.
    $this->actingAs($admin)
        ->post(route('admin.referral-tiers.backfill'))
        ->assertRedirect();

    expect($referrer->fresh()->points_balance)->toBe(750);
});
