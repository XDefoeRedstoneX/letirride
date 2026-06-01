@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8" x-data="dashboardManager()" x-init="init()">

    {{-- ================================================================ --}}
    {{-- HEADER / CONTEXT                                                  --}}
    {{-- ================================================================ --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden shadow-sm sticky top-16 md:top-0 z-30"
         x-data="dashboardHeader({{ $updatedAt->timestamp }})">

        {{-- Row 1: Title + Admin Identity --}}
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 px-6 py-5">
            <div>
                <div class="flex items-center gap-1.5 mb-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/50 shrink-0"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Ridly Admin</span>
                    <span class="text-muted-foreground/30 select-none">/</span>
                    <span class="text-[9px] font-black uppercase tracking-widest">Dashboard</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase leading-none">Dashboard</h1>
                <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 mt-2">
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                        @if($period === 'today')
                            {{ $startDate->format('F d, Y') }}
                        @else
                            {{ $startDate->format('M d') }} – {{ $endDate->format('M d, Y') }}
                        @endif
                    </p>
                    <span class="text-muted-foreground/25 select-none hidden sm:inline">·</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md border text-[8px] font-black uppercase tracking-widest bg-primary/10 text-primary border-primary/25">{{ $periodLabel }}</span>
                    @if($stats['pending_orders'] > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border text-[8px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-500 border-amber-500/25">
                            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                            {{ $stats['pending_orders'] }} pending
                        </span>
                    @endif
                </div>
            </div>
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

        {{-- Row 2: Period Filter + Controls --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-3 border-t border-border bg-foreground/[0.015]">
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest mr-1 hidden sm:inline">Period</span>
                @foreach($periods as $key => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $key]) }}"
                       class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-all duration-100 border
                              {{ $period === $key ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-transparent hover:bg-foreground/10 hover:text-foreground hover:border-border' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <span x-text="timeAgo" class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline whitespace-nowrap">↻ Updated just now</span>
                <span class="w-px h-4 bg-border hidden sm:inline-block"></span>

                <a href="{{ request()->fullUrl() }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-transparent text-[9px] font-black uppercase tracking-widest text-muted-foreground bg-foreground/5 hover:bg-foreground/10 hover:text-foreground hover:border-border transition-all duration-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    Refresh
                </a>

                <a href="{{ route('admin.dashboard.export', ['period' => $period]) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-transparent text-[9px] font-black uppercase tracking-widest text-muted-foreground bg-foreground/5 hover:bg-primary/10 hover:text-primary hover:border-primary/20 transition-all duration-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Export CSV
                </a>

                <span class="w-px h-4 bg-border hidden sm:inline-block"></span>

                {{-- Reorder --}}
                <button @click="toggleReorder()"
                        :class="reorderMode ? 'bg-primary/15 text-primary border-primary/30' : 'text-muted-foreground bg-foreground/5 border-transparent hover:bg-foreground/10 hover:text-foreground hover:border-border'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-[9px] font-black uppercase tracking-widest transition-all duration-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    <span x-text="reorderMode ? 'Done' : 'Reorder'">Reorder</span>
                </button>

                {{-- Reset Layout --}}
                <button @click="resetLayout()"
                        title="Reset dashboard to default layout"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-transparent text-[9px] font-black uppercase tracking-widest text-muted-foreground bg-foreground/5 hover:bg-red-500/10 hover:text-red-500 hover:border-red-500/20 transition-all duration-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    Reset Layout
                </button>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- WIDGET GRID — flat, dense, drag-reorderable, resizable           --}}
    {{-- ================================================================ --}}
    {{-- Safelist: keep dynamic col-span utilities in the Tailwind v4 build --}}
    <span class="hidden lg:col-span-1 lg:col-span-2 lg:col-span-3"></span>
    <div id="dashboard-grid"
         :class="reorderMode ? 'reorder-active' : ''"
         class="grid grid-cols-1 lg:grid-cols-3 gap-6 grid-flow-row-dense items-start">

    {{-- KPI CARDS --}}
    <div data-widget-id="kpi_cards" :class="spanClass('kpi_cards')"
         x-show="widgetVisible('kpi_cards')"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        {{-- Section header --}}
        <div class="flex items-center justify-between mb-3 px-0.5">
            <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-8"/></svg>
                KPI Overview
            </span>
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-transparent bg-foreground/5 text-muted-foreground hover:bg-foreground/10 hover:text-foreground hover:border-border transition-all duration-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
                <div x-show="open" x-cloak
                     class="absolute right-0 top-full mt-1.5 w-44 bg-card border border-border rounded-xl shadow-xl z-40 py-1.5 overflow-hidden">
                    {{-- Width / size --}}
                    <div class="px-3.5 pt-1.5 pb-2">
                        <p class="text-[8px] font-black text-muted-foreground uppercase tracking-widest mb-1.5">Width</p>
                        <div class="flex gap-1">
                            <button @click="setWidgetSpan('kpi_cards', 1)"
                                    :class="getWidgetSpan('kpi_cards') === 1 ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="flex-1 px-1.5 py-1 rounded text-[9px] font-black uppercase tracking-widest border transition-all">⅓</button>
                            <button @click="setWidgetSpan('kpi_cards', 2)"
                                    :class="getWidgetSpan('kpi_cards') === 2 ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="flex-1 px-1.5 py-1 rounded text-[9px] font-black uppercase tracking-widest border transition-all">⅔</button>
                            <button @click="setWidgetSpan('kpi_cards', 3)"
                                    :class="getWidgetSpan('kpi_cards') === 3 ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="flex-1 px-1.5 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Full</button>
                        </div>
                    </div>
                    <div class="h-px bg-border mx-3 my-1"></div>
                    <button @click="openChangeWidget('kpi_cards'); open = false"
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-[9px] font-black uppercase tracking-widest hover:bg-foreground/5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>
                        Change Widget
                    </button>
                    <div class="h-px bg-border mx-3 my-1"></div>
                    <button @click="removeWidget('kpi_cards'); open = false"
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-[9px] font-black uppercase tracking-widest text-red-500 hover:bg-red-500/5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                        Remove Widget
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($kpiCards as $card)
                @php
                    $maxBar  = !empty($card['sparkline']) ? max(1, max($card['sparkline'])) : 1;
                    $hasLink = !empty($card['link']);
                @endphp
                <div class="relative bg-card border rounded-2xl p-5 flex flex-col gap-3 h-full
                            {{ $card['alert'] ? 'border-amber-500/40' : 'border-border' }}
                            {{ $hasLink ? 'transition-all duration-150 hover:shadow-md hover:border-foreground/25 cursor-pointer' : '' }}">
                    @if($card['alert'])
                        <span class="absolute top-3.5 right-3.5 flex h-2 w-2 z-20">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                    @endif
                    @if($hasLink)
                        <a href="{{ $card['link'] }}" class="absolute inset-0 rounded-2xl z-10" aria-label="Go to {{ $card['label'] }}"></a>
                    @endif
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest leading-tight pr-4">{{ $card['label'] }}</p>
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ $card['icon_bg'] }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $card['icon_c'] }}"><path d="{{ $card['icon'] }}"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-black {{ $card['color'] }} leading-none tabular-nums tracking-tight">{{ $card['value'] }}</p>
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
                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[8px] font-black bg-foreground/5 text-muted-foreground border border-border">{{ $card['delta']['label'] }}</span>
                            @endif
                            <span class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">vs prior</span>
                        </div>
                    @else
                        <div class="h-[22px]"></div>
                    @endif
                    <div class="mt-auto pt-3 border-t border-border/40 space-y-1.5">
                        @if(!empty($card['sparkline']))
                            <div class="flex items-end gap-[3px]" style="height:28px;">
                                @foreach($card['sparkline'] as $barVal)
                                    @php $barH = $maxBar > 0 ? max(8, round(($barVal / $maxBar) * 100)) : 8; @endphp
                                    <div class="flex-1 rounded-sm {{ $card['bar'] }} opacity-60" style="height:{{ $barH }}%"></div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center" style="height:28px;"><div class="w-full h-px bg-border/60 rounded-full"></div></div>
                        @endif
                        <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">{{ $card['footer'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- REVENUE TREND --}}
    <div data-widget-id="revenue_trend" :class="spanClass('revenue_trend')" class="bg-card border border-border rounded-2xl overflow-hidden"
         x-show="widgetVisible('revenue_trend')"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-border flex items-center gap-3">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2 flex-1 min-w-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16l4-8 4 4 4-8"/></svg>
                    Revenue Trend
                </h3>
                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest shrink-0">{{ $periodLabel }}</span>
                {{-- Controls --}}
                @include('admin.partials.widget-controls', ['id' => 'revenue_trend'])
            </div>
            {{-- Configure Panel --}}
            <div x-show="isConfigOpen('revenue_trend')" x-cloak
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 class="px-5 py-3.5 border-b border-border bg-foreground/[0.015]">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2.5">
                    <div class="flex items-center gap-2">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Type</span>
                        <div class="flex gap-1">
                            <button @click="setWidgetConfig('revenue_trend','type','line')"
                                    :class="getWidgetConfig('revenue_trend','type','line')==='line' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Line</button>
                            <button @click="setWidgetConfig('revenue_trend','type','bar')"
                                    :class="getWidgetConfig('revenue_trend','type','line')==='bar' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Bar</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 opacity-40 pointer-events-none select-none">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Color</span>
                        <div class="flex gap-1.5">
                            <div class="w-3.5 h-3.5 rounded-full bg-violet-500 ring-2 ring-offset-1 ring-violet-500/70"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-blue-500"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-teal-500"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-amber-500"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-rose-500"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 opacity-40 pointer-events-none select-none">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Fill</span>
                        <div class="w-8 h-4 rounded-full bg-primary/30 flex items-center px-0.5"><div class="w-3 h-3 rounded-full bg-primary shadow"></div></div>
                    </div>
                </div>
                <p class="text-[8px] font-bold text-muted-foreground/40 uppercase tracking-widest mt-2.5">Color & fill options coming soon</p>
            </div>
            <div class="p-6 pb-4">
                <canvas id="revenueChart" class="max-h-[220px]"></canvas>
            </div>
        </div>

    {{-- ORDER STATUS --}}
    <div data-widget-id="order_status" :class="spanClass('order_status')" class="bg-card border border-border rounded-2xl overflow-hidden"
         x-show="widgetVisible('order_status')"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-border flex items-center gap-3">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2 flex-1 min-w-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 10 10 0 0 0 0-20"/></svg>
                    Order Status
                </h3>
                {{-- Controls --}}
                @include('admin.partials.widget-controls', ['id' => 'order_status'])
            </div>
            {{-- Configure Panel --}}
            <div x-show="isConfigOpen('order_status')" x-cloak
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 class="px-5 py-3.5 border-b border-border bg-foreground/[0.015]">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2.5">
                    <div class="flex items-center gap-2">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Type</span>
                        <div class="flex gap-1">
                            <button @click="setWidgetConfig('order_status','type','doughnut')"
                                    :class="getWidgetConfig('order_status','type','doughnut')==='doughnut' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Doughnut</button>
                            <button @click="setWidgetConfig('order_status','type','pie')"
                                    :class="getWidgetConfig('order_status','type','doughnut')==='pie' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Pie</button>
                            <button @click="setWidgetConfig('order_status','type','bar')"
                                    :class="getWidgetConfig('order_status','type','doughnut')==='bar' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Bar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 flex flex-col items-center gap-5">
                {{-- Doughnut / Pie view --}}
                <div class="relative w-44 h-44"
                     x-show="getWidgetConfig('order_status','type','doughnut') !== 'bar'">
                    <canvas id="statusChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none select-none"
                         x-show="getWidgetConfig('order_status','type','doughnut') === 'doughnut'">
                        <p class="text-2xl font-black tabular-nums leading-none">{{ number_format($stats['total_orders']) }}</p>
                        <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">Orders</p>
                    </div>
                </div>
                {{-- Bar view --}}
                <div class="w-full" x-show="getWidgetConfig('order_status','type','doughnut') === 'bar'">
                    <canvas id="statusChartBar" class="max-h-[160px]"></canvas>
                </div>
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

    {{-- TOP PRODUCTS --}}
    <div data-widget-id="top_products" :class="spanClass('top_products')" class="bg-card border border-border rounded-2xl overflow-hidden"
         x-show="widgetVisible('top_products')"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="px-6 py-4 border-b border-border flex items-center gap-3">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2 flex-1 min-w-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                    Top Products
                </h3>
                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest shrink-0 hidden sm:inline">by revenue</span>
                @include('admin.partials.widget-controls', ['id' => 'top_products'])
            </div>
            <div x-show="isConfigOpen('top_products')" x-cloak
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 class="px-5 py-3.5 border-b border-border bg-foreground/[0.015]">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2.5">
                    <div class="flex items-center gap-2">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Type</span>
                        <div class="flex gap-1">
                            <button @click="setWidgetConfig('top_products','type','hbar')"
                                    :class="getWidgetConfig('top_products','type','hbar')==='hbar' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">H-Bar</button>
                            <button @click="setWidgetConfig('top_products','type','vbar')"
                                    :class="getWidgetConfig('top_products','type','hbar')==='vbar' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">V-Bar</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 opacity-40 pointer-events-none select-none">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Show Top</span>
                        <div class="flex gap-1">
                            <span class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground border border-border">3</span>
                            <span class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest bg-primary/15 text-primary border border-primary/30">5</span>
                            <span class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground border border-border">10</span>
                        </div>
                    </div>
                </div>
                <p class="text-[8px] font-bold text-muted-foreground/40 uppercase tracking-widest mt-2.5">Show Top N selector coming soon</p>
            </div>
            @if(count($supportingData['topProducts']) > 0)
                <div class="p-5"><canvas id="topProductsChart" class="max-h-[200px]"></canvas></div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/30 mb-3"><path d="M3 3v18h18"/><path d="M7 16l4-8 4 4 4-8"/></svg>
                    <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">No paid orders this period</p>
                </div>
            @endif
        </div>

    {{-- BY CATEGORY --}}
    <div data-widget-id="category_revenue" :class="spanClass('category_revenue')" class="bg-card border border-border rounded-2xl overflow-hidden"
         x-show="widgetVisible('category_revenue')"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="px-6 py-4 border-b border-border flex items-center gap-3">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2 flex-1 min-w-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                    By Category
                </h3>
                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest shrink-0 hidden sm:inline">revenue</span>
                @include('admin.partials.widget-controls', ['id' => 'category_revenue'])
            </div>
            <div x-show="isConfigOpen('category_revenue')" x-cloak
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 class="px-5 py-3.5 border-b border-border bg-foreground/[0.015]">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2.5">
                    <div class="flex items-center gap-2">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Type</span>
                        <div class="flex gap-1">
                            <button @click="setWidgetConfig('category_revenue','type','hbar')"
                                    :class="getWidgetConfig('category_revenue','type','hbar')==='hbar' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">H-Bar</button>
                            <button @click="setWidgetConfig('category_revenue','type','doughnut')"
                                    :class="getWidgetConfig('category_revenue','type','hbar')==='doughnut' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Doughnut</button>
                            <button @click="setWidgetConfig('category_revenue','type','vbar')"
                                    :class="getWidgetConfig('category_revenue','type','hbar')==='vbar' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">V-Bar</button>
                        </div>
                    </div>
                </div>
            </div>
            @if(count($supportingData['categoryRevenue']) > 0)
                <div class="p-5"><canvas id="categoryChart" class="max-h-[200px]"></canvas></div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/30 mb-3"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                    <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">No category data this period</p>
                </div>
            @endif
        </div>

    {{-- USER GROWTH --}}
    <div data-widget-id="user_growth" :class="spanClass('user_growth')" class="bg-card border border-border rounded-2xl overflow-hidden"
         x-show="widgetVisible('user_growth')"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="px-6 py-4 border-b border-border flex items-center gap-3">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2 flex-1 min-w-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
                    User Growth
                </h3>
                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest shrink-0">{{ $periodLabel }}</span>
                @include('admin.partials.widget-controls', ['id' => 'user_growth'])
            </div>
            <div x-show="isConfigOpen('user_growth')" x-cloak
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 class="px-5 py-3.5 border-b border-border bg-foreground/[0.015]">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2.5">
                    <div class="flex items-center gap-2">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Type</span>
                        <div class="flex gap-1">
                            <button @click="setWidgetConfig('user_growth','type','line')"
                                    :class="getWidgetConfig('user_growth','type','line')==='line' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Line</button>
                            <button @click="setWidgetConfig('user_growth','type','bar')"
                                    :class="getWidgetConfig('user_growth','type','line')==='bar' ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                                    class="px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Bar</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 opacity-40 pointer-events-none select-none">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Color</span>
                        <div class="flex gap-1.5">
                            <div class="w-3.5 h-3.5 rounded-full bg-violet-500 ring-2 ring-offset-1 ring-violet-500/70"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-blue-500"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-teal-500"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-amber-500"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-rose-500"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 opacity-40 pointer-events-none select-none">
                        <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Fill</span>
                        <div class="w-8 h-4 rounded-full bg-primary/30 flex items-center px-0.5"><div class="w-3 h-3 rounded-full bg-primary shadow"></div></div>
                    </div>
                </div>
                <p class="text-[8px] font-bold text-muted-foreground/40 uppercase tracking-widest mt-2.5">Color & fill options coming soon</p>
            </div>
            <div class="p-5"><canvas id="userGrowthChart" class="max-h-[200px]"></canvas></div>
        </div>

    {{-- RECENT ORDERS --}}
    <div data-widget-id="recent_orders" :class="spanClass('recent_orders')" class="bg-card border border-border rounded-2xl overflow-hidden"
         x-show="widgetVisible('recent_orders')"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="px-6 py-4 border-b border-border flex items-center gap-3">
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
                @include('admin.partials.widget-controls', ['id' => 'recent_orders', 'configurable' => false])
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
                                <td class="px-5 py-3.5 whitespace-nowrap"><span class="font-mono text-[11px] font-bold text-foreground/80">{{ $order->display_noinv }}</span></td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-6 h-6 rounded-full {{ $avatarColor }} flex items-center justify-center text-[9px] font-black text-white shrink-0 select-none">{{ $initial }}</div>
                                        <span class="text-xs font-bold truncate max-w-[120px]">{{ $order->user?->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-[11px] text-muted-foreground font-bold tabular-nums">{{ $order->orderDetails->count() }}</td>
                                <td class="px-5 py-3.5 whitespace-nowrap"><span class="text-xs font-black tabular-nums">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</span></td>
                                <td class="px-5 py-3.5">
                                    @if($order->status === 'paid')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-green-500/10 text-green-500 border border-green-500/15"><span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0"></span>Paid</span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-500 border border-amber-500/15"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>Pending</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-red-500/10 text-red-500 border border-red-500/15"><span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>{{ $order->status }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-[10px] text-muted-foreground whitespace-nowrap">{{ $order->created_at?->diffForHumans() }}</td>
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

        {{-- NEEDS ATTENTION --}}
        <div data-widget-id="sidebar_attention" :class="spanClass('sidebar_attention')" class="bg-card border border-border rounded-2xl overflow-hidden"
             x-show="widgetVisible('sidebar_attention')"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="px-5 py-3.5 border-b border-border flex items-center justify-between gap-2">
                    <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                        Needs Attention
                    </p>
                    @include('admin.partials.widget-controls', ['id' => 'sidebar_attention', 'configurable' => false])
                </div>
                <div class="divide-y divide-border">
                    <a href="{{ route('admin.orders', ['status' => 'pending']) }}"
                       class="flex items-center justify-between px-5 py-4 transition-colors group {{ $stats['pending_orders'] > 0 ? 'hover:bg-amber-500/5' : 'hover:bg-foreground/5' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $stats['pending_orders'] > 0 ? 'bg-amber-500/15 text-amber-500' : 'bg-foreground/5 text-muted-foreground' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase">Pending Orders</p>
                                <p class="text-[9px] font-bold uppercase tracking-widest {{ $stats['pending_orders'] > 0 ? 'text-amber-500' : 'text-muted-foreground' }}">
                                    {{ $stats['pending_orders'] > 0 ? $stats['pending_orders'].' need review' : 'All clear' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($stats['pending_orders'] > 0)
                                <span class="min-w-[20px] h-5 px-1.5 bg-amber-500 text-white text-[9px] font-black rounded-full flex items-center justify-center tabular-nums">{{ $stats['pending_orders'] }}</span>
                            @endif
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/40 group-hover:text-foreground/60 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                    </a>
                    <a href="{{ route('admin.tickets') }}"
                       class="flex items-center justify-between px-5 py-4 transition-colors group {{ $stats['open_tickets'] > 0 ? 'hover:bg-blue-500/5' : 'hover:bg-foreground/5' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $stats['open_tickets'] > 0 ? 'bg-blue-500/15 text-blue-500' : 'bg-foreground/5 text-muted-foreground' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase">Support Tickets</p>
                                <p class="text-[9px] font-bold uppercase tracking-widest {{ $stats['open_tickets'] > 0 ? 'text-blue-500' : 'text-muted-foreground' }}">
                                    {{ $stats['open_tickets'] > 0 ? $stats['open_tickets'].' open / in progress' : 'No open tickets' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($stats['open_tickets'] > 0)
                                <span class="min-w-[20px] h-5 px-1.5 bg-blue-500 text-white text-[9px] font-black rounded-full flex items-center justify-center tabular-nums">{{ $stats['open_tickets'] }}</span>
                            @endif
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/40 group-hover:text-foreground/60 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                    </a>
                </div>
            </div>

        {{-- QUICK NAVIGATE --}}
        <div data-widget-id="sidebar_quicknav" :class="spanClass('sidebar_quicknav')" class="bg-card border border-border rounded-2xl overflow-hidden"
             x-show="widgetVisible('sidebar_quicknav')"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="px-5 py-3.5 border-b border-border flex items-center justify-between gap-2">
                    <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        Quick Navigate
                    </p>
                    @include('admin.partials.widget-controls', ['id' => 'sidebar_quicknav', 'configurable' => false])
                </div>
                <div class="p-3 grid grid-cols-3 gap-2">
                    @php
                        $navLinks = [
                            ['label' => 'Products', 'route' => 'admin.products', 'color' => 'text-violet-500', 'bg' => 'bg-violet-500/10 hover:bg-violet-500/20', 'icon' => 'm7.5 4.27 9 5.15M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Zm-3.3-7-8.7 5-8.7-5M12 22V12'],
                            ['label' => 'Orders',   'route' => 'admin.orders',   'color' => 'text-blue-500',   'bg' => 'bg-blue-500/10 hover:bg-blue-500/20',   'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8ZM14 2v6h6'],
                            ['label' => 'Users',    'route' => 'admin.users',    'color' => 'text-purple-500', 'bg' => 'bg-purple-500/10 hover:bg-purple-500/20','icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 8 0 4 4 0 0 0-8 0'],
                            ['label' => 'Tickets',  'route' => 'admin.tickets',  'color' => 'text-teal-500',   'bg' => 'bg-teal-500/10 hover:bg-teal-500/20',   'icon' => 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z'],
                            ['label' => 'Gacha',    'route' => 'admin.gacha',    'color' => 'text-amber-500',  'bg' => 'bg-amber-500/10 hover:bg-amber-500/20',  'icon' => 'M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 5v5l3 3'],
                            ['label' => 'Pt Shop',  'route' => 'admin.point-shop','color'=> 'text-rose-500',   'bg' => 'bg-rose-500/10 hover:bg-rose-500/20',   'icon' => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
                        ];
                    @endphp
                    @foreach($navLinks as $nav)
                        <a href="{{ route($nav['route']) }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl {{ $nav['bg'] }} transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $nav['color'] }}"><path d="{{ $nav['icon'] }}"/></svg>
                            <span class="text-[8px] font-black uppercase tracking-widest text-muted-foreground leading-none">{{ $nav['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

        {{-- STORE SNAPSHOT --}}
        <div data-widget-id="sidebar_snapshot" :class="spanClass('sidebar_snapshot')" class="bg-card border border-border rounded-2xl p-5 space-y-4"
             x-show="widgetVisible('sidebar_snapshot')"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Store Snapshot</p>
                    @include('admin.partials.widget-controls', ['id' => 'sidebar_snapshot', 'configurable' => false])
                </div>
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

    </div>{{-- end #dashboard-grid --}}

    {{-- ================================================================ --}}
    {{-- HIDDEN WIDGETS — Add back removed widgets                        --}}
    {{-- ================================================================ --}}
    <div x-show="hiddenWidgets.length > 0"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-px flex-1 bg-border"></div>
            <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" x2="23" y1="1" y2="23"/></svg>
                Hidden Widgets
            </span>
            <div class="h-px flex-1 bg-border"></div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <template x-for="w in hiddenWidgets" :key="w.id">
                <button @click="restoreWidget(w.id)"
                        class="group flex flex-col items-center gap-2.5 p-4 rounded-2xl border-2 border-dashed border-border hover:border-primary/40 hover:bg-primary/5 transition-all duration-150 text-center">
                    <div class="w-9 h-9 rounded-xl bg-foreground/5 group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-hover:text-primary transition-colors"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-foreground/80" x-text="w.name"></p>
                        <p class="text-[8px] font-bold text-muted-foreground mt-0.5 leading-relaxed" x-text="w.desc"></p>
                    </div>
                </button>
            </template>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- CHANGE WIDGET MODAL                                              --}}
    {{-- ================================================================ --}}
    <div x-show="changeTarget !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeChangeWidget()"></div>
        {{-- Panel --}}
        <div class="relative bg-card border border-border rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest">Change Widget</h3>
                    <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">
                        Replace <span class="text-foreground font-black" x-text="getCatalogItem(changeTarget)?.name ?? ''"></span> — pick a widget to show instead
                    </p>
                </div>
                <button @click="closeChangeWidget()"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-muted-foreground hover:bg-foreground/10 hover:text-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            {{-- Catalog Grid --}}
            <div class="p-5 grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[60vh] overflow-y-auto">
                <template x-for="item in catalog" :key="item.id">
                    <button @click="!isCurrentOrActive(item.id) && selectChangeWidget(item.id)"
                            :disabled="isCurrentOrActive(item.id)"
                            :class="{
                                'border-primary/40 bg-primary/5 ring-1 ring-primary/20 cursor-default': item.id === changeTarget,
                                'border-green-500/30 bg-green-500/5 opacity-50 cursor-not-allowed': item.id !== changeTarget && widgetVisible(item.id),
                                'hover:border-foreground/30 hover:bg-foreground/5 cursor-pointer': !isCurrentOrActive(item.id),
                            }"
                            class="flex flex-col items-start gap-2.5 p-4 rounded-xl border border-border transition-all text-left">
                        <div class="w-8 h-8 rounded-lg bg-foreground/5 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground">
                                <path :d="item.iconPath"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-black uppercase tracking-widest leading-tight" x-text="item.name"></p>
                            <p class="text-[9px] font-bold text-muted-foreground mt-0.5 leading-relaxed" x-text="item.desc"></p>
                        </div>
                        <template x-if="item.id === changeTarget">
                            <span class="text-[7px] font-black uppercase tracking-widest px-1.5 py-0.5 bg-primary/15 text-primary rounded-md">Current</span>
                        </template>
                        <template x-if="item.id !== changeTarget && widgetVisible(item.id)">
                            <span class="text-[7px] font-black uppercase tracking-widest px-1.5 py-0.5 bg-green-500/10 text-green-500 rounded-md">Active</span>
                        </template>
                    </button>
                </template>
            </div>
        </div>
    </div>

</div>{{-- end space-y-8 / dashboardManager --}}

<style>
    /* Reorder mode — visual affordance on draggable widgets */
    #dashboard-grid.reorder-active > [data-widget-id] {
        cursor: move;
        outline: 2px dashed rgba(139,92,246,0.45);
        outline-offset: 3px;
        border-radius: 1rem;
        transition: outline-color .15s ease;
    }
    #dashboard-grid.reorder-active > [data-widget-id]:hover {
        outline-color: rgba(139,92,246,0.9);
    }
    /* SortableJS drag states */
    .sortable-ghost  { opacity: .35; }
    .sortable-chosen { outline-color: rgba(139,92,246,0.9) !important; }
    .sortable-drag   { opacity: .9; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    // ── Shared theme ───────────────────────────────────────────────────
    const isDark = document.documentElement.classList.contains('dark');
    const grid   = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const tick   = isDark ? 'rgba(255,255,255,0.40)' : 'rgba(0,0,0,0.45)';
    const tip    = { backgroundColor: isDark ? '#1c1c1e' : '#fff',
                     borderColor:     isDark ? 'rgba(255,255,255,0.10)' : 'rgba(0,0,0,0.10)',
                     borderWidth: 1, titleColor: tick, bodyColor: tick };
    const rpFmt  = v => v >= 1_000_000 ? 'Rp '+(v/1_000_000).toFixed(1)+'M' : v >= 1_000 ? 'Rp '+(v/1_000).toFixed(0)+'K' : 'Rp '+v;

    // ── Shared data (referenced by rebuild functions) ──────────────────
    window._dashData = {
        labels:   @json($chartData['labels']),
        revenue:  @json($chartData['revenue']),
        users:    @json($chartData['users']),
        status:   @json($chartData['status']),
        products: @json($supportingData['topProducts']),
        cats:     @json($supportingData['categoryRevenue']),
    };
    window._dashCharts = {};

    // ── Builder: Revenue Trend ─────────────────────────────────────────
    function buildRevenueChart(type) {
        const el = document.getElementById('revenueChart');
        if (!el) return;
        const { labels, revenue } = window._dashData;
        const isLine = type !== 'bar';
        window._dashCharts['revenue_trend'] = new Chart(el, {
            type: isLine ? 'line' : 'bar',
            data: { labels, datasets: [{ label: 'Revenue', data: revenue,
                borderColor: 'rgb(139,92,246)',
                backgroundColor: isLine ? 'rgba(139,92,246,0.08)' : 'rgba(139,92,246,0.75)',
                ...(isLine
                    ? { fill: true, tension: 0.4, borderWidth: 2, pointRadius: labels.length <= 14 ? 3 : 0, pointHoverRadius: 5, pointBackgroundColor: 'rgb(139,92,246)' }
                    : { borderRadius: 4, borderSkipped: false, borderWidth: 0 }),
            }]},
            options: {
                responsive: true, maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { ...tip, callbacks: { label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID') } } },
                scales: {
                    x: { grid: { color: grid }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, maxTicksLimit: 8, maxRotation: 0 } },
                    y: { grid: { color: grid }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, callback: rpFmt } },
                },
            },
        });
    }

    // ── Builder: Order Status ──────────────────────────────────────────
    function buildStatusChart(type) {
        const { status } = window._dashData;
        const statusData = [status.paid, status.pending, status.failed];
        const statusColors = ['rgba(34,197,94,0.85)', 'rgba(245,158,11,0.85)', 'rgba(239,68,68,0.85)'];

        if (type === 'bar') {
            const el = document.getElementById('statusChartBar');
            if (!el) return;
            window._dashCharts['order_status'] = new Chart(el, {
                type: 'bar',
                data: { labels: ['Paid', 'Pending', 'Failed'], datasets: [{ data: statusData,
                    backgroundColor: statusColors, borderRadius: 4, borderSkipped: false, borderWidth: 0 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: tip },
                    scales: {
                        x: { grid: { display: false }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' } } },
                        y: { grid: { color: grid }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, precision: 0 } },
                    },
                },
            });
        } else {
            const el = document.getElementById('statusChart');
            if (!el) return;
            window._dashCharts['order_status'] = new Chart(el, {
                type: 'doughnut',
                data: { labels: ['Paid', 'Pending', 'Failed'], datasets: [{ data: statusData,
                    backgroundColor: statusColors, borderColor: isDark ? '#111113' : '#ffffff', borderWidth: 2, hoverBorderWidth: 0 }] },
                options: { responsive: true, cutout: type === 'pie' ? '0%' : '72%',
                    plugins: { legend: { display: false }, tooltip: tip } },
            });
        }
    }

    // ── Builder: Top Products ──────────────────────────────────────────
    function buildTopProductsChart(type) {
        const el = document.getElementById('topProductsChart');
        if (!el) return;
        const { products } = window._dashData;
        if (!products.length) return;
        const colors = ['rgba(139,92,246,0.8)','rgba(59,130,246,0.8)','rgba(20,184,166,0.8)','rgba(34,197,94,0.8)','rgba(245,158,11,0.8)'];
        const isH = type !== 'vbar';
        window._dashCharts['top_products'] = new Chart(el, {
            type: 'bar',
            data: { labels: products.map(p => p.name), datasets: [{ data: products.map(p => p.revenue),
                backgroundColor: colors.slice(0, products.length), borderRadius: 4, borderSkipped: false }] },
            options: {
                ...(isH ? { indexAxis: 'y' } : {}),
                responsive: true, maintainAspectRatio: true,
                plugins: { legend: { display: false }, tooltip: { ...tip, callbacks: {
                    label: ctx => ' Rp ' + (isH ? ctx.parsed.x : ctx.parsed.y).toLocaleString('id-ID'),
                    afterLabel: ctx => `  ${products[ctx.dataIndex].qty} unit(s) sold`,
                }}},
                scales: isH ? {
                    x: { grid: { color: grid }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, callback: rpFmt } },
                    y: { grid: { display: false }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' } } },
                } : {
                    x: { grid: { display: false }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, maxRotation: 30 } },
                    y: { grid: { color: grid }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, callback: rpFmt } },
                },
            },
        });
    }

    // ── Builder: By Category ───────────────────────────────────────────
    function buildCategoryChart(type) {
        const el = document.getElementById('categoryChart');
        if (!el) return;
        const { cats } = window._dashData;
        if (!cats.length) return;
        const colors = ['rgba(59,130,246,0.8)','rgba(139,92,246,0.8)','rgba(20,184,166,0.8)','rgba(245,158,11,0.8)','rgba(239,68,68,0.8)','rgba(34,197,94,0.8)'];

        if (type === 'doughnut') {
            window._dashCharts['category_revenue'] = new Chart(el, {
                type: 'doughnut',
                data: { labels: cats.map(c => c.name), datasets: [{ data: cats.map(c => c.revenue),
                    backgroundColor: colors.slice(0, cats.length), borderColor: isDark ? '#111113' : '#ffffff', borderWidth: 2, hoverBorderWidth: 0 }] },
                options: { responsive: true, cutout: '60%',
                    plugins: { legend: { display: true, position: 'bottom',
                        labels: { color: tick, font: { size: 8, weight: '700' }, boxWidth: 8, padding: 10 } },
                        tooltip: { ...tip, callbacks: { label: ctx => ' Rp ' + ctx.parsed.toLocaleString('id-ID') } } } },
            });
        } else {
            const isH = type !== 'vbar';
            window._dashCharts['category_revenue'] = new Chart(el, {
                type: 'bar',
                data: { labels: cats.map(c => c.name), datasets: [{ data: cats.map(c => c.revenue),
                    backgroundColor: colors.slice(0, cats.length), borderRadius: 4, borderSkipped: false }] },
                options: {
                    ...(isH ? { indexAxis: 'y' } : {}),
                    responsive: true, maintainAspectRatio: true,
                    plugins: { legend: { display: false }, tooltip: { ...tip, callbacks: {
                        label: ctx => ' Rp ' + (isH ? ctx.parsed.x : ctx.parsed.y).toLocaleString('id-ID') } } },
                    scales: isH ? {
                        x: { grid: { color: grid }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, callback: rpFmt } },
                        y: { grid: { display: false }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' } } },
                    } : {
                        x: { grid: { display: false }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, maxRotation: 30 } },
                        y: { grid: { color: grid }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, callback: rpFmt } },
                    },
                },
            });
        }
    }

    // ── Builder: User Growth ───────────────────────────────────────────
    function buildUserGrowthChart(type) {
        const el = document.getElementById('userGrowthChart');
        if (!el) return;
        const { labels, users } = window._dashData;
        const isLine = type !== 'bar';
        window._dashCharts['user_growth'] = new Chart(el, {
            type: isLine ? 'line' : 'bar',
            data: { labels, datasets: [{ label: 'New Users', data: users,
                borderColor: 'rgb(168,85,247)',
                backgroundColor: isLine ? 'rgba(168,85,247,0.08)' : 'rgba(168,85,247,0.75)',
                ...(isLine
                    ? { fill: true, tension: 0.4, borderWidth: 2, pointRadius: labels.length <= 14 ? 3 : 0, pointHoverRadius: 5, pointBackgroundColor: 'rgb(168,85,247)' }
                    : { borderRadius: 4, borderSkipped: false, borderWidth: 0 }),
            }]},
            options: {
                responsive: true, maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { ...tip, callbacks: { label: ctx => ` ${ctx.parsed.y} new user${ctx.parsed.y !== 1 ? 's' : ''}` } } },
                scales: {
                    x: { grid: { color: grid }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, maxTicksLimit: 8, maxRotation: 0 } },
                    y: { grid: { color: grid }, border: { color: 'transparent' }, ticks: { color: tick, font: { size: 9, weight: '700' }, precision: 0 } },
                },
            },
        });
    }

    // ── Global rebuild dispatcher ──────────────────────────────────────
    window.rebuildChart = function (id, config) {
        const existing = window._dashCharts[id];
        if (existing) { existing.destroy(); delete window._dashCharts[id]; }
        switch (id) {
            case 'revenue_trend':    buildRevenueChart(config.type ?? 'line');     break;
            case 'order_status':     buildStatusChart(config.type ?? 'doughnut');  break;
            case 'top_products':     buildTopProductsChart(config.type ?? 'hbar'); break;
            case 'category_revenue': buildCategoryChart(config.type ?? 'hbar');    break;
            case 'user_growth':      buildUserGrowthChart(config.type ?? 'line');  break;
        }
    };

    // ── Initial render — read saved prefs from localStorage ───────────
    const _saved = JSON.parse(localStorage.getItem('ridly_dashboard_v1') || '{}');
    const _cfg   = id => _saved[id]?.config ?? {};

    buildRevenueChart(   _cfg('revenue_trend').type    ?? 'line');
    buildStatusChart(    _cfg('order_status').type     ?? 'doughnut');
    buildTopProductsChart(_cfg('top_products').type    ?? 'hbar');
    buildCategoryChart(  _cfg('category_revenue').type ?? 'hbar');
    buildUserGrowthChart(_cfg('user_growth').type      ?? 'line');
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

function dashboardManager() {
    const LS_KEY    = 'ridly_dashboard_v1';
    const ORDER_KEY = 'ridly_dashboard_order_v1';

    // Canonical default render order of every widget in the grid
    const DEFAULT_ORDER = [
        'kpi_cards', 'revenue_trend', 'order_status', 'top_products',
        'category_revenue', 'user_growth', 'recent_orders',
        'sidebar_attention', 'sidebar_quicknav', 'sidebar_snapshot',
    ];

    const DEFAULTS = {
        kpi_cards:         { visible: true, config: { span: 3 } },
        revenue_trend:     { visible: true, config: { type: 'line',     span: 2 } },
        order_status:      { visible: true, config: { type: 'doughnut', span: 1 } },
        top_products:      { visible: true, config: { type: 'hbar',     span: 1 } },
        category_revenue:  { visible: true, config: { type: 'hbar',     span: 1 } },
        user_growth:       { visible: true, config: { type: 'line',     span: 1 } },
        recent_orders:     { visible: true, config: { span: 2 } },
        sidebar_attention: { visible: true, config: { span: 1 } },
        sidebar_quicknav:  { visible: true, config: { span: 1 } },
        sidebar_snapshot:  { visible: true, config: { span: 1 } },
    };

    const CATALOG = [
        { id: 'kpi_cards',         name: 'KPI Overview',    desc: 'Key performance indicators',     iconPath: 'M3 3v18h18M7 16l4-4 4 4 4-8' },
        { id: 'revenue_trend',     name: 'Revenue Trend',   desc: 'Revenue over time',              iconPath: 'M3 3v18h18M7 16l4-8 4 4 4-8' },
        { id: 'order_status',      name: 'Order Status',    desc: 'Paid / pending / failed split',  iconPath: 'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z' },
        { id: 'top_products',      name: 'Top Products',    desc: 'Best sellers by revenue',        iconPath: 'M18 2H6v7a6 6 0 0 0 12 0V2Z' },
        { id: 'category_revenue',  name: 'By Category',     desc: 'Revenue by product category',   iconPath: 'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z' },
        { id: 'user_growth',       name: 'User Growth',     desc: 'New user registrations',        iconPath: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2' },
        { id: 'recent_orders',     name: 'Recent Orders',   desc: 'Latest order activity table',   iconPath: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z' },
        { id: 'sidebar_attention', name: 'Needs Attention', desc: 'Pending orders & tickets',      iconPath: 'M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z' },
        { id: 'sidebar_quicknav',  name: 'Quick Navigate',  desc: 'Shortcuts to admin sections',  iconPath: 'M13 2 3 14h9l-1 8 10-12h-9l1-8z' },
        { id: 'sidebar_snapshot',  name: 'Store Snapshot',  desc: 'All-time users & revenue',     iconPath: 'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z' },
    ];

    return {
        widgets:      {},
        configOpen:   {},
        changeTarget: null,
        order:        [],
        reorderMode:  false,
        sortable:     null,

        init() {
            const saved = JSON.parse(localStorage.getItem(LS_KEY) || '{}');
            this.widgets = JSON.parse(JSON.stringify(DEFAULTS));
            Object.keys(saved).forEach(id => {
                if (this.widgets[id]) {
                    this.widgets[id] = {
                        ...this.widgets[id],
                        ...saved[id],
                        config: { ...(this.widgets[id].config ?? {}), ...(saved[id]?.config ?? {}) },
                    };
                }
            });
            Object.keys(this.widgets).forEach(id => { this.configOpen[id] = false; });

            // Resolve saved widget order, reconciling against known widgets
            const savedOrder = JSON.parse(localStorage.getItem(ORDER_KEY) || 'null');
            if (Array.isArray(savedOrder)) {
                this.order = savedOrder.filter(id => DEFAULT_ORDER.includes(id));
                DEFAULT_ORDER.forEach(id => { if (!this.order.includes(id)) this.order.push(id); });
            } else {
                this.order = [...DEFAULT_ORDER];
            }

            // Apply the saved DOM order and wire up drag-and-drop once rendered
            this.$nextTick(() => {
                this.applyOrder();
                this.initSortable();
            });
        },

        // Physically reorder grid children to match this.order
        applyOrder() {
            const grid = document.getElementById('dashboard-grid');
            if (!grid) return;
            this.order.forEach(id => {
                const el = grid.querySelector(`:scope > [data-widget-id="${id}"]`);
                if (el) grid.appendChild(el);
            });
        },

        saveOrder() {
            localStorage.setItem(ORDER_KEY, JSON.stringify(this.order));
        },

        initSortable() {
            const grid = document.getElementById('dashboard-grid');
            if (!grid || !window.Sortable) return;
            this.sortable = window.Sortable.create(grid, {
                animation: 180,
                disabled: true,                 // off until the user enters reorder mode
                draggable: '[data-widget-id]',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: () => {
                    this.order = Array.from(grid.querySelectorAll(':scope > [data-widget-id]'))
                        .map(el => el.dataset.widgetId);
                    this.saveOrder();
                },
            });
        },

        toggleReorder() {
            this.reorderMode = !this.reorderMode;
            if (this.sortable) this.sortable.option('disabled', !this.reorderMode);
        },

        save() {
            localStorage.setItem(LS_KEY, JSON.stringify(this.widgets));
        },

        widgetVisible(id) {
            return this.widgets[id]?.visible ?? true;
        },

        getWidgetConfig(id, key, defaultVal) {
            return this.widgets[id]?.config?.[key] ?? defaultVal;
        },

        setWidgetConfig(id, key, value) {
            if (!this.widgets[id]) return;
            if (!this.widgets[id].config) this.widgets[id].config = {};
            this.widgets[id].config[key] = value;
            this.save();
            if (window.rebuildChart) window.rebuildChart(id, this.widgets[id].config);
        },

        // ── Widget width (column span: 1 = ⅓, 2 = ⅔, 3 = full) ──────────
        getWidgetSpan(id) {
            return this.widgets[id]?.config?.span ?? 1;
        },

        setWidgetSpan(id, n) {
            if (!this.widgets[id]) return;
            if (!this.widgets[id].config) this.widgets[id].config = {};
            this.widgets[id].config.span = n;
            this.save();
            // Charts auto-resize via Chart.js ResizeObserver when the container width changes
        },

        spanClass(id) {
            const n = this.getWidgetSpan(id);
            return {
                'lg:col-span-1': n === 1,
                'lg:col-span-2': n === 2,
                'lg:col-span-3': n === 3,
            };
        },

        isConfigOpen(id) {
            return this.configOpen[id] ?? false;
        },

        toggleConfig(id) {
            this.configOpen[id] = !this.configOpen[id];
        },

        removeWidget(id) {
            if (this.widgets[id]) this.widgets[id].visible = false;
            this.configOpen[id] = false;
            this.save();
        },

        restoreWidget(id) {
            if (this.widgets[id]) this.widgets[id].visible = true;
            this.save();
        },

        openChangeWidget(id) {
            this.changeTarget = id;
        },

        closeChangeWidget() {
            this.changeTarget = null;
        },

        isCurrentOrActive(id) {
            return id === this.changeTarget || this.widgetVisible(id);
        },

        selectChangeWidget(newId) {
            if (!this.changeTarget || this.isCurrentOrActive(newId)) return;
            this.widgets[this.changeTarget].visible = false;
            this.configOpen[this.changeTarget]      = false;
            this.widgets[newId].visible             = true;
            this.save();
            this.changeTarget = null;
        },

        resetLayout() {
            localStorage.removeItem(LS_KEY);
            localStorage.removeItem(ORDER_KEY);
            window.location.reload();
        },

        get hiddenWidgets() {
            return CATALOG.filter(w => !this.widgetVisible(w.id));
        },

        getCatalogItem(id) {
            return CATALOG.find(w => w.id === id) ?? null;
        },

        get catalog() {
            return CATALOG;
        },
    };
}
</script>
@endsection
