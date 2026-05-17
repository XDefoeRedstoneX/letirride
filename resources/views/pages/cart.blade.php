@php
    use \Illuminate\Support\Js;
@endphp
<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8" x-data="{
            items: {{ Js::from($cartItems ?? []) }},
            discounts: {{ Js::from($userDiscounts ?? []) }},
            selectedDiscount: null,
            topupCredentials: {},
            paying: false,
            pendingOrder: {{Js::from($pendingOrder ?? null) }},
            csrfToken: '{{ csrf_token() }}',
            midtransClientKey: '{{ $midtransClientKey ?? '' }}',
            init() {
                this.items.forEach(item => {
                    if (item.product_type === 'direct_topup' && item.topup_meta) {
                        this.topupCredentials[item.product_id] = { ...item.topup_meta };
                    }
                });
            },
            get hasTopupProducts() { return this.items.some(i => i.product_type === 'direct_topup'); },
            get cartCategoryIds() { return [...new Set(this.items.map(i => i.category_id))]; },
            get eligibleDiscounts() {
                return this.discounts.filter(d => {
                    if (!d.target_category_id) return true;
                    return this.cartCategoryIds.includes(d.target_category_id);
                });
            },
            get subtotal() { return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0); },
            get discountAmount() {
                if (!this.selectedDiscount) return 0;
                const d = this.selectedDiscount;
                if (d.target_category_id) {
                    const eligibleTotal = this.items.filter(i => i.category_id === d.target_category_id).reduce((sum, i) => sum + (i.price * i.quantity), 0);
                    if (d.type === 'percent') return eligibleTotal * (d.value / 100);
                    return Math.min(d.value, eligibleTotal);
                }
                if (d.type === 'percent') return this.subtotal * (d.value / 100);
                return Math.min(d.value, this.subtotal);
            },
            get total() { return Math.max(0, this.subtotal - this.discountAmount); },
            selectVoucher(event) { this.selectedDiscount = this.eligibleDiscounts.find(d => d.id == event.target.value) || null; },
            async updateQty(item, delta) {
                const newQty = item.quantity + delta;
                if (newQty < 1) { this.removeItem(item); return; }
                const response = await fetch(`/cart/${item.id}`, { method: 'PATCH', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfToken }, body: JSON.stringify({ quantity: newQty }) });
                if (response.ok) { item.quantity = newQty; const data = await response.json(); window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } })); }
            },
            async removeItem(item) {
                const response = await fetch(`/cart/${item.id}`, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfToken } });
                if (response.ok) { this.items = this.items.filter(i => i.id !== item.id); const data = await response.json(); window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } })); if (this.selectedDiscount && this.selectedDiscount.target_category_id) { if (!this.items.some(i => i.category_id === this.selectedDiscount.target_category_id)) this.selectedDiscount = null; } }
            },
            async pay() {
                if (this.items.length === 0 || this.paying) return;
                this.paying = true;
                for (const item of this.items.filter(i => i.product_type === 'direct_topup')) {
                    const creds = this.topupCredentials[item.product_id];
                    if (!creds || !creds.player_id || creds.player_id.trim() === '') { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Please enter your Player ID for ' + item.name + '.', type: 'error' } })); this.paying = false; return; }
                }
                try {
                    const response = await fetch('/checkout', { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfToken }, body: JSON.stringify({ user_discount_id: this.selectedDiscount?.id || null }) });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422 && data.order_id) { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message + ` <a href='/checkout/finish/${data.order_id}' class='underline font-black'>View Order</a>`, type: 'warning', duration: 8000 } })); }
                        else { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Checkout failed.', type: 'error' } })); }
                        this.paying = false; return;
                    }
                    if (typeof window.snap === 'undefined') { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment gateway not loaded. Please refresh.', type: 'error' } })); this.paying = false; return; }
                    const csrfToken = this.csrfToken, orderId = data.order_id, self = this;
                    window.snap.pay(data.snap_token, {
                        onSuccess: async function() { try { await fetch('/checkout/verify/' + orderId, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } }); } catch(err) {} self.paying = false; window.location.href = '/transactions'; },
                        onPending: function() { self.paying = false; window.location.href = '/transactions'; },
                        onError: function() { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment failed.', type: 'error' } })); self.paying = false; },
                        onClose: function() { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment cancelled. Order still pending.', type: 'warning' } })); self.paying = false; window.location.href = '/transactions'; }
                    });
                } catch (e) { this.paying = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error.', type: 'error' } })); }
            },
            onSnapFinish() { this.paying = false; },
            formatRp(amount) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount); }
        }">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div><h1 class="px-heading">Order <span class="gold">Receipt</span></h1><p class="px-subheading">REVIEW YOUR ITEMS BEFORE CHECKOUT</p></div>
                <div style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;background:rgba(245,158,11,0.1);border:2px solid rgba(245,158,11,0.25);color:var(--gold);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/></svg>
                </div>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <template x-for="item in items" :key="item.id">
                        <div class="px-card" style="padding:20px;display:flex;align-items:center;gap:20px;">
                            <div style="width:80px;height:80px;background:var(--dark-card2);border:2px solid var(--dark-line);display:flex;align-items:center;justify-content:center;padding:12px;flex-shrink:0;"><img :src="item.image" class="w-full h-full object-contain pixel-render" /></div>
                            <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    <span class="px-badge px-badge-gold" x-text="item.category"></span>
                                    <button @click="removeItem(item)" style="color:var(--text-dim);cursor:pointer;background:none;border:none;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='var(--text-dim)'"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></button>
                                </div>
                                <h3 style="font-family:var(--font-sans);font-size:14px;font-weight:800;color:#e8f0ff;" x-text="item.name"></h3>
                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <button @click="updateQty(item,-1)" class="px-btn-ghost" style="width:28px;height:28px;padding:0;display:flex;align-items:center;justify-content:center;font-size:14px;font-family:var(--font-sans);">−</button>
                                        <span style="font-family:var(--font-sans);font-size:14px;font-weight:800;color:#e8f0ff;width:24px;text-align:center;" x-text="item.quantity"></span>
                                        <button @click="updateQty(item,1)" class="px-btn-ghost" style="width:28px;height:28px;padding:0;display:flex;align-items:center;justify-content:center;font-size:14px;font-family:var(--font-sans);">+</button>
                                    </div>
                                    <p style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--gold);" x-text="formatRp(item.price * item.quantity)"></p>
                                </div>
                                <template x-if="item.product_type === 'direct_topup'">
                                    <div style="margin-top:8px;padding:14px;background:rgba(245,158,11,0.08);border:2px solid rgba(245,158,11,0.2);">
                                        <div style="display:flex;align-items:center;gap:6px;color:var(--gold);margin-bottom:10px;"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg><span style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;">DIRECT TOP-UP — ENTER CREDENTIALS</span></div>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <input type="text" placeholder="Player ID *" :value="topupCredentials[item.product_id]?.player_id || ''" @input="topupCredentials[item.product_id]={...(topupCredentials[item.product_id]||{}),player_id:$event.target.value}" class="px-input" style="padding:8px 12px;font-size:12px;" />
                                            <input type="text" placeholder="Zone ID" :value="topupCredentials[item.product_id]?.zone_id || ''" @input="topupCredentials[item.product_id]={...(topupCredentials[item.product_id]||{}),zone_id:$event.target.value}" class="px-input" style="padding:8px 12px;font-size:12px;" />
                                            <input type="text" placeholder="Server ID" :value="topupCredentials[item.product_id]?.server_id || ''" @input="topupCredentials[item.product_id]={...(topupCredentials[item.product_id]||{}),server_id:$event.target.value}" class="px-input" style="padding:8px 12px;font-size:12px;" />
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div x-show="items.length === 0" class="px-empty-state" style="border:3px dashed var(--dark-line);"><div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/></svg></div><p class="empty-text">YOUR CART IS EMPTY</p><a href="{{ route('home') }}" class="empty-link">START SHOPPING →</a></div>
                </div>
                <div class="space-y-6">
                    <div class="px-card-static" style="padding:28px;position:sticky;top:80px;">
                        <div class="px-section-header"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div><span class="section-title">SUMMARY</span></div>
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:var(--text-dim);">SUBTOTAL</span><span style="font-family:var(--font-sans);font-size:14px;font-weight:700;color:#e8f0ff;" x-text="formatRp(subtotal)"></span></div>
                            <template x-if="eligibleDiscounts.length > 0"><div style="display:flex;flex-direction:column;gap:8px;"><label style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:var(--text-dim);">APPLY VOUCHER</label><select @change="selectVoucher($event)" class="px-input" style="padding:10px 14px;font-size:12px;cursor:pointer;"><option value="">None</option><template x-for="d in eligibleDiscounts" :key="d.id"><option :value="d.id" x-text="d.name + (d.type === 'percent' ? ' (' + d.value + '%)' : ' (Rp ' + new Intl.NumberFormat('id-ID').format(d.value) + ')') + (d.target_category_name ? ' — ' + d.target_category_name + ' only' : ' — All products')"></option></template></select></div></template>
                            <template x-if="selectedDiscount"><div style="background:rgba(34,197,94,0.1);border:2px solid rgba(34,197,94,0.25);padding:12px;"><div style="display:flex;justify-content:space-between;"><div style="display:flex;align-items:center;gap:6px;"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" style="color:#22c55e;"><polyline points="20 6 9 17 4 12"/></svg><span style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;color:#22c55e;">VOUCHER APPLIED</span></div><button @click="selectedDiscount=null" style="color:var(--text-dim);cursor:pointer;background:none;border:none;"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button></div><p style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#4ade80;margin-top:4px;" x-text="selectedDiscount.name"></p><p style="font-family:var(--font-sans);font-size:11px;color:rgba(34,197,94,0.7);margin-top:2px;" x-text="selectedDiscount.target_category_name ? 'Applies to: ' + selectedDiscount.target_category_name + ' items only' : 'Applies to all items'"></p></div></template>
                            <template x-if="discountAmount > 0"><div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:#22c55e;">DISCOUNT</span><span style="font-family:var(--font-sans);font-size:14px;font-weight:700;color:#22c55e;" x-text="'- ' + formatRp(discountAmount)"></span></div></template>
                            <div style="height:2px;background:var(--dark-line);"></div>
                            <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:8px;letter-spacing:0.1em;color:#e8f0ff;">GRAND TOTAL</span><span style="font-family:var(--font-sans);font-size:22px;font-weight:800;color:var(--gold);" x-text="formatRp(total)"></span></div>
                        </div>
                        <template x-if="pendingOrder"><div style="margin-top:16px;padding:14px;background:rgba(245,158,11,0.08);border:2px solid rgba(245,158,11,0.2);"><div style="display:flex;align-items:center;gap:6px;color:var(--gold);"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;">PENDING ORDER</span></div><p style="font-family:var(--font-sans);font-size:12px;color:var(--gold);margin-top:6px;">Complete or cancel your pending order first.</p><a :href="'/checkout/finish/' + pendingOrder.id" class="px-btn-gold" style="display:inline-block;margin-top:10px;padding:8px 14px;font-size:6px;">VIEW PENDING ORDER</a></div></template>
                        <button @click="pay()" :disabled="items.length === 0 || paying || pendingOrder" class="px-btn-gold" style="width:100%;padding:18px;margin-top:20px;font-size:8px;display:flex;align-items:center;justify-content:center;gap:8px;"><template x-if="paying"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></template><span x-text="paying ? 'PROCESSING...' : (pendingOrder ? 'COMPLETE PENDING ORDER' : 'PAY NOW')"></span></button>
                        <p style="font-family:var(--px);font-size:6px;text-align:center;color:var(--text-dim);letter-spacing:0.1em;margin-top:12px;">SECURE ENCRYPTED CHECKOUT VIA MIDTRANS</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(config('midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ $midtransClientKey ?? '' }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $midtransClientKey ?? '' }}"></script>
    @endif
    <script>
        document.addEventListener('snap-pay-cart', function(e) {
            var snapToken = e.detail.snapToken, orderId = e.detail.orderId, csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            window.snap.pay(snapToken, {
                onSuccess: async function() { try { await fetch('/checkout/verify/' + orderId, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } }); } catch(err) {} document.querySelector('[x-data]').__x.$data.onSnapFinish(); window.location.href = '/transactions'; },
                onPending: function() { document.querySelector('[x-data]').__x.$data.onSnapFinish(); window.location.href = '/transactions'; },
                onError: function() { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment failed.', type: 'error' } })); document.querySelector('[x-data]').__x.$data.onSnapFinish(); },
                onClose: function() { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment cancelled.', type: 'warning' } })); document.querySelector('[x-data]').__x.$data.onSnapFinish(); window.location.href = '/transactions'; }
            });
        });
    </script>
</x-app-layout>
