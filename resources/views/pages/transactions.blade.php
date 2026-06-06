@php
    use Illuminate\Support\Js;
@endphp
<x-app-layout>
    <style>
        /* Mobile: drop the wide table and stack each transaction as a compact
           card so nothing requires horizontal scrolling. */
        @media (max-width: 639px) {
            .tx-table thead { display: none; }
            .tx-table, .tx-table tbody, .tx-table tr, .tx-table td { display: block; width: 100%; }
            .tx-table tr {
                padding: 12px 14px;
                border-bottom: 2px solid var(--border);
            }
            .tx-table td {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 5px 0 !important;
                text-align: right;
            }
            /* Label each cell with the column name it replaced. */
            .tx-table td::before {
                content: attr(data-label);
                font-family: var(--px);
                font-size: 6px;
                letter-spacing: 0.1em;
                color: var(--muted-foreground);
                text-transform: uppercase;
                flex-shrink: 0;
                text-align: left;
            }
            /* The product cell leads the card: image + name span the full row,
               no label needed since it's self-evident. */
            .tx-table td[data-label="ITEM"] { padding-bottom: 8px !important; }
            .tx-table td[data-label="ITEM"]::before { display: none; }
            .tx-table td[data-label="ITEM"] > div { width: 100%; }
            .tx-table td[data-label="ITEM"] span { text-align: left; }
            /* Action button cell: full-width tap target, no label. */
            .tx-table td[data-label=""] { padding-top: 8px !important; }
            .tx-table td[data-label=""]::before { display: none; }
            .tx-table td[data-label=""] a { flex: 1; text-align: center; padding: 10px !important; }
        }
    </style>
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
                    <table class="px-table tx-table">
                        <thead><tr><th>TRANSACTION ID</th><th>PRODUCT / ITEM</th><th>AMOUNT</th><th>STATUS</th><th>DATE</th><th>ACTION</th></tr></thead>
                        <tbody>
                            @foreach($orders as $trx)
                            <tr>
                                <td data-label="ID" style="font-family:var(--font-mono);font-size:12px;color:var(--muted-foreground);">{{ $trx['id'] }}</td>
                                <td data-label="ITEM">
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <div style="width:36px;height:36px;background:var(--muted);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;padding:6px;flex-shrink:0;">
                                            <img src="{{ $trx['image'] }}" class="w-full h-full object-contain pixel-render" />
                                        </div>
                                        <span style="font-weight:800;font-size:13px;">{{ $trx['name'] }}</span>
                                    </div>
                                </td>
                                <td data-label="AMOUNT" style="font-weight:800;font-size:13px;">Rp {{ number_format($trx['amount'], 0, ',', '.') }}</td>
                                <td data-label="STATUS">
                                    <span class="px-badge @if($trx['status'] === 'PAID') px-badge-green @elseif($trx['status'] === 'PENDING') px-badge-amber @else px-badge-red @endif">{{ $trx['status'] }}</span>
                                </td>
                                <td data-label="DATE" style="font-size:12px;color:var(--muted-foreground);">{{ $trx['date'] }}</td>
                                <td data-label="">
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
