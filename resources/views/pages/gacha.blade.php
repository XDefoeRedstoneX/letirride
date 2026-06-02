<x-app-layout>
    @auth
        <div class="px-page">
            <div class="px-page-inner space-y-10"
                 x-data='gachaPage(@json($prizes), {{ $spinCost }}, @json($boosters ?? []), @json($pity["active_boosters"] ?? []), {{ (int) ($pity["free_spins"] ?? 0) }}, @json($rarityBreakdown ?? []), @json($pity["base_rarity_chances"] ?? (object)[]), @json($pity["effective_rarity_chances"] ?? (object)[]))'>

                {{-- Spin dimmer: darkens the page so the cabinet takes the spotlight --}}
                <div class="gacha-dim" :class="{ 'on': spinning }" aria-hidden="true"></div>

                {{-- Header --}}
                <div style="text-align:center;">
                    <h1 class="px-heading">Arcade <span class="gold">Carousel</span></h1>
                    <p class="px-subheading">SPIN AND WIN EXCITING PRIZES!</p>
                </div>
                <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

                {{-- Gacha Carousel --}}
                @php $hasCabinet = file_exists(public_path('gacha-assets/arcade-cabinet.png')); @endphp
                <div class="gacha-carousel-frame {{ $hasCabinet ? 'has-cabinet' : '' }}"
                     :class="{ 'cabinet-on': spinning, 'cabinet-rumble': animationClass === 'gacha-spinning', 'cabinet-impact': impact }">
                    @if($hasCabinet)
                        {{-- Arcade cabinet backdrop: fades in during a spin; cards land in its green screen. --}}
                        <img src="/gacha-assets/arcade-cabinet.png" class="gacha-cabinet" alt="" aria-hidden="true" />
                    @endif
                    {{-- Skip Button --}}
                    <div x-show="showSkip" x-transition.opacity class="gacha-skip-wrap">
                        <button @click="skipAnimation()" class="gacha-skip-btn">
                            SKIP &#9654;&#9654;
                        </button>
                    </div>
                    {{-- Center Pointer --}}
                    <div class="gacha-pointer">
                        <div class="gacha-pointer-cap gacha-pointer-cap-top"></div>
                        <div class="gacha-pointer-cap gacha-pointer-cap-bottom"></div>
                    </div>

                    <div class="gacha-screen">
                    <div x-ref="carousel" class="gacha-track"
                         :class="animationClass"
                         :style="(spinning || animationClass === 'gacha-idle-loop') ? '' : 'transform: translateX(' + dragOffset + 'px)'">
                        <template x-for="i in [1,2,3,4,5]" :key="i">
                            <div class="gacha-track-set">
                                <template x-for="item in items" :key="i + '-' + item.id">
                                    <div class="gacha-card"
                                         :class="'gacha-rarity-' + item.rarity"
                                         :style="'border-color:' + rarityColor(item.rarity) + '; --rarity-color:' + rarityColor(item.rarity) + ';'">
                                        <div class="gacha-card-img">
                                            <img :src="item.image" class="w-full h-full object-contain pixel-render" :alt="item.name" x-on:error="$el.src = '/gacha-icons/coin.svg'" />
                                        </div>
                                        <p class="gacha-card-name" x-text="item.name"></p>
                                        <span class="px-badge gacha-card-badge" :style="'color:' + rarityColor(item.rarity) + ';border-color:' + rarityColor(item.rarity) + '40;background:' + rarityColor(item.rarity) + '15;'" x-text="item.rarity"></span>
                                        <span class="gacha-card-rarity-wedge" aria-hidden="true"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    </div>
                </div>

                {{-- Spin Buttons --}}
                <div class="gacha-spin-buttons">
                    <button @click="spin('points')" :disabled="spinning"
                            class="px-btn-gold gacha-spin-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                        SPIN ({{ $spinCost }} PTS)
                    </button>
                    <button x-show="freeSpins > 0" @click="spin('free_spin')" :disabled="spinning"
                            class="px-btn-success gacha-spin-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/></svg>
                        USE FREE SPIN (<span x-text="freeSpins"></span>×)
                    </button>
                    <button @click="spin('midtrans')" :disabled="spinning"
                            class="px-btn-ghost gacha-spin-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        SPIN (RP {{ number_format($paidSpinPrice, 0, ',', '.') }})
                    </button>
                </div>

                {{-- Pity Chip + History Link + Rules --}}
                @if($pity)
                    <div class="gacha-status-row">
                        <span class="gacha-status-chip gacha-status-pity">
                            PITY {{ $pity['pity_counter'] }}/{{ $hardPityThreshold }}
                            <span class="gacha-status-sep">·</span>
                            MINI {{ $pity['mini_pity_counter'] }}/{{ $miniPityThreshold }}
                        </span>
                        <span x-show="freeSpins > 0" class="gacha-status-chip gacha-status-free">
                            <span x-text="freeSpins">{{ $pity['free_spins'] }}</span> FREE SPIN<span x-show="freeSpins > 1">S</span>
                        </span>
                        <button @click="showInfoModal = true" type="button" class="gacha-status-chip gacha-status-rules" title="How it works">
                            ?  RULES
                        </button>
                        <button @click="toggleMute()" type="button" class="gacha-status-chip gacha-status-rules" :title="muted ? 'Unmute sound' : 'Mute sound'">
                            <span x-text="muted ? '♪ OFF' : '♪ ON'"></span>
                        </button>
                        <a href="{{ route('gacha.history') }}" class="gacha-status-chip gacha-status-link">
                            HISTORY &rarr;
                        </a>
                    </div>
                @endif

                {{-- Drop Rates & Boosters --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 gacha-panels">
                    {{-- Drop Rates per RARITY --}}
                    <div class="px-card-static gacha-panel">
                        <div class="px-section-header">
                            <div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg></div>
                            <span class="section-title">DROP RATES</span>
                        </div>
                        <div class="gacha-rarity-rows">
                            <template x-for="row in rarityBreakdown" :key="row.rarity">
                                <div class="gacha-rarity-row">
                                    <div class="gacha-rarity-label">
                                        <div class="gacha-rarity-dot" :style="'background:' + rarityColor(row.rarity)"></div>
                                        <span class="gacha-rarity-name" x-text="rarityDisplayName(row.rarity)"></span>
                                        <span class="gacha-rarity-count" x-text="'(' + row.prize_count + ')'"></span>
                                    </div>
                                    <div class="gacha-rarity-numbers">
                                        <span class="gacha-rarity-base" x-text="formatPct(baseChance(row.rarity)) + '%'"></span>
                                        <span x-show="chanceDelta(row.rarity) > 0.001" class="gacha-chance-bonus" x-text="'+ ' + formatPct(chanceDelta(row.rarity)) + '%'"></span>
                                        <span x-show="chanceDelta(row.rarity) < -0.001" class="gacha-chance-penalty" x-text="'- ' + formatPct(Math.abs(chanceDelta(row.rarity))) + '%'"></span>
                                        <span x-show="Math.abs(chanceDelta(row.rarity)) > 0.001" class="gacha-rarity-eff" x-text="'(= ' + formatPct(effectiveChance(row.rarity)) + '%)'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Luck Boosters --}}
                    <div class="px-card-static gacha-panel">
                        <div class="px-section-header">
                            <div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/></svg></div>
                            <span class="section-title">LUCK BOOSTERS</span>
                        </div>
                        <div class="gacha-booster-list">
                            <template x-for="b in boosters" :key="b.id">
                                <div class="gacha-booster-card">
                                    <div class="gacha-booster-body">
                                        <h4 class="gacha-booster-name" x-text="b.name"></h4>
                                        <p class="gacha-booster-desc"
                                           x-text="b.description || ('+' + b.bonus_percent + '% ' + b.rarity_floor + '+ for ' + b.rolls_granted + ' rolls')"></p>
                                        <p x-show="activeBoosterFor(b.id)" class="gacha-booster-active"
                                           x-text="'ACTIVE — ' + activeRollsLeft(b.id) + ' ROLLS LEFT'"></p>
                                    </div>
                                    <button @click="activateBooster(b)" :disabled="boosterBusy === b.id"
                                            class="px-btn-gold gacha-booster-btn">
                                        <span x-show="boosterBusy !== b.id" x-text="b.point_cost + ' PTS'"></span>
                                        <span x-show="boosterBusy === b.id">...</span>
                                    </button>
                                </div>
                            </template>
                            <p x-show="!boosters || boosters.length === 0" class="gacha-booster-empty">No boosters available right now.</p>
                        </div>
                    </div>
                </div>

                {{-- Result Modal --}}
                <div x-show="showResult" class="px-modal-overlay" x-transition>
                    <div class="px-modal-box gacha-result-box"
                         :class="winner ? 'gacha-rarity-' + winner.rarity : ''"
                         :style="winner ? '--rarity-color:' + rarityColor(winner.rarity) + ';' : ''">
                        <div class="gacha-result-img">
                            <img :src="winner ? winner.image : ''" class="w-full h-full object-contain pixel-render animate-bounce" :alt="winner ? winner.name : ''" x-on:error="$el.src = '/gacha-icons/coin.svg'" />
                            <span class="gacha-card-rarity-wedge" aria-hidden="true"></span>
                        </div>
                        <p class="gacha-result-rarity" :style="'color:' + (winner ? rarityColor(winner.rarity) : 'var(--gold)')" x-text="winner ? winner.rarity.toUpperCase() : ''"></p>
                        <h2 class="gacha-result-name" x-text="winner ? winner.name : ''"></h2>
                        <p class="gacha-result-sub" x-text="winner && winner.discount_name ? winner.discount_name : 'Congratulations on your prize!'"></p>
                        <button @click="claimReward()" class="px-btn-gold gacha-result-claim">CLAIM REWARD</button>
                    </div>
                </div>

                {{-- Rules / Info Modal --}}
                <div x-show="showInfoModal" class="px-modal-overlay" x-transition @click.self="showInfoModal = false">
                    <div class="px-modal-box gacha-info-box">
                        <div class="gacha-info-header">
                            <h2 class="gacha-info-title">HOW THE GACHA WORKS</h2>
                            <button @click="showInfoModal = false" class="gacha-info-close" aria-label="Close">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>

                        <section class="gacha-info-section">
                            <h3 class="gacha-info-section-title">SPIN COSTS</h3>
                            <ul class="gacha-info-list">
                                <li><strong>{{ $spinCost }} points</strong> — deducted from your balance.</li>
                                <li><strong>Rp {{ number_format($paidSpinPrice, 0, ',', '.') }}</strong> — via Midtrans, fulfils after payment confirmation.</li>
                                <li><strong>1 free spin token</strong> — won occasionally from the pool; consumes one token per use.</li>
                            </ul>
                        </section>

                        <section class="gacha-info-section">
                            <h3 class="gacha-info-section-title">PITY SYSTEM</h3>
                            <ul class="gacha-info-list">
                                <li><strong>Mini Pity</strong> — after {{ $miniPityThreshold }} spins without an Uncommon+ win, your next spin is guaranteed to be Uncommon+.</li>
                                <li><strong>Hard Pity</strong> — after {{ $hardPityThreshold }} spins without an Epic+ win, your next spin is guaranteed to be Epic+.</li>
                                <li>An Uncommon+ win resets the mini counter. An Epic+ win resets both.</li>
                            </ul>
                        </section>

                        <section class="gacha-info-section">
                            <h3 class="gacha-info-section-title">RARITY CHANCES</h3>
                            <p class="gacha-info-note">
                                Each rarity carries a fixed base chance. Per-prize odds = rarity chance ÷ number of prizes in that rarity.
                                Active boosters shift weight into their floor-and-above rarities (shown in <span class="gacha-chance-bonus-inline">green</span>), taken from lower rarities (shown in <span class="gacha-chance-penalty-inline">red</span>).
                            </p>
                            <div class="gacha-info-rarity-list">
                                <template x-for="row in rarityBreakdown" :key="'i-' + row.rarity">
                                    <div class="gacha-info-rarity-block">
                                        <div class="gacha-info-rarity-head">
                                            <div class="gacha-rarity-label">
                                                <div class="gacha-rarity-dot" :style="'background:' + rarityColor(row.rarity)"></div>
                                                <span class="gacha-rarity-name" x-text="rarityDisplayName(row.rarity)"></span>
                                            </div>
                                            <div class="gacha-rarity-numbers">
                                                <span class="gacha-rarity-base" x-text="formatPct(baseChance(row.rarity)) + '%'"></span>
                                                <span x-show="chanceDelta(row.rarity) > 0.001" class="gacha-chance-bonus" x-text="'+ ' + formatPct(chanceDelta(row.rarity)) + '%'"></span>
                                                <span x-show="chanceDelta(row.rarity) < -0.001" class="gacha-chance-penalty" x-text="'- ' + formatPct(Math.abs(chanceDelta(row.rarity))) + '%'"></span>
                                                <span x-show="Math.abs(chanceDelta(row.rarity)) > 0.001" class="gacha-rarity-eff" x-text="'(= ' + formatPct(effectiveChance(row.rarity)) + '%)'"></span>
                                            </div>
                                        </div>
                                        <ul class="gacha-info-prize-list">
                                            <template x-for="p in prizesIn(row.rarity)" :key="'ip-' + p.id">
                                                <li>
                                                    <span x-text="p.name"></span>
                                                    <span class="gacha-info-prize-rate" x-text="formatPct(perPrizeChance(row.rarity)) + '%'"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </section>

                        <section class="gacha-info-section">
                            <h3 class="gacha-info-section-title">BOOSTERS</h3>
                            <ul class="gacha-info-list">
                                <li>Each booster grants a fixed number of <strong>roll charges</strong>. One charge is consumed per spin (any cost type) while active.</li>
                                <li>Re-activating a booster you already own <strong>stacks the charges</strong>.</li>
                                <li>The bonus % shifts weight INTO the booster's rarity floor and above, taken proportionally from lower rarities. Multiple boosters stack.</li>
                            </ul>
                        </section>

                        <div class="gacha-info-footer">
                            <button @click="showInfoModal = false" class="px-btn-gold gacha-info-close-btn">GOT IT</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    function gachaPage(items, spinCost, boosters, activeBoosters, freeSpins, rarityBreakdown, baseRarityChances, effectiveRarityChances) {
        const RARITY_RANK = { common: 1, uncommon: 2, rare: 3, epic: 4, legendary: 5, grand_prize: 6 };

        return {
            spinning: false,
            showResult: false,
            showInfoModal: false,
            winner: null,
            pendingPrize: null,
            revealTimers: [],
            showSkip: false,
            // Idle: gentle continuous spin until the user pulls. During an actual spin we
            // swap to the fast loop, then to the deceleration class for the reveal.
            animationClass: 'gacha-idle-loop',
            dragOffset: 0,
            items,
            spinCost,
            boosters,
            activeBoosters,
            freeSpins,
            boosterBusy: null,
            rarityBreakdown,
            baseRarityChances: baseRarityChances || {},
            liveEffectiveChances: effectiveRarityChances || {},
            pityCounter: {{ (int) ($pity['pity_counter'] ?? 0) }},
            miniPityCounter: {{ (int) ($pity['mini_pity_counter'] ?? 0) }},

            // Cabinet impact shake + arcade sound (Web Audio synth, no asset files).
            impact: false,
            muted: false,
            audioCtx: null,
            tickTimer: null,

            init() {
                // Shuffle the carousel order so the idle drift interleaves rarities
                // (the backend sorts highest-rarity-first, which otherwise parks the
                // idle view on grand/legendary and never reaches uncommon/common).
                // Spin/reveal looks up the winner by id, so order is purely cosmetic.
                this.items = this.shuffle(this.items.slice());

                // Drive the idle drift across one FULL set width so every prize scrolls
                // past before the loop repeats; keep per-card speed constant (~3.2s each).
                const cardStride = 176; // 160px card + 16px gap, matches reveal math
                const stride = this.items.length * cardStride;
                this.$nextTick(() => {
                    if (this.$refs.carousel) {
                        this.$refs.carousel.style.setProperty('--gacha-idle-stride', stride + 'px');
                        this.$refs.carousel.style.setProperty('--gacha-idle-duration', (this.items.length * 3.2) + 's');
                    }
                });

                // Make sure the carousel is in the idle-loop class on first paint.
                this.animationClass = 'gacha-idle-loop';

                this.muted = localStorage.getItem('gachaMuted') === '1';
            },

            shuffle(arr) {
                for (let i = arr.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [arr[i], arr[j]] = [arr[j], arr[i]];
                }
                return arr;
            },

            /* ---- Arcade sound (synthesised, no audio files) ---- */
            toggleMute() {
                this.muted = !this.muted;
                localStorage.setItem('gachaMuted', this.muted ? '1' : '0');
                if (this.muted) this.stopTicks();
            },
            initAudio() {
                if (this.audioCtx || this.muted) return;
                try {
                    const Ctx = window.AudioContext || window.webkitAudioContext;
                    if (Ctx) this.audioCtx = new Ctx();
                } catch (e) { /* audio unsupported — silent */ }
            },
            beep(freq, durMs, type = 'square', gain = 0.04) {
                if (this.muted || !this.audioCtx) return;
                const ctx = this.audioCtx;
                if (ctx.state === 'suspended') ctx.resume();
                const o = ctx.createOscillator();
                const g = ctx.createGain();
                o.type = type;
                o.frequency.value = freq;
                o.connect(g); g.connect(ctx.destination);
                const t = ctx.currentTime;
                g.gain.setValueAtTime(gain, t);
                g.gain.exponentialRampToValueAtTime(0.0001, t + durMs / 1000);
                o.start(t);
                o.stop(t + durMs / 1000);
            },
            startTicks() {
                if (this.muted) return;
                this.stopTicks();
                this.tickTimer = setInterval(() => this.beep(1300, 22, 'square', 0.025), 95);
            },
            stopTicks() {
                if (this.tickTimer) { clearInterval(this.tickTimer); this.tickTimer = null; }
            },
            playWin(rarity) {
                const base = { common: 440, uncommon: 523, rare: 587, epic: 659, legendary: 784, grand_prize: 880 }[rarity] || 523;
                const notes = (rarity === 'legendary' || rarity === 'grand_prize') ? [0, 90, 180, 280] : [0, 110, 220];
                notes.forEach((d, i) => setTimeout(() => this.beep(base * (1 + i * 0.26), 170, 'square', 0.05), d));
            },

            rarityDisplayName(rarity) {
                return (rarity || '').replace('_', ' ').toUpperCase();
            },

            formatPct(value) {
                const v = Number(value) || 0;
                return v.toFixed(2);
            },

            baseChance(rarity) {
                return Number(this.baseRarityChances[rarity] ?? 0);
            },

            effectiveChance(rarity) {
                return Number(this.liveEffectiveChances[rarity] ?? this.baseChance(rarity));
            },

            chanceDelta(rarity) {
                return this.effectiveChance(rarity) - this.baseChance(rarity);
            },

            prizesIn(rarity) {
                return this.items.filter(it => it.rarity === rarity);
            },

            perPrizeChance(rarity) {
                const row = this.rarityBreakdown.find(r => r.rarity === rarity);
                if (!row || row.prize_count === 0) return 0;
                return this.effectiveChance(rarity) / row.prize_count;
            },

            activeBoosterFor(boosterId) {
                return this.activeBoosters.find(ab => ab.booster_id === boosterId);
            },

            activeRollsLeft(boosterId) {
                const ab = this.activeBoosterFor(boosterId);
                return ab ? ab.rolls_remaining : 0;
            },

            /**
             * Mirrors GachaRollService::effectiveRarityWeights — recomputes per-rarity
             * weights locally whenever the user activates a booster client-side, so the
             * UI deltas refresh without a round trip.
             */
            recomputeEffectiveChances() {
                const present = this.rarityBreakdown.map(r => r.rarity);
                const weights = {};
                let sum = 0;
                present.forEach(r => {
                    const w = Number(this.baseRarityChances[r] ?? 0);
                    if (w > 0) { weights[r] = w; sum += w; }
                });
                if (sum <= 0) {
                    this.liveEffectiveChances = {};
                    return;
                }
                const scale = 100 / sum;
                Object.keys(weights).forEach(r => { weights[r] *= scale; });

                this.activeBoosters.forEach(ab => {
                    if (!ab || ab.rolls_remaining <= 0) return;
                    const floorRank = RARITY_RANK[ab.rarity_floor] || 0;
                    const bonus = Number(ab.bonus_percent) || 0;
                    const bonusKeys = Object.keys(weights).filter(r => (RARITY_RANK[r] || 0) >= floorRank);
                    const nonBonusKeys = Object.keys(weights).filter(r => !bonusKeys.includes(r));
                    const bonusSum = bonusKeys.reduce((s, k) => s + weights[k], 0);
                    const nonBonusSum = nonBonusKeys.reduce((s, k) => s + weights[k], 0);
                    if (bonusSum <= 0 || nonBonusSum <= 0) return;
                    const shift = Math.min(bonus, nonBonusSum);
                    const bonusScale = (bonusSum + shift) / bonusSum;
                    const nonBonusScale = (nonBonusSum - shift) / nonBonusSum;
                    bonusKeys.forEach(k => { weights[k] *= bonusScale; });
                    nonBonusKeys.forEach(k => { weights[k] *= nonBonusScale; });
                });

                this.liveEffectiveChances = weights;
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
                    this.recomputeEffectiveChances();
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

                await this.spinWithCostType(costType);
            },

            beginSpinAnimation() {
                this.spinning = true;
                this.showResult = false;
                this.winner = null;
                this.showSkip = true;
                // Swap from idle drift → fast loop.
                this.animationClass = 'gacha-spinning';
                // Arcade SFX: a start blip then the reel-tick loop.
                this.initAudio();
                this.beep(660, 120, 'square', 0.05);
                this.startTicks();
            },

            async spinWithCostType(costType) {
                try {
                    const body = costType === 'free_spin'
                        ? JSON.stringify({ cost_type: 'free_spin' })
                        : undefined;
                    const res = await fetch('{{ route("gacha.roll") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body,
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        this.toastError(data.message || 'Spin failed.');
                        return;
                    }

                    this.beginSpinAnimation();
                    this.absorbPitySnapshot(data.pity);
                    this.revealPrize(data.prize);
                } catch (e) {
                    this.toastError('Network error. Please try again.');
                }
            },

            absorbPitySnapshot(snapshot) {
                if (!snapshot) return;
                this.freeSpins = Number(snapshot.free_spins ?? this.freeSpins);
                this.pityCounter = Number(snapshot.pity_counter ?? this.pityCounter);
                this.miniPityCounter = Number(snapshot.mini_pity_counter ?? this.miniPityCounter);
                if (Array.isArray(snapshot.active_boosters)) {
                    this.activeBoosters = snapshot.active_boosters;
                }
                if (snapshot.base_rarity_chances) {
                    this.baseRarityChances = snapshot.base_rarity_chances;
                }
                if (snapshot.effective_rarity_chances) {
                    this.liveEffectiveChances = snapshot.effective_rarity_chances;
                } else {
                    this.recomputeEffectiveChances();
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
                    onClose: () => { this.resetSpin(); /* Webhook will still fulfil if paid out-of-band. */ },
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
                // Return to gentle idle drift so the carousel is never frozen.
                this.animationClass = 'gacha-idle-loop';
                this.$refs.carousel.style.removeProperty('transform');
                // Reel ticks stop; cabinet "ka-chunk" impact + win jingle.
                this.stopTicks();
                this.impact = true;
                setTimeout(() => { this.impact = false; }, 450);
                if (this.winner) this.playWin(this.winner.rarity);
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
                this.animationClass = 'gacha-idle-loop';
                this.stopTicks();
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
