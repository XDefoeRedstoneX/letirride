<x-app-layout>
    @auth
        <div class="space-y-10" x-data="{ 
            spinning: false,
            showResult: false,
            winner: null,
            animationClass: '',
            items: [
                { id: '1', name: '50 Points', rarity: 'common', image: '/gacha/points.svg', rate: 30, valueType: 'points', value: '50', description: 'Lumayan buat tabungan!' },
                { id: '2', name: '100 Points', rarity: 'common', image: '/gacha/points.svg', rate: 20, valueType: 'points', value: '100', description: 'Dapatkan 100 Points gratis!' },
                { id: '3', name: '250 Points', rarity: 'uncommon', image: '/gacha/points.svg', rate: 15, valueType: 'points', value: '250', description: 'Dapatkan 250 Points gratis!' },
                { id: '4', name: '150 Points', rarity: 'common', image: '/gacha/points.svg', rate: 10, valueType: 'points', value: '150', description: 'Hampir dapat yang bagus!' },
                { id: '5', name: 'Voucher Diskon 5%', rarity: 'uncommon', image: '/gacha/voucher.svg', rate: 10, valueType: 'voucher', value: '5', description: 'Voucher diskon 5% untuk semua produk.' },
                { id: '6', name: '500 Points', rarity: 'rare', image: '/gacha/points.svg', rate: 5, valueType: 'points', value: '500', description: 'Wow! 500 Points masuk kantong!' },
            ],
            cost: { points: 200, balance: 15000 },
            
            spin(costType) {
                if (this.spinning) return;
                
                if (costType === 'points' && {{ Auth::user()->points }} < this.cost.points) {
                    alert('Points tidak cukup!');
                    return;
                }
                if (costType === 'balance' && {{ Auth::user()->balance }} < this.cost.balance) {
                    alert('Saldo tidak cukup!');
                    return;
                }

                this.spinning = true;
                this.showResult = false;
                this.winner = null;
                
                // Gacha logic simplified for UI focus
                const totalRate = this.items.reduce((sum, item) => sum + item.rate, 0);
                let random = Math.random() * totalRate;
                let winItem = this.items[0];

                for (const item of this.items) {
                    random -= item.rate;
                    if (random <= 0) {
                        winItem = item;
                        break;
                    }
                }

                setTimeout(() => {
                    this.winner = winItem;
                    this.showResult = true;
                    this.spinning = false;
                }, 2000);
            }
        }">
            <!-- Header (Based on Screenshot) -->
            <div class="text-center space-y-2">
                <div class="flex items-center justify-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-neon-cyan"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01"/><path d="M18 12h.01"/></svg>
                    <h1 class="text-4xl font-black tracking-tight text-white drop-shadow-[0_0_10px_rgba(0,245,255,0.5)]">Gacha Arcade</h1>
                </div>
                <p class="text-gray-400 font-medium">Putar dan menangkan hadiah menarik!</p>
            </div>

            <!-- Gacha Carousel (Horizontal List from Screenshot) -->
            <div class="relative max-w-5xl mx-auto overflow-hidden px-4">
                <div class="flex items-center justify-center gap-4 py-8 overflow-x-auto scrollbar-hide">
                    <template x-for="item in items" :key="item.id">
                        <div class="w-40 flex-shrink-0 bg-midnight/40 border-2 rounded-2xl p-4 flex flex-col items-center justify-center gap-3 transition-all hover:scale-105"
                             :class="{
                                'border-gray-500/30 shadow-[0_0_15px_rgba(156,163,175,0.1)]': item.rarity === 'common',
                                'border-green-500/40 shadow-[0_0_15px_rgba(34,197,94,0.1)]': item.rarity === 'uncommon',
                                'border-blue-500/50 shadow-[0_0_20px_rgba(59,130,246,0.2)]': item.rarity === 'rare',
                             }">
                            <div class="relative w-16 h-16">
                                <img :src="item.image" class="w-full h-full object-contain pixel-render" />
                            </div>
                            <p class="text-xs font-bold text-center" x-text="item.name"></p>
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full border"
                                  :class="{
                                    'text-gray-400 border-gray-500/30 bg-gray-500/10': item.rarity === 'common',
                                    'text-green-400 border-green-500/30 bg-green-500/10': item.rarity === 'uncommon',
                                    'text-blue-400 border-blue-500/30 bg-blue-500/10': item.rarity === 'rare',
                                  }" x-text="item.rarity"></span>
                        </div>
                    </template>
                </div>
                <!-- Selector (Optional visual cue) -->
                <div class="absolute left-1/2 top-0 bottom-0 w-0.5 bg-neon-cyan/50 -translate-x-1/2 pointer-events-none hidden"></div>
            </div>

            <!-- Spin Buttons (Based on Screenshot) -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button @click="spin('points')" :disabled="spinning" 
                        class="w-full sm:w-auto px-10 py-4 bg-yellow-500 text-black font-black text-lg rounded-2xl shadow-xl shadow-yellow-500/20 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                    PUTAR (200 POIN)
                </button>
                <button @click="spin('balance')" :disabled="spinning" 
                        class="w-full sm:w-auto px-10 py-4 bg-midnight/60 border-2 border-primary/50 text-white font-black text-lg rounded-2xl shadow-xl hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    PUTAR (RP 15.000)
                </button>
            </div>

            <!-- Probability Section (Based on Screenshot) -->
            <div class="max-w-4xl mx-auto bg-midnight/40 border border-white/5 rounded-3xl p-6 space-y-6">
                <div class="flex items-center gap-2 text-neon-cyan">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                    <h3 class="font-bold uppercase tracking-wider text-sm">Tingkat Kemungkinan</h3>
                </div>
                <div class="flex flex-wrap gap-x-8 gap-y-4 text-xs font-bold">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                        <span class="text-gray-400">50 Points</span>
                        <span>30%</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                        <span class="text-gray-400">100 Points</span>
                        <span>20%</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-gray-400">250 Points</span>
                        <span class="text-green-400">15%</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                        <span class="text-gray-400">150 Points</span>
                        <span>10%</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-gray-400">Voucher Diskon 5%</span>
                        <span class="text-green-400">10%</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <span class="text-gray-400">500 Point</span>
                        <span class="text-blue-400">5%</span>
                    </div>
                </div>
            </div>

            <!-- Booster Section (Based on Screenshot) -->
            <div class="max-w-4xl mx-auto bg-midnight/40 border border-white/5 rounded-3xl p-6 space-y-6">
                <div class="flex items-center gap-2 text-yellow-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/></svg>
                    <h3 class="font-bold uppercase tracking-wider text-sm">Booster Aktif</h3>
                </div>
                <div class="bg-card/20 border border-white/5 rounded-2xl p-4">
                    <p class="text-sm text-gray-500">Belum ada booster aktif. Beli booster untuk meningkatkan peluang!</p>
                </div>
                
                <div class="space-y-4">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Beli Booster</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-midnight/60 border border-white/10 rounded-2xl p-4 flex items-center justify-between group hover:border-yellow-500/50 transition-all">
                            <div class="space-y-1">
                                <h4 class="font-bold text-sm">Lucky Charm</h4>
                                <p class="text-[10px] text-gray-500">Meningkatkan rate item Rare+ sebesar 5% selama 30m</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-[9px] font-bold text-yellow-500 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                        +5%
                                    </span>
                                    <span class="text-[9px] font-bold text-gray-400 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        30m
                                    </span>
                                </div>
                            </div>
                            <button class="px-4 py-2 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded-xl font-bold text-xs hover:bg-yellow-500 hover:text-black transition-all flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/></svg>
                                500 Poin
                            </button>
                        </div>

                        <div class="bg-midnight/60 border border-white/10 rounded-2xl p-4 flex items-center justify-between group hover:border-neon-cyan/50 transition-all">
                            <div class="space-y-1">
                                <h4 class="font-bold text-sm">Golden Touch</h4>
                                <p class="text-[10px] text-gray-500">Meningkatkan rate item Epic+ sebesar 10% selama 15m</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-[9px] font-bold text-neon-cyan flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                        +10%
                                    </span>
                                    <span class="text-[9px] font-bold text-gray-400 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        15m
                                    </span>
                                </div>
                            </div>
                            <button class="px-4 py-2 bg-neon-cyan/10 text-neon-cyan border border-neon-cyan/20 rounded-xl font-bold text-xs hover:bg-neon-cyan hover:text-black transition-all flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                Rp 25.000
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Result Modal (Simplified) -->
            <div x-show="showResult" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-transition>
                <div class="bg-midnight border-2 border-primary/50 rounded-[2rem] p-8 max-w-sm w-full text-center space-y-6 shadow-[0_0_50px_rgba(var(--primary-rgb),0.3)]">
                    <div class="w-32 h-32 mx-auto relative">
                        <img :src="winner ? winner.image : ''" class="w-full h-full object-contain pixel-render animate-bounce" />
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary" x-text="winner ? winner.rarity : ''"></p>
                        <h2 class="text-3xl font-black" x-text="winner ? winner.name : ''"></h2>
                        <p class="text-sm text-gray-400" x-text="winner ? winner.description : ''"></p>
                    </div>
                    <button @click="showResult = false" class="w-full py-4 bg-primary text-white font-black rounded-2xl hover:scale-105 transition-all">CLAIM REWARD</button>
                </div>
            </div>
        </div>
    @else
        <div class="min-h-[60vh] flex flex-col items-center justify-center text-center space-y-8 p-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
            <div class="space-y-4 max-w-md" x-show="show" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 scale-90 translate-y-8" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                <div class="w-24 h-24 bg-primary/10 rounded-3xl flex items-center justify-center text-primary mx-auto mb-6 shadow-[0_0_30px_rgba(var(--primary-rgb),0.1)]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01"/><path d="M18 12h.01"/></svg>
                </div>
                <h1 class="text-4xl font-black tracking-tight">Gacha Arcade</h1>
                <p class="text-muted-foreground text-lg">Test your luck and win exclusive prizes! You need to login to start spinning.</p>
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

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full max-w-4xl mt-12">
                @foreach(['Points', 'Vouchers', 'Balance', 'Products'] as $index => $item)
                    <div class="p-4 bg-card/50 border border-border rounded-2xl flex flex-col items-center gap-2"
                         x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                         style="transition-delay: {{ ($index + 6) * 100 }}ms">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                        </div>
                        <p class="font-bold text-xs">{{ $item }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endauth
</x-app-layout>                    <p class="text-[10px] font-bold text-purple-500 uppercase">Epic</p>
                    <p class="text-lg font-black">4%</p>
                </div>
                <div class="bg-card/50 border border-border p-3 rounded-xl text-center col-span-2 md:col-span-1">
                    <p class="text-[10px] font-bold text-amber-500 uppercase">Legendary</p>
                    <p class="text-lg font-black">1%</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>