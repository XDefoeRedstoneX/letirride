<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-8 py-8">
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
                <span class="text-xs font-mono font-black text-muted-foreground">{{ $order->noinv }}</span>
            </div>

            <div class="space-y-4">
                @foreach($order->orderDetails as $detail)
                <div class="flex items-center justify-between py-3 border-b border-border/30 last:border-0">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-foreground/5 rounded-xl flex items-center justify-center p-2">
                            <img src="/products/{{ $detail->product->image ?? 'soundcloud.svg' }}" class="w-full h-full object-contain pixel-render" />
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
</x-app-layout>
