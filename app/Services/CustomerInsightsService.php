<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CustomerInsightsService
{
    /**
     * Get dashboard summary statistics.
     */
    public function getDashboardStats(): array
    {
        $totalCustomers = User::where('role', '!=', 'admin')->count();
        
        $paidOrdersQuery = Order::where('status', 'paid');
        $totalOrders = $paidOrdersQuery->count();
        $totalRevenue = (float) $paidOrdersQuery->sum('total_price_after_discount');

        return [
            'totalCustomers' => $totalCustomers,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'averageSpend' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
        ];
    }

    /**
     * Get detailed customer insights including charts and top items.
     */
    public function getCustomerInsights(User $user): array
    {
        // Get basic metrics
        $metrics = $user->getPurchaseMetrics();

        // Get monthly aggregation for charts (GROUP BY, not loading full objects)
        $monthlyData = $this->getMonthlyChartData($user);

        // Get top products (using raw query with grouping)
        $topProducts = $this->getTopProducts($user, 5);

        // Get top categories (using raw query with grouping)
        $topCategories = $this->getTopCategories($user, 5);

        // Get most frequently purchased product
        $topProductId = DB::table('order_details')
            ->whereIn('order_id', $user->orders()->where('status', 'paid')->pluck('id'))
            ->select('product_id', DB::raw('COUNT(*) as count'))
            ->groupBy('product_id')
            ->orderByDesc('count')
            ->first()?->product_id;

        // Get most frequently purchased category
        $topCategoryId = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereIn('order_details.order_id', $user->orders()->where('status', 'paid')->pluck('id'))
            ->select('products.category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('products.category_id')
            ->orderByDesc('count')
            ->first()?->category_id;

        return [
            'metrics' => array_merge($metrics, [
                'topProduct' => $topProductId ? \App\Models\Product::with('category')->find($topProductId) : null,
                'topCategory' => $topCategoryId ? \App\Models\Category::find($topCategoryId) : null,
            ]),
            'chartData' => $monthlyData,
            'topProducts' => $topProducts,
            'topCategories' => $topCategories,
        ];
    }

    /**
     * Get monthly spending and order count data for charts.
     */
    private function getMonthlyChartData(User $user): array
    {
        // Use raw SQL GROUP BY instead of loading all objects
        $monthlyData = DB::table('orders')
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->select(
                DB::raw("DATE_TRUNC('month', created_at) as month"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_price_after_discount) as total_spent')
            )
            ->groupByRaw("DATE_TRUNC('month', created_at)")
            ->orderByRaw("DATE_TRUNC('month', created_at)")
            ->get();

        $labels = [];
        $spending = [];
        $orderCounts = [];

        foreach ($monthlyData as $item) {
            $labels[] = \Carbon\Carbon::parse($item->month)->format('M Y');
            $spending[] = (float) $item->total_spent;
            $orderCounts[] = (int) $item->order_count;
        }

        return [
            'labels' => $labels,
            'spending' => $spending,
            'orderCounts' => $orderCounts,
        ];
    }

    /**
     * Get top products purchased by customer.
     */
    private function getTopProducts(User $user, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $productIds = DB::table('order_details')
            ->whereIn('order_id', $user->orders()->where('status', 'paid')->pluck('id'))
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->pluck('product_id');

        return \App\Models\Product::with('category')
            ->whereIn('id', $productIds)
            ->get();
    }

    /**
     * Get top categories purchased by customer.
     */
    private function getTopCategories(User $user, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $categoryIds = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereIn('order_details.order_id', $user->orders()->where('status', 'paid')->pluck('id'))
            ->select('products.category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('products.category_id')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('category_id');

        return \App\Models\Category::whereIn('id', $categoryIds)->get();
    }
}
