<?php

namespace App\Http\Controllers;

use App\Models\GachaBooster;
use App\Models\GachaHistory;
use App\Models\Order;
use App\Models\PointShopPurchase;
use App\Models\UserActiveBooster;
use App\Models\UserDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointsHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $pageSize = (int) ($request->get('per_page') ?: 10);
        $pageSize = $pageSize > 0 ? min(50, $pageSize) : 10;
        $page = (int) ($request->get('page') ?: 1);
        $page = max(1, $page);

        // Source 1: Product purchases (points earned)
        $productOrders = Order::with(['orderDetails.product'])
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->orderByDesc('created_at')
            ->get();

        $productActivities = $productOrders->map(function (Order $order) {
            // calculateTotalPointsAwarded expects orderDetails + product loaded.
            $points = $order->calculateTotalPointsAwarded();

            // Build a meaningful activity label using the actual purchased product(s).
            // If the order has multiple products, show the first + count.
            $products = $order->orderDetails
                ->map(fn ($d) => $d->product?->name)
                ->filter()
                ->values();

            $activity = 'Order #' . $order->display_noinv;
            if ($products->count() === 1) {
                $activity = 'Purchased ' . $products->first();
            } elseif ($products->count() > 1) {
                $activity = 'Purchased ' . $products->first() . ' +' . ($products->count() - 1) . ' more';
            }

            return [
                'id' => 'order_' . $order->id,
                'date' => $order->created_at,
                'category' => 'Product Purchase',
                // Use actual product label in the Activity column.
                'description' => $activity,
                'points_change' => (int) $points,
                'sign' => 'positive',
            ];
        });

        // Source 2: Point shop redemptions (points spent)
        $shopPurchases = PointShopPurchase::with('pointShopItem')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $shopActivities = $shopPurchases->map(function (PointShopPurchase $purchase) {
            return [
                'id' => 'psp_' . $purchase->id,
                'date' => $purchase->created_at,
                'category' => 'Point Shop Redemption',
                'description' => $purchase->pointShopItem?->name ?? 'Point Shop Item',
                'points_change' => -(int) $purchase->points_spent,
                'sign' => 'negative',
            ];
        });

        // Source 3: Gacha spins
        // - points_spent -> points spent tab (negative)
        // - points_amount on the prize (derived from gachaPool) -> points earned tab (positive)
        $gachaSpins = GachaHistory::with('gachaPool')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $gachaActivities = $gachaSpins->flatMap(function (GachaHistory $spin) {
            $activities = [];

            // Points spent when cost_type=points (points_spent stores the deducted amount)
            $spent = (int) $spin->points_spent;
            if ($spent > 0) {
                $activities[] = [
                    'id' => 'gacha_spent_' . $spin->id,
                    'date' => $spin->created_at,
                    'category' => 'Gacha Spin',
                    'description' => $spin->gachaPool?->prize_name ?? 'Gacha',
                    'points_change' => -$spent,
                    'sign' => 'negative',
                ];
            }

            // Points earned when the gacha reward grants points
            $wonPoints = (int) ($spin->gachaPool?->points_amount ?? 0);
            if ($wonPoints > 0) {
                $rewardName = $spin->gachaPool?->prize_name;
                $activities[] = [
                    'id' => 'gacha_reward_' . $spin->id,
                    'date' => $spin->created_at,
                    'category' => 'Gacha Reward',
                    'description' => 'Gacha Reward: ' . $wonPoints . ' Points' . ($rewardName ? ' (' . $rewardName . ')' : ''),
                    'points_change' => $wonPoints,
                    'sign' => 'positive',
                ];
            }

            return $activities;
        });


        // Source 4: Gacha booster activations (points spent)
        $boosterActivations = UserActiveBooster::with('booster')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $boosterActivities = $boosterActivations->map(function (UserActiveBooster $active) {
            // Points cost is stored on the booster itself (activation-time cost)
            $cost = (int) ($active->booster?->point_cost ?? 0);

            return [
                'id' => 'booster_' . $active->id,
                'date' => $active->created_at,
                'category' => 'Gacha Booster Purchase',
                'description' => $active->booster?->name ?? 'Booster',
                'points_change' => -$cost,
                'sign' => 'negative',
            ];
        });

        $activities = collect()
            ->merge($productActivities)
            ->merge($shopActivities)
            ->merge($gachaActivities)
            ->merge($boosterActivities)
            ->filter(fn ($a) => $a['date'] !== null)
            ->sortByDesc(fn ($a) => $a['date']);

        // Summary totals from the unified dataset
        $totalEarned = (int) $activities->where('points_change', '>', 0)->sum('points_change');
        $totalSpent = (int) abs($activities->where('points_change', '<', 0)->sum('points_change'));

        // Pagination (manual slicing because we combined multiple sources)
        $total = $activities->count();
        $itemsForPage = $activities
            ->slice(($page - 1) * $pageSize, $pageSize)
            ->values();

        $earnedActivities = $itemsForPage->where('points_change', '>', 0)->values();
        $spentActivities = $itemsForPage->where('points_change', '<', 0)->values();

        return view('points-history', [
            'earnedActivities' => $earnedActivities,
            'spentActivities' => $spentActivities,
            'page' => $page,
            'pageSize' => $pageSize,
            'total' => $total,
            'totalEarned' => $totalEarned,
            'totalSpent' => $totalSpent,
            'currentBalance' => (int) $user->points_balance,
        ]);

    }
}

