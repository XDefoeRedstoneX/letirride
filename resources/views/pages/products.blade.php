<x-app-layout>
<style>
    /* Hide the global pixel-city background only on this page */
    .fixed.inset-0.-z-10 { display: none !important; }
    
    /* Make default background elements transparent so the new bg shows through */
    body, .ridly-products {
        background-color: transparent !important;
    }
    
    body {
        background-color: #050810 !important; 
        background-image: 
            url('{{ asset("bg/bg_night.svg") }}'), 
            url('{{ asset("bg/night_sky.svg") }}') !important;
        /* City at bottom, sky at top */
        background-position: bottom center, top center !important;
        background-repeat: no-repeat, no-repeat !important;
        
        /* 
           bg_night is 100% auto 
           night_sky is 100% width. To make its height stretch EXACTLY to the top of bg_night, 
           we subtract the height of bg_night from 100%.
           Since bg_night aspect ratio is 480x1000, its height is (1000/480)*100vw = 208.33vw.
        */
        background-size: 100% auto, 100% calc(100% - 208.33vw) !important;
        background-attachment: scroll, scroll !important;
    }
</style>

@auth
    @if (! empty($showReferralPrompt))
        <section x-data="{
            show: localStorage.getItem('ridly_ref_dismissed') !== '1'
        }"
        x-show="show"
        x-transition.opacity
        class="referral-banner">
            <div class="referral-banner-body">
                <span class="referral-banner-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11h-6"/><path d="M19 8v6"/></svg>
                </span>
                <p class="referral-banner-text">
                    Got invited by a friend? Enter their code in
                    <a href="{{ route('referrals') }}" class="referral-banner-link">Refer Friends</a>
                    @if ($referralWelcomePoints > 0)
                        to claim <strong>{{ number_format($referralWelcomePoints) }} bonus points</strong>.
                    @else
                        before your first purchase.
                    @endif
                </p>
            </div>
            <button type="button"
                    @click="show = false; localStorage.setItem('ridly_ref_dismissed', '1')"
                    class="referral-banner-close" aria-label="Dismiss">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </section>
    @endif
@endauth
{{-- ── Hero Billboard ──────────────────────────────────────────── --}}
<section class="hero-section" aria-label="Hero banner">
    <div class="hero-frame-grid">
        {{-- Frame border image --}}
        <img src="{{ asset('components/frame/news.png') }}" class="hero-frame-img pixel-render" alt="Hero Frame">

        {{-- News slideshow inside the frame --}}
        <script>window._ridlyNews = {!! json_encode(array_values($newsImages), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};</script>
        <div class="hero-frame-content"
             x-data="{
                 imgs: window._ridlyNews || [],
                 i: 0,
                 init() {
                     if (this.imgs.length > 1)
                         setInterval(() => this.i = (this.i + 1) % this.imgs.length, 4000)
                 }
             }">
            <template x-for="(src, idx) in imgs" :key="idx">
                <img :src="src"
                     x-show="i === idx"
                     x-transition.opacity.duration.500ms
                     class="hero-news-img"
                     alt="">
            </template>
        </div>
    </div>
</section>

{{-- ── Store (hub + full catalog, one Alpine component) ─────────── --}}
<div class="ridly-products"
     x-data="ridlyStore({{ \Illuminate\Support\Js::from($products) }}, {{ \Illuminate\Support\Js::from($favoriteIds ?? []) }}, {{ Auth::check() ? 'true' : 'false' }}, '{{ csrf_token() }}')"
     x-cloak>

    <div class="page-inner">

        {{-- ── Popular (placeholder — content TBD) ───────────────────
             Section intentionally kept in place but empty for now.    --}}
        <section class="popular-section popular-placeholder">
            {{-- Reserved for the "Popular" feature. --}}
        </section>

        {{-- ════════════════════════════════════════════════════════
             FULL CATALOG (merged from the old All Products page).
             VIEW ALL / brand tiles above filter and scroll to here.
        ════════════════════════════════════════════════════════ --}}
        <div class="catalog-merged" x-ref="catalog">

            <div class="px-divider">
                <div class="px-divider-dot"></div>
                <div class="px-divider-line"></div>
                <div class="px-divider-dot"></div>
            </div>

            <div class="catalog-heading">
                <h2 class="page-title">ALL <span class="gold">PRODUCTS</span></h2>
                <p class="page-sub">SEARCH, FILTER, AND BUY THE FULL RIDLY CATALOG</p>
            </div>

            {{-- ── Sticky Header: Search + Horizontal Categories ── --}}
            <div class="sticky z-40 bg-background/90 backdrop-blur-xl pt-4 pb-4 border-b border-border shadow-sm mb-8 -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 transition-all" style="top: 56px;">
                <div class="search-wrap mb-4" style="margin-bottom: 16px;">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="square" stroke-linejoin="miter">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="text"
                           x-model="search"
                           placeholder="SEARCH PRODUCTS..."
                           class="w-full bg-card border-2 border-border rounded-xl px-12 py-3.5 text-sm font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all shadow-sm">
                </div>

                <div class="flex gap-3 overflow-x-auto scrollbar-hide py-1">
                    <button @click="setFilter('All')"
                            :class="activeFilter === 'All' ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-muted-foreground border-border hover:border-primary/50 hover:text-primary'"
                            class="whitespace-nowrap px-6 py-2.5 rounded-xl font-black text-[10px] tracking-widest border-2 shadow-sm transition-all flex-shrink-0">
                        ALL
                    </button>
                    <template x-for="cat in categories.filter(c => c !== 'All')" :key="cat">
                        <button @click="setFilter(cat)"
                                :class="activeFilter === cat ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-muted-foreground border-border hover:border-primary/50 hover:text-primary'"
                                class="whitespace-nowrap px-6 py-2.5 rounded-xl font-black text-[10px] tracking-widest border-2 shadow-sm transition-all flex-shrink-0"
                                x-text="cat">
                        </button>
                    </template>
                </div>
            </div>

            {{-- ── Full Width Main Catalog ── --}}
            <div class="w-full">

                {{-- Browse By Brand (3D Coverflow) --}}
                <div x-show="brands.length > 0" class="mb-2">
                    <div x-data="brandSlider()" x-init="initSlider()" class="relative w-full py-2" style="perspective: 1200px;">
                        <div x-ref="slider" class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide py-4 px-[calc(50%-6rem)] sm:px-[calc(50%-7rem)] items-center"
                             style="scroll-behavior: smooth; cursor: grab;"
                             @mousedown="startDrag" @mouseleave="endDrag" @mouseup="endDrag" @mousemove="doDrag"
                             @touchstart="startDrag" @touchend="endDrag" @touchmove="doDrag">
                            <template x-for="(brand, index) in brands" :key="brand.name">
                                <div class="snap-center shrink-0 w-48 sm:w-56 transition-all duration-300 ease-out cursor-pointer group relative mx-[-1rem] sm:mx-[-1.5rem]"
                                     @click="if(!isDragging) { scrollTo(index); $nextTick(() => selectBrand(brand.name)); }"
                                     :style="getCardStyle(index)">

                                     <div class="flex flex-col bg-card shadow-2xl rounded-xl h-full transition-all duration-300 border-2 pointer-events-none select-none overflow-hidden"
                                          :class="selectedBrand === brand.name ? 'border-primary ring-4 ring-primary/20' : 'border-border'">

                                         {{-- Full Frame Thumbnail Area --}}
                                         <div class="w-full aspect-[4/3] flex items-center justify-center p-4 bg-gradient-to-br from-slate-800 to-slate-950 dark:from-[#0a1020] dark:to-[#040812]">
                                             <img :src="brand.image" draggable="false" class="w-3/4 h-3/4 object-contain drop-shadow-xl transition-transform duration-500 group-hover:scale-110">
                                         </div>

                                         {{-- Text Area Below --}}
                                         <div class="flex flex-col items-center p-3 bg-card border-t border-border/50">
                                             <span class="text-xs font-black uppercase tracking-widest text-foreground text-center" x-text="brand.name"></span>
                                             <span class="text-[9px] font-bold text-muted-foreground mt-1 tracking-widest" x-text="brand.count + ' ITEM' + (brand.count !== 1 ? 'S' : '')"></span>
                                         </div>

                                     </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Sentinel — selectBrand() and the ↓ button scroll here --}}
                <div id="product-list" style="scroll-margin-top: 140px;"></div>

                {{-- Product Groups (catalog OR selected brand variants) --}}
                <template x-for="group in displayGroups" :key="group.key">
                    <section class="products-section">

                        <div class="section-bar">
                            <span class="section-title">
                                <span class="cat-emoji" x-show="group.emoji" x-text="group.emoji"></span>
                                <span x-text="(group.isBrand ? '▣ ' : '') + group.label"></span>
                            </span>
                            <div class="section-right">
                                <span class="section-meta"
                                      x-text="group.products.length + ' ITEM' + (group.products.length !== 1 ? 'S' : '')"></span>
                                <button x-show="group.isBrand"
                                        class="clear-brand-btn"
                                        @click="clearBrand()"
                                        type="button">✕ CLEAR</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 pb-8">
                                <template x-for="product in group.products" :key="product.id">
                                    <div class="product-card px-border-card"
                                         :class="!product.in_stock ? 'is-out-of-stock' : ''"
                                         @click="product.in_stock && openBuyModal(product)">

                                        <div class="card-img-wrap">
                                            <img :src="product.image"
                                                 alt="steam-wallet.png"
                                                 class="card-img">
                                            <div class="card-badges">
                                                <span class="badge-cat" x-text="product.category"></span>
                                                <span class="badge-topup"
                                                      x-show="product.product_type === 'direct_topup'">
                                                    ⚡ TOP-UP
                                                </span>
                                            </div>
                                            <template x-if="!product.in_stock">
                                                <div class="stockout-overlay">
                                                    <span class="stockout-text">OUT OF STOCK</span>
                                                </div>
                                            </template>


                                        </div>

                                        <div class="card-body">
                                            <h3 class="card-title" x-text="product.name"></h3>
                                            <div class="card-footer">
                                                <div>
                                                    <span class="price-label">PRICE</span>
                                                    <span class="price-value" :class="!product.in_stock ? '!text-gray-500' : ''"
                                                          x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)">
                                                    </span>
                                                    <template x-if="product.product_type === 'voucher' && product.in_stock">
            <span class="stock-line" :class="product.stock <= 3 ? 'low' : 'ok'" x-text="product.stock + ' IN STOCK'"></span>
        </template>
                                                </div>
                                                <div class="card-actions" @click.stop>
                                                    <button @click="toggleFavorite(product.id)"
                                                            :class="favorites.includes(product.id) ? 'active' : ''"
                                                            class="fav-btn"
                                                            title="Favorite">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                             viewBox="0 0 24 24"
                                                             :fill="favorites.includes(product.id) ? 'currentColor' : 'none'"
                                                             stroke="currentColor" stroke-width="2.5"
                                                             stroke-linecap="square">
                                                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                                                        </svg>
                                                    </button>
                                                    <button @click="product.in_stock && openBuyModal(product)"
                                                            :disabled="!product.in_stock"
                                                            :class="!product.in_stock ? 'buy-btn-disabled' : 'buy-btn'"
                                                            x-text="product.in_stock ? 'BUY' : 'SOLD OUT'">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </template>
                            </div>

                        </section>
                    </template>

                    {{-- Empty State --}}
                    <div x-show="!hasResults" class="empty-state" x-transition>
                        <div class="empty-icon-box">🔍</div>
                        <p class="empty-text">NO PRODUCTS FOUND</p>
                        <p class="empty-sub">TRY A DIFFERENT SEARCH TERM OR CATEGORY</p>
                    </div>

                </div>{{-- /w-full --}}
            </div>{{-- /catalog-merged --}}

        </div>{{-- /page-inner --}}

    {{-- ── Buy Modal ──────────────────────────────────────────── --}}
    <div x-show="showCartModal"
         class="modal-overlay"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="showCartModal = false"
             class="px-frame"
             style="max-width: 480px; width: 100%;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Frame border pieces --}}
            <div class="px-frame-tl"></div>
            <div class="px-frame-t"></div>
            <div class="px-frame-tr"></div>
            <div class="px-frame-l"></div>

            {{-- Content inside the frame --}}
            <div class="px-frame-content" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">

                {{-- Header --}}
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" x-text="selectedProduct?.name"></h2>
                        <p class="modal-cat" x-text="selectedProduct?.category"></p>
                    </div>
                    <button @click="showCartModal = false" class="modal-close" title="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Product Image --}}
                <div class="modal-img-wrap">
                    <img :src="selectedProduct?.image" alt="steam-wallet.png">
                </div>

                {{-- Direct Top-Up Fields --}}
                <template x-if="selectedProduct?.product_type === 'direct_topup'">
                    <div class="modal-topup-box">
                    <div class="topup-heading">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" x2="3" y1="12" y2="12"/>
                        </svg>
                        ENTER YOUR GAME CREDENTIALS
                    </div>
                    <div>
                        <label class="field-label">PLAYER ID <span class="req">*</span></label>
                        <input type="text" x-model="topupPlayerId"
                               placeholder="Enter your Player ID"
                               class="px-input">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="field-label">ZONE ID</label>
                            <input type="text" x-model="topupZoneId"
                                   placeholder="Optional"
                                   class="px-input">
                        </div>
                        <div>
                            <label class="field-label">SERVER ID</label>
                            <input type="text" x-model="topupServerId"
                                   placeholder="Optional"
                                   class="px-input">
                        </div>
                    </div>
                </div>
            </template>

            {{-- Price Row --}}
            <div class="modal-price-row">
                <span class="modal-price-label">TOTAL PRICE</span>
                <span class="modal-price-val"
                      x-text="'Rp ' + (selectedProduct ? new Intl.NumberFormat('id-ID').format(selectedProduct.price) : 0)">
                </span>
            </div>

            {{-- Stock Row (voucher only) --}}
            <template x-if="selectedProduct && selectedProduct.product_type === 'voucher'">
                <div class="modal-stock-row"
                     :class="selectedProduct.in_stock ? (selectedProduct.stock <= 3 ? 'low' : 'ok') : 'none'">
                    <span class="modal-stock-label">STOCK</span>
                    <span class="modal-stock-val"
                          x-text="selectedProduct.in_stock ? (selectedProduct.stock + ' KEY' + (selectedProduct.stock !== 1 ? 'S' : '') + ' AVAILABLE') : 'OUT OF STOCK'">
                    </span>
                </div>
            </template>

            {{-- Actions --}}
            <div class="modal-actions">
                <button @click="selectedProduct?.in_stock && addToCart()"
                        :disabled="!(selectedProduct?.in_stock)"
                        :class="!(selectedProduct?.in_stock) ? 'modal-btn-disabled' : 'modal-btn-secondary'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/>
                    </svg>
                    ADD TO CART
                </button>
                <button @click="selectedProduct?.in_stock && buyNow()"
                        :disabled="!(selectedProduct?.in_stock)"
                        :class="!(selectedProduct?.in_stock) ? 'modal-btn-disabled' : 'modal-btn-primary'"
                        x-text="selectedProduct?.in_stock ? 'BUY NOW' : 'SOLD OUT'">
                </button>
                </div>

            </div>{{-- /px-frame-content --}}

            <div class="px-frame-r"></div>
            <div class="px-frame-bl"></div>
            <div class="px-frame-b"></div>
            <div class="px-frame-br"></div>
        </div>{{-- /px-frame --}}
    </div>{{-- /modal overlay --}}

</div>{{-- /ridly-products --}}


{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════════ --}}
<script>

/* Browse-hub type groups. Each maps one or more product categories to a tile
   section. mode 'brand' → one tile per subcategory (a brand); mode 'product'
   → one tile per product (game keys are one-off titles, not brands). */
const RIDLY_HUB_GROUPS = [
    { key: 'topups',   label: 'GAME TOP-UPS',          emoji: '💎', cats: ['In-Game Currency'],            mode: 'brand'   },
    { key: 'vouchers', label: 'VOUCHERS & GIFT CARDS', emoji: '👛', cats: ['Wallet Top-Ups', 'Gift Cards'], mode: 'brand'  },
    { key: 'subs',     label: 'SUBSCRIPTIONS',         emoji: '📺', cats: ['Subscriptions'],               mode: 'brand'   },
    { key: 'games',    label: 'GAME KEYS',             emoji: '🎮', cats: ['Games'],                       mode: 'product' },
];

/* Category whose items are one-off titles, NOT brand denominations. */
const RIDLY_GAMES_CATEGORY = 'Games';

/* Hub group key → the categories it covers. VIEW ALL scopes the catalog
   below to these categories. */
const RIDLY_GROUP_CATS = {
    topups:   ['In-Game Currency'],
    vouchers: ['Wallet Top-Ups', 'Gift Cards'],
    subs:     ['Subscriptions'],
    games:    ['Games'],
};

/* ── Alpine.js Store (hub + catalog) ────────────────────────── */
function ridlyStore(initialProducts, initialFavorites, isAuthenticated, csrfToken) {
    return {
        /* ─ State ─────────────────────────────────────────────── */
        products:         initialProducts,
        groups:           RIDLY_HUB_GROUPS,
        search:           '',
        activeFilter:     'All',
        selectedBrand:    null,
        groupCats:        null,
        favorites:        initialFavorites,
        isAuthenticated,
        csrfToken,
        showCartModal:    false,
        selectedProduct:  null,
        topupPlayerId:    '',
        topupZoneId:      '',
        topupServerId:    '',

        /* ─ Lifecycle ─────────────────────────────────────────── */
        init() {
            const urlParams = new URLSearchParams(window.location.search);

            const search = urlParams.get('search');
            if (search) this.search = search;

            const group = urlParams.get('group');
            if (group && RIDLY_GROUP_CATS[group]) this.groupCats = RIDLY_GROUP_CATS[group];

            const brand = urlParams.get('brand');
            if (brand && this.products.some(p => (p.subcategory || 'Other') === brand)) {
                this.selectedBrand = brand;
            }

            const buyId = urlParams.get('buy');
            if (buyId) {
                const product = this.products.find(p => p.id == buyId);
                if (product) {
                    this.$nextTick(() => {
                        this.selectedProduct = product;
                        this.showCartModal = true;
                    });
                }
            }

            // A deep link that scopes the catalog should land at the products.
            if ((group || brand || search) && !buyId) {
                this.$nextTick(() => this.scrollToProducts());
            }

            if (search || group || brand || buyId) {
                window.history.replaceState({}, '', window.location.pathname);
            }
        },

        /* ════ HUB (top of page) ══════════════════════════════ */

        // Top row shares one split grid; the rest are full-width rows.
        get splitGroups() { return [this.groups[0], this.groups[1]]; },
        get fullGroups()  { return [this.groups[2], this.groups[3]]; },

        _productsIn(group) {
            return this.products.filter(p => group.cats.includes(p.category || 'Other'));
        },

        // Tiles for a hub group — brand tiles, or one tile per game key.
        tilesFor(group) {
            const items = this._productsIn(group);

            if (group.mode === 'product') {
                return items.map(p => ({
                    key:     'p' + p.id,
                    name:    p.name,
                    image:   p.image,
                    meta:    null,
                    product: p,
                }));
            }

            const map = {};
            for (const p of items) {
                const name = p.subcategory || 'Other';
                if (!map[name]) map[name] = { name, image: p.image, count: 0 };
                map[name].count++;
            }
            return Object.values(map).map(b => ({
                key:     'b:' + b.name,
                name:    b.name,
                image:   b.image,
                meta:    b.count + ' ITEM' + (b.count !== 1 ? 'S' : ''),
                product: null,
            }));
        },

        // Click a hub tile: game key → buy modal; brand → filter + scroll.
        onTileClick(tile) {
            if (tile.product) {
                if (tile.product.in_stock) this.openBuyModal(tile.product);
                return;
            }
            this.goToBrand(tile.name);
        },

        // VIEW ALL: scope the catalog to a group's categories, then scroll.
        viewGroup(key) {
            this.activeFilter  = 'All';
            this.selectedBrand = null;
            this.groupCats     = RIDLY_GROUP_CATS[key] || null;
            this.$nextTick(() => this.scrollToProducts());
        },

        // Pre-select a brand in the catalog, then scroll.
        goToBrand(name) {
            this.activeFilter  = 'All';
            this.groupCats     = null;
            this.selectedBrand = name;
            this.$nextTick(() => this.scrollToCatalog());
        },

        scrollToCatalog() {
            this.$refs.catalog?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        scrollToProducts() {
            document.getElementById('product-list')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        /* ════ CATALOG (bottom of page) ═══════════════════════ */

        get uniqueCategories() {
            return [...new Set(this.products.map(p => p.category || 'Other'))];
        },

        get categories() {
            return ['All', ...this.uniqueCategories];
        },

        get visibleCategories() {
            return this.uniqueCategories.filter(c => this._inScope(c));
        },

        // Brand tiles = subcategories, EXCLUDING the Games category.
        get brands() {
            const map = {};
            for (const p of this.products) {
                if ((p.category || 'Other') === RIDLY_GAMES_CATEGORY) continue;
                if (!this._inScope(p.category || 'Other')) continue;
                if (!this._matchesSearch(p)) continue;
                const name = p.subcategory || 'Other';
                if (!map[name]) {
                    map[name] = { name, category: p.category || 'Other', image: p.image, count: 0 };
                }
                map[name].count++;
            }
            return Object.values(map).sort((a, b) => a.name.localeCompare(b.name));
        },

        get displayGroups() {
            if (this.selectedBrand) {
                return [{
                    key: 'brand::' + this.selectedBrand,
                    label: this.selectedBrand,
                    emoji: '',
                    isBrand: true,
                    products: this.productsForBrand(this.selectedBrand),
                }];
            }
            return this.visibleCategories
                .map(cat => ({
                    key: 'cat::' + cat,
                    label: cat,
                    emoji: this.categoryEmoji(cat),
                    isBrand: false,
                    products: this.productsForCategory(cat),
                }))
                .filter(g => g.products.length > 0);
        },

        get hasResults() {
            return this.brands.length > 0 || this.displayGroups.length > 0;
        },

        /* ─ Helpers ───────────────────────────────────────────── */
        categoryEmoji(cat) {
            const map = {
                'Games':            '🎮',
                'Wallet Top-Ups':   '👛',
                'In-Game Currency': '💎',
                'Gift Cards':       '🎁',
                'Subscriptions':    '📺',
                'Other':            '✦',
            };
            return map[cat] || '✦';
        },

        _matchesSearch(p) {
            const kw = this.search.trim().toLowerCase();
            if (!kw) return true;
            return p.name.toLowerCase().includes(kw)
                || (p.category || '').toLowerCase().includes(kw)
                || (p.subcategory || '').toLowerCase().includes(kw);
        },

        _inScope(cat) {
            if (this.activeFilter !== 'All' && cat !== this.activeFilter) return false;
            if (this.groupCats && !this.groupCats.includes(cat)) return false;
            return true;
        },

        productsForCategory(cat) {
            return this.products.filter(p =>
                (p.category || 'Other') === cat && this._matchesSearch(p)
            );
        },

        productsForBrand(name) {
            return this.products.filter(p =>
                (p.subcategory || 'Other') === name
                && (p.category || 'Other') !== RIDLY_GAMES_CATEGORY
                && this._inScope(p.category || 'Other')
                && this._matchesSearch(p)
            );
        },

        /* ─ Actions ───────────────────────────────────────────── */
        setFilter(cat) {
            this.activeFilter = cat;
            this.selectedBrand = null;
            this.groupCats = null;
        },

        selectBrand(name) {
            this.selectedBrand = (this.selectedBrand === name) ? null : name;
            if (this.selectedBrand) this.$nextTick(() => this.scrollToProducts());
        },

        clearBrand() {
            this.selectedBrand = null;
        },

        openBuyModal(product) {
            this.selectedProduct = product;
            this.topupPlayerId   = '';
            this.topupZoneId     = '';
            this.topupServerId   = '';
            this.showCartModal   = true;
        },

        /* ─ Favorites ─────────────────────────────────────────── */
        async toggleFavorite(productId) {
            if (!this.isAuthenticated) {
                window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'login' } }));
                return;
            }
            const isFavorited = this.favorites.includes(productId);
            const response = await fetch(`/favorites/${productId}`, {
                method:  isFavorited ? 'DELETE' : 'POST',
                headers: {
                    'Accept':            'application/json',
                    'X-Requested-With':  'XMLHttpRequest',
                    'X-CSRF-TOKEN':       this.csrfToken,
                },
            });
            if (!response.ok) return;
            if (isFavorited) {
                this.favorites = this.favorites.filter(id => id !== productId);
            } else {
                this.favorites.push(productId);
            }
        },

        /* ─ Cart body builder ─────────────────────────────────── */
        _buildCartBody() {
            if (!this.selectedProduct) return null;
            const body = {};
            if (this.selectedProduct.product_type === 'direct_topup') {
                if (!this.topupPlayerId || !this.topupPlayerId.trim()) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'Please enter your Player ID.', type: 'error' },
                    }));
                    return null;
                }
                body.player_id = this.topupPlayerId.trim();
                body.zone_id   = this.topupZoneId.trim()   || null;
                body.server_id = this.topupServerId.trim()  || null;
            }
            return body;
        },

        async addToCart() {
            if (!this.isAuthenticated) {
                window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'login' } }));
                return;
            }
            if (!this.selectedProduct) return;
            const body = this._buildCartBody();
            if (body === null) return;
            try {
                const response = await fetch(`/cart/${this.selectedProduct.id}`, {
                    method:  'POST',
                    headers: {
                        'Accept':           'application/json',
                        'Content-Type':     'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN':      this.csrfToken,
                    },
                    body: JSON.stringify(body),
                });
                const data = await response.json();
                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
                    window.dispatchEvent(new CustomEvent('show-toast',   { detail: { message: data.message, type: 'success' } }));
                } else {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Failed to add to cart.', type: 'error' } }));
                }
            } catch (e) {
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error. Please try again.', type: 'error' } }));
            }
            this.showCartModal = false;
        },

        /* ─ Buy Now ───────────────────────────────────────────── */
        async buyNow() {
            if (!this.isAuthenticated) {
                window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'login' } }));
                return;
            }
            if (!this.selectedProduct) return;
            const body = this._buildCartBody();
            if (body === null) return;
            try {
                const response = await fetch(`/cart/${this.selectedProduct.id}`, {
                    method:  'POST',
                    headers: {
                        'Accept':           'application/json',
                        'Content-Type':     'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN':      this.csrfToken,
                    },
                    body: JSON.stringify(body),
                });
                if (response.ok) {
                    window.location.href = '/cart';
                    return;
                }
                const data = await response.json();
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Failed.', type: 'error' } }));
            } catch (e) {
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error.', type: 'error' } }));
            }
        },
    };
}

function brandSlider() {
    return {
        activeIdx: 0,
        isDown: false,
        startX: 0,
        scrollLeft: 0,
        isDragging: false,
        initSlider() {
            const el = this.$refs.slider;
            el.addEventListener("scroll", () => { this.calcActive(); });
            this.$nextTick(() => this.calcActive());
        },
        calcActive() {
            const el = this.$refs.slider;
            const center = el.scrollLeft + (el.clientWidth / 2);
            let closest = 0;
            let minDiff = Infinity;
            const cards = el.querySelectorAll(".snap-center");
            cards.forEach((card, i) => {
                const cc = card.offsetLeft + (card.offsetWidth / 2);
                const d = Math.abs(center - cc);
                if (d < minDiff) { minDiff = d; closest = i; }
            });
            this.activeIdx = closest;
        },
        scrollTo(index) {
            const el = this.$refs.slider;
            const cards = el.querySelectorAll(".snap-center");
            const child = cards[index];
            if (!child) return;
            el.scrollTo({ left: child.offsetLeft - (el.clientWidth / 2) + (child.offsetWidth / 2), behavior: "smooth" });
            this.activeIdx = index;
        },
        startDrag(e) {
            this.isDown = true;
            this.isDragging = false;
            const pageX = e.pageX || (e.touches && e.touches[0].pageX);
            this.startX = pageX - this.$refs.slider.getBoundingClientRect().left;
            this.scrollLeft = this.$refs.slider.scrollLeft;
        },
        endDrag() {
            this.isDown = false;
            setTimeout(() => { this.isDragging = false; }, 50);
        },
        doDrag(e) {
            if (!this.isDown) return;
            e.preventDefault();
            this.isDragging = true;
            const pageX = e.pageX || (e.touches && e.touches[0].pageX);
            const x = pageX - this.$refs.slider.getBoundingClientRect().left;
            this.$refs.slider.scrollLeft = this.scrollLeft - (x - this.startX) * 1.5;
        },
        getCardStyle(index) {
            const diff = Math.abs(this.activeIdx - index);
            const isCenter = diff === 0;
            const direction = Math.sign(index - this.activeIdx);
            const zIndex = 50 - diff;

            // Pengaturan 3D
            const scale = Math.max(0.65, 1 - (diff * 0.1)); // Lebih landai mengecilnya
            const rotateY = isCenter ? 0 : direction * -10; // Sedikit diputar ke tengah
            const translateZ = isCenter ? "0px" : (-40 * diff) + "px"; // Mundurnya tidak terlalu jauh

            // Note: filter brightness 100% agar tidak gelap
            return `z-index:${zIndex};transform:perspective(1200px) translateZ(${translateZ}) rotateY(${rotateY}deg) scale(${scale});`;
        }
    };
}
</script>
</x-app-layout>
