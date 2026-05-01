<x-app-layout>
    @auth
        <div class="space-y-8" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
            <!-- Header -->
            <div class="text-center md:text-left" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-500" x-transition:enter-start="md:opacity-0 md:translate-y-4" x-transition:enter-end="md:opacity-100 md:translate-y-0">
                <h1 class="text-3xl font-bold tracking-tight">Point Shop</h1>
                <p class="text-muted-foreground">Exchange your hard-earned points for exclusive rewards and vouchers.</p>
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
                    <div class="group bg-card border border-border rounded-2xl p-5 space-y-4 hover:border-primary/50 transition-all hover:shadow-lg"
                         x-show="show" x-transition:enter="md:transition md:ease-out md:duration-500" x-transition:enter-start="md:opacity-0 md:scale-95" x-transition:enter-end="md:opacity-100 md:scale-100"
                         style="transition-delay: {{ ($index + 3) * 100 }}ms">
                        <div class="aspect-square relative rounded-xl bg-muted overflow-hidden flex items-center justify-center">
                            <img src="{{ $reward['image'] }}" class="w-20 h-20 object-contain group-hover:scale-110 transition-transform pixel-render" />
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-0.5 rounded-full bg-background/80 backdrop-blur-sm text-[9px] font-bold border border-border uppercase">{{ $reward['type'] }}</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <h3 class="font-bold text-sm">{{ $reward['name'] }}</h3>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1 text-yellow-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                                    <span class="font-black text-sm">{{ number_format($reward['cost']) }}</span>
                                </div>
                                <button class="px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-xs font-bold hover:bg-primary hover:text-primary-foreground transition-all flex items-center gap-1.5">
                                    Redeem
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="m12 15 2 2 4-4"/><rect width="20" height="14" x="2" y="6" rx="2"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="min-h-[60vh] flex flex-col items-center justify-center text-center space-y-8 p-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
            <div class="space-y-4 max-w-md" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-700" x-transition:enter-start="md:opacity-0 md:scale-90 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:scale-100 md:translate-y-0">
                <div class="w-24 h-24 bg-yellow-500/10 rounded-3xl flex items-center justify-center text-yellow-500 mx-auto mb-6 shadow-[0_0_30px_rgba(234,179,8,0.1)]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                </div>
                <h1 class="text-4xl font-black tracking-tight">Point Shop</h1>
                <p class="text-muted-foreground text-lg">You need an account to access the Point Shop and redeem exclusive rewards.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 w-full max-w-sm" x-show="show" x-transition:enter="transition ease-out duration-700 delay-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <button @click="$dispatch('open-auth-modal', { tab: 'login' })" 
                        class="flex-1 px-8 py-4 bg-primary text-primary-foreground font-bold rounded-2xl shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all text-lg">
                    Login Now
                </button>
                <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" 
                        class="flex-1 px-8 py-4 bg-card border-2 border-primary/20 text-primary font-bold rounded-2xl hover:bg-primary/5 hover:scale-105 active:scale-95 transition-all text-lg">
                    Sign Up
                </button>
            </div>

            <!-- Features Preview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl mt-12">
                @foreach(['Exclusive Vouchers', 'Digital Items', 'Luck Boosters'] as $index => $feature)
                    <div class="p-6 bg-card/50 border border-border rounded-2xl"
                         x-show="show" x-transition:enter="md:transition md:ease-out md:duration-500" x-transition:enter-start="md:opacity-0 md:translate-y-4" x-transition:enter-end="md:opacity-100 md:translate-y-0"
                         style="transition-delay: {{ ($index + 6) * 100 }}ms">
                        <p class="font-bold text-sm">{{ $feature }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endauth
</x-app-layout>
