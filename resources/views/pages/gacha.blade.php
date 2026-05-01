<x-app-layout>
    @auth
        <div class="space-y-10" x-data="{ 
            spinning: false,
            showResult: false,
            winner: null,
            animationClass: '',
            dragOffset: 0,
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
                this.animationClass = 'gacha-spinning';
                
                // Gacha logic simplified for UI focus
                const totalRate = this.items.reduce((sum, item) => sum + item.rate, 0);
                let random = Math.random() * totalRate;
                let winIndex = 0;

                for (let i = 0; i < this.items.length; i++) {
                    random -= this.items[i].rate;
                    if (random <= 0) {
                        winIndex = i;
                        break;
                    }
                }

                const winItem = this.items[winIndex];
                const cardWidth = 160; // w-40 = 160px
                const gap = 16; // gap-4 = 16px
                const itemTotalWidth = cardWidth + gap;
                const setWidth = this.items.length * itemTotalWidth;
                
                // Deceleration target: scroll past 2 full sets + land on the winner in the 3rd set
                const stopPosition = -(setWidth * 2 + winIndex * itemTotalWidth);
                
                setTimeout(() => {
                    this.$refs.carousel.style.setProperty('--gacha-stop-position', `${stopPosition}px`);
                    this.animationClass = 'gacha-decelerating';
                    
                    setTimeout(() => {
                        this.winner = winItem;
                        this.showResult = true;
                        this.spinning = false;
                        this.animationClass = '';
                        this.$refs.carousel.style.transform = 'translateX(0px)';
                    }, 4000);
                }, 2000);
            }
        }">
            <!-- Header -->
            <div class="text-center space-y-2">
                <div class="flex items-center justify-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary animate-pulse"><path d="M7 11V7a5 5 0 0 1 10 0v4"/><rect width="18" height="12" x="3" y="11" rx="2"/><circle cx="12" cy="17" r="1"/></svg>
                    <h1 class="text-4xl font-black tracking-tighter uppercase text-white">Arcade <span class="text-primary">Carousel</span></h1>
                </div>
                <p class="text-gray-400 font-medium uppercase tracking-widest text-[10px]">Spin and win exciting prizes!</p>
            </div>

            <!-- Gacha Carousel -->
            <div class="relative max-w-5xl mx-auto overflow-hidden px-4 h-64 flex items-center">
                <!-- Center Pointer -->
                <div class="absolute left-1/2 top-0 bottom-0 w-1 bg-primary/80 -translate-x-1/2 z-10 shadow-[0_0_15px_rgba(var(--primary-rgb),0.5)]">
                    <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-4 h-4 bg-primary rotate-45 border-2 border-background"></div>
                    <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-4 bg-primary rotate-45 border-2 border-background"></div>
                </div>

                <div x-ref="carousel" class="flex items-center gap-4 py-8 transition-transform duration-[4000ms] cubic-bezier(0.15, 0, 0.15, 1)"
                     :class="animationClass"
                     :style="spinning ? '' : 'transform: translateX(' + dragOffset + 'px)'">
                    <!-- Duplicate items 5 times for long spin -->
                    <template x-for="i in [1,2,3,4,5]">
                        <div class="flex gap-4">
                            <template x-for="item in items" :key="i + '-' + item.id">
                                <div class="w-40 flex-shrink-0 glass-card border-2 rounded-2xl p-4 flex flex-col items-center justify-center gap-3"
                                     :class="{
                                        'border-gray-500/30': item.rarity === 'common',
                                        'border-green-500/40': item.rarity === 'uncommon',
                                        'border-primary/50': item.rarity === 'rare',
                                     }">
                                    <div class="relative w-16 h-16">
                                        <img :src="item.image" class="w-full h-full object-contain pixel-render" />
                                    </div>
                                    <p class="text-[10px] font-black text-center uppercase tracking-tight" x-text="item.name"></p>
                                    <span class="text-[8px] font-black uppercase px-2 py-0.5 rounded-full border"
                                          :class="{
                                            'text-gray-400 border-gray-500/30 bg-gray-500/10': item.rarity === 'common',
                                            'text-green-400 border-green-500/30 bg-green-500/10': item.rarity === 'uncommon',
                                            'text-primary border-primary/30 bg-primary/10': item.rarity === 'rare',
                                          }" x-text="item.rarity"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Spin Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button @click="spin('points')" :disabled="spinning" 
                        class="w-full sm:w-auto px-10 py-4 bg-primary text-primary-foreground font-black text-lg rounded-2xl shadow-xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50 tracking-widest">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                    SPIN (200 PTS)
                </button>
                <button @click="spin('balance')" :disabled="spinning" 
                        class="w-full sm:w-auto px-10 py-4 bg-card border-2 border-border text-foreground font-black text-lg rounded-2xl shadow-xl hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50 tracking-widest">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    SPIN (RP 15.000)
                </button>
            </div>

            <!-- Drop Rates & Boosters -->
            <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Drop Rates -->
                <div class="bg-card border border-border rounded-3xl p-6 space-y-6 shadow-sm">
                    <div class="flex items-center gap-2 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                        <h3 class="font-black uppercase tracking-widest text-xs">Drop Rates</h3>
                    </div>
                    <div class="space-y-3">
                        <template x-for="item in items" :key="item.id">
                            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-tighter">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="{
                                        'bg-gray-400': item.rarity === 'common',
                                        'bg-green-500': item.rarity === 'uncommon',
                                        'bg-primary': item.rarity === 'rare',
                                    }"></div>
                                    <span class="text-muted-foreground" x-text="item.name"></span>
                                </div>
                                <span x-text="item.rate + '%'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Active Boosters -->
                <div class="bg-card border border-border rounded-3xl p-6 space-y-6 shadow-sm">
                    <div class="flex items-center gap-2 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/></svg>
                        <h3 class="font-black uppercase tracking-widest text-xs">Luck Boosters</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-foreground/5 border border-border rounded-2xl p-4 flex items-center justify-between group hover:border-primary/50 transition-all">
                            <div class="space-y-1">
                                <h4 class="font-black text-[10px] uppercase tracking-tight">Lucky Charm</h4>
                                <p class="text-[8px] font-bold text-muted-foreground uppercase">Increase Rare+ item rates by 5% for 30m</p>
                            </div>
                            <button class="px-3 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-lg font-black text-[8px] uppercase tracking-widest hover:bg-primary hover:text-primary-foreground transition-all">
                                500 Pts
                            </button>
                        </div>
                        <div class="bg-foreground/5 border border-border rounded-2xl p-4 flex items-center justify-between group hover:border-primary/50 transition-all">
                            <div class="space-y-1">
                                <h4 class="font-black text-[10px] uppercase tracking-tight">Golden Touch</h4>
                                <p class="text-[8px] font-bold text-muted-foreground uppercase">Increase Epic+ item rates by 10% for 15m</p>
                            </div>
                            <button class="px-3 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-lg font-black text-[8px] uppercase tracking-widest hover:bg-primary hover:text-primary-foreground transition-all">
                                Rp 25K
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Result Modal -->
            <div x-show="showResult" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md" x-transition>
                <div class="bg-card border-2 border-primary/50 rounded-[2rem] p-8 max-w-sm w-full text-center space-y-6 shadow-2xl shadow-primary/30">
                    <div class="w-32 h-32 mx-auto relative">
                        <img :src="winner ? winner.image : ''" class="w-full h-full object-contain pixel-render animate-bounce" />
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary" x-text="winner ? winner.rarity : ''"></p>
                        <h2 class="text-3xl font-black uppercase" x-text="winner ? winner.name : ''"></h2>
                        <p class="text-xs text-muted-foreground" x-text="winner ? winner.description : ''"></p>
                    </div>
                    <button @click="showResult = false" class="w-full py-4 bg-primary text-primary-foreground font-black rounded-2xl hover:scale-105 transition-all uppercase tracking-widest text-xs">CLAIM REWARD</button>
                </div>
            </div>
        </div>

        <style>
            .gacha-spinning {
                animation: gacha-spin-loop 0.5s linear infinite;
            }
            @keyframes gacha-spin-loop {
                from { transform: translateX(0); }
                to { transform: translateX(-880px); }
            }
            .gacha-decelerating {
                transform: translateX(var(--gacha-stop-position));
            }
        </style>
    @else
        <div class="min-h-[70vh] flex flex-col items-center justify-center text-center space-y-12 p-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
            <div class="space-y-6 max-w-xl" x-show="show" x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 scale-95 translate-y-12" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                <div class="w-24 h-24 bg-primary/10 rounded-[2.5rem] flex items-center justify-center text-primary mx-auto mb-8 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01"/><path d="M18 12h.01"/></svg>
                </div>
                <h1 class="text-5xl font-black tracking-tighter leading-tight uppercase">Test Your Luck in the <span class="text-primary">Arcade</span></h1>
                <p class="text-muted-foreground text-lg font-medium leading-relaxed">Experience the thrill of our digital gacha system. Win premium products, massive point bundles, and exclusive balance rewards with a single spin.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 w-full max-w-md" x-show="show" x-transition:enter="transition ease-out duration-1000 delay-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                <button @click="$dispatch('open-auth-modal', { tab: 'login' })" 
                        class="flex-1 px-8 py-5 bg-primary text-primary-foreground font-black rounded-[1.5rem] shadow-2xl shadow-primary/30 hover:scale-105 active:scale-95 transition-all text-xs tracking-widest uppercase">
                    Start Spinning
                </button>
                <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" 
                        class="flex-1 px-8 py-5 glass-card font-black rounded-[1.5rem] hover:bg-white/20 hover:scale-105 active:scale-95 transition-all text-xs tracking-widest uppercase border border-border">
                    Join Community
                </button>
            </div>
        </div>
    @endauth
</x-app-layout>