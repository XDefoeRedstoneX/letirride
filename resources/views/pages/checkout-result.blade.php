<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8" style="max-width:800px;" x-data="{
            resuming: false, cancelling: false,
            orderStatus: '{{ $order->status }}',
            csrfToken: '{{ csrf_token() }}',
            pollInterval: null,
            init() { if (this.orderStatus === 'pending') { this.pollInterval = setInterval(() => this.checkStatus(), 10000); } },
            async checkStatus() { try { const r = await fetch('/checkout/status/{{ $order->id }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }); if (r.ok) { const d = await r.json(); if (d.status !== 'pending') { clearInterval(this.pollInterval); window.location.reload(); } } } catch(e) {} },
            async resumePayment() {
                if (this.resuming) return; this.resuming = true;
                try {
                    const r = await fetch('/checkout/pay/{{ $order->id }}', { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfToken } });
                    const d = await r.json();
                    if (!r.ok) { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: d.message || 'Unable to resume.', type: 'error' } })); this.resuming = false; return; }
                    if (typeof window.snap === 'undefined') { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment gateway not loaded.', type: 'error' } })); this.resuming = false; return; }
                    const csrfToken = this.csrfToken, orderId = '{{ $order->id }}', self = this;
                    window.snap.pay(d.snap_token, {
                        onSuccess: async function() { try { await fetch('/checkout/verify/' + orderId, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } }); } catch(e) {} window.location.reload(); },
                        onPending: function() { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment still pending.', type: 'info' } })); self.resuming = false; },
                        onError: function() { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment failed.', type: 'error' } })); self.resuming = false; },
                        onClose: async function() { try { await fetch('/checkout/verify/' + orderId, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } }); } catch(e) {} self.resuming = false; window.location.reload(); }
                    });
                } catch(e) { this.resuming = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error.', type: 'error' } })); }
            },
            onSnapFinish() { this.resuming = false; },
            async cancelOrder() {
                if (this.cancelling) return;
                const ok = await window.openConfirmModal({
                    title: 'Cancel This Order?',
                    message: 'Your reserved items will be released back to stock. This cannot be undone.',
                    confirmLabel: 'YES, CANCEL ORDER',
                    cancelLabel: 'NO, KEEP IT',
                });
                if (!ok) return;
                this.cancelling = true;
                try {
                    const r = await fetch('/transactions/{{ $order->id }}/cancel', { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfToken } });
                    const d = await r.json();
                    if (r.ok) { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: d.message, type: 'success' } })); clearInterval(this.pollInterval); setTimeout(() => { window.location.href = '/transactions'; }, 1500); }
                    else { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: d.message || 'Failed.', type: 'error' } })); this.cancelling = false; }
                } catch(e) { this.cancelling = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error.', type: 'error' } })); }
            }
        }">
            {{-- Status Header --}}
            @if($order->status === 'paid')
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;gap:16px;">
                <div style="width:80px;height:80px;background:rgba(34,197,94,0.15);border:3px solid #22c55e;display:flex;align-items:center;justify-content:center;color:#22c55e;box-shadow:0 0 30px rgba(34,197,94,0.3);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h1 class="px-heading">Payment <span style="color:#22c55e;">Successful!</span></h1>
                <p class="px-subheading">YOUR DIGITAL CODES ARE NOW IN YOUR INVENTORY</p>
            </div>
            @elseif($order->status === 'pending')
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;gap:16px;">
                <div style="width:80px;height:80px;background:rgba(245,158,11,0.15);border:3px solid var(--gold);display:flex;align-items:center;justify-content:center;color:var(--gold);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h1 class="px-heading">Payment <span class="gold">Pending</span></h1>
                <p class="px-subheading">COMPLETE YOUR PAYMENT TO RECEIVE YOUR ITEMS</p>
                <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-top:8px;">
                    <button @click="resumePayment()" :disabled="resuming" class="px-btn-gold" style="padding:14px 28px;font-size:7px;display:flex;align-items:center;gap:8px;">
                        <template x-if="resuming"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></template>
                        <span x-text="resuming ? 'OPENING PAYMENT...' : 'RESUME PAYMENT'"></span>
                    </button>
                    <button @click="cancelOrder()" :disabled="cancelling" class="px-btn-ghost" style="padding:14px 28px;font-size:7px;">
                        <span x-text="cancelling ? 'CANCELLING...' : 'CANCEL ORDER'"></span>
                    </button>
                </div>
            </div>
            @elseif($order->status === 'cancelled')
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;gap:16px;">
                <div style="width:80px;height:80px;background:var(--dark-card2);border:3px solid var(--dark-line);display:flex;align-items:center;justify-content:center;color:var(--text-dim);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                </div>
                <h1 class="px-heading">Order <span style="color:var(--text-dim);">Cancelled</span></h1>
                <p class="px-subheading">THIS ORDER HAS BEEN CANCELLED</p>
            </div>
            @else
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;gap:16px;">
                <div style="width:80px;height:80px;background:rgba(239,68,68,0.1);border:3px solid #ef4444;display:flex;align-items:center;justify-content:center;color:#ef4444;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </div>
                <h1 class="px-heading">Payment <span style="color:#ef4444;">Failed</span></h1>
                <p class="px-subheading">SOMETHING WENT WRONG. PLEASE TRY AGAIN OR CONTACT SUPPORT</p>
            </div>
            @endif

            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Order Details --}}
            <div class="px-card-static" style="padding:28px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <div class="px-section-header" style="border:none;margin:0;padding:0;"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div><span class="section-title">ORDER DETAILS</span></div>
                    <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-dim);">{{ $order->display_noinv }}</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:0;">
                    @foreach($order->orderDetails as $detail)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:2px solid var(--dark-line);">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:36px;height:36px;background:var(--dark-card2);border:2px solid var(--dark-line);display:flex;align-items:center;justify-content:center;padding:6px;"><img src="{{ $detail->product?->imageUrl() ?? '/products/'.\App\Models\Product::FALLBACK_IMAGE }}" class="w-full h-full object-contain pixel-render" /></div>
                            <div><p style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#e8f0ff;">{{ $detail->product->name ?? 'Unknown' }}</p><p style="font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.1em;margin-top:2px;">QTY: {{ $detail->quantity }}</p></div>
                        </div>
                        <p style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#e8f0ff;">Rp {{ number_format($detail->total_price_in_cart, 0, ',', '.') }}</p>
                    </div>
                    @endforeach
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;padding-top:16px;margin-top:16px;border-top:3px solid var(--dark-line);">
                    <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:var(--text-dim);">SUBTOTAL</span><span style="font-family:var(--font-sans);font-size:13px;font-weight:700;color:#e8f0ff;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                    @if($order->discount_amount > 0)
                    <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:#22c55e;">DISCOUNT</span><span style="font-family:var(--font-sans);font-size:13px;font-weight:700;color:#22c55e;">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span></div>
                    @endif
                    <div style="height:2px;background:var(--dark-line);"></div>
                    <div style="display:flex;justify-content:space-between;align-items:center;"><span style="font-family:var(--px);font-size:8px;letter-spacing:0.1em;color:#e8f0ff;">TOTAL</span><span style="font-family:var(--font-sans);font-size:22px;font-weight:800;color:var(--gold);">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</span></div>
                </div>
            </div>

            {{-- Codes Notice (codes only viewable in inventory) --}}
            @if($order->status === 'paid' && $order->productKeys->count() > 0)
            <div class="px-card-static" style="padding:20px;display:flex;align-items:center;gap:14px;">
                <div class="section-icon" style="background:rgba(34,197,94,0.1);border:2px solid rgba(34,197,94,0.25);color:#22c55e;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div>
                    <p style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#e8f0ff;">{{ $order->productKeys->count() }} code(s) ready</p>
                    <p style="font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.1em;margin-top:2px;">VIEW YOUR CODES IN INVENTORY</p>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div style="display:flex;align-items:center;justify-content:center;gap:12px;">
                <a href="{{ route('inventory') }}" class="px-btn-gold" style="padding:14px 28px;font-size:7px;text-decoration:none;">GO TO INVENTORY</a>
                <a href="{{ route('home') }}" class="px-btn-ghost" style="padding:14px 28px;font-size:7px;text-decoration:none;">CONTINUE SHOPPING</a>
            </div>
        </div>
    </div>

    @if($order->status === 'pending')
        @if(config('midtrans.is_production'))
            <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        @else
            <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        @endif
        <script>
            document.addEventListener('snap-pay', function(e) {
                var snapToken = e.detail.snapToken, orderId = e.detail.orderId, csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                window.snap.pay(snapToken, {
                    onSuccess: async function() { try { await fetch('/checkout/verify/' + orderId, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } }); } catch(e) {} window.location.reload(); },
                    onPending: function() { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment still pending.', type: 'info' } })); document.querySelector('[x-data]').__x.$data.onSnapFinish(); },
                    onError: function() { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment failed.', type: 'error' } })); document.querySelector('[x-data]').__x.$data.onSnapFinish(); },
                    onClose: async function() { try { await fetch('/checkout/verify/' + orderId, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } }); } catch(e) {} document.querySelector('[x-data]').__x.$data.onSnapFinish(); window.location.reload(); }
                });
            });
        </script>
    @endif
</x-app-layout>
