<x-app-layout>
    @auth
        <div class="px-page">
            <div class="px-page-inner space-y-10"
                 x-data='gachaPage(@json($prizes), {{ $spinCost }}, @json($boosters ?? []), @json($pity["active_boosters"] ?? []))'>

                {{-- Header --}}
                <div style="text-align:center;">
                    <h1 class="px-heading">Arcade <span class="gold">Carousel</span></h1>
                    <p class="px-subheading">SPIN AND WIN EXCITING PRIZES!</p>
                </div>
                <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

                {{-- Gacha Carousel --}}
                <div style="position:relative;max-width:900px;margin:0 auto;overflow:hidden;height:240px;display:flex;align-items:center;">
                    {{-- Skip Button --}}
                    <div x-show="showSkip" x-transition.opacity
                         style="position:absolute;bottom:10px;right:12px;z-index:20;">
                        <button @click="skipAnimation()"
                                style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;padding:6px 14px;background:rgba(0,0,0,0.6);border:2px solid var(--dark-line);color:var(--text-dim);cursor:pointer;transition:color 0.15s,border-color 0.15s;"
                                onmouseover="this.style.color='var(--gold)';this.style.borderColor='var(--gold)'"
                                onmouseout="this.style.color='var(--text-dim)';this.style.borderColor='var(--dark-line)'">
                            SKIP &#9654;&#9654;
                        </button>
                    </div>
                    {{-- Center Pointer --}}
                    <div style="position:absolute;left:50%;top:0;bottom:0;width:3px;background:var(--gold);transform:translateX(-50%);z-index:10;box-shadow:0 0 15px rgba(245,158,11,0.5);">
                        <div style="position:absolute;top:-1px;left:50%;transform:translateX(-50%) rotate(45deg);width:12px;height:12px;background:var(--gold);border:2px solid var(--dark-bg);"></div>
                        <div style="position:absolute;bottom:-1px;left:50%;transform:translateX(-50%) rotate(45deg);width:12px;height:12px;background:var(--gold);border:2px solid var(--dark-bg);"></div>
                    </div>

                    <div x-ref="carousel" class="flex items-center gap-4 py-8 transition-transform duration-[4000ms] cubic-bezier(0.15, 0, 0.15, 1)"
                         :class="animationClass"
                         :style="spinning ? '' : 'transform: translateX(' + dragOffset + 'px)'">
                        <template x-for="i in [1,2,3,4,5]">
                            <div class="flex gap-4">
                                <template x-for="item in items" :key="i + '-' + item.id">
                                    <div class="w-40 flex-shrink-0 px-card" style="padding:16px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;"
                                         :style="'border-color:' + rarityColor(item.rarity) + ';'">
                                        <div style="width:56px;height:56px;">
                                            <img :src="item.image" class="w-full h-full object-contain pixel-render" />
                                        </div>
                                        <p style="font-family:var(--font-sans);font-size:10px;font-weight:800;text-align:center;color:#e8f0ff;" x-text="item.name"></p>
                                        <span class="px-badge" :style="'color:' + rarityColor(item.rarity) + ';border-color:' + rarityColor(item.rarity) + '40;background:' + rarityColor(item.rarity) + '15;'" x-text="item.rarity"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Spin Buttons --}}
                <div style="display:flex;flex-direction:column;align-items:center;gap:12px;" class="sm:flex-row">
                    <button @click="spin('points')" :disabled="spinning"
                            class="px-btn-gold" style="padding:18px 36px;font-size:9px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                        SPIN ({{ $spinCost }} PTS)
                    </button>
                    <button @click="spin('midtrans')" :disabled="spinning"
                            class="px-btn-ghost" style="padding:18px 36px;font-size:9px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        SPIN (RP {{ number_format($paidSpinPrice, 0, ',', '.') }})
                    </button>
                </div>

                {{-- Pity Chip + History Link --}}
                @if($pity)
                    <div style="display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:10px;max-width:900px;margin:0 auto;">
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:rgba(245,158,11,0.1);border:2px solid rgba(245,158,11,0.25);font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:var(--gold);">
                            PITY {{ $pity['pity_counter'] }}/{{ $hardPityThreshold }}
                            <span style="opacity:0.5;">·</span>
                            MINI {{ $pity['mini_pity_counter'] }}/{{ $miniPityThreshold }}
                        </span>
                        @if($pity['free_spins'] > 0)
                            <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:rgba(34,197,94,0.12);border:2px solid rgba(34,197,94,0.3);font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:#22c55e;">
                                {{ $pity['free_spins'] }} FREE SPIN{{ $pity['free_spins'] > 1 ? 'S' : '' }}
                            </span>
                        @endif
                        <a href="{{ route('gacha.history') }}" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:rgba(255,255,255,0.04);border:2px solid var(--dark-line);font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:var(--text-dim);text-decoration:none;transition:color 0.15s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-dim)'">
                            HISTORY &rarr;
                        </a>
                    </div>
                @endif

                {{-- Drop Rates & Boosters --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" style="max-width:900px;margin:0 auto;">
                    {{-- Drop Rates --}}
                    <div class="px-card-static" style="padding:24px;">
                        <div class="px-section-header"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg></div><span class="section-title">DROP RATES</span></div>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <template x-for="item in items" :key="item.id">
                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="width:6px;height:6px;" :style="'background:' + rarityColor(item.rarity)"></div>
                                        <span style="font-family:var(--font-sans);font-size:12px;color:var(--text-dim);" x-text="item.name"></span>
                                    </div>
                                    <span style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:#e8f0ff;" x-text="item.rate + '%'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Luck Boosters --}}
                    <div class="px-card-static" style="padding:24px;">
                        <div class="px-section-header"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/></svg></div><span class="section-title">LUCK BOOSTERS</span></div>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <template x-for="b in boosters" :key="b.id">
                                <div style="background:var(--dark-card2);border:2px solid var(--dark-line);padding:14px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                                    <div style="flex:1;min-width:0;">
                                        <h4 style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:#e8f0ff;" x-text="b.name"></h4>
                                        <p style="font-family:var(--font-sans);font-size:10px;color:var(--text-dim);margin-top:2px;" x-text="b.description || ('+' + b.bonus_percent + '% ' + b.rarity_floor + '+ for ' + b.duration_minutes + ' min')"></p>
                                        <p x-show="activeBoosterFor(b.id)" style="font-family:var(--font-sans);font-size:9px;color:#22c55e;margin-top:4px;font-weight:800;" x-text="'ACTIVE — ' + activeCountdown(b.id)"></p>
                                    </div>
                                    <button @click="activateBooster(b)" :disabled="boosterBusy === b.id"
                                            class="px-btn-gold" style="padding:6px 12px;font-size:6px;white-space:nowrap;">
                                        <span x-show="boosterBusy !== b.id" x-text="b.point_cost + ' PTS'"></span>
                                        <span x-show="boosterBusy === b.id">...</span>
                                    </button>
                                </div>
                            </template>
                            <p x-show="!boosters || boosters.length === 0" style="font-family:var(--font-sans);font-size:10px;color:var(--text-dim);text-align:center;padding:12px;">No boosters available right now.</p>
                        </div>
                    </div>
                </div>

                {{-- Result Modal --}}
                <div x-show="showResult" class="px-modal-overlay" x-transition>
                    <div class="px-modal-box" style="text-align:center;">
                        <div style="width:120px;height:120px;margin:0 auto;">
                            <img :src="winner ? winner.image : ''" class="w-full h-full object-contain pixel-render animate-bounce" />
                        </div>
                        <p style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;margin-top:16px;" :style="'color:' + (winner ? rarityColor(winner.rarity) : 'var(--gold)')" x-text="winner ? winner.rarity.toUpperCase() : ''"></p>
                        <h2 style="font-family:var(--font-sans);font-size:24px;font-weight:800;color:#e8f0ff;margin-top:6px;" x-text="winner ? winner.name : ''"></h2>
                        <p style="font-family:var(--font-sans);font-size:12px;color:var(--text-dim);margin-top:4px;" x-text="winner && winner.discount_name ? winner.discount_name : 'Congratulations on your prize!'"></p>
                        <button @click="claimReward()" class="px-btn-gold" style="width:100%;padding:16px;margin-top:20px;font-size:8px;">CLAIM REWARD</button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .gacha-spinning { animation: gacha-spin-loop 0.5s linear infinite; }
            @keyframes gacha-spin-loop { from { transform: translateX(0); } to { transform: translateX(-880px); } }
            .gacha-decelerating { transform: translateX(var(--gacha-stop-position)); }
        </style>
    @else
        <div class="px-page" x-data="{}">
            <div class="px-empty-state" style="min-height:70vh;">
                <div style="width:80px;height:80px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01"/><path d="M18 12h.01"/></svg>
                </div>
                <h1 class="px-heading" style="text-align:center;">Spin Your Luck in The <span class="gold">Carousel</span></h1>
                <p style="font-family:var(--font-sans);font-size:14px;color:var(--text-dim);text-align:center;max-width:480px;line-height:1.7;">Experience the thrill of our digital gacha system. Win premium products, massive point bundles, and exclusive rewards.</p>
                <div style="display:flex;gap:12px;">
                    <button @click="$dispatch('open-auth-modal', { tab: 'login' })" class="px-btn-gold" style="padding:16px 28px;font-size:8px;">LOGIN</button>
                    <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" class="px-btn-ghost" style="padding:16px 28px;font-size:8px;">SIGN UP</button>
                </div>
            </div>
        </div>
    @endauth

    <script defer
            src="{{ $midtransIsProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ $midtransClientKey }}"></script>

    <script>
    function gachaPage(items, spinCost, boosters, activeBoosters) {
        return {
            spinning: false,
            showResult: false,
            winner: null,
            pendingPrize: null,
            revealTimers: [],
            showSkip: false,
            animationClass: '',
            dragOffset: 0,
            items,
            spinCost,
            boosters,
            activeBoosters,
            boosterBusy: null,
            now: Math.floor(Date.now() / 1000),

            init() {
                setInterval(() => { this.now = Math.floor(Date.now() / 1000); }, 1000);
            },

            activeBoosterFor(boosterId) {
                return this.activeBoosters.find(ab => ab.booster_id === boosterId);
            },

            activeCountdown(boosterId) {
                const ab = this.activeBoosterFor(boosterId);
                if (!ab) return '';
                const expiresAt = Math.floor(new Date(ab.expires_at).getTime() / 1000);
                let remaining = expiresAt - this.now;
                if (remaining <= 0) {
                    this.activeBoosters = this.activeBoosters.filter(x => x.booster_id !== boosterId);
                    return 'expired';
                }
                const mins = Math.floor(remaining / 60);
                const secs = remaining % 60;
                return `${mins}m ${secs.toString().padStart(2, '0')}s`;
            },

            async activateBooster(b) {
                if (this.boosterBusy) return;
                this.boosterBusy = b.id;
                try {
                    const res = await fetch(`{{ url('/gacha/boosters') }}/${b.id}/activate`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.toastError(data.message || 'Could not activate booster.');
                        return;
                    }
                    const incoming = data.active_booster;
                    const existing = this.activeBoosters.findIndex(ab => ab.booster_id === incoming.booster_id);
                    if (existing >= 0) {
                        this.activeBoosters[existing] = incoming;
                    } else {
                        this.activeBoosters.push(incoming);
                    }
                    this.toastSuccess(data.message || 'Booster activated.');
                } catch (e) {
                    this.toastError('Network error. Please try again.');
                } finally {
                    this.boosterBusy = null;
                }
            },

            toastSuccess(message) {
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message, type: 'success' } }));
            },

            rarityColor(rarity) {
                const map = {
                    common: '#9ca3af', uncommon: '#22c55e', rare: '#3b82f6',
                    epic: '#a855f7', legendary: '#f59e0b', grand_prize: '#f43f5e',
                };
                return map[rarity] || '#f59e0b';
            },

            async spin(costType) {
                if (this.spinning) return;

                if (costType === 'midtrans') {
                    await this.spinWithMidtrans();
                    return;
                }

                await this.spinWithPoints();
            },

            beginSpinAnimation() {
                this.spinning = true;
                this.showResult = false;
                this.winner = null;
                this.showSkip = true;
                this.animationClass = 'gacha-spinning';
            },

            async spinWithPoints() {
                // Fetch first — animation only starts on success so errors never move the carousel.
                try {
                    const res = await fetch('{{ route("gacha.roll") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        this.toastError(data.message || 'Spin failed.');
                        return;
                    }

                    this.beginSpinAnimation();
                    this.revealPrize(data.prize);
                } catch (e) {
                    this.toastError('Network error. Please try again.');
                }
            },

            async spinWithMidtrans() {
                if (!window.snap || typeof window.snap.pay !== 'function') {
                    this.toastError('Payment system not ready. Please wait a moment and try again.');
                    return;
                }

                let data;
                try {
                    const res = await fetch('{{ route("gacha.pay") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    data = await res.json();
                    if (!res.ok) {
                        this.toastError(data.message || 'Payment failed. Please try again.');
                        return;
                    }
                } catch (e) {
                    this.toastError('Network error. Please check your connection.');
                    return;
                }
                this.spinning = true; 
                window.snap.pay(data.snap_token, {
                    onSuccess: () => { this.beginSpinAnimation(); this.pollAndReveal(data.payment_id); },
                    onPending: () => { this.beginSpinAnimation(); this.pollAndReveal(data.payment_id); },
                    onError: () => { this.resetSpin(); this.toastError('Payment failed. Please try again.'); },   
                    onClose: () => { this.resetSpin(); /* User dismissed without paying — webhook will still fulfill if paid out-of-band. */ },
                });
            },

            async pollAndReveal(paymentId) {
                for (let i = 0; i < 10; i++) {
                    const res = await fetch(`{{ url('/gacha/pay/verify') }}/${paymentId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();

                    if (data.status === 'paid' && data.prize) {
                        this.revealPrize(data.prize, true);
                        return;
                    }

                    if (data.status === 'failed') {
                        this.toastError('Payment was not completed.');
                        this.resetSpin();
                        return;
                    }

                    await new Promise(r => setTimeout(r, 2000));
                }

                this.toastError('Payment status unknown. Check your inventory.');
                this.resetSpin();
            },

            revealPrize(prize, skipSpinDelay = false) {
                this.pendingPrize = prize;

                const winIndex = this.items.findIndex(i => String(i.id) === String(prize.id));
                const targetIdx = winIndex >= 0 ? winIndex : 0;
                const cardWidth = 160;
                const itemTotalWidth = cardWidth + 16;
                const setStride = this.items.length * itemTotalWidth;
                const cardLeftFromCarouselOrigin = setStride * 2 + targetIdx * itemTotalWidth;
                const cardCenter = cardLeftFromCarouselOrigin + cardWidth / 2;

                // For Midtrans, we've already been spinning during the poll — skip the extra wait.
                const spinDelay = skipSpinDelay ? 0 : 2000;

                const t1 = setTimeout(() => {
                    const containerWidth = this.$refs.carousel.parentElement.clientWidth;
                    const stopPosition = (containerWidth / 2) - cardCenter;

                    this.$refs.carousel.style.setProperty('--gacha-stop-position', `${stopPosition}px`);
                    this.animationClass = 'gacha-decelerating';

                    const t2 = setTimeout(() => {
                        this.showWinner();
                    }, 4000);
                    this.revealTimers.push(t2);
                }, spinDelay);
                this.revealTimers.push(t1);
            },

            showWinner() {
                this.revealTimers.forEach(t => clearTimeout(t));
                this.revealTimers = [];
                this.winner = this.pendingPrize;
                this.pendingPrize = null;
                this.showResult = true;
                this.spinning = false;
                this.showSkip = false;
                this.animationClass = '';
                this.$refs.carousel.style.transform = 'translateX(0px)';
            },

            skipAnimation() {
                if (!this.pendingPrize) return;
                this.showWinner();
            },

            resetSpin() {
                this.revealTimers.forEach(t => clearTimeout(t));
                this.revealTimers = [];
                this.pendingPrize = null;
                this.spinning = false;
                this.showSkip = false;
                this.animationClass = '';
            },

            claimReward() {
                // Full reload so navbar points, pity counter, and any newly-won discount/free-spin
                // are reflected fresh in server-rendered chrome.
                this.showResult = false;
                window.location.reload();
            },

            toastError(message) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message, type: 'error' }
                }));
            },
        };
    }
    </script>
</x-app-layout>
