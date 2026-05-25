@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 text-primary hover:text-primary/80 transition-colors mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to Customers
            </a>
            <h1 class="text-3xl font-black tracking-tighter uppercase">{{ $user->name }}'s Dashboard</h1>
            <p class="text-sm text-muted-foreground mt-2">{{ $user->email }} • ID #{{ $user->id }}</p>
        </div>
    </div>

    <!-- User Info & Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Card -->
        <div class="lg:col-span-1">
            <div class="bg-card border-2 border-border rounded-2xl p-6 space-y-5">
                <div class="flex items-center gap-3">
                    <div class="w-16 h-16 rounded-full bg-primary/20 flex items-center justify-center text-3xl font-black uppercase shrink-0">
                        {{ substr($user->name, 0, 2) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold">{{ $user->name }}</p>
                        <p class="text-[10px] text-muted-foreground">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="border-t border-border pt-4 space-y-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">Member Since</p>
                        <p class="text-sm font-bold">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>

                    @if($user->referral_code)
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">Referral Code</p>
                        <p class="text-sm font-mono font-bold text-primary">{{ $user->referral_code }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">Current Points</p>
                        <p class="text-2xl font-black text-yellow-500">{{ number_format($user->points_balance) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics Grid -->
        <div class="lg:col-span-2 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-card border-2 border-border rounded-2xl p-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Total Orders</p>
                    <p class="text-4xl font-black text-foreground">{{ $metrics['totalOrders'] }}</p>
                </div>

                <div class="bg-card border-2 border-border rounded-2xl p-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Lifetime Spending</p>
                    <p class="text-2xl font-black text-green-500">Rp {{ number_format($metrics['totalSpent'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-card border-2 border-border rounded-2xl p-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Average Order Value</p>
                    <p class="text-2xl font-black text-primary">Rp {{ number_format($metrics['averageOrderValue'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-card border-2 border-border rounded-2xl p-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Last Purchase</p>
                    <p class="text-sm font-bold">
                        @if($metrics['lastPurchaseDate'])
                            {{ $metrics['lastPurchaseDate']->format('M d, Y') }}
                        @else
                            <span class="text-muted-foreground">Never</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-card border-2 border-border rounded-2xl p-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Top Product</p>
                    @if($metrics['topProduct'])
                        <p class="text-sm font-bold">{{ $metrics['topProduct']->name }}</p>
                        <p class="text-[10px] text-muted-foreground">{{ $metrics['topProduct']->category->name ?? 'N/A' }}</p>
                    @else
                        <p class="text-muted-foreground text-sm">—</p>
                    @endif
                </div>

                <div class="bg-card border-2 border-border rounded-2xl p-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Top Category</p>
                    @if($metrics['topCategory'])
                        <p class="text-sm font-bold">{{ $metrics['topCategory']->name }}</p>
                    @else
                        <p class="text-muted-foreground text-sm">—</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    @if(count($chartData['labels']) > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-card border-2 border-border rounded-2xl p-6">
            <h2 class="text-lg font-black uppercase tracking-tighter mb-4">Monthly Spending Trend</h2>
            <canvas id="spendingChart" class="max-h-[300px]"></canvas>
        </div>

        <div class="bg-card border-2 border-border rounded-2xl p-6">
            <h2 class="text-lg font-black uppercase tracking-tighter mb-4">Monthly Order Count</h2>
            <canvas id="orderCountChart" class="max-h-[300px]"></canvas>
        </div>
    </div>
    @endif

    <!-- Top Items Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-card border-2 border-border rounded-2xl p-6">
            <h2 class="text-lg font-black uppercase tracking-tighter mb-4">Top Products</h2>
            @if($topProducts->isNotEmpty())
                <div class="space-y-3">
                    @foreach($topProducts as $product)
                    <div class="flex items-center justify-between p-3 bg-foreground/5 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-bold">{{ $product->name }}</p>
                            <p class="text-[10px] text-muted-foreground">{{ $product->category->name ?? 'N/A' }}</p>
                        </div>
                        <p class="text-sm font-black text-primary shrink-0">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted-foreground text-sm text-center py-8">No purchases</p>
            @endif
        </div>

        <div class="bg-card border-2 border-border rounded-2xl p-6">
            <h2 class="text-lg font-black uppercase tracking-tighter mb-4">Top Categories</h2>
            @if($topCategories->isNotEmpty())
                <div class="space-y-3">
                    @foreach($topCategories as $category)
                    <div class="p-3 bg-foreground/5 rounded-lg">
                        <p class="text-sm font-bold">{{ $category->name }}</p>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted-foreground text-sm text-center py-8">No purchases</p>
            @endif
        </div>
    </div>

    <!-- Order History -->
    <div class="bg-card border-2 border-border rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-border">
            <h2 class="text-lg font-black uppercase tracking-tighter">Order History</h2>
        </div>
        
        @if($orderHistory->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground border-b border-border">
                        <th class="px-5 py-3">Invoice</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Items</th>
                        <th class="px-5 py-3">Total</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($orderHistory as $order)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-primary">{{ $order->display_noinv }}</td>
                        <td class="px-5 py-3 text-xs text-muted-foreground">{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $order->orderDetails->count() }} item(s)</td>
                        <td class="px-5 py-3 text-xs font-bold text-green-500">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3">
                            @php
                            $statusColors = [
                                'paid' => 'bg-green-500/10 text-green-500',
                                'pending' => 'bg-yellow-500/10 text-yellow-500',
                                'failed' => 'bg-red-500/10 text-red-500',
                                'cancelled' => 'bg-red-500/10 text-red-500',
                                'completed' => 'bg-blue-500/10 text-blue-500',
                            ];
                            @endphp
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $statusColors[$order->status] ?? 'bg-gray-500/10 text-gray-500' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-border">
            {{ $orderHistory->links() }}
        </div>
        @else
        <div class="p-12 text-center">
            <p class="text-muted-foreground">No orders found</p>
        </div>
        @endif
    </div>
</div>

<!-- Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    @if(count($chartData['labels']) > 0)
    // Spending Chart
    const spendingCtx = document.getElementById('spendingChart');
    if (spendingCtx) {
        new Chart(spendingCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [{
                    label: 'Monthly Spending',
                    data: {!! json_encode($chartData['spending']) !!},
                    borderColor: 'rgb(var(--color-primary))',
                    backgroundColor: 'rgba(var(--color-primary), 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgb(var(--color-primary))',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: true, labels: { color: 'rgb(var(--color-foreground))', font: { weight: 'bold', size: 12 } } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'rgb(var(--color-muted-foreground))', callback: v => 'Rp ' + v.toLocaleString('id-ID') },
                        grid: { color: 'rgba(var(--color-border), 0.1)' }
                    },
                    x: { ticks: { color: 'rgb(var(--color-muted-foreground))' }, grid: { display: false } }
                }
            }
        });
    }

    // Order Count Chart
    const orderCountCtx = document.getElementById('orderCountChart');
    if (orderCountCtx) {
        new Chart(orderCountCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [{
                    label: 'Orders per Month',
                    data: {!! json_encode($chartData['orderCounts']) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: true, labels: { color: 'rgb(var(--color-foreground))', font: { weight: 'bold', size: 12 } } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'rgb(var(--color-muted-foreground))', stepSize: 1 },
                        grid: { color: 'rgba(var(--color-border), 0.1)' }
                    },
                    x: { ticks: { color: 'rgb(var(--color-muted-foreground))' }, grid: { display: false } }
                }
            }
        });
    }
    @endif
</script>

@endsection
