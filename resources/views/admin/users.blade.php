@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    showEditModal: false,
    showHistoryModal: false,
    showInsightsModal: false,
    user: {},
    insights: null,
    insightsLoading: false,
    insightsError: null,
    spendingChart: null,
    orderCountChart: null,
    openEdit(u) {
        this.user = JSON.parse(JSON.stringify(u));
        this.showEditModal = true;
    },
    openHistory(u) {
        this.user = u;
        this.showHistoryModal = true;
        // Reuse the insights payload — its `recentOrders` array IS the history.
        // Avoids a second endpoint and keeps both modals consistent.
        this.loadInsights(u);
    },
    openInsights(u) {
        this.user = u;
        this.showInsightsModal = true;
        this.loadInsights(u);
    },
    loadInsights(u) {
        this.insightsLoading = true;
        this.insights = null;
        this.insightsError = null;

        // Reset charts if reopening
        if (this.spendingChart) { this.spendingChart.destroy(); this.spendingChart = null; }
        if (this.orderCountChart) { this.orderCountChart.destroy(); this.orderCountChart = null; }

        const url = '{{ route('admin.users.insights', ['user' => '__USER__']) }}'.replace('__USER__', u.id);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Request failed');
            return res.json();
        })
        .then(data => {
            this.insights = data;
            this.insightsLoading = false;

            this.$nextTick(() => {
                const labels = data?.spendingAnalytics?.labels || [];

                // Charts only render when the Insights modal is open — History
                // shares the same payload but has no canvases.
                const probe = document.getElementById('insightsSpendingChart');
                if (!probe) return;

                // Read theme-aware colors from the actual DOM so the charts
                // are legible in both light and dark mode. Chart.js can't
                // resolve CSS vars inside string color values.
                const textColor = getComputedStyle(probe).color;
                const gridColor = textColor.replace(')', ' / 0.12)').replace('rgb', 'rgba');

                if (labels.length > 0) {
                    const spendingEl = document.getElementById('insightsSpendingChart');
                    if (spendingEl) {
                        this.spendingChart = new Chart(spendingEl, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Monthly Spending',
                                    data: data.spendingAnalytics.monthlySpending,
                                    borderColor: 'rgb(59, 130, 246)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 6,
                                    pointBackgroundColor: 'rgb(59, 130, 246)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: true, labels: { color: textColor, font: { weight: 'bold', size: 12 } } },
                                    tooltip: { titleColor: textColor, bodyColor: textColor }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            color: textColor,
                                            callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID')
                                        },
                                        grid: { color: gridColor }
                                    },
                                    x: { ticks: { color: textColor }, grid: { display: false } }
                                }
                            }
                        });
                    }

                    const orderEl = document.getElementById('insightsOrderCountChart');
                    if (orderEl) {
                        this.orderCountChart = new Chart(orderEl, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Orders per Month',
                                    data: data.spendingAnalytics.monthlyOrderCounts,
                                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderWidth: 2,
                                    borderRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: true, labels: { color: textColor, font: { weight: 'bold', size: 12 } } },
                                    tooltip: { titleColor: textColor, bodyColor: textColor }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { color: textColor, stepSize: 1 },
                                        grid: { color: gridColor }
                                    },
                                    x: { ticks: { color: textColor }, grid: { display: false } }
                                }
                            }
                        });
                    }
                }
            });
        })
        .catch(() => {
            this.insightsLoading = false;
            this.insights = null;
            this.insightsError = 'Failed to load insights. Please try again.';
        });
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Customers</h1>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Points</th>
                        <th class="px-5 py-3">Orders</th>
                        <th class="px-5 py-3">Referral</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($users as $u)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $u->id }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-foreground/10 flex items-center justify-center text-[10px] font-black uppercase shrink-0">
                                    {{ substr($u->name, 0, 2) }}
                                </div>
                                <span class="text-xs font-bold">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-xs text-muted-foreground">{{ $u->email }}</td>
                        <td class="px-5 py-3">
                            @if($u->role === 'banned')
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-red-500/10 text-red-500">Banned</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-green-500/10 text-green-500">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($u->role === 'admin')
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-primary/10 text-primary">Admin</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground">Buyer</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs font-bold text-yellow-500">{{ number_format($u->points_balance) }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $u->orders_count }}</td>
                        <td class="px-5 py-3 text-[10px] text-muted-foreground">
                            @if($u->referral_code)
                                <span class="font-mono font-bold text-primary">{{ $u->referral_code }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <button @click="openInsights({{ json_encode($u) }})" class="px-2.5 py-1.5 bg-primary/10 text-primary rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/20 transition-colors">Insights</button>
                                <button @click="openHistory({{ json_encode($u) }})" class="px-2.5 py-1.5 bg-blue-500/10 text-blue-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-500/20 transition-colors">History</button>

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-border">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="showEditModal" @click.self="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-card border border-border rounded-3xl shadow-2xl w-full max-w-lg overflow-y-auto max-h-[95vh]">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT USER</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + user.id + ' — ' + user.name"></p>
                    </div>
                    <button type="button" @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ route('admin.users') }}/' + user.id" class="flex flex-col gap-4 mt-2">
                    @csrf @method('PATCH')
                    
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINTS BALANCE <span class="text-red-500">*</span></label>
                        <input type="number" name="points_balance" x-model="user.points_balance" required min="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                    </div>
                    
                    <!-- Hidden role input to pass backend validation -->
                    <input type="hidden" name="role" :value="user.role">
                    
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE USER</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Insights Modal -->
    <div x-show="showInsightsModal" @click.self="showInsightsModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-card border border-border rounded-3xl shadow-2xl w-full max-w-5xl overflow-y-auto max-h-[95vh]">
            <div class="p-6 sm:p-8 flex flex-col gap-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black uppercase tracking-tighter text-foreground">Customer Insights</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="user.name ? (user.name + ' — Insights') : ''"></p>
                    </div>
                    <button type="button" @click="showInsightsModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <!-- Loading State -->
                <div x-show="insightsLoading" class="border-2 border-border/50 rounded-2xl p-6 bg-foreground/5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full border-2 border-primary/30 border-t-primary animate-spin"></div>
                        <div>
                            <p class="text-sm font-bold">Loading customer analytics…</p>
                            <p class="text-[10px] text-muted-foreground">Please wait.</p>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div x-show="!insightsLoading && insightsError" x-cloak class="border-2 border-red-500/40 bg-red-500/10 rounded-2xl p-6">
                    <p class="text-sm font-bold text-red-500" x-text="insightsError"></p>
                </div>

                <!-- Content -->
                <div x-show="!insightsLoading && insights" class="space-y-6">
                    <!-- Section 1: User Overview -->
                    <div class="bg-foreground/5 border-2 border-border/50 rounded-2xl p-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Name</p>
                                <p class="text-sm font-bold" x-text="insights?.user?.name || '—'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Email</p>
                                <p class="text-sm font-bold text-muted-foreground break-all" x-text="insights?.user?.email || '—'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Role</p>
                                <p class="text-sm font-bold" x-text="insights?.user?.role ? insights.user.role.toUpperCase() : '—'"></p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Registration date</p>
                                <p class="text-sm font-bold" x-text="insights?.user?.registrationDate || '—'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Referral code</p>
                                <p class="text-sm font-mono font-bold text-primary" x-text="insights?.user?.referralCode || '—'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Current points balance</p>
                                <p class="text-2xl font-black text-yellow-500" x-text="insights?.user?.pointsBalance != null ? Number(insights.user.pointsBalance).toLocaleString('id-ID') : '—'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Purchase Metrics -->
                    <div class="bg-foreground/5 border-2 border-border/50 rounded-2xl p-5">
                        <h3 class="text-lg font-black uppercase tracking-tighter mb-4">Purchase Metrics</h3>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-card border border-border rounded-2xl p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Total orders</p>
                                <p class="text-2xl font-black text-foreground" x-text="insights?.overview?.totalOrders != null ? Number(insights.overview.totalOrders) : 0"></p>
                            </div>
                            <div class="bg-card border border-border rounded-2xl p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Lifetime spending</p>
                                <p class="text-xl font-black text-green-500" x-text="insights?.overview?.totalSpent != null ? ('Rp ' + Number(insights.overview.totalSpent).toLocaleString('id-ID')) : 'Rp 0'"></p>
                            </div>
                            <div class="bg-card border border-border rounded-2xl p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Average order value</p>
                                <p class="text-xl font-black text-primary" x-text="insights?.overview?.averageOrderValue != null ? ('Rp ' + Number(insights.overview.averageOrderValue).toLocaleString('id-ID')) : 'Rp 0'"></p>
                            </div>
                            <div class="bg-card border border-border rounded-2xl p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Last purchase date</p>
                                <p class="text-sm font-bold" x-text="insights?.overview?.lastPurchaseDate || 'Never'"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div class="bg-card border border-border rounded-2xl p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Most purchased product</p>
                                <template x-if="insights?.overview?.topProduct">
                                    <div>
                                        <p class="text-sm font-bold" x-text="insights.overview.topProduct.name"></p>
                                        <p class="text-[10px] text-muted-foreground" x-text="insights.overview.topProduct.categoryName ? insights.overview.topProduct.categoryName : 'N/A'"></p>
                                    </div>
                                </template>
                                <template x-if="!insights?.overview?.topProduct">
                                    <p class="text-muted-foreground text-sm">—</p>
                                </template>
                            </div>
                            <div class="bg-card border border-border rounded-2xl p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">Most purchased category</p>
                                <template x-if="insights?.overview?.topCategory">
                                    <p class="text-sm font-bold" x-text="insights.overview.topCategory.name"></p>
                                </template>
                                <template x-if="!insights?.overview?.topCategory">
                                    <p class="text-muted-foreground text-sm">—</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Charts -->
                    @php $hasChart = "(insights?.spendingAnalytics?.labels || []).length > 0"; @endphp
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-card border border-border rounded-2xl p-5">
                            <h3 class="text-lg font-black uppercase tracking-tighter mb-4">Monthly spending line</h3>
                            <div class="w-full h-[320px] relative" x-show="{{ $hasChart }}">
                                <canvas id="insightsSpendingChart"></canvas>
                            </div>
                            <div class="w-full h-[320px] flex flex-col items-center justify-center text-muted-foreground" x-show="!({{ $hasChart }})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2 opacity-60"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                <p class="text-sm font-bold">No orders</p>
                            </div>
                        </div>

                        <div class="bg-card border border-border rounded-2xl p-5">
                            <h3 class="text-lg font-black uppercase tracking-tighter mb-4">Monthly order count</h3>
                            <div class="w-full h-[320px] relative" x-show="{{ $hasChart }}">
                                <canvas id="insightsOrderCountChart"></canvas>
                            </div>
                            <div class="w-full h-[320px] flex flex-col items-center justify-center text-muted-foreground" x-show="!({{ $hasChart }})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2 opacity-60"><path d="M3 3v18h18"/><rect x="7" y="12" width="3" height="6"/><rect x="14" y="8" width="3" height="10"/></svg>
                                <p class="text-sm font-bold">No orders</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Top Purchases -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-card border border-border rounded-2xl p-5">
                            <h3 class="text-lg font-black uppercase tracking-tighter mb-4">Top 5 products purchased</h3>
                            <template x-if="(insights?.topPurchases?.products || []).length > 0">
                                <div class="space-y-3">
                                    <template x-for="(p, idx) in insights.topPurchases.products" :key="p.id">
                                        <div class="flex items-center justify-between p-3 bg-foreground/5 rounded-lg">
                                            <div class="flex-1">
                                                <p class="text-sm font-bold" x-text="p.name"></p>
                                                <p class="text-[10px] text-muted-foreground" x-text="p.category?.name || 'N/A'"></p>
                                            </div>
                                            <p class="text-sm font-black text-primary shrink-0">#<span x-text="idx + 1"></span></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="(insights?.topPurchases?.products || []).length === 0">
                                <p class="text-muted-foreground text-sm text-center py-8">No purchases.</p>
                            </template>
                        </div>

                        <div class="bg-card border border-border rounded-2xl p-5">
                            <h3 class="text-lg font-black uppercase tracking-tighter mb-4">Top 5 categories purchased</h3>
                            <template x-if="(insights?.topPurchases?.categories || []).length > 0">
                                <div class="space-y-3">
                                    <template x-for="c in insights.topPurchases.categories" :key="c.id">
                                        <div class="p-3 bg-foreground/5 rounded-lg">
                                            <p class="text-sm font-bold" x-text="c.name"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="(insights?.topPurchases?.categories || []).length === 0">
                                <p class="text-muted-foreground text-sm text-center py-8">No purchases.</p>
                            </template>
                        </div>
                    </div>

                    <!-- Section 5: Recent Purchase History -->
                    <div class="bg-card border border-border rounded-2xl overflow-hidden">
                        <div class="p-5 border-b border-border">
                            <h3 class="text-lg font-black uppercase tracking-tighter">Recent purchase history</h3>
                            <p class="text-[10px] text-muted-foreground uppercase tracking-widest font-bold mt-1">Latest 10 paid orders</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground border-b border-border">
                                        <th class="px-5 py-3">Invoice</th>
                                        <th class="px-5 py-3">Date</th>
                                        <th class="px-5 py-3">Status</th>
                                        <th class="px-5 py-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <template x-if="(insights?.recentOrders || []).length > 0">
                                        <template x-for="o in insights.recentOrders" :key="o.invoice">
                                            <tr class="hover:bg-foreground/5">
                                                <td class="px-5 py-3 text-xs font-mono font-bold text-primary" x-text="o.invoice"></td>
                                                <td class="px-5 py-3 text-xs text-muted-foreground" x-text="o.date"></td>
                                                <td class="px-5 py-3 text-xs">
                                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-500" x-text="(o.status || '').toUpperCase()"></span>
                                                </td>
                                                <td class="px-5 py-3 text-xs font-bold text-green-500" x-text="'Rp ' + Number(o.total).toLocaleString('id-ID')"></td>
                                            </tr>
                                        </template>
                                    </template>

                                    <template x-if="(insights?.recentOrders || []).length === 0">
                                        <tr>
                                            <td colspan="4" class="px-5 py-12 text-center">
                                                <p class="text-muted-foreground">No paid orders found.</p>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border/50">
                    <button type="button" @click="showInsightsModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CLOSE</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order History Modal -->
    <div x-show="showHistoryModal" @click.self="showHistoryModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="bg-card border border-border rounded-3xl shadow-2xl w-full max-w-3xl overflow-y-auto max-h-[95vh]">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">ORDER HISTORY</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="user.name + '\'s Past Purchases'"></p>
                    </div>
                    <button type="button" @click="showHistoryModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                
                <div class="mt-4 border-2 border-border/50 rounded-2xl overflow-hidden bg-foreground/5">
                    {{-- Loading --}}
                    <div x-show="insightsLoading" class="p-8 flex items-center gap-3 text-muted-foreground">
                        <div class="w-6 h-6 rounded-full border-2 border-primary/30 border-t-primary animate-spin"></div>
                        <p class="text-xs font-bold">Loading order history…</p>
                    </div>

                    {{-- Empty --}}
                    <div x-show="!insightsLoading && (!insights?.recentOrders || insights.recentOrders.length === 0)" class="p-10 flex flex-col items-center justify-center text-muted-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2 opacity-60"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        <p class="text-sm font-bold">No paid orders yet</p>
                    </div>

                    {{-- List --}}
                    <div class="overflow-x-auto" x-show="!insightsLoading && insights?.recentOrders && insights.recentOrders.length > 0">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground border-b-2 border-border/50">
                                    <th class="px-5 py-3">Invoice</th>
                                    <th class="px-5 py-3">Date</th>
                                    <th class="px-5 py-3">Total</th>
                                    <th class="px-5 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-border/50">
                                <template x-for="o in (insights?.recentOrders || [])" :key="o.invoice">
                                    <tr class="hover:bg-foreground/5">
                                        <td class="px-5 py-3 text-xs font-mono font-bold" x-text="o.invoice"></td>
                                        <td class="px-5 py-3 text-xs" x-text="o.date"></td>
                                        <td class="px-5 py-3 text-xs font-bold" x-text="'Rp ' + Number(o.total).toLocaleString('id-ID')"></td>
                                        <td class="px-5 py-3">
                                            <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-green-500/10 text-green-500" x-text="o.status"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    <button type="button" @click="showHistoryModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CLOSE</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endsection
