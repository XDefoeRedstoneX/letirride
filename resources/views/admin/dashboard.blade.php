<x-admin::layouts.admin>
    <div class="space-y-8">
        <h1 class="text-3xl font-black tracking-tighter uppercase">Dashboard</h1>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $cards = [
                    ['label' => 'Total Revenue', 'value' => 'Rp ' . number_format($stats['total_revenue'], 0, ',', '.'), 'color' => 'text-green-500'],
                    ['label' => 'Total Orders', 'value' => $stats['total_orders'], 'color' => 'text-blue-500'],
                    ['label' => 'Paid Orders', 'value' => $stats['paid_orders'], 'color' => 'text-emerald-500'],
                    ['label' => 'Pending Orders', 'value' => $stats['pending_orders'], 'color' => 'text-amber-500'],
                    ['label' => 'Total Users', 'value' => $stats['total_users'], 'color' => 'text-purple-500'],
                    ['label' => 'Active Products', 'value' => $stats['total_products'], 'color' => 'text-primary'],
                ];
            @endphp
            @foreach($cards as $card)
                <div class="bg-card border border-border rounded-2xl p-6 space-y-2">
                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">{{ $card['label'] }}</p>
                    <p class="text-3xl font-black {{ $card['color'] }}">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- Recent Orders -->
        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
                <h3 class="text-sm font-black uppercase tracking-widest">Recent Orders</h3>
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-6 py-4">Invoice</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-foreground/5 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono font-bold">{{ $order->noinv }}</td>
                            <td class="px-6 py-4 text-xs font-bold">{{ $order->user?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-xs font-bold">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest
                                    @if($order->status === 'paid') bg-green-500/10 text-green-500
                                    @elseif($order->status === 'pending') bg-amber-500/10 text-amber-500
                                    @else bg-red-500/10 text-red-500
                                    @endif">{{ $order->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-[10px] text-muted-foreground">{{ $order->created_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts.admin>
