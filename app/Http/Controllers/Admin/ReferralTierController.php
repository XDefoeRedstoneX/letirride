<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountType;
use App\Models\ReferralTier;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralTierController extends Controller
{
    public function __construct(private readonly ReferralService $referrals) {}

    public function index(): View
    {
        $tiers = ReferralTier::with('discountType')
            ->orderBy('threshold')
            ->get();

        return view('admin.referral-tiers', [
            'tiers' => $tiers,
            'discountTypes' => DiscountType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['is_active'] = $request->boolean('is_active', true);

        $tier = ReferralTier::create($data);

        // New tier: backfill so existing qualifying users get it immediately.
        if ($tier->is_active) {
            $this->referrals->backfillMilestonesForAll();
        }

        return back()->with('success', 'Tier created.');
    }

    public function update(Request $request, ReferralTier $tier): RedirectResponse
    {
        $data = $this->validatedData($request, $tier->id);
        $wasActive = $tier->is_active;
        $data['is_active'] = $request->boolean('is_active', true);

        $tier->update($data);

        // Re-activated or freshly-active tier: backfill outstanding grants.
        if (! $wasActive && $tier->is_active) {
            $this->referrals->backfillMilestonesForAll();
        }

        return back()->with('success', 'Tier updated.');
    }

    public function destroy(ReferralTier $tier): RedirectResponse
    {
        $tier->delete();

        return back()->with('success', 'Tier deleted. Already-paid rewards stay in the audit log.');
    }

    public function backfill(): RedirectResponse
    {
        $count = $this->referrals->backfillMilestonesForAll();

        return back()->with('success', "Backfill complete — {$count} milestone(s) granted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = $ignoreId !== null
            ? 'unique:referral_tiers,threshold,'.$ignoreId
            : 'unique:referral_tiers,threshold';

        return $request->validate([
            'threshold' => 'required|integer|min:1|max:1000|'.$uniqueRule,
            'title' => 'required|string|max:128',
            'description' => 'nullable|string|max:255',
            'points_reward' => 'required|integer|min:0|max:1000000',
            'discount_type_id' => 'nullable|exists:discount_types,id',
            'free_spins_reward' => 'required|integer|min:0|max:1000',
            'icon' => 'nullable|string|max:64',
            'sort_order' => 'nullable|integer|min:0|max:1000',
        ]);
    }
}
