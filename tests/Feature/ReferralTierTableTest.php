<?php

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

it('renders the milestone checkmark table when tiers exist', function () {
    tierRow(1, 1, 'First Catch', 200);
    tierRow(2, 3, 'Triple Threat', 500);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('referrals'));

    $response->assertOk();
    $response->assertSee('REWARD MILESTONES', false);
    $response->assertSee('referral-tier-table', false);
    $response->assertSee('First Catch', false);
    $response->assertSee('Triple Threat', false);
});

it('hides the milestone section entirely when no tiers are configured', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('referrals'));

    $response->assertOk();
    $response->assertDontSee('referral-tier-table', false);
});
