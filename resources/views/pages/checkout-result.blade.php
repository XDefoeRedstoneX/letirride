<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-8 py-8" x-data="{
        resuming: false,
        cancelling: false,
        orderStatus: '{{ $order->status }}',
        csrfToken: '{{ csrf_token() }}',
        pollInterval: null,

        init() {
            if (this.orderStatus === 'pending') {
                this.pollInterval = setInterval(() => this.checkStatus(), 10000);
            }
        },

        async checkStatus() {
            try {
                const response = await fetch('/checkout/status/{{ $order->id }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.status !== 'pending') {
                        clearInterval(this.pollInterval);
                        window.location.reload();
                    }
                }
            } catch (e) {
                
            }
        },

        async resumePayment() {
            if (this.resuming) return;
            this.resuming = true;

            try {
                const response = await fetch('/checkout/pay/{{ $order->id }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Unable to resume payment.', type: 'error' } }));
                    this.resuming = false;
                    return;
                }

                if (typeof window.snap === 'undefined') {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment gateway not loaded. Please refresh the page.', type: 'error' } }));
                    this.resuming = false;
                    return;
                }

                const csrfToken = this.csrfToken;
                const orderId = '{{ $order->id }}';
                const self = this;

                window.snap.pay(data.snap_token, {
                    onSuccess: async function() {
                        try {
                            await fetch('/checkout/verify/' + orderId, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                            });
                        } catch(err) {}
                        window.location.reload();
                    },
                    onPending: function() {
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment still pending.', type: 'info' } }));
                        self.resuming = false;
                    },
                    onError: function() {
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment failed.', type: 'error' } }));
                        self.resuming = false;
                    },
                    onClose: async function() {
                        try {
                            await fetch('/checkout/verify/' + orderId, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                            });
                        } catch(err) {}
                        self.resuming = false;
                        window.location.reload();
                    }
                });
            } catch (e) {
                this.resuming = false;
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error.', type: 'error' } }));
            }
        },

        onSnapFinish() {
            this.resuming = false;
        },

        async cancelOrder() {
            if (this.cancelling) return;
            if (!confirm('Are you sure you want to cancel this order?')) return;
            this.cancelling = true;

            try {
                const response = await fetch('/transactions/{{ $order->id }}/cancel', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const data = await response.json();

                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message, type: 'success' } }));
                    clearInterval(this.pollInterval);
                    setTimeout(() => { window.location.href = '/transactions'; }, 1500);
                } else {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Failed to cancel.', type: 'error' } }));
                    this.cancelling = false;
                }
            } catch (e) {
                this.cancelling = false;
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error.', type: 'error' } }));
            }
        }
    }">
        @if($order->status === 'paid')
        <!-- Success -->
        <div class="text-center space-y-6">
            <div class="relative w-24 h-24 mx-auto">
                <div class="absolute inset-0 bg-green-500/20 rounded-full animate-ping"></div>
                <div class="relative w-24 h-24 bg-green-500 rounded-full flex items-center justify-center text-white shadow-2xl shadow-green-500/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Payment <span class="text-primary">Successful!</span></h1>
            <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest">Your digital codes are now in your inventory.</p>
        </div>
        @elseif($order->status === 'pending')
        <!-- Pending -->
        <div class="text-center space-y-6">
            <div class="w-24 h-24 mx-auto bg-amber-500/10 rounded-full flex items-center justify-center text-amber-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Payment <span class="text-amber-500">Pending</span></h1>
            <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest">Complete your payment to receive your items.</p>

            <!-- Resume Payment Button -->
            <div class="flex items-center justify-center gap-4">
                <button @click="resumePayment()" :disabled="resuming"
                        class="inline-flex items-center gap-3 px-10 py-4 font-black rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-xl uppercase tracking-widest text-[10px] disabled:opacity-50 disabled:hover:scale-100"
                        style="background-color: #f59e0b; color: #ffffff; box-shadow: 0 20px 25px -5px rgb(245 158 11 / 0.2);">
                    <template x-if="resuming">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="resuming ? 'Opening Payment...' : 'Resume Payment'"></span>
                </button>

                <!-- Bug 1: Cancel Order Button -->
                <button @click="cancelOrder()" :disabled="cancelling"
                        class="inline-flex items-center gap-3 px-10 py-4 bg-foreground/5 border border-border text-foreground font-black rounded-2xl hover:scale-105 active:scale-95 transition-all uppercase tracking-widest text-[10px] disabled:opacity-50 disabled:hover:scale-100">
                    <span x-text="cancelling ? 'Cancelling...' : 'Cancel Order'"></span>
                </button>
            </div>
        </div>
        @elseif($order->status === 'cancelled')
        <!-- Cancelled -->
        <div class="text-center space-y-6">
            <div class="w-24 h-24 mx-auto bg-muted rounded-full flex items-center justify-center text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
            </div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Order <span class="text-muted-foreground">Cancelled</span></h1>
            <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest">This order has been cancelled.</p>
        </div>
        @else
        <!-- Failed -->
        <div class="text-center space-y-6">
            <div class="w-24 h-24 mx-auto bg-red-500/10 rounded-full flex items-center justify-center text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Payment <span class="text-red-500">Failed</span></h1>
            <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest">Something went wrong. Please try again or contact support.</p>
        </div>
        @endif

        <!-- Order Details Card -->
        <div class="glass-card rounded-[2.5rem] p-8 space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-black uppercase tracking-tighter">Order Details</h3>
                <span class="text-xs font-mono font-black text-muted-foreground">{{ $order->display_noinv }}</span>
            </div>

            <div class="space-y-4">
                @foreach($order->orderDetails as $detail)
                <div class="flex items-center justify-between py-3 border-b border-border/30 last:border-0">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-foreground/5 rounded-xl flex items-center justify-center p-2">
                            <img src="/products/{{ $detail->product->image ?? 'soundcloud.png' }}" class="w-full h-full object-contain pixel-render" />
                        </div>
                        <div>
                            <p class="font-black text-sm">{{ $detail->product->name ?? 'Unknown' }}</p>
                            <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Qty: {{ $detail->quantity }}</p>
                        </div>
                    </div>
                    <p class="font-black text-sm">Rp {{ number_format($detail->total_price_in_cart, 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>

            <div class="space-y-3 pt-4 border-t border-border/50">
                <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-green-500">
                    <span>Discount</span>
                    <span>- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="h-px bg-border"></div>
                <div class="flex justify-between">
                    <span class="text-xs font-black uppercase tracking-widest">Total</span>
                    <span class="text-xl font-black text-primary">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Product Keys (if paid) -->
        @if($order->status === 'paid' && $order->productKeys->count() > 0)
        <div class="glass-card rounded-[2.5rem] p-8 space-y-6">
            <h3 class="text-lg font-black uppercase tracking-tighter">Your Codes</h3>
            <div class="space-y-3" x-data="{ copiedIdx: null }">
                @foreach($order->productKeys as $idx => $key)
                <div class="flex items-center justify-between p-4 bg-foreground/5 rounded-2xl border border-border">
                    <div>
                        <p class="text-[10px] font-black text-primary uppercase tracking-widest">{{ $key->product->name }}</p>
                        <code class="text-sm font-mono font-black text-foreground">{{ $key->key_code }}</code>
                    </div>
                    <button @click="navigator.clipboard.writeText('{{ $key->key_code }}'); copiedIdx = {{ $idx }}; setTimeout(() => copiedIdx = null, 2000)"
                            class="px-4 py-2 bg-primary/10 text-primary rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/20 transition-colors">
                        <span x-text="copiedIdx === {{ $idx }} ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex items-center justify-center gap-4">
            <a href="{{ route('inventory') }}" class="px-10 py-4 bg-primary text-primary-foreground font-black rounded-2xl hover:scale-105 transition-all shadow-xl shadow-primary/20 uppercase tracking-widest text-[10px]">Go to Inventory</a>
            <a href="{{ route('home') }}" class="px-10 py-4 bg-card border-2 border-border text-foreground font-black rounded-2xl hover:scale-105 transition-all shadow-xl uppercase tracking-widest text-[10px]">Continue Shopping</a>
        </div>
    </div>

    <!-- Midtrans Snap.js (needed for pending resume) -->
    @if($order->status === 'pending')
        @if(config('midtrans.is_production'))
            <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        @else
            <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        @endif

        <script>
            document.addEventListener('snap-pay', function(e) {
                var snapToken = e.detail.snapToken;
                var orderId = e.detail.orderId;
                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                window.snap.pay(snapToken, {
                    onSuccess: async function() {
                        try {
                            await fetch('/checkout/verify/' + orderId, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                            });
                        } catch(err) {}
                        window.location.reload();
                    },
                    onPending: function() {
                        // We need to reach into Alpine's scope or just dispatch an event
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment still pending.', type: 'info' } }));
                        document.querySelector('[x-data]').__x.$data.onSnapFinish();
                    },
                    onError: function() {
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment failed.', type: 'error' } }));
                        document.querySelector('[x-data]').__x.$data.onSnapFinish();
                    },
                    onClose: async function() {
                        try {
                            await fetch('/checkout/verify/' + orderId, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                            });
                        } catch(err) {}
                        document.querySelector('[x-data]').__x.$data.onSnapFinish();
                        window.location.reload();
                    }
                });
            });
        </script>
    @endif
</x-app-layout>
