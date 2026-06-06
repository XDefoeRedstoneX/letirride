<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    private const PERIODS = [
        'today' => 'Today',
        '7d'    => 'Last 7 Days',
        '30d'   => 'Last 30 Days',
        '90d'   => 'Last 90 Days',
    ];

    public function index(Request $request)
    {
        $period = array_key_exists($request->get('period', '7d'), self::PERIODS)
            ? $request->get('period', '7d')
            : '7d';

        [$start, $end, $prevStart, $prevEnd] = $this->periodDates($period);

        // ── Current period ──────────────────────────────────────────────
        $revenue     = (float) Order::where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_price_after_discount');
        $paid        = Order::where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])->count();
        $allOrders   = Order::whereBetween('created_at', [$start, $end])->count();
        $pending     = Order::where('status', 'pending')
            ->whereBetween('created_at', [$start, $end])->count();
        $newUsers    = User::whereBetween('created_at', [$start, $end])->count();
        $avgOrderVal = $paid > 0 ? $revenue / $paid : 0;
        $totalProds  = Product::where('is_active', true)->count();

        // ── Previous period (delta comparison) ──────────────────────────
        $prevRevenue  = (float) Order::where('status', 'paid')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total_price_after_discount');
        $prevPaid     = Order::where('status', 'paid')
            ->whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevOrders   = Order::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevPending  = Order::where('status', 'pending')
            ->whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevUsers    = User::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevAvgVal   = $prevPaid > 0 ? $prevRevenue / $prevPaid : 0;

        // ── Sparklines (2 DB queries for all metrics) ───────────────────
        $sparklines = $this->buildSparklines($start, $end);

        // ── KPI cards (left → right = most → least important) ───────────
        $kpiCards = [
            [
                'label'     => 'Total Revenue',
                'value'     => 'Rp ' . number_format($revenue, 0, ',', '.'),
                'delta'     => $this->deltaInfo($revenue, $prevRevenue, false),
                'sparkline' => $sparklines['revenue'],
                'color'     => 'text-green-500',
                'bar'       => 'bg-green-500',
                'icon_bg'   => 'bg-green-500/10',
                'icon_c'    => 'text-green-500',
                'icon'      => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6',
                'footer'    => self::PERIODS[$period],
                'alert'     => false,
                'link'      => route('admin.orders'),
            ],
            [
                'label'     => 'Total Orders',
                'value'     => number_format($allOrders),
                'delta'     => $this->deltaInfo($allOrders, $prevOrders, false),
                'sparkline' => $sparklines['orders'],
                'color'     => 'text-blue-500',
                'bar'       => 'bg-blue-500',
                'icon_bg'   => 'bg-blue-500/10',
                'icon_c'    => 'text-blue-500',
                'icon'      => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8ZM14 2v6h6',
                'footer'    => self::PERIODS[$period],
                'alert'     => false,
                'link'      => route('admin.orders'),
            ],
            [
                'label'     => 'Pending Orders',
                'value'     => number_format($pending),
                'delta'     => $this->deltaInfo($pending, $prevPending, true),
                'sparkline' => $sparklines['pending'],
                'color'     => $pending > 0 ? 'text-amber-500' : 'text-muted-foreground',
                'bar'       => 'bg-amber-500',
                'icon_bg'   => 'bg-amber-500/10',
                'icon_c'    => 'text-amber-500',
                'icon'      => 'M12 8v4l3 3M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z',
                'footer'    => $pending > 0 ? 'Needs review' : 'All clear',
                'alert'     => $pending > 0,
                'link'      => route('admin.orders', ['status' => 'pending']),
            ],
            [
                'label'     => 'Avg Order Value',
                'value'     => 'Rp ' . number_format($avgOrderVal, 0, ',', '.'),
                'delta'     => $this->deltaInfo($avgOrderVal, $prevAvgVal, false),
                'sparkline' => $sparklines['revenue'],
                'color'     => 'text-primary',
                'bar'       => 'bg-primary',
                'icon_bg'   => 'bg-primary/10',
                'icon_c'    => 'text-primary',
                'icon'      => 'M3 3v18h18M7 16l4-4 4 4 4-8',
                'footer'    => 'Paid orders only',
                'alert'     => false,
                'link'      => null,
            ],
            [
                'label'     => 'New Users',
                'value'     => number_format($newUsers),
                'delta'     => $this->deltaInfo($newUsers, $prevUsers, false),
                'sparkline' => $sparklines['users'],
                'color'     => 'text-purple-500',
                'bar'       => 'bg-purple-500',
                'icon_bg'   => 'bg-purple-500/10',
                'icon_c'    => 'text-purple-500',
                'icon'      => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 8 0 4 4 0 0 0-8 0',
                'footer'    => self::PERIODS[$period],
                'alert'     => false,
                'link'      => route('admin.users'),
            ],
            [
                'label'     => 'Active Products',
                'value'     => number_format($totalProds),
                'delta'     => null,
                'sparkline' => null,
                'color'     => 'text-foreground',
                'bar'       => 'bg-foreground',
                'icon_bg'   => 'bg-foreground/10',
                'icon_c'    => 'text-muted-foreground',
                'icon'      => 'm7.5 4.27 9 5.15M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Zm-3.3-7-8.7 5-8.7-5M12 22V12',
                'footer'    => 'Store catalog',
                'alert'     => false,
                'link'      => route('admin.products'),
            ],
        ];

        $stats = [
            'total_revenue'   => $revenue,
            'total_orders'    => $allOrders,
            'paid_orders'     => $paid,
            'pending_orders'  => $pending,
            'avg_order_value' => $avgOrderVal,
            'total_users'     => User::count(),
            'total_products'  => $totalProds,
            'new_users'       => $newUsers,
            'open_tickets'    => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
        ];

        $recentOrders = Order::with(['user', 'orderDetails'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $chartData      = $this->buildChartData($start, $end, $period);
        $supportingData = $this->buildSupportingData($start, $end);

        return view('admin.dashboard', [
            'stats'          => $stats,
            'kpiCards'       => $kpiCards,
            'recentOrders'   => $recentOrders,
            'chartData'      => $chartData,
            'supportingData' => $supportingData,
            'period'       => $period,
            'periods'      => self::PERIODS,
            'periodLabel'  => self::PERIODS[$period],
            'startDate'    => $start,
            'endDate'      => $end,
            'updatedAt'    => now(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $period = array_key_exists($request->get('period', '7d'), self::PERIODS)
            ? $request->get('period', '7d')
            : '7d';

        // Only destructure the first two — periodDates returns 4
        [$start, $end] = $this->periodDates($period);

        $orders = Order::with('user')
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'ridly-orders-' . $period . '-' . now()->format('Ymd') . '.csv';

        return response()->stream(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice', 'Customer', 'Total (Rp)', 'Status', 'Date']);
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->display_noinv,
                    $order->user?->name ?? 'N/A',
                    number_format((float) $order->total_price_after_discount, 0, '.', ''),
                    $order->status,
                    $order->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    private function periodDates(string $period): array
    {
        $end = now()->endOfDay();

        return match ($period) {
            'today' => [
                now()->startOfDay(), $end,
                now()->subDay()->startOfDay(), now()->subDay()->endOfDay(),
            ],
            '30d' => [
                now()->subDays(29)->startOfDay(), $end,
                now()->subDays(59)->startOfDay(), now()->subDays(30)->endOfDay(),
            ],
            '90d' => [
                now()->subDays(89)->startOfDay(), $end,
                now()->subDays(179)->startOfDay(), now()->subDays(90)->endOfDay(),
            ],
            default => [
                now()->subDays(6)->startOfDay(), $end,
                now()->subDays(13)->startOfDay(), now()->subDays(7)->endOfDay(),
            ],
        };
    }

    private function deltaInfo(float|int $current, float|int $previous, bool $invertColors): array
    {
        if ($previous == 0 && $current == 0) {
            return ['label' => '—', 'type' => 'neutral'];
        }
        if ($previous == 0) {
            return ['label' => 'New', 'type' => $invertColors ? 'down' : 'up'];
        }

        $pct  = round((($current - $previous) / abs($previous)) * 100, 1);
        $sign = $pct > 0 ? '+' : '';

        $type = match (true) {
            $pct > 0  => $invertColors ? 'down' : 'up',
            $pct < 0  => $invertColors ? 'up'   : 'down',
            default   => 'neutral',
        };

        return ['label' => $sign . number_format($pct, 1) . '%', 'type' => $type];
    }

    private function buildChartData(Carbon $start, Carbon $end, string $period): array
    {
        $isToday = $period === 'today';
        $labels  = [];

        if ($isToday) {
            $slots = array_fill(0, 24, ['revenue' => 0.0, 'orders' => 0, 'users' => 0]);
            for ($h = 0; $h < 24; $h++) {
                $labels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            }

            foreach (
                Order::selectRaw(
                    "HOUR(created_at) as h,
                     SUM(CASE WHEN status = 'paid' THEN total_price_after_discount ELSE 0 END) as revenue,
                     COUNT(*) as orders"
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupByRaw('HOUR(created_at)')
                ->get() as $row
            ) {
                $slots[(int) $row->h]['revenue'] = (float) $row->revenue;
                $slots[(int) $row->h]['orders']  = (int)   $row->orders;
            }

            foreach (
                User::selectRaw("HOUR(created_at) as h, COUNT(*) as cnt")
                    ->whereBetween('created_at', [$start, $end])
                    ->groupByRaw('HOUR(created_at)')
                    ->get() as $row
            ) {
                $slots[(int) $row->h]['users'] = (int) $row->cnt;
            }

            $revenueData = array_column($slots, 'revenue');
            $ordersData  = array_column($slots, 'orders');
            $usersData   = array_column($slots, 'users');
        } else {
            $days   = [];
            $cursor = $start->copy()->startOfDay();
            while ($cursor <= $end) {
                $key        = $cursor->format('Y-m-d');
                $days[$key] = ['revenue' => 0.0, 'orders' => 0, 'users' => 0];
                $labels[]   = $cursor->format('M d');
                $cursor->addDay();
            }

            foreach (
                Order::selectRaw(
                    "DATE(created_at) as d,
                     SUM(CASE WHEN status = 'paid' THEN total_price_after_discount ELSE 0 END) as revenue,
                     COUNT(*) as orders"
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupByRaw('DATE(created_at)')
                ->get() as $row
            ) {
                if (isset($days[$row->d])) {
                    $days[$row->d]['revenue'] = (float) $row->revenue;
                    $days[$row->d]['orders']  = (int)   $row->orders;
                }
            }

            foreach (
                User::selectRaw("DATE(created_at) as d, COUNT(*) as cnt")
                    ->whereBetween('created_at', [$start, $end])
                    ->groupByRaw('DATE(created_at)')
                    ->get() as $row
            ) {
                if (isset($days[$row->d])) {
                    $days[$row->d]['users'] = (int) $row->cnt;
                }
            }

            $revenueData = array_column(array_values($days), 'revenue');
            $ordersData  = array_column(array_values($days), 'orders');
            $usersData   = array_column(array_values($days), 'users');
        }

        $statusCounts = Order::selectRaw('status, COUNT(*) as cnt')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        return [
            'labels'  => $labels,
            'revenue' => $revenueData,
            'orders'  => $ordersData,
            'users'   => $usersData,
            'status'  => [
                'paid'    => (int) ($statusCounts['paid']      ?? 0),
                'pending' => (int) ($statusCounts['pending']   ?? 0),
                'failed'  => (int) ($statusCounts['failed']    ?? 0)
                           + (int) ($statusCounts['cancelled'] ?? 0)
                           + (int) ($statusCounts['expired']   ?? 0),
            ],
        ];
    }

    private function buildSupportingData(Carbon $start, Carbon $end): array
    {
        $topProducts = DB::table('order_details as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('products as p', 'p.id', '=', 'od.product_id')
            ->selectRaw('p.name, SUM(od.total_price_in_cart) as revenue, SUM(od.quantity) as qty')
            ->where('o.status', 'paid')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('revenue')
            ->limit(10) // up to 10 so the dashboard "Show Top N" selector (3/5/10) has data
            ->get()
            ->map(fn ($r) => [
                'name'    => Str::limit($r->name, 22, '…'),
                'revenue' => (float) $r->revenue,
                'qty'     => (int)   $r->qty,
            ])
            ->values()
            ->toArray();

        $categoryRevenue = DB::table('order_details as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('products as p', 'p.id', '=', 'od.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->selectRaw('c.name, SUM(od.total_price_in_cart) as revenue')
            ->where('o.status', 'paid')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'revenue' => (float) $r->revenue])
            ->values()
            ->toArray();

        // ── Point Usage: Gacha vs Point Shop ────────────────────────────
        $gachaPoints = Schema::hasTable('gacha_histories')
            ? (int) DB::table('gacha_histories')
                ->whereBetween('created_at', [$start, $end])
                ->sum('points_spent')
            : 0;

        $shopPoints = Schema::hasTable('point_shop_purchases')
            ? (int) DB::table('point_shop_purchases')
                ->whereBetween('created_at', [$start, $end])
                ->sum('points_spent')
            : 0;

        // ── Gacha Economy ───────────────────────────────────────────────
        $gachaEconomy = ['spins' => 0, 'burned' => 0, 'pity_count' => 0, 'top_prize' => null];
        if (Schema::hasTable('gacha_histories')) {
            $gachaStats = DB::table('gacha_histories')
                ->selectRaw('COUNT(*) as spins, COALESCE(SUM(points_spent), 0) as burned, SUM(CASE WHEN pity_triggered = 1 THEN 1 ELSE 0 END) as pity_count')
                ->whereBetween('created_at', [$start, $end])
                ->first();

            $gachaEconomy['spins']      = (int) $gachaStats->spins;
            $gachaEconomy['burned']     = (int) $gachaStats->burned;
            $gachaEconomy['pity_count'] = (int) $gachaStats->pity_count;

            if (Schema::hasTable('gacha_pools')) {
                $topPrize = DB::table('gacha_histories as gh')
                    ->join('gacha_pools as gp', 'gp.id', '=', 'gh.gacha_pool_id')
                    ->selectRaw('gp.prize_name, COUNT(*) as cnt')
                    ->whereBetween('gh.created_at', [$start, $end])
                    ->groupBy('gp.id', 'gp.prize_name')
                    ->orderByDesc('cnt')
                    ->first();

                $gachaEconomy['top_prize'] = $topPrize?->prize_name;
            }
        }

        // ── Low Stock Alert ─────────────────────────────────────────────
        $lowStock = [];
        if (Schema::hasTable('product_keys')) {
            $lowStock = DB::table('products as p')
                ->leftJoin('product_keys as pk', function ($join) {
                    $join->on('pk.product_id', '=', 'p.id')
                        ->where('pk.status', '=', 'available');
                })
                ->selectRaw('p.id, p.name, COUNT(pk.id) as available_keys')
                ->where('p.is_active', true)
                ->groupBy('p.id', 'p.name')
                ->havingRaw('COUNT(pk.id) <= 5')
                ->orderBy('available_keys')
                ->limit(8)
                ->get()
                ->map(fn ($r) => [
                    'name'           => Str::limit($r->name, 28, '…'),
                    'available_keys' => (int) $r->available_keys,
                ])
                ->values()
                ->toArray();
        }

        return [
            'topProducts'     => $topProducts,
            'categoryRevenue' => $categoryRevenue,
            'pointUsage'      => ['gacha' => $gachaPoints, 'shop' => $shopPoints],
            'gachaEconomy'    => $gachaEconomy,
            'lowStock'        => $lowStock,
        ];
    }

    private function buildSparklines(Carbon $start, Carbon $end): array
    {
        $buckets = 7;
        $isToday = $start->isSameDay($end);

        if ($isToday) {
            // Hourly slots for today (24 slots → bucketed to 7)
            $slots = array_fill(0, 24, ['revenue' => 0.0, 'orders' => 0, 'pending' => 0, 'users' => 0]);

            foreach (
                Order::selectRaw(
                    "HOUR(created_at) as h,
                     SUM(CASE WHEN status = 'paid' THEN total_price_after_discount ELSE 0 END) as revenue,
                     COUNT(*) as orders,
                     SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupByRaw('HOUR(created_at)')
                ->get() as $row
            ) {
                $h = (int) $row->h;
                $slots[$h]['revenue'] = (float) $row->revenue;
                $slots[$h]['orders']  = (int)   $row->orders;
                $slots[$h]['pending'] = (int)   $row->pending;
            }

            foreach (
                User::selectRaw("HOUR(created_at) as h, COUNT(*) as cnt")
                    ->whereBetween('created_at', [$start, $end])
                    ->groupByRaw('HOUR(created_at)')
                    ->get() as $row
            ) {
                $slots[(int) $row->h]['users'] = (int) $row->cnt;
            }

            $values = $slots;
        } else {
            // Daily slots, keyed by date string
            $days   = [];
            $cursor = $start->copy()->startOfDay();
            while ($cursor <= $end) {
                $days[$cursor->format('Y-m-d')] = ['revenue' => 0.0, 'orders' => 0, 'pending' => 0, 'users' => 0];
                $cursor->addDay();
            }

            foreach (
                Order::selectRaw(
                    "DATE(created_at) as d,
                     SUM(CASE WHEN status = 'paid' THEN total_price_after_discount ELSE 0 END) as revenue,
                     COUNT(*) as orders,
                     SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupByRaw('DATE(created_at)')
                ->get() as $row
            ) {
                if (isset($days[$row->d])) {
                    $days[$row->d]['revenue'] = (float) $row->revenue;
                    $days[$row->d]['orders']  = (int)   $row->orders;
                    $days[$row->d]['pending'] = (int)   $row->pending;
                }
            }

            foreach (
                User::selectRaw("DATE(created_at) as d, COUNT(*) as cnt")
                    ->whereBetween('created_at', [$start, $end])
                    ->groupByRaw('DATE(created_at)')
                    ->get() as $row
            ) {
                if (isset($days[$row->d])) {
                    $days[$row->d]['users'] = (int) $row->cnt;
                }
            }

            $values = array_values($days);
        }

        // Fold all slots into 7 equal-width buckets
        $total  = count($values);
        $result = ['revenue' => [], 'orders' => [], 'pending' => [], 'users' => []];

        for ($i = 0; $i < $buckets; $i++) {
            $bStart = (int) floor($i * $total / $buckets);
            $bEnd   = (int) floor(($i + 1) * $total / $buckets);
            $slice  = array_slice($values, $bStart, max(1, $bEnd - $bStart));

            $result['revenue'][] = array_sum(array_column($slice, 'revenue'));
            $result['orders'][]  = array_sum(array_column($slice, 'orders'));
            $result['pending'][] = array_sum(array_column($slice, 'pending'));
            $result['users'][]   = array_sum(array_column($slice, 'users'));
        }

        return $result;
    }
}
