<x-app-layout>
    @auth
        <div class="space-y-8" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
            <!-- Header -->
            <div class="text-center md:text-left" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-500" x-transition:enter-start="md:opacity-0 md:translate-y-4" x-transition:enter-end="md:opacity-100 md:translate-y-0">
                <h1 class="text-4xl font-black tracking-tighter uppercase">Point <span class="text-primary">Shop</span></h1>
                <p class="text-muted-foreground font-medium">Exchange your hard-earned points for exclusive rewards and vouchers.</p>
            </div>

            <!-- User Points Card -->
            <div class="bg-gradient-to-r from-yellow-500/20 to-amber-500/20 border border-yellow-500/30 rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-6"
                 x-show="show" x-transition:enter="md:transition md:ease-out md:duration-500 md:delay-100" x-transition:enter-start="md:opacity-0 md:translate-y-4" x-transition:enter-end="md:opacity-100 md:translate-y-0">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-yellow-500/20 rounded-full flex items-center justify-center text-yellow-500 shadow-[0_0_20px_rgba(234,179,8,0.2)]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-widest">Available Points</p>
                        <p class="text-4xl font-black text-yellow-600 dark:text-yellow-500">{{ number_format(Auth::user()->points) }}</p>
                    </div>
                </div>
                {{-- Button removed for auth users as per request --}}
            </div>

            <!-- Shop Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @php
                    $rewards = [
                        ['name' => '5% Discount Voucher', 'cost' => 500, 'type' => 'Voucher', 'image' => '/gacha/voucher.svg'],
                        ['name' => '10% Discount Voucher', 'cost' => 950, 'type' => 'Voucher', 'image' => '/gacha/voucher.svg'],
                        ['name' => '25% Discount Voucher', 'cost' => 2200, 'type' => 'Voucher', 'image' => '/gacha/voucher.svg'],
                        ['name' => 'Rp 10.000 Balance', 'cost' => 1500, 'type' => 'Balance', 'image' => '/gacha/balance.svg'],
                        ['name' => 'Double Luck Booster', 'cost' => 800, 'type' => 'Booster', 'image' => '/gacha/jackpot.svg'],
                        ['name' => 'Exclusive Avatar Frame', 'cost' => 5000, 'type' => 'Cosmetic', 'image' => '/avatars/default.svg'],
                    ];
                @endphp

                @foreach($rewards as $index => $reward)
                    <div class="group glass-card rounded-[2rem] p-6 space-y-6 hover:shadow-2xl hover:shadow-primary/10 transition-all duration-500 hover:-translate-y-2"
                         x-show="show" x-transition:enter="md:transition md:ease-out md:duration-500" x-transition:enter-start="md:opacity-0 md:scale-95" x-transition:enter-end="md:opacity-100 md:scale-100"
                         style="transition-delay: {{ ($index + 3) * 100 }}ms">
                        <div class="aspect-square relative rounded-2xl bg-white/5 overflow-hidden flex items-center justify-center group-hover:bg-primary/5 transition-colors duration-500">
                            <img src="{{ $reward['image'] }}" class="w-24 h-24 object-contain group-hover:scale-110 group-hover:rotate-3 transition-transform duration-700 pixel-render" />
                            <div class="absolute top-3 right-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-[9px] font-black border border-white/20 uppercase tracking-widest">{{ $reward['type'] }}</span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h3 class="font-black text-base leading-tight">{{ $reward['name'] }}</h3>
                            <div class="flex items-center justify-between pt-2">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Cost</span>
                                    <div class="flex items-center gap-1.5 text-yellow-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                                        <span class="font-black text-lg">{{ number_format($reward['cost']) }}</span>
                                    </div>
                                </div>
                                <button class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl font-black text-xs hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/20 tracking-widest uppercase">
                                    Redeem
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="min-h-[70vh] flex flex-col items-center justify-center text-center space-y-12 p-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
            <div class="space-y-6 max-w-xl" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000" x-transition:enter-start="md:opacity-0 md:scale-95 md:translate-y-12" x-transition:enter-end="md:opacity-100 md:scale-100 md:translate-y-0">
                <div class="w-24 h-24 bg-primary/10 rounded-[2.5rem] flex items-center justify-center text-primary mx-auto mb-8 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                </div>
                <h1 class="text-5xl font-black tracking-tighter leading-tight">Elevate Your Experience with <span class="text-primary">Points</span></h1>
                <p class="text-muted-foreground text-lg font-medium leading-relaxed">Join our community to start earning points from every transaction and redeem them for premium digital assets and exclusive vouchers.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 w-full max-w-md" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-300" x-transition:enter-start="md:opacity-0 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:translate-y-0">
                <button @click="$dispatch('open-auth-modal', { tab: 'login' })" 
                        class="flex-1 px-8 py-5 bg-primary text-primary-foreground font-black rounded-[1.5rem] shadow-2xl shadow-primary/30 hover:scale-105 active:scale-95 transition-all text-sm tracking-widest uppercase">
                    Login to Access
                </button>
                <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" 
                        class="flex-1 px-8 py-5 glass-card font-black rounded-[1.5rem] hover:bg-white/20 hover:scale-105 active:scale-95 transition-all text-sm tracking-widest uppercase">
                    Create Account
                </button>
            </div>

            <!-- Staggered Features Preview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full max-w-5xl mt-16">
                @php
                    $features = [
                        ['title' => 'Premium Vouchers', 'desc' => 'Up to 50% discount on top digital products.'],
                        ['title' => 'Exclusive Items', 'desc' => 'Rare assets only available via point redemption.'],
                        ['title' => 'Loyalty Rewards', 'desc' => 'Progressive benefits for active community members.']
                    ];
                @endphp
                @foreach($features as $index => $f)
                    <div class="glass-card p-8 rounded-[2rem] text-left space-y-3 group hover:border-primary/30 transition-all duration-500"
                         x-show="show" x-transition:enter="md:transition md:ease-out md:duration-700" x-transition:enter-start="md:opacity-0 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:translate-y-0"
                         style="transition-delay: {{ ($index + 6) * 150 }}ms">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h3 class="font-black text-sm uppercase tracking-widest">{{ $f['title'] }}</h3>
                        <p class="text-xs text-muted-foreground leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endauth
</x-app-layout>
