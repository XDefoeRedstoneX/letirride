@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">

    {{-- ================================================================ --}}
    {{-- PHASE 1: HEADER / CONTEXT                                        --}}
    {{-- ================================================================ --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden"
         x-data="dashboardHeader({{ $updatedAt->timestamp }})">

        {{-- Row 1: Title + Admin Identity --}}
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 px-6 py-5">

            <div>
                {{-- Breadcrumb --}}
                <div class="flex items-center gap-1.5 mb-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/50 shrink-0"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Ridly Admin</span>
                    <span class="text-muted-foreground/30 select-none">/</span>
                    <span class="text-[9px] font-black uppercase tracking-widest">Dashboard</span>
                </div>

                {{-- Main title --}}
                <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase leading-none">
                    Dashboard
                </h1>

                {{-- Date range + period badge --}}
                <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 mt-2">
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                        @if($period === 'today')
                            {{ $startDate->format('F d, Y') }}
                        @else
                            {{ $startDate->format('M d') }} – {{ $endDate->format('M d, Y') }}
                        @endif
                    </p>
                    <span class="text-muted-foreground/25 select-none hidden sm:inline">·</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md border text-[8px] font-black uppercase tracking-widest bg-primary/10 text-primary border-primary/25">
                        {{ $periodLabel }}
                    </span>
                    @if($stats['pending_orders'] > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border text-[8px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-500 border-amber-500/25">
                            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                            {{ $stats['pending_orders'] }} pending
                        </span>
                    @endif
                </div>
            </div>

            {{-- Admin Identity --}}
            <div class="flex items-center gap-3 shrink-0">
                <div class="text-right hidden sm:block">
                    <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Signed in as</p>
                    <p class="text-sm font-black leading-snug">{{ Auth::user()->name }}</p>
                    <p class="text-[9px] font-black text-primary uppercase tracking-widest">Administrator</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-foreground/10 overflow-hidden ring-2 ring-primary/20 flex items-center justify-center text-sm font-black uppercase shrink-0">
                    @if(Auth::user()->profile_picture)
                        <img src="{{ Auth::user()->avatar_url }}" class="w-full h-full object-cover" alt="Avatar" />
                    @else
                        {{ substr(Auth::user()->name, 0, 2) }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Row 2: Period Filter Bar + Controls --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-3 border-t border-border bg-foreground/[0.015]">

            {{-- Period Toggle Pills --}}
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest mr-1 hidden sm:inline">
                    Period
                </span>
                @foreach($periods as $key => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $key]) }}"
                       class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-all duration-100 border
                              {{ $period === $key
                                  ? 'bg-primary/15 text-primary border-primary/30'
                                  : 'bg-foreground/5 text-muted-foreground border-transparent hover:bg-foreground/10 hover:text-foreground hover:border-border' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Right Controls --}}
            <div class="flex items-center gap-2 flex-wrap">

                {{-- Live "last updated" ticker --}}
                <span x-text="timeAgo"
                      class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline whitespace-nowrap">
                    ↻ Updated just now
                </span>

                {{-- Divider --}}
                <span class="w-px h-4 bg-border hidden sm:inline-block"></span>

                {{-- Refresh button --}}
                <a href="{{ request()->fullUrl() }}"
                   title="Refresh data"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-transparent text-[9px] font-black uppercase tracking-widest text-muted-foreground bg-foreground/5 hover:bg-foreground/10 hover:text-foreground hover:border-border transition-all duration-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    Refresh
                </a>

                {{-- Export CSV --}}
                <a href="{{ route('admin.dashboard.export', ['period' => $period]) }}"
                   title="Download orders as CSV"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-transparent text-[9px] font-black uppercase tracking-widest text-muted-foreground bg-foreground/5 hover:bg-primary/10 hover:text-primary hover:border-primary/20 transition-all duration-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Export CSV
                </a>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- KPI STATS (period-filtered, layout unchanged until Phase 2)      --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        @php
            $statCards = [
                [
                    'label' => 'Total Revenue',
                    'value' => 'Rp ' . number_format($stats['total_revenue'], 0, ',', '.'),
                    'icon'  => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6',
                    'color' => 'text-green-500',
                ],
                [
                    'label' => 'Total Orders',
                    'value' => $stats['total_orders'],
                    'icon'  => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6',
                    'color' => 'text-blue-500',
                ],
                [
                    'label' => 'Paid Orders',
                    'value' => $stats['paid_orders'],
                    'icon'  => 'M22 11.08V12a10 10 0 1 1-5.93-9.14 M22 4 12 14.01l-3-3',
                    'color' => 'text-emerald-500',
                ],
                [
                    'label' => 'Pending',
                    'value' => $stats['pending_orders'],
                    'icon'  => 'M12 8v4l3 3 M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z',
                    'color' => 'text-amber-500',
                ],
                [
                    'label' => 'Total Users',
                    'value' => $stats['total_users'],
                    'icon'  => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M22 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75',
                    'color' => 'text-purple-500',
                ],
                [
                    'label' => 'Avg Order Value',
                    'value' => 'Rp ' . number_format($stats['avg_order_value'], 0, ',', '.'),
                    'icon'  => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6',
                    'color' => 'text-primary',
                ],
            ];
        @endphp

        @foreach($statCards as $card)
            <div class="bg-card border border-border rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">{{ $card['label'] }}</p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-40 shrink-0"><path d="{{ $card['icon'] }}"/></svg>
                </div>
                <p class="text-2xl font-black {{ $card['color'] }}">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ================================================================ --}}
    {{-- RECENT ORDERS + QUICK ACTIONS (period-filtered)                  --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Recent Orders Table --}}
        <div class="lg:col-span-2 bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                    Recent Orders
                </h3>
                <div class="flex items-center gap-3">
                    <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">{{ $periodLabel }}</span>
                    <a href="{{ route('admin.orders') }}" class="text-[10px] font-black text-primary uppercase tracking-widest hover:underline">View All</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                            <th class="px-6 py-3">Invoice</th>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3">Items</th>
                            <th class="px-6 py-3">Total</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-foreground/5 transition-colors">
                                <td class="px-6 py-3 text-xs font-mono font-bold">{{ $order->display_noinv }}</td>
                                <td class="px-6 py-3 text-xs font-bold">{{ $order->user?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-3 text-xs text-muted-foreground">{{ $order->orderDetails->count() }} item(s)</td>
                                <td class="px-6 py-3 text-xs font-bold">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest
                                        @if($order->status === 'paid') bg-green-500/10 text-green-500
                                        @elseif($order->status === 'pending') bg-amber-500/10 text-amber-500
                                        @else bg-red-500/10 text-red-500
                                        @endif">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-[10px] text-muted-foreground whitespace-nowrap">{{ $order->created_at?->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                                    No orders in this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Actions + Attention --}}
        <div class="bg-card border border-border rounded-2xl p-6 space-y-5">
            <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Quick Actions
            </h3>
            <div class="space-y-2">
                <a href="{{ route('admin.products') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-foreground/5 rounded-xl hover:bg-primary/10 transition-colors">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center text-primary shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase">Add New Product</p>
                        <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">Create a product or voucher</p>
                    </div>
                </a>

                <a href="{{ route('admin.orders', ['status' => 'pending']) }}"
                   class="flex items-center gap-3 px-4 py-3 bg-foreground/5 rounded-xl hover:bg-amber-500/10 transition-colors {{ $stats['pending_orders'] > 0 ? 'ring-1 ring-amber-500/20' : '' }}">
                    <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center text-amber-500 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase">Pending Orders</p>
                        <p class="text-[9px] font-bold uppercase tracking-widest {{ $stats['pending_orders'] > 0 ? 'text-amber-500' : 'text-muted-foreground' }}">
                            {{ $stats['pending_orders'] }} {{ $stats['pending_orders'] === 1 ? 'order needs' : 'orders need' }} attention
                        </p>
                    </div>
                </a>

                <a href="{{ route('admin.tickets') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-foreground/5 rounded-xl hover:bg-blue-500/10 transition-colors">
                    <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-500 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase">Support Tickets</p>
                        <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">View customer requests</p>
                    </div>
                </a>

                <a href="{{ route('admin.users') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-foreground/5 rounded-xl hover:bg-purple-500/10 transition-colors">
                    <div class="w-8 h-8 bg-purple-500/10 rounded-lg flex items-center justify-center text-purple-500 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase">Manage Users</p>
                        <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">{{ $stats['total_users'] }} registered users</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardHeader(serverTimestamp) {
    return {
        updatedAt: serverTimestamp * 1000,
        timeAgo: '↻ Updated just now',

        init() {
            this.tick();
            setInterval(() => this.tick(), 30_000);
            // Auto-refresh every 5 minutes so data stays current
            setTimeout(() => window.location.reload(), 5 * 60 * 1000);
        },

        tick() {
            const seconds = Math.floor((Date.now() - this.updatedAt) / 1000);
            if (seconds < 60)        this.timeAgo = '↻ Updated just now';
            else if (seconds < 3600) this.timeAgo = `↻ Updated ${Math.floor(seconds / 60)}m ago`;
            else                     this.timeAgo = `↻ Updated ${Math.floor(seconds / 3600)}h ago`;
        },
    };
}
</script>
@endsection
