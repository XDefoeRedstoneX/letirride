<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralConfig;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $config = ReferralConfig::current();

        $leaderboard = User::query()
            ->select('users.id', 'users.name', 'users.referral_code')
            ->selectSub(function ($q) {
                $q->from('referrals')
                    ->whereColumn('referrals.referrer_id', 'users.id')
                    ->selectRaw('COUNT(*)');
            }, 'referrals_count')
            ->selectSub(function ($q) {
                $q->from('referrals')
                    ->whereColumn('referrals.referrer_id', 'users.id')
                    ->where('referrals.status', Referral::STATUS_FIRST_PURCHASE_REWARDED)
                    ->selectRaw('COUNT(*)');
            }, 'rewarded_count')
            ->selectSub(function ($q) {
                $q->from('referral_rewards')
                    ->whereColumn('referral_rewards.recipient_id', 'users.id')
                    ->whereIn('referral_rewards.kind', [ReferralReward::KIND_FIRST_PURCHASE, ReferralReward::KIND_COMMISSION])
                    ->selectRaw('COALESCE(SUM(points_amount), 0)');
            }, 'points_earned')
            ->orderByDesc('points_earned')
            ->orderByDesc('referrals_count')
            ->limit(20)
            ->get();

        $referrals = Referral::with(['referrer', 'referredUser', 'firstPurchaseOrder'])
            ->orderByDesc('created_at')
            ->paginate(25);

        $totals = [
            'total_referrals' => Referral::count(),
            'rewarded' => Referral::where('status', Referral::STATUS_FIRST_PURCHASE_REWARDED)->count(),
            'pending' => Referral::where('status', Referral::STATUS_PENDING)->count(),
            'void' => Referral::where('status', Referral::STATUS_VOID)->count(),
            'total_points_paid' => (int) ReferralReward::sum('points_amount'),
        ];

        return view('admin.referrals', [
            'config' => $config,
            'leaderboard' => $leaderboard,
            'referrals' => $referrals,
            'totals' => $totals,
        ]);
    }

    public function updateConfig(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'referee_welcome_points' => 'required|integer|min:0|max:100000',
            'referrer_first_purchase_pts' => 'required|integer|min:0|max:100000',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'commission_per_order_cap' => 'required|integer|min:0|max:1000000',
            'commission_lifetime_cap' => 'required|integer|min:0|max:10000000',
            'referrer_min_account_age_h' => 'required|integer|min:0|max:8760',
        ]);

        DB::transaction(function () use ($data) {
            $config = ReferralConfig::current();
            $config->forceFill($data)->save();
        });

        return back()->with('success', 'Referral config updated.');
    }

    public function void(Referral $referral): RedirectResponse
    {
        if ($referral->status !== Referral::STATUS_VOID) {
            $referral->forceFill(['status' => Referral::STATUS_VOID])->save();
        }

        return back()->with('success', 'Referral marked as void. Future commission payouts will be skipped.');
    }
}
