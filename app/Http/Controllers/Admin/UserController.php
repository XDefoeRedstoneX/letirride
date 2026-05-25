<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('orders')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.users', ['users' => $users]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:buyer,admin',
            'points_balance' => 'required|integer|min:0',
        ]);

        $user->update([
            'role' => $request->role,
            'points_balance' => $request->points_balance,
        ]);

        return back()->with('success', $user->name . ' updated.');
    }

    /**
     * Return JSON data for the Insights modal.
     */
    public function insights(User $user): JsonResponse
    {
        $paidOrdersBaseQuery = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'paid');

        $totalOrders = (int) (clone $paidOrdersBaseQuery)->count();
        $totalSpent = (float) (clone $paidOrdersBaseQuery)->sum('total_price_after_discount');
        $averageOrderValue = $totalOrders > 0 ? ($totalSpent / $totalOrders) : 0.0;

        $lastPurchaseAt = (clone $paidOrdersBaseQuery)
            ->orderByDesc('created_at')
            ->value('created_at');

        // Most purchased product (by quantity)
        $topProductAgg = OrderDetail::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', 'paid')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(1)
            ->first();

        $topProduct = null;
        if ($topProductAgg?->product_id) {
            $topProduct = Product::query()
                ->with('category')
                ->find($topProductAgg->product_id);
        }

        // Most purchased category
        $topCategoryAgg = OrderDetail::query()
            ->select('products.category_id', DB::raw('SUM(order_details.quantity) as total_qty'))
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', 'paid')
            ->groupBy('products.category_id')
            ->orderByDesc('total_qty')
            ->limit(1)
            ->first();

        $topCategory = null;
        if ($topCategoryAgg?->category_id) {
            $topCategory = Category::query()->find($topCategoryAgg->category_id);
        }

        // Charts: monthly spending + monthly order count
        // Note: uses DATE_TRUNC which is Postgres-compatible. Matches existing service usage.
        $monthlyRows = DB::table('orders')
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->selectRaw('DATE_TRUNC(\'month\', created_at) as month')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(total_price_after_discount) as total_spent')
            ->groupByRaw('DATE_TRUNC(\'month\', created_at)')
            ->orderByRaw('DATE_TRUNC(\'month\', created_at)')
            ->get();

        $labels = [];
        $monthlySpending = [];
        $monthlyOrderCounts = [];
        foreach ($monthlyRows as $row) {
            $labels[] = Carbon::parse($row->month)->format('M Y');
            $monthlySpending[] = (float) $row->total_spent;
            $monthlyOrderCounts[] = (int) $row->order_count;
        }

        // Top 5 products
        $topProductRows = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', 'paid')
            ->select('order_details.product_id')
            ->selectRaw('SUM(order_details.quantity) as total_qty')
            ->groupBy('order_details.product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $topProductIds = $topProductRows->pluck('product_id')->all();
        $topProducts = [];
        if (!empty($topProductIds)) {
            $products = Product::query()
                ->with('category')
                ->whereIn('id', $topProductIds)
                ->get()
                ->keyBy('id');

            foreach ($topProductRows as $r) {
                $p = $products->get($r->product_id);
                if ($p) {
                    $topProducts[] = [
                        'id' => $p->id,
                        'name' => $p->name,
                        'category' => [
                            'id' => $p->category?->id,
                            'name' => $p->category?->name,
                        ],
                    ];
                }
            }
        }

        // Top 5 categories
        $topCategoryRows = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', 'paid')
            ->select('products.category_id')
            ->selectRaw('SUM(order_details.quantity) as total_qty')
            ->groupBy('products.category_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $topCategoryIds = $topCategoryRows->pluck('category_id')->all();
        $topCategories = [];
        if (!empty($topCategoryIds)) {
            $categories = Category::query()
                ->whereIn('id', $topCategoryIds)
                ->get()
                ->keyBy('id');

            foreach ($topCategoryRows as $r) {
                $c = $categories->get($r->category_id);
                if ($c) {
                    $topCategories[] = [
                        'id' => $c->id,
                        'name' => $c->name,
                    ];
                }
            }
        }

        // Recent 10 paid order history
        $recentOrders = (clone $paidOrdersBaseQuery)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'noinv', 'created_at', 'status', 'total_price_after_discount']);

        $recentOrdersPayload = $recentOrders->map(function (Order $o) {
            return [
                'invoice' => $o->display_noinv,
                'date' => optional($o->created_at)->format('M d, Y'),
                'status' => $o->status,
                'total' => (float) $o->total_price_after_discount,
            ];
        })->values();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'registrationDate' => optional($user->created_at)->format('M d, Y'),
                'referralCode' => $user->referral_code,
                'pointsBalance' => (int) $user->points_balance,
            ],
            'overview' => [
                'totalOrders' => $totalOrders,
                'totalSpent' => (float) $totalSpent,
                'averageOrderValue' => (float) $averageOrderValue,
                'lastPurchaseDate' => $lastPurchaseAt ? Carbon::parse($lastPurchaseAt)->format('M d, Y') : null,
                'topProduct' => $topProduct ? [
                    'id' => $topProduct->id,
                    'name' => $topProduct->name,
                    'categoryName' => $topProduct->category?->name,
                ] : null,
                'topCategory' => $topCategory ? [
                    'id' => $topCategory->id,
                    'name' => $topCategory->name,
                ] : null,
            ],
            'spendingAnalytics' => [
                'monthlySpending' => $monthlySpending,
                'monthlyOrderCounts' => $monthlyOrderCounts,
                'labels' => $labels,
            ],
            'topPurchases' => [
                'products' => $topProducts,
                'categories' => $topCategories,
            ],
            'recentOrders' => $recentOrdersPayload,
        ]);
    }
}

