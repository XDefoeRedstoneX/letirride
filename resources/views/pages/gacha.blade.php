<x-app-layout>
    @auth
        <div class="space-y-10" x-data="{
            spinning: false,
            showResult: false,
            winner: null,
            animationClass: '',
            userPoints: {{ Auth::user()->points_balance }},
            spinCost: {{ $spinCost ?? 200 }},
            items: {{ \Illuminate\Support\Js::from($prizes ?? []) }},
            csrfToken: '{{ csrf_token() }}',

            async spin() {
                if (this.spinning) return;

                if (this.userPoints < this.spinCost) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Not enough points! You need ' + this.spinCost + ' PTS.', type: 'error' } }));
                    return;
                }

                this.spinning = true;
                this.showResult = false;
                this.winner = null;
                this.animationClass = 'gacha-spinning';

                try {
                    const response = await fetch('/gacha/roll', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.spinning = false;
                        this.animationClass = '';
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Spin failed.', type: 'error' } }));
                        return;
                    }

                    // Find the winning item index for carousel positioning
                    const winIndex = this.items.findIndex(i => i.id === data.prize.id);
                    const cardWidth = 160;
                    const gap = 16;
                    const itemTotalWidth = cardWidth + gap;
                    const setWidth = this.items.length * itemTotalWidth;
                    const stopPosition = -(setWidth * 2 + (winIndex >= 0 ? winIndex : 0) * itemTotalWidth);

                    // Deceleration phase
                    setTimeout(() => {
                        this.$refs.carousel.style.setProperty('--gacha-stop-position', `${stopPosition}px`);
                        this.animationClass = 'gacha-decelerating';

                        setTimeout(() => {
                            this.winner = data.prize;
                            this.userPoints = data.new_balance;
                            this.showResult = true;
                            this.spinning = false;
                            this.animationClass = '';
                            this.$refs.carousel.style.transform = 'translateX(0px)';
                        }, 4000);
                    }, 2000);

                } catch (e) {
                    this.spinning = false;
                    this.animationClass = '';
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error. Please try again.', type: 'error' } }));
                }
            },

            rarityColor(rarity) {
                const colors = {
                    common: 'border-gray-500/30',
                    uncommon: 'border-green-500/40',
                    rare: 'border-blue-500/50',
                    epic: 'border-purple-500/50',
                    grand_prize: 'border-yellow-500/50',
                    legendary: 'border-primary/60',
                };
                return colors[rarity] || 'border-border';
            },
            rarityBadge(rarity) {
                const styles = {
                    common: 'text-gray-400 border-gray-500/30 bg-gray-500/10',
                    uncommon: 'text-green-400 border-green-500/30 bg-green-500/10',
                    rare: 'text-blue-400 border-blue-500/30 bg-blue-500/10',
                    epic: 'text-purple-400 border-purple-500/30 bg-purple-500/10',
                    grand_prize: 'text-yellow-400 border-yellow-500/30 bg-yellow-500/10',
                    legendary: 'text-primary border-primary/30 bg-primary/10',
                };
                return styles[rarity] || '';
            },
            rarityDot(rarity) {
                const dots = {
                    common: 'bg-gray-400',
                    uncommon: 'bg-green-500',
                    rare: 'bg-blue-500',
                    epic: 'bg-purple-500',
                    grand_prize: 'bg-yellow-500',
                    legendary: 'bg-primary',
                };
                return dots[rarity] || 'bg-muted';
            }
        }">
            <!-- Header -->
            <div class="text-center space-y-2">
                <div class="flex items-center justify-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary animate-pulse"><path d="M7 11V7a5 5 0 0 1 10 0v4"/><rect width="18" height="12" x="3" y="11" rx="2"/><circle cx="12" cy="17" r="1"/></svg>
                    <h1 class="text-4xl font-black tracking-tighter uppercase">Arcade <span class="text-primary">Carousel</span></h1>
                </div>
                <p class="text-muted-foreground font-medium uppercase tracking-widest text-[10px]">Spin and win exciting prizes!</p>
                <div class="flex items-center justify-center gap-2 text-yellow-500 mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                    <span class="font-black text-lg" x-text="new Intl.NumberFormat('id-ID').format(userPoints) + ' PTS'"></span>
                </div>
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
                     :style="spinning ? '' : 'transform: translateX(0px)'">
                    <!-- Duplicate items 5 times for long spin -->
                    <template x-for="i in [1,2,3,4,5]">
                        <div class="flex gap-4">
                            <template x-for="item in items" :key="i + '-' + item.id">
                                <div class="w-40 flex-shrink-0 glass-card border-2 rounded-2xl p-4 flex flex-col items-center justify-center gap-3"
                                     :class="rarityColor(item.rarity)">
                                    <div class="relative w-16 h-16">
                                        <img :src="item.image" class="w-full h-full object-contain pixel-render" />
                                    </div>
                                    <p class="text-[10px] font-black text-center uppercase tracking-tight" x-text="item.name"></p>
                                    <span class="text-[8px] font-black uppercase px-2 py-0.5 rounded-full border"
                                          :class="rarityBadge(item.rarity)" x-text="item.rarity.replace('_', ' ')"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Spin Button (Points Only) -->
            <div class="flex items-center justify-center">
                <button @click="spin()" :disabled="spinning || userPoints < spinCost"
                        :class="(spinning || userPoints < spinCost) ? 'opacity-50 cursor-not-allowed' : 'hover:scale-105 active:scale-95'"
                        class="px-12 py-5 bg-primary text-primary-foreground font-black text-lg rounded-2xl shadow-xl shadow-primary/20 transition-all flex items-center justify-center gap-3 tracking-widest">
                    <template x-if="spinning">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!spinning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                    </template>
                    <span x-text="spinning ? 'SPINNING...' : 'SPIN (' + spinCost + ' PTS)'"></span>
                </button>
            </div>

            <!-- Drop Rates -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-card border border-border rounded-3xl p-6 space-y-6 shadow-sm">
                    <div class="flex items-center gap-2 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                        <h3 class="font-black uppercase tracking-widest text-xs">Drop Rates</h3>
                    </div>
                    <div class="space-y-3">
                        <template x-for="item in items" :key="item.id">
                            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-tighter">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="rarityDot(item.rarity)"></div>
                                    <span class="text-muted-foreground" x-text="item.name"></span>
                                </div>
                                <span x-text="item.rate + '%'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Result Modal -->
            <div x-show="showResult" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-card border-2 border-primary/50 rounded-[2rem] p-8 max-w-sm w-full text-center space-y-6 shadow-2xl shadow-primary/30"
                     x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100">
                    <div class="w-32 h-32 mx-auto relative">
                        <img :src="winner ? winner.image : ''" class="w-full h-full object-contain pixel-render animate-bounce" />
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest" :class="winner ? rarityBadge(winner.rarity).split(' ')[0] : 'text-primary'" x-text="winner ? winner.rarity.replace('_', ' ') : ''"></p>
                        <h2 class="text-3xl font-black uppercase" x-text="winner ? winner.name : ''"></h2>
                        <p class="text-xs text-muted-foreground">A voucher has been added to your inventory.</p>
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