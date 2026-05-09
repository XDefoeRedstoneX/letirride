<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-8" x-data="{
        items: {{ \Illuminate\Support\Js::from($cartItems ?? []) }},
        discounts: {{ \Illuminate\Support\Js::from($userDiscounts ?? []) }},
        selectedDiscount: null,
        paying: false,
        paid: false,
        paidOrderId: null,
        csrfToken: '{{ csrf_token() }}',
        midtransClientKey: '{{ $midtransClientKey ?? '' }}',

        get cartCategoryIds() {
            return [...new Set(this.items.map(i => i.category_id))];
        },
        get eligibleDiscounts() {
            return this.discounts.filter(d => {
                if (!d.target_category_id) return true;
                return this.cartCategoryIds.includes(d.target_category_id);
            });
        },
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        get discountAmount() {
            if (!this.selectedDiscount) return 0;
            const d = this.selectedDiscount;
            if (d.target_category_id) {
                const eligibleTotal = this.items
                    .filter(i => i.category_id === d.target_category_id)
                    .reduce((sum, i) => sum + (i.price * i.quantity), 0);
                if (d.type === 'percent') return eligibleTotal * (d.value / 100);
                return Math.min(d.value, eligibleTotal);
            }
            if (d.type === 'percent') return this.subtotal * (d.value / 100);
            return Math.min(d.value, this.subtotal);
        },
        get total() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },

        selectVoucher(event) {
            const id = event.target.value;
            this.selectedDiscount = this.eligibleDiscounts.find(d => d.id == id) || null;
        },

        async updateQty(item, delta) {
            const newQty = item.quantity + delta;
            if (newQty < 1) { this.removeItem(item); return; }

            const response = await fetch(`/cart/${item.id}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({ quantity: newQty }),
            });

            if (response.ok) {
                item.quantity = newQty;
                const data = await response.json();
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
            }
        },

        async removeItem(item) {
            const response = await fetch(`/cart/${item.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });

            if (response.ok) {
                this.items = this.items.filter(i => i.id !== item.id);
                const data = await response.json();
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));

                // If selected voucher is no longer eligible, reset it
                if (this.selectedDiscount && this.selectedDiscount.target_category_id) {
                    const stillHas = this.items.some(i => i.category_id === this.selectedDiscount.target_category_id);
                    if (!stillHas) this.selectedDiscount = null;
                }
            }
        },

        async pay() {
            if (this.items.length === 0 || this.paying) return;
            this.paying = true;

            try {
                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        user_discount_id: this.selectedDiscount?.id || null,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Checkout failed.', type: 'error' } }));
                    this.paying = false;
                    return;
                }

                // Open Midtrans Snap popup
                window.snap.pay(data.snap_token, {
                    onSuccess: (result) => {
                        this.paying = false;
                        this.paid = true;
                        this.paidOrderId = data.order_id;
                        this.items = [];
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: 0 } }));
                    },
                    onPending: (result) => {
                        this.paying = false;
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment pending. Complete your payment to receive your items.', type: 'info' } }));
                        this.items = [];
                        this.paidOrderId = data.order_id;
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: 0 } }));
                    },
                    onError: (result) => {
                        this.paying = false;
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment failed. Please try again.', type: 'error' } }));
                    },
                    onClose: () => {
                        this.paying = false;
                    },
                });
            } catch (e) {
                this.paying = false;
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error. Please try again.', type: 'error' } }));
            }
        },

        formatRp(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }
    }">
        <div class="flex items-center justify-between">
            <div class="space-y-2">
                <h1 class="text-4xl font-black tracking-tighter uppercase leading-none">Order <span class="text-primary">Receipt</span></h1>
                <p class="text-muted-foreground text-[10px] font-black uppercase tracking-widest mt-2">Review your items before checkout.</p>
            </div>
            <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary shadow-lg shadow-primary/10 border border-white/20 backdrop-blur-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/></svg>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Items List -->
            <div class="lg:col-span-2 space-y-4" x-show="!paid" x-transition>
                <template x-for="item in items" :key="item.id">
                    <div class="glass-card rounded-[2.5rem] p-6 flex items-center gap-6 group hover:border-primary/30 transition-all duration-500 border-border/50">
                        <div class="w-24 h-24 bg-foreground/5 rounded-3xl flex items-center justify-center p-4 shrink-0">
                            <img :src="item.image" class="w-full h-full object-contain pixel-render group-hover:scale-110 transition-transform duration-500" />
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-black uppercase tracking-widest text-primary" x-text="item.category"></span>
                                <button @click="removeItem(item)" class="text-muted-foreground hover:text-destructive transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                </button>
                            </div>
                            <h3 class="font-black text-sm uppercase tracking-tight" x-text="item.name"></h3>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <button @click="updateQty(item, -1)" class="w-8 h-8 rounded-xl bg-foreground/5 hover:bg-foreground/10 flex items-center justify-center text-foreground transition-colors font-black text-sm">−</button>
                                    <span class="text-sm font-black w-6 text-center" x-text="item.quantity"></span>
                                    <button @click="updateQty(item, 1)" class="w-8 h-8 rounded-xl bg-foreground/5 hover:bg-foreground/10 flex items-center justify-center text-foreground transition-colors font-black text-sm">+</button>
                                </div>
                                <p class="font-black text-primary" x-text="formatRp(item.price * item.quantity)"></p>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="items.length === 0" class="py-20 text-center glass-card rounded-[3rem] border-dashed border-2 border-border">
                    <p class="text-muted-foreground font-black uppercase tracking-widest text-xs">Your cart is empty.</p>
                    <a href="{{ route('home') }}" class="inline-block mt-4 text-primary font-black uppercase tracking-widest text-[10px] hover:underline">Start Shopping</a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="space-y-6" x-show="!paid" x-transition>
                <div class="glass-card rounded-[2.5rem] p-8 space-y-6 border-border/50 sticky top-24">
                    <h3 class="text-lg font-black uppercase tracking-tighter">Summary</h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                            <span>Subtotal</span>
                            <span x-text="formatRp(subtotal)"></span>
                        </div>

                        <!-- Discount Voucher Selector -->
                        <template x-if="eligibleDiscounts.length > 0">
                            <div class="space-y-3">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Apply Voucher</label>
                                <select @change="selectVoucher($event)"
                                        class="w-full px-4 py-3 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/50">
                                    <option value="">None</option>
                                    <template x-for="d in eligibleDiscounts" :key="d.id">
                                        <option :value="d.id" x-text="d.name + (d.type === 'percent' ? ' (' + d.value + '%)' : ' (Rp ' + new Intl.NumberFormat('id-ID').format(d.value) + ')') + (d.target_category_name ? ' — ' + d.target_category_name + ' only' : ' — All products')"></option>
                                    </template>
                                </select>
                            </div>
                        </template>

                        <!-- Applied Voucher Badge -->
                        <template x-if="selectedDiscount">
                            <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-3 space-y-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-green-500">Voucher Applied</span>
                                    </div>
                                    <button @click="selectedDiscount = null" class="text-muted-foreground hover:text-red-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </div>
                                <p class="text-xs font-black text-green-400" x-text="selectedDiscount.name"></p>
                                <p class="text-[9px] font-bold text-green-500/70" x-text="selectedDiscount.target_category_name ? 'Applies to: ' + selectedDiscount.target_category_name + ' items only' : 'Applies to all items'"></p>
                            </div>
                        </template>

                        <template x-if="discountAmount > 0">
                            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-green-500">
                                <span>Discount</span>
                                <span x-text="'- ' + formatRp(discountAmount)"></span>
                            </div>
                        </template>

                        <div class="h-px bg-border"></div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-widest">Grand Total</span>
                            <span class="text-xl font-black text-primary" x-text="formatRp(total)"></span>
                        </div>
                    </div>

                    <button @click="pay()" :disabled="items.length === 0 || paying"
                            class="w-full py-5 bg-primary text-primary-foreground font-black rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/20 uppercase tracking-widest text-xs flex items-center justify-center gap-3 disabled:opacity-50 disabled:hover:scale-100">
                        <template x-if="paying">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="paying ? 'Processing...' : 'Pay Now'"></span>
                    </button>

                    <p class="text-[8px] font-black text-center text-muted-foreground uppercase tracking-widest">Secure encrypted checkout via Midtrans</p>
                </div>
            </div>

            <!-- Success State -->
            <div class="col-span-3 py-20 text-center space-y-8" x-show="paid" x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-1000" x-transition:enter-start="opacity-0 scale-50 translate-y-12" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                <div class="relative w-32 h-32 mx-auto">
                    <div class="absolute inset-0 bg-primary/20 rounded-full animate-ping"></div>
                    <div class="relative w-32 h-32 bg-primary rounded-full flex items-center justify-center text-primary-foreground shadow-2xl shadow-primary/50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </div>

                <div class="space-y-3">
                    <h2 class="text-4xl font-black tracking-tighter uppercase">Payment <span class="text-primary">Successful!</span></h2>
                    <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest leading-relaxed max-w-md mx-auto">Your order has been processed. The digital codes will be available in your <a href="{{ route('inventory') }}" class="text-foreground underline">Inventory</a> shortly.</p>
                </div>

                <div class="pt-6 flex items-center justify-center gap-4">
                    <a href="{{ route('inventory') }}" class="px-10 py-4 bg-primary text-primary-foreground font-black rounded-2xl hover:scale-105 transition-all shadow-xl shadow-primary/20 uppercase tracking-widest text-[10px]">Go to Inventory</a>
                    <a href="{{ route('home') }}" class="px-10 py-4 bg-card border-2 border-border text-foreground font-black rounded-2xl hover:scale-105 transition-all shadow-xl uppercase tracking-widest text-[10px]">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Midtrans Snap.js -->
    @if(config('midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ $midtransClientKey ?? '' }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $midtransClientKey ?? '' }}"></script>
    @endif
</x-app-layout>