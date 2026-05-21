@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Dashboard</h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Overview of your store</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right">
                <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Logged in as</p>
                <p class="text-xs font-black">{{ Auth::user()->name }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-foreground/10 overflow-hidden ring-2 ring-primary/20 flex items-center justify-center text-sm font-black uppercase shrink-0">
                @if(Auth::user()->profile_picture)
                    <img src="{{ Auth::user()->avatar_url }}" class="w-full h-full object-cover" />
                @else
                    {{ substr(Auth::user()->name, 0, 2) }}
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        @php
            $statCards = [
                ['label' => 'Avg Order Value', 'value' => 'Rp ' . number_format($stats['total_orders'] > 0 ? ($stats['total_revenue'] / $stats['total_orders']) : 0, 0, ',', '.'), 'icon' => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6', 'color' => 'text-green-500'],
                ['label' => 'Total Orders', 'value' => $stats['total_orders'] ?? 0, 'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6', 'color' => 'text-blue-500'],
                ['label' => 'Paid Orders', 'value' => $stats['paid_orders'] ?? 0, 'icon' => 'M22 11.08V12a10 10 0 1 1-5.93-9.14 M22 4 12 14.01l-3-3', 'color' => 'text-emerald-500'],
                ['label' => 'Pending', 'value' => $stats['pending_orders'] ?? 0, 'icon' => 'M12 8v4l3 3 M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z', 'color' => 'text-amber-500'],
                ['label' => 'Total Users', 'value' => $stats['total_users'] ?? 0, 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M22 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75', 'color' => 'text-purple-500'],
                ['label' => 'Active Products', 'value' => $stats['total_products'] ?? 0, 'icon' => 'm7.5 4.27 9 5.15 M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z m3.3 7 8.7 5 8.7-5 M12 22V12', 'color' => 'text-primary'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="bg-card border border-border rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">{{ $card['label'] }}</p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-40"><path d="{{ $card['icon'] }}"/></svg>
                </div>
                <p class="text-2xl font-black {{ $card['color'] }}">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                    Recent Orders
                </h3>
                <a href="{{ route('admin.orders') }}" class="text-[10px] font-black text-primary uppercase tracking-widest">View All</a>
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
                            <tr class="hover:bg-foreground/5">
                                <td class="px-6 py-3 text-xs font-mono font-bold">{{ $order->noinv }}</td>
                                <td class="px-6 py-3 text-xs font-bold">{{ $order->user?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-3 text-xs text-muted-foreground">{{ $order->orderDetails->count() }} item(s)</td>
                                <td class="px-6 py-3 text-xs font-bold">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest
                                        @if($order->status === 'paid') bg-green-500/10 text-green-500
                                        @elseif($order->status === 'pending') bg-amber-500/10 text-amber-500
                                        @else bg-red-500/10 text-red-500
                                        @endif">{{ $order->status }}</span>
                                </td>
                                <td class="px-6 py-3 text-[10px] text-muted-foreground">{{ $order->created_at?->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-[10px] font-bold text-muted-foreground uppercase tracking-widest">No orders yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-card border border-border rounded-2xl p-6 space-y-5">
            <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Quick Actions
            </h3>
            <div class="space-y-2">
                <a href="{{ route('admin.products') }}" class="flex items-center gap-3 px-4 py-3 bg-foreground/5 rounded-xl hover:bg-primary/10 transition-colors">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase">Add New Product</p>
                        <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">Create a product or voucher</p>
                    </div>
                </a>
                <a href="{{ route('admin.orders', ['status' => 'pending']) }}" class="flex items-center gap-3 px-4 py-3 bg-foreground/5 rounded-xl hover:bg-amber-500/10 transition-colors">
                    <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center text-amber-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase">Pending Orders</p>
                        <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">{{ $stats['pending_orders'] ?? 0 }} orders need attention</p>
                    </div>
                </a>
                <a href="{{ route('admin.tickets', ['status' => 'open']) }}" class="flex items-center gap-3 px-4 py-3 bg-foreground/5 rounded-xl hover:bg-green-500/10 transition-colors">
                    <div class="w-8 h-8 bg-green-500/10 rounded-lg flex items-center justify-center text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase">Open Tickets</p>
                        <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">Customer support requests</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
