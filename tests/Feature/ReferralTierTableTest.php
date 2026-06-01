<?php

use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\ReferralTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function tierRow(int $id, int $threshold, string $title, int $points): ReferralTier
{
    return ReferralTier::create([
        'id' => $id,
        'threshold' => $threshold,
        'title' => $title,
        'description' => $title.' description',
        'points_reward' => $points,
        'discount_type_id' => null,
        'free_spins_reward' => 0,
        'icon' => 'sparkles',
        'sort_order' => $id,
        'is_active' => true,
    ]);
}

it('self-heals: backfills milestones a user already qualified for but never received', function () {
    tierRow(1, 1, 'First Catch', 200);
    tierRow(2, 3, 'Triple Threat', 500);

    $alice = User::factory()->create();
    $bob = User::factory()->create(['referred_by' => $alice->id]);

    // A referral that has already crossed Tier 1's threshold, but no milestone
    // reward row exists yet (mirrors an old account or a manual seed).
    Referral::create([
        'referrer_id' => $alice->id,
        'referred_user_id' => $bob->id,
        'status' => 'first_purchase_rewarded',
        'first_purchase_rewarded_at' => now(),
        'total_commission_paid' => 0,
    ]);

    expect(ReferralReward::where('recipient_id', $alice->id)
        ->where('kind', ReferralReward::KIND_MILESTONE)
        ->count())->toBe(0);

    $this->actingAs($alice)->get(route('referrals'))->assertOk();

    // After visiting the page, the missed Tier 1 reward exists.
    expect(ReferralReward::where('recipient_id', $alice->id)
        ->where('kind', ReferralReward::KIND_MILESTONE)
        ->where('tier_id', 1)
        ->exists())->toBeTrue();
});

it('hides the milestone section entirely when no tiers are configured', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('referrals'));

    $response->assertOk();
    $response->assertDontSee('REWARD MILESTONES', false);
});
