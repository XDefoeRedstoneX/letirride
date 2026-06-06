<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GachaHistory;
use App\Models\Order;
use App\Models\PointShopPurchase;
use App\Models\User;
use App\Models\UserActiveBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointsHistoryController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = (int) ($request->get('per_page') ?: 10);
        $pageSize = $pageSize > 0 ? min(50, $pageSize) : 10;
        $page = (int) ($request->get('page') ?: 1);
        $page = max(1, $page);

        // NOTE: We build the unified timeline in PHP (multiple sources).

        // Product purchase points earned
        $paidOrders = Order::with(['user', 'orderDetails.product'])
            ->where('status', 'paid')
            ->orderByDesc('created_at')
            ->get();

        $productActivities = $paidOrders->map(function (Order $order) {
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
                'user_name' => $order->user?->name,
                'description' => $activity,
                'category' => 'Product Purchase',
                'points_change' => (int) $points,
            ];
        });

        // Point shop redemptions
        $shopPurchases = PointShopPurchase::with(['user', 'pointShopItem'])
            ->orderByDesc('created_at')
            ->get();

        $shopActivities = $shopPurchases->map(function (PointShopPurchase $purchase) {
            return [
                'id' => 'psp_' . $purchase->id,
                'date' => $purchase->created_at,
                'user_name' => $purchase->user?->name,
                'description' => $purchase->pointShopItem?->name ?? 'Point Shop Item',
                'category' => 'Point Shop Redemption',
                'points_change' => -(int) $purchase->points_spent,
            ];
        });

        // Gacha spins
        $gachaSpins = GachaHistory::with(['user', 'gachaPool'])
            ->orderByDesc('created_at')
            ->get();

        $gachaActivities = $gachaSpins->map(function (GachaHistory $spin) {
            return [
                'id' => 'gacha_' . $spin->id,
                'date' => $spin->created_at,
                'user_name' => $spin->user?->name,
                'description' => $spin->gachaPool?->prize_name ?? 'Gacha',
                'category' => 'Gacha Spin',
                'points_change' => -((int) $spin->points_spent),
            ];
        });

        // Gacha booster activations
        $boosterActivations = UserActiveBooster::with(['user', 'booster'])
            ->orderByDesc('id')
            ->get();

        $boosterActivities = $boosterActivations->map(function (UserActiveBooster $active) {
            $cost = (int) ($active->booster?->point_cost ?? 0);

            return [
                'id' => 'booster_' . $active->id,
                'date' => $active->created_at,
                'user_name' => $active->user?->name,
                'description' => $active->booster?->name ?? 'Booster',
                'category' => 'Gacha Booster Purchase',
                'points_change' => -$cost,
            ];
        });

        $activities = collect()
            ->merge($productActivities)
            ->merge($shopActivities)
            ->merge($gachaActivities)
            ->merge($boosterActivities)
            ->filter(fn ($a) => !empty($a['date']))
            ->sortByDesc(fn ($a) => $a['date']);

        $total = $activities->count();
        $itemsForPage = $activities
            ->slice(($page - 1) * $pageSize, $pageSize)
            ->values();

        // We don't use Laravel LengthAwarePaginator because we built a
        // unified timeline from multiple sources in-memory.
        return view('admin.points-history', [
            'activities' => $itemsForPage,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
        ]);
    }
}

