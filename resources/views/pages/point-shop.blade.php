<x-app-layout>
    @auth
        <div class="space-y-8" x-data="{ 
            redeemingId: null,
            favorites: [],
            toggleFavorite(id) {
                const idx = this.favorites.indexOf(id);
                if (idx === -1) { this.favorites.push(id); }
                else { this.favorites.splice(idx, 1); }
            },
            async redeem(itemId) {
                if (this.redeemingId) return;
                this.redeemingId = itemId;
                const response = await fetch('/point-shop/redeem/' + itemId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                this.redeemingId = null;
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Redemption failed.');
                }
            }
        }">
            <!-- Header -->
            <div class="text-center md:text-left">
                <h1 class="text-4xl font-black tracking-tighter uppercase">Point <span class="text-primary">Shop</span></h1>
                <p class="text-muted-foreground font-medium">Exchange your hard-earned points for exclusive rewards and vouchers.</p>
            </div>

            <!-- User Points Card -->
            <div class="bg-gradient-to-r from-yellow-500/20 to-amber-500/20 border border-yellow-500/30 rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-yellow-500/20 rounded-full flex items-center justify-center text-yellow-500 shadow-[0_0_20px_rgba(234,179,8,0.2)]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-widest">Available Points</p>
                        <p class="text-4xl font-black text-yellow-600 dark:text-yellow-500">{{ number_format(Auth::user()->points_balance) }}</p>
                    </div>
                </div>
            </div>

            <!-- Shop Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($shopItems as $item)
                    <div class="group glass-card rounded-[2rem] overflow-hidden hover:shadow-2xl hover:shadow-primary/10 transition-all duration-500 hover:-translate-y-2 animate-in fade-in zoom-in duration-300">
                        <div class="aspect-square relative bg-white/5 overflow-hidden flex items-center justify-center group-hover:bg-primary/5 transition-colors duration-500">
                            <img src="{{ $item['image'] }}" class="w-full h-full object-contain group-hover:scale-110 group-hover:rotate-3 transition-transform duration-700 pixel-render" />
                        </div>
                        <div class="relative flex items-center justify-between px-6 py-3 bg-gradient-to-r from-primary/[0.04] via-transparent to-primary/[0.04] border-y border-primary/10">
                            <div class="absolute left-0 top-2 bottom-2 w-[3px] rounded-r-full bg-gradient-to-b from-transparent via-primary/40 to-transparent"></div>
                            <div class="flex items-center gap-2 pl-1.5">
                                <span class="px-3 py-1 rounded-full bg-black/40 backdrop-blur-md text-[8px] font-black border border-white/10 uppercase tracking-widest text-white">{{ $item['reward_type'] }}</span>
                            </div>
                            <button @click="toggleFavorite({{ $item['id'] }})" 
                                    class="w-9 h-9 flex items-center justify-center rounded-xl backdrop-blur-md border border-white/10 transition-all"
                                    :class="favorites.includes({{ $item['id'] }}) ? 'bg-red-500/20 text-red-500' : 'bg-black/20 text-white/70 hover:text-white'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" :fill="favorites.includes({{ $item['id'] }}) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            </button>
                        </div>
                        <div class="p-6 space-y-4">
                            <h3 class="font-black text-base leading-tight">{{ $item['name'] }}</h3>
                            <p class="text-xs text-muted-foreground font-medium">{{ $item['description'] }}</p>
                            <div class="flex items-center justify-between pt-2">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Cost</span>
                                    <div class="flex items-center gap-1.5 text-yellow-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                                        <span class="font-black text-lg">{{ number_format($item['point_cost']) }}</span>
                                    </div>
                                </div>
                                <button @click="redeem({{ $item['id'] }})" :disabled="redeemingId === {{ $item['id'] }}"
                                        class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl font-black text-xs hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/20 tracking-widest uppercase disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-text="redeemingId === {{ $item['id'] }} ? 'Redeeming...' : 'Redeem'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
        </div>
        </div>
    @else
        <div class="min-h-[70vh] flex flex-col items-center justify-center text-center space-y-12 p-6">
            <div class="space-y-6 max-w-xl">
                <div class="w-24 h-24 bg-primary/10 rounded-[2.5rem] flex items-center justify-center text-primary mx-auto mb-8 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                </div>
                <h1 class="text-5xl font-black tracking-tighter leading-tight">Elevate Your Experience with <span class="text-primary">Points</span></h1>
                <p class="text-muted-foreground text-lg font-medium leading-relaxed">Join now to start earning points from every transaction and redeem them for site-wide exclusive discounts.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 w-full max-w-md">
                <button @click="$dispatch('open-auth-modal', { tab: 'login' })" 
                        class="flex-1 px-8 py-5 bg-primary text-primary-foreground font-black rounded-[1.5rem] shadow-2xl shadow-primary/30 hover:scale-105 active:scale-95 transition-all text-sm tracking-widest uppercase">
                    Login
                </button>
                <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" 
                        class="flex-1 px-8 py-5 glass-card font-black rounded-[1.5rem] hover:bg-white/20 hover:scale-105 active:scale-95 transition-all text-sm tracking-widest uppercase">
                    Get Started
                </button>
            </div>
        </div>
    @endauth
</x-app-layout>
