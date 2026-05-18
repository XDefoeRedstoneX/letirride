@php
    use Illuminate\Support\Js;
@endphp
<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8"
             x-data="transactionsPage({{ Js::from($orders->where('status', 'PENDING')->pluck('order_id')->values()) }}, '{{ csrf_token() }}')"
             x-init="setTimeout(() => show = true, 50)">
            <div>
                <h1 class="px-heading">Transaction <span class="gold">History</span></h1>
                <p class="px-subheading">KEEP TRACK OF YOUR PURCHASES AND POINT REDEMPTIONS</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            @if($orders->count() > 0)
            <div class="px-card-static" style="overflow:hidden;">
                <div style="overflow-x:auto;">
                    <table class="px-table">
                        <thead><tr><th>TRANSACTION ID</th><th>PRODUCT / ITEM</th><th>AMOUNT</th><th>STATUS</th><th>DATE</th><th>ACTION</th></tr></thead>
                        <tbody>
                            @foreach($orders as $trx)
                            <tr>
                                <td style="font-family:var(--font-mono);font-size:12px;color:var(--text-dim);">{{ $trx['id'] }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <div style="width:36px;height:36px;background:var(--dark-card2);border:2px solid var(--dark-line);display:flex;align-items:center;justify-content:center;padding:6px;">
                                            <img src="{{ $trx['image'] }}" class="w-full h-full object-contain pixel-render" />
                                        </div>
                                        <span style="font-weight:800;font-size:13px;">{{ $trx['name'] }}</span>
                                    </div>
                                </td>
                                <td style="font-weight:800;font-size:13px;">Rp {{ number_format($trx['amount'], 0, ',', '.') }}</td>
                                <td>
                                    <span class="px-badge @if($trx['status'] === 'PAID') px-badge-green @elseif($trx['status'] === 'PENDING') px-badge-amber @else px-badge-red @endif">{{ $trx['status'] }}</span>
                                </td>
                                <td style="font-size:12px;color:var(--text-dim);">{{ $trx['date'] }}</td>
                                <td>
                                    <a href="{{ route('checkout.finish', $trx['order_id']) }}"
                                       class="@if($trx['status'] === 'PENDING') px-btn-gold @else px-btn-ghost @endif"
                                       style="padding:6px 12px;font-size:6px;text-decoration:none;display:inline-block;">
                                        {{ $trx['status'] === 'PENDING' ? 'RESUME' : 'VIEW' }}
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="px-empty-state">
                <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/><path d="M2 12h20"/><path d="m7 7-5 5 5 5"/><path d="m17 7 5 5-5 5"/></svg></div>
                <p class="empty-text">NO TRANSACTIONS YET</p>
                <a href="{{ route('home') }}" class="empty-link">START SHOPPING →</a>
            </div>
            @endif
        </div>
    </div>

    <script>
    function transactionsPage(pendingOrders, csrfToken) {
        return {
            show: false,
            pendingOrders,
            pollInterval: null,
            csrfToken,
            init() {
                if (this.pendingOrders.length > 0) {
                    this.pollInterval = setInterval(() => this.checkPendingStatuses(), 15000);
                }
            },
            async checkPendingStatuses() {
                for (const orderId of this.pendingOrders) {
                    try {
                        const r = await fetch('/checkout/verify/' + orderId, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }
                        });
                        if (r.ok) {
                            const data = await r.json();
                            if (data.status !== 'pending') {
                                clearInterval(this.pollInterval);
                                window.location.reload();
                                return;
                            }
                        }
                    } catch (e) {}
                }
            }
        };
    }
    </script>
</x-app-layout>
