@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">

    {{-- ================================================================ --}}
    {{-- PHASE 1: HEADER / CONTEXT                                        --}}
    {{-- ================================================================ --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden shadow-sm sticky top-16 md:top-0 z-30"
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
    {{-- PHASE 2: KPI CARDS                                               --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($kpiCards as $card)
            @php
                $maxBar  = !empty($card['sparkline']) ? max(1, max($card['sparkline'])) : 1;
                $hasLink = !empty($card['link']);
            @endphp

            <div class="relative bg-card border rounded-2xl p-5 flex flex-col gap-3 h-full
                        {{ $card['alert'] ? 'border-amber-500/40' : 'border-border' }}
                        {{ $hasLink ? 'transition-all duration-150 hover:shadow-md hover:border-foreground/25 cursor-pointer' : '' }}">

                {{-- Pulsing alert dot (pending orders only) --}}
                @if($card['alert'])
                    <span class="absolute top-3.5 right-3.5 flex h-2 w-2 z-20">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                @endif

                {{-- Full-card invisible link overlay --}}
                @if($hasLink)
                    <a href="{{ $card['link'] }}"
                       class="absolute inset-0 rounded-2xl z-10"
                       aria-label="Go to {{ $card['label'] }}"></a>
                @endif

                {{-- Label + Icon --}}
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest leading-tight pr-4">
                        {{ $card['label'] }}
                    </p>
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ $card['icon_bg'] }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5"
                             stroke-linecap="round" stroke-linejoin="round" class="{{ $card['icon_c'] }}">
                            <path d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                </div>

                {{-- Primary value --}}
                <p class="text-2xl sm:text-3xl font-black {{ $card['color'] }} leading-none tabular-nums tracking-tight">
                    {{ $card['value'] }}
                </p>

                {{-- Delta badge --}}
                @if($card['delta'])
                    <div class="flex items-center gap-2">
                        @if($card['delta']['type'] === 'up')
                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[8px] font-black bg-green-500/10 text-green-500 border border-green-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15 12 9 6 15"/></svg>
                                {{ $card['delta']['label'] }}
                            </span>
                        @elseif($card['delta']['type'] === 'down')
                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[8px] font-black bg-red-500/10 text-red-500 border border-red-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                {{ $card['delta']['label'] }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[8px] font-black bg-foreground/5 text-muted-foreground border border-border">
                                {{ $card['delta']['label'] }}
                            </span>
                        @endif
                        <span class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">vs prior</span>
                    </div>
                @else
                    {{-- Spacer keeps card height consistent across rows --}}
                    <div class="h-[22px]"></div>
                @endif

                {{-- Sparkline + footer — always pinned to bottom --}}
                <div class="mt-auto pt-3 border-t border-border/40 space-y-1.5">
                    @if(!empty($card['sparkline']))
                        <div class="flex items-end gap-[3px]" style="height:28px;">
                            @foreach($card['sparkline'] as $barVal)
                                @php $barH = $maxBar > 0 ? max(8, round(($barVal / $maxBar) * 100)) : 8; @endphp
                                <div class="flex-1 rounded-sm {{ $card['bar'] }} opacity-60"
                                     style="height:{{ $barH }}%"></div>
                            @endforeach
                        </div>
                    @else
                        {{-- Flat line placeholder for static metrics --}}
                        <div class="flex items-center" style="height:28px;">
                            <div class="w-full h-px bg-border/60 rounded-full"></div>
                        </div>
                    @endif
                    <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">
                        {{ $card['footer'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ================================================================ --}}
    {{-- PHASE 3: MAIN CHARTS                                             --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Revenue Trend Line Chart (2/3 width) --}}
        <div class="lg:col-span-2 bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16l4-8 4 4 4-8"/></svg>
                    Revenue Trend
                </h3>
                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">{{ $periodLabel }}</span>
            </div>
            <div class="p-6 pb-4">
                <canvas id="revenueChart" class="max-h-[220px]"></canvas>
            </div>
        </div>

        {{-- Orders by Status Donut (1/3 width) --}}
        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 10 10 0 0 0 0-20"/></svg>
                    Order Status
                </h3>
            </div>
            <div class="p-6 flex flex-col items-center gap-5">
                <div class="relative w-44 h-44">
                    <canvas id="statusChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none select-none">
                        <p class="text-2xl font-black tabular-nums leading-none">{{ number_format($stats['total_orders']) }}</p>
                        <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">Orders</p>
                    </div>
                </div>

                {{-- Legend --}}
                @php
                    $statusTotal = max(1, $stats['total_orders']);
                    $legendItems = [
                        ['label' => 'Paid',    'color' => 'bg-green-500', 'value' => $stats['paid_orders']],
                        ['label' => 'Pending', 'color' => 'bg-amber-500', 'value' => $stats['pending_orders']],
                        ['label' => 'Failed',  'color' => 'bg-red-500',   'value' => $chartData['status']['failed']],
                    ];
                @endphp
                <div class="w-full space-y-2.5">
                    @foreach($legendItems as $item)
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2 h-2 rounded-sm {{ $item['color'] }} shrink-0"></div>
                                <span class="text-[9px] font-bold uppercase tracking-widest text-muted-foreground">{{ $item['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-black tabular-nums">{{ number_format($item['value']) }}</span>
                                <span class="text-[8px] font-bold text-muted-foreground w-8 text-right">{{ round(($item['value'] / $statusTotal) * 100) }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PHASE 4: SUPPORTING CHARTS                                       --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Top 5 Products by Revenue --}}
        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                    Top Products
                </h3>
                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">by revenue · {{ $periodLabel }}</span>
            </div>
            @if(count($supportingData['topProducts']) > 0)
                <div class="p-5">
                    <canvas id="topProductsChart" class="max-h-[200px]"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/30 mb-3"><path d="M3 3v18h18"/><path d="M7 16l4-8 4 4 4-8"/></svg>
                    <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">No paid orders this period</p>
                </div>
            @endif
        </div>

        {{-- Revenue by Category --}}
        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                    By Category
                </h3>
                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">revenue · {{ $periodLabel }}</span>
            </div>
            @if(count($supportingData['categoryRevenue']) > 0)
                <div class="p-5">
                    <canvas id="categoryChart" class="max-h-[200px]"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/30 mb-3"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                    <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">No category data this period</p>
                </div>
            @endif
        </div>

        {{-- New User Growth --}}
        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
                    User Growth
                </h3>
                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">{{ $periodLabel }}</span>
            </div>
            <div class="p-5">
                <canvas id="userGrowthChart" class="max-h-[200px]"></canvas>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PHASE 5: DETAIL TABLE + ACTION PANEL                             --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Recent Orders (2/3 width) ─────────────────────────────────── --}}
        <div class="lg:col-span-2 bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between gap-4">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                    Recent Orders
                </h3>
                <div class="flex items-center gap-2 ml-auto flex-wrap justify-end">
                    <span class="px-2 py-0.5 bg-foreground/5 border border-border rounded-md text-[8px] font-black uppercase tracking-widest text-muted-foreground">
                        Showing {{ $recentOrders->count() }} · {{ $periodLabel }}
                    </span>
                    <a href="{{ route('admin.orders') }}"
                       class="px-3 py-1.5 rounded-lg border border-transparent text-[9px] font-black uppercase tracking-widest text-primary bg-primary/5 hover:bg-primary/10 hover:border-primary/20 transition-all duration-100">
                        View All →
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-foreground/[0.03] text-[9px] font-black uppercase tracking-widest text-muted-foreground border-b border-border">
                            <th class="px-5 py-3 whitespace-nowrap">Invoice</th>
                            <th class="px-5 py-3 whitespace-nowrap">Customer</th>
                            <th class="px-5 py-3 whitespace-nowrap">Items</th>
                            <th class="px-5 py-3 whitespace-nowrap">Total</th>
                            <th class="px-5 py-3 whitespace-nowrap">Status</th>
                            <th class="px-5 py-3 whitespace-nowrap">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($recentOrders as $order)
                            @php
                                $avatarPalette = ['bg-violet-500','bg-blue-500','bg-teal-500','bg-amber-500','bg-rose-500'];
                                $avatarColor   = $avatarPalette[($order->user?->id ?? 0) % 5];
                                $initial       = strtoupper(substr($order->user?->name ?? '?', 0, 1));
                                $isPending     = $order->status === 'pending';
                            @endphp
                            <tr class="hover:bg-foreground/[0.03] transition-colors {{ $isPending ? 'bg-amber-500/[0.025]' : '' }}">

                                {{-- Invoice --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-mono text-[11px] font-bold text-foreground/80">{{ $order->display_noinv }}</span>
                                </td>

                                {{-- Customer --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-6 h-6 rounded-full {{ $avatarColor }} flex items-center justify-center text-[9px] font-black text-white shrink-0 select-none">
                                            {{ $initial }}
                                        </div>
                                        <span class="text-xs font-bold truncate max-w-[120px]">{{ $order->user?->name ?? 'N/A' }}</span>
                                    </div>
                                </td>

                                {{-- Items --}}
                                <td class="px-5 py-3.5 text-[11px] text-muted-foreground font-bold tabular-nums">
                                    {{ $order->orderDetails->count() }}
                                </td>

                                {{-- Total --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="text-xs font-black tabular-nums">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</span>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-3.5">
                                    @if($order->status === 'paid')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-green-500/10 text-green-500 border border-green-500/15">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0"></span>Paid
                                        </span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-500 border border-amber-500/15">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-red-500/10 text-red-500 border border-red-500/15">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>{{ $order->status }}
                                        </span>
                                    @endif
                                </td>

                                {{-- When --}}
                                <td class="px-5 py-3.5 text-[10px] text-muted-foreground whitespace-nowrap">
                                    {{ $order->created_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/25"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/></svg>
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">No orders in this period</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Right Sidebar (1/3 width) ──────────────────────────────────── --}}
        <div class="flex flex-col gap-4">

            {{-- Attention Required --}}
            <div class="bg-card border border-border rounded-2xl overflow-hidden">
                <div class="px-5 py-3.5 border-b border-border">
                    <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                        Needs Attention
                    </p>
                </div>
                <div class="divide-y divide-border">

                    {{-- Pending Orders --}}
                    <a href="{{ route('admin.orders', ['status' => 'pending']) }}"
                       class="flex items-center justify-between px-5 py-4 transition-colors group
                              {{ $stats['pending_orders'] > 0 ? 'hover:bg-amber-500/5' : 'hover:bg-foreground/5' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                        {{ $stats['pending_orders'] > 0 ? 'bg-amber-500/15 text-amber-500' : 'bg-foreground/5 text-muted-foreground' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase">Pending Orders</p>
                                <p class="text-[9px] font-bold uppercase tracking-widest
                                          {{ $stats['pending_orders'] > 0 ? 'text-amber-500' : 'text-muted-foreground' }}">
                                    {{ $stats['pending_orders'] > 0 ? $stats['pending_orders'].' need review' : 'All clear' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($stats['pending_orders'] > 0)
                                <span class="min-w-[20px] h-5 px-1.5 bg-amber-500 text-white text-[9px] font-black rounded-full flex items-center justify-center tabular-nums">
                                    {{ $stats['pending_orders'] }}
                                </span>
                            @endif
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/40 group-hover:text-foreground/60 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                    </a>

                    {{-- Open Tickets --}}
                    <a href="{{ route('admin.tickets') }}"
                       class="flex items-center justify-between px-5 py-4 transition-colors group
                              {{ $stats['open_tickets'] > 0 ? 'hover:bg-blue-500/5' : 'hover:bg-foreground/5' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                        {{ $stats['open_tickets'] > 0 ? 'bg-blue-500/15 text-blue-500' : 'bg-foreground/5 text-muted-foreground' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase">Support Tickets</p>
                                <p class="text-[9px] font-bold uppercase tracking-widest
                                          {{ $stats['open_tickets'] > 0 ? 'text-blue-500' : 'text-muted-foreground' }}">
                                    {{ $stats['open_tickets'] > 0 ? $stats['open_tickets'].' open / in progress' : 'No open tickets' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($stats['open_tickets'] > 0)
                                <span class="min-w-[20px] h-5 px-1.5 bg-blue-500 text-white text-[9px] font-black rounded-full flex items-center justify-center tabular-nums">
                                    {{ $stats['open_tickets'] }}
                                </span>
                            @endif
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/40 group-hover:text-foreground/60 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Quick Navigate --}}
            <div class="bg-card border border-border rounded-2xl overflow-hidden">
                <div class="px-5 py-3.5 border-b border-border">
                    <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        Quick Navigate
                    </p>
                </div>
                <div class="p-3 grid grid-cols-3 gap-2">
                    @php
                        $navLinks = [
                            ['label' => 'Products', 'route' => 'admin.products', 'color' => 'text-violet-500', 'bg' => 'bg-violet-500/10 hover:bg-violet-500/20',
                             'icon' => 'm7.5 4.27 9 5.15M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Zm-3.3-7-8.7 5-8.7-5M12 22V12'],
                            ['label' => 'Orders',   'route' => 'admin.orders',   'color' => 'text-blue-500',   'bg' => 'bg-blue-500/10 hover:bg-blue-500/20',
                             'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8ZM14 2v6h6'],
                            ['label' => 'Users',    'route' => 'admin.users',    'color' => 'text-purple-500', 'bg' => 'bg-purple-500/10 hover:bg-purple-500/20',
                             'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 8 0 4 4 0 0 0-8 0'],
                            ['label' => 'Tickets',  'route' => 'admin.tickets',  'color' => 'text-teal-500',   'bg' => 'bg-teal-500/10 hover:bg-teal-500/20',
                             'icon' => 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z'],
                            ['label' => 'Gacha',    'route' => 'admin.gacha',    'color' => 'text-amber-500',  'bg' => 'bg-amber-500/10 hover:bg-amber-500/20',
                             'icon' => 'M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 5v5l3 3'],
                            ['label' => 'Pt Shop',  'route' => 'admin.point-shop','color' => 'text-rose-500',  'bg' => 'bg-rose-500/10 hover:bg-rose-500/20',
                             'icon' => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
                        ];
                    @endphp
                    @foreach($navLinks as $nav)
                        <a href="{{ route($nav['route']) }}"
                           class="flex flex-col items-center gap-1.5 p-3 rounded-xl {{ $nav['bg'] }} transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $nav['color'] }}">
                                <path d="{{ $nav['icon'] }}"/>
                            </svg>
                            <span class="text-[8px] font-black uppercase tracking-widest text-muted-foreground leading-none">{{ $nav['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Store Snapshot --}}
            <div class="bg-card border border-border rounded-2xl p-5 space-y-4">
                <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Store Snapshot</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-2xl font-black tabular-nums leading-none">{{ number_format($stats['total_users']) }}</p>
                        <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Total Users</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black tabular-nums leading-none">{{ number_format($stats['total_products']) }}</p>
                        <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Active Products</p>
                    </div>
                </div>
                <div class="pt-3 border-t border-border/60">
                    <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">All-time Revenue</p>
                    @php $allTimeRevenue = \App\Models\Order::where('status','paid')->sum('total_price_after_discount'); @endphp
                    <p class="text-sm font-black tabular-nums text-green-500">Rp {{ number_format($allTimeRevenue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels  = @json($chartData['labels']);
    const revenue = @json($chartData['revenue']);
    const status  = @json($chartData['status']);

    const isDark = document.documentElement.classList.contains('dark');
    const grid   = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const tick   = isDark ? 'rgba(255,255,255,0.40)' : 'rgba(0,0,0,0.45)';
    const tip    = { backgroundColor: isDark ? '#1c1c1e' : '#fff',
                     borderColor:     isDark ? 'rgba(255,255,255,0.10)' : 'rgba(0,0,0,0.10)',
                     borderWidth: 1, titleColor: tick, bodyColor: tick };

    // ── Revenue Trend ──────────────────────────────────────────────────
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Revenue',
                data: revenue,
                borderColor: 'rgb(139,92,246)',
                backgroundColor: 'rgba(139,92,246,0.08)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: labels.length <= 14 ? 3 : 0,
                pointHoverRadius: 5,
                pointBackgroundColor: 'rgb(139,92,246)',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tip,
                    callbacks: {
                        label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: grid },
                    border: { color: 'transparent' },
                    ticks: {
                        color: tick,
                        font: { size: 9, weight: '700' },
                        maxTicksLimit: 8,
                        maxRotation: 0,
                    },
                },
                y: {
                    grid: { color: grid },
                    border: { color: 'transparent' },
                    ticks: {
                        color: tick,
                        font: { size: 9, weight: '700' },
                        callback: v => v >= 1_000_000
                            ? 'Rp ' + (v / 1_000_000).toFixed(1) + 'M'
                            : v >= 1_000
                            ? 'Rp ' + (v / 1_000).toFixed(0) + 'K'
                            : 'Rp ' + v,
                    },
                },
            },
        },
    });

    // ── Phase 4: Top Products (horizontal bar) ────────────────────────
    const topProductsEl = document.getElementById('topProductsChart');
    if (topProductsEl) {
        const products = @json($supportingData['topProducts']);
        const barColors = [
            'rgba(139,92,246,0.8)',
            'rgba(59,130,246,0.8)',
            'rgba(20,184,166,0.8)',
            'rgba(34,197,94,0.8)',
            'rgba(245,158,11,0.8)',
        ];
        new Chart(topProductsEl, {
            type: 'bar',
            data: {
                labels: products.map(p => p.name),
                datasets: [{
                    data: products.map(p => p.revenue),
                    backgroundColor: barColors.slice(0, products.length),
                    borderRadius: 4,
                    borderSkipped: false,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tip,
                        callbacks: {
                            label: ctx => ' Rp ' + ctx.parsed.x.toLocaleString('id-ID'),
                            afterLabel: ctx => `  ${products[ctx.dataIndex].qty} unit(s) sold`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: grid },
                        border: { color: 'transparent' },
                        ticks: {
                            color: tick,
                            font: { size: 9, weight: '700' },
                            callback: v => v >= 1_000_000
                                ? 'Rp ' + (v / 1_000_000).toFixed(1) + 'M'
                                : v >= 1_000
                                ? 'Rp ' + (v / 1_000).toFixed(0) + 'K'
                                : 'Rp ' + v,
                        },
                    },
                    y: {
                        grid: { display: false },
                        border: { color: 'transparent' },
                        ticks: { color: tick, font: { size: 9, weight: '700' } },
                    },
                },
            },
        });
    }

    // ── Phase 4: Revenue by Category (horizontal bar) ─────────────────
    const categoryEl = document.getElementById('categoryChart');
    if (categoryEl) {
        const cats = @json($supportingData['categoryRevenue']);
        const catColors = [
            'rgba(59,130,246,0.8)',
            'rgba(139,92,246,0.8)',
            'rgba(20,184,166,0.8)',
            'rgba(245,158,11,0.8)',
            'rgba(239,68,68,0.8)',
            'rgba(34,197,94,0.8)',
        ];
        new Chart(categoryEl, {
            type: 'bar',
            data: {
                labels: cats.map(c => c.name),
                datasets: [{
                    data: cats.map(c => c.revenue),
                    backgroundColor: catColors.slice(0, cats.length),
                    borderRadius: 4,
                    borderSkipped: false,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tip,
                        callbacks: {
                            label: ctx => ' Rp ' + ctx.parsed.x.toLocaleString('id-ID'),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: grid },
                        border: { color: 'transparent' },
                        ticks: {
                            color: tick,
                            font: { size: 9, weight: '700' },
                            callback: v => v >= 1_000_000
                                ? 'Rp ' + (v / 1_000_000).toFixed(1) + 'M'
                                : v >= 1_000
                                ? 'Rp ' + (v / 1_000).toFixed(0) + 'K'
                                : 'Rp ' + v,
                        },
                    },
                    y: {
                        grid: { display: false },
                        border: { color: 'transparent' },
                        ticks: { color: tick, font: { size: 9, weight: '700' } },
                    },
                },
            },
        });
    }

    // ── Phase 4: User Growth (area line) ──────────────────────────────
    new Chart(document.getElementById('userGrowthChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'New Users',
                data: @json($chartData['users']),
                borderColor: 'rgb(168,85,247)',
                backgroundColor: 'rgba(168,85,247,0.08)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: labels.length <= 14 ? 3 : 0,
                pointHoverRadius: 5,
                pointBackgroundColor: 'rgb(168,85,247)',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tip,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} new user${ctx.parsed.y !== 1 ? 's' : ''}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: grid },
                    border: { color: 'transparent' },
                    ticks: {
                        color: tick,
                        font: { size: 9, weight: '700' },
                        maxTicksLimit: 8,
                        maxRotation: 0,
                    },
                },
                y: {
                    grid: { color: grid },
                    border: { color: 'transparent' },
                    ticks: {
                        color: tick,
                        font: { size: 9, weight: '700' },
                        precision: 0,
                    },
                },
            },
        },
    });

    // ── Orders by Status Donut ─────────────────────────────────────────
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Pending', 'Failed'],
            datasets: [{
                data: [status.paid, status.pending, status.failed],
                backgroundColor: [
                    'rgba(34,197,94,0.85)',
                    'rgba(245,158,11,0.85)',
                    'rgba(239,68,68,0.85)',
                ],
                borderColor: isDark ? '#111113' : '#ffffff',
                borderWidth: 2,
                hoverBorderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: tip,
            },
        },
    });
})();
</script>

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
