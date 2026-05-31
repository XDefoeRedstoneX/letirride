<?php

namespace App\Http\Controllers;

use App\Models\ReferralConfig;
use App\Models\ReferralReward;
use App\Models\ReferralTier;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function __construct(private readonly ReferralService $referrals) {}

    /**
     * Profile widget data: the user's own code, share URL, stats, friend list.
     */
    public function show()
    {
        $user = Auth::user();
        $config = ReferralConfig::current();

        $invited = $user->referrals()
            ->with('referredUser')
            ->orderByDesc('created_at')
            ->get();

        $earnedPoints = (int) ReferralReward::where('recipient_id', $user->id)
            ->whereIn('kind', [
                ReferralReward::KIND_FIRST_PURCHASE,
                ReferralReward::KIND_COMMISSION,
                ReferralReward::KIND_MILESTONE,
            ])
            ->sum('points_amount');

        $rewardedCount = $invited->where('status', 'first_purchase_rewarded')->count();

        $hasPaidOrder = $user->orders()->where('status', 'paid')->exists();
        $canClaim = ! $user->referred_by && ! $hasPaidOrder;

        $referrer = $user->referred_by ? $user->referredBy()->first() : null;

        // Milestone progression
        $tiers = ReferralTier::active()
            ->with('discountType')
            ->orderBy('threshold')
            ->get();

        $grantedTiers = ReferralReward::where('recipient_id', $user->id)
            ->where('kind', ReferralReward::KIND_MILESTONE)
            ->whereNotNull('tier_id')
            ->get()
            ->keyBy('tier_id');

        $paidReferrals = $rewardedCount;
        $nextTier = $tiers->first(fn (ReferralTier $t) => $t->threshold > $paidReferrals && ! $grantedTiers->has($t->id));

        // Stamp last-seen so the navbar pulse clears on visit.
        $user->forceFill(['referrals_last_seen_at' => now()])->save();

        return view('pages.referrals', [
            'user' => $user,
            'config' => $config,
            'invited' => $invited,
            'invitedCount' => $invited->count(),
            'rewardedCount' => $rewardedCount,
            'earnedPoints' => $earnedPoints,
            'canClaim' => $canClaim,
            'referrer' => $referrer,
            'shareUrl' => url('/?ref='.$user->referral_code),
            'tiers' => $tiers,
            'grantedTiers' => $grantedTiers,
            'paidReferrals' => $paidReferrals,
            'nextTier' => $nextTier,
            'unlockedCount' => $grantedTiers->count(),
        ]);
    }

    /**
     * Logged-in user submits a code they got from a friend.
     */
    public function claim(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:16',
        ]);

        $result = $this->referrals->claimCode(Auth::user(), $data['code']);

        return match ($result['status']) {
            ReferralService::CLAIM_OK => response()->json([
                'message' => 'Referral code applied! You received '.($result['points_awarded'] ?? 0).' bonus points.',
                'points_awarded' => $result['points_awarded'] ?? 0,
                'new_balance' => Auth::user()->fresh()->points_balance,
            ]),
            ReferralService::CLAIM_NOT_FOUND => response()->json([
                'message' => 'That referral code doesn\'t exist.',
            ], 422),
            ReferralService::CLAIM_SELF => response()->json([
                'message' => 'You can\'t use your own referral code.',
            ], 422),
            ReferralService::CLAIM_ALREADY_HAS_REFERRER => response()->json([
                'message' => 'You\'ve already used a referral code.',
            ], 422),
            ReferralService::CLAIM_HAS_PAID_ORDER => response()->json([
                'message' => 'Referral codes can only be claimed before your first purchase.',
            ], 422),
            default => response()->json(['message' => 'Could not apply the code.'], 422),
        };
    }
}
