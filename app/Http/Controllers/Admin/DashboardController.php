<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;

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

        [$start, $end] = $this->periodDates($period);

        $periodRevenue   = (float) Order::where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_price_after_discount');

        $periodPaid      = Order::where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $periodAllOrders = Order::whereBetween('created_at', [$start, $end])->count();

        $periodPending   = Order::where('status', 'pending')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $stats = [
            'total_revenue'   => $periodRevenue,
            'total_orders'    => $periodAllOrders,
            'paid_orders'     => $periodPaid,
            'pending_orders'  => $periodPending,
            'avg_order_value' => $periodPaid > 0 ? $periodRevenue / $periodPaid : 0,
            'total_users'     => User::count(),
            'total_products'  => Product::where('is_active', true)->count(),
        ];

        $recentOrders = Order::with(['user', 'orderDetails'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
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

    private function periodDates(string $period): array
    {
        $end = now()->endOfDay();

        return match ($period) {
            'today' => [now()->startOfDay(), $end],
            '30d'   => [now()->subDays(29)->startOfDay(), $end],
            '90d'   => [now()->subDays(89)->startOfDay(), $end],
            default => [now()->subDays(6)->startOfDay(), $end],
        };
    }
}
