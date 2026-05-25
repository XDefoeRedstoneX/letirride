<x-app-layout>
{{-- ── Hero Billboard ──────────────────────────────────────────── --}}
<section class="hero-section" aria-label="Hero banner">
    <div class="hero-frame-grid">
        {{-- Frame border pieces --}}
        <div class="px-frame-tl"></div>
        <div class="px-frame-t"></div>
        <div class="px-frame-tr"></div>
        <div class="px-frame-l"></div>

        {{-- News slideshow inside the frame --}}
        <div class="hero-frame-content"
             x-data="{
                 imgs: [
                     '{{ asset('news/1.jpg') }}',
                     '{{ asset('news/2.jpg') }}',
                     '{{ asset('news/3.jpg') }}',
                     '{{ asset('news/4.jpg') }}',
                 ],
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

        <div class="px-frame-r"></div>
        <div class="px-frame-bl"></div>
        <div class="px-frame-b"></div>
        <div class="px-frame-br"></div>
    </div>

    {{-- Frameplate label below the banner --}}
    <div class="hero-frameplate">
        <img src="{{ asset('components/frame/frameplate.png') }}" alt="RIDLY NEWS" class="pixel-render">
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

        {{-- ── Split Row: Game Top-Ups | Vouchers & Gift Cards ───── --}}
        <div class="hub-split">
            <template x-for="group in splitGroups" :key="group.key">
                <section class="hub-pane">
                    <div class="section-bar">
                        <span class="section-title">
                            <span class="cat-emoji" x-text="group.emoji"></span>
                            <span x-text="group.label"></span>
                        </span>
                        <div class="section-right">
                            <button class="section-viewall" type="button"
                                    @click="viewGroup(group.key)">VIEW ALL →</button>
                        </div>
                    </div>
                    <div class="brand-grid">
                        <template x-for="tile in tilesFor(group)" :key="tile.key">
                            <button class="brand-tile" type="button"
                                    @click="onTileClick(tile)">
                                <div class="brand-img-wrap">
                                    <img :src="tile.image" alt="steam-wallet.png" class="brand-img">
                                </div>
                                <span class="brand-name" x-text="tile.name"></span>
                                <span class="brand-count" x-show="tile.meta" x-text="tile.meta"></span>
                            </button>
                        </template>
                    </div>
                </section>
            </template>
        </div>

        {{-- ── Product Groups (catalog OR selected brand variants) ─ --}}
        <template x-for="group in displayGroups" :key="group.key">
            <section class="products-section">

                {{-- Group header (non-collapsible) --}}
                <div class="section-bar">
                    <span class="section-title">
                        <span class="cat-emoji" x-show="group.emoji" x-text="group.emoji"></span>
                        <span x-text="(group.isBrand ? '▣ ' : '') + group.label"></span>
                    </span>
                    <div class="section-right">
                        <button class="section-viewall" type="button"
                                @click="viewGroup(group.key)">VIEW ALL →</button>
                    </div>
                </div>
                <div class="brand-grid">
                    <template x-for="tile in tilesFor(group)" :key="tile.key">
                        <button class="brand-tile" type="button"
                                @click="onTileClick(tile)">
                            <div class="brand-img-wrap">
                                <img :src="tile.image" alt="steam-wallet.png" class="brand-img">
                            </div>
                            <span class="brand-name" x-text="tile.name"></span>
                            <span class="brand-count" x-show="tile.meta" x-text="tile.meta"></span>
                        </button>
                    </template>
                </div>

            </section>
        </template>

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

            {{-- ── Search Bar ──────────────────────────────────────── --}}
            <div class="search-wrap">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="square" stroke-linejoin="miter">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text"
                       x-model="search"
                       placeholder="SEARCH PRODUCTS..."
                       class="px-search">
            </div>

            {{-- ── Two-column: filter rail (left) + results (right) ── --}}
            <div class="catalog-layout">

                {{-- Left: category filter buttons --}}
                <aside class="filter-rail">
                    <span class="filter-rail-label">CATEGORY</span>
                    <template x-for="cat in categories" :key="cat">
                        <button @click="setFilter(cat)"
                                :class="activeFilter === cat ? 'px-tab-active' : 'px-tab-inactive'"
                                class="px-tab filter-rail-btn"
                                x-text="cat">
                        </button>
                    </template>
                </aside>

                {{-- Right: brand grid + product groups --}}
                <div class="catalog-main">

                    {{-- Browse By Brand --}}
                    <section class="brand-section" x-show="brands.length > 0">
                        <div class="section-bar">
                            <span class="section-title">▣ BROWSE BY BRAND</span>
                            <div class="section-right">
                                <span class="section-meta"
                                      x-text="brands.length + ' BRAND' + (brands.length !== 1 ? 'S' : '')"></span>
                            </div>
                        </div>

                        <div class="brand-grid">
                            <template x-for="brand in brands" :key="brand.name">
                                <button class="brand-tile"
                                        :class="selectedBrand === brand.name ? 'active' : (selectedBrand ? 'faded' : '')"
                                        @click="selectBrand(brand.name)"
                                        type="button">
                                    <div class="brand-img-wrap">
                                        <img :src="brand.image" alt="steam-wallet.png" class="brand-img">
                                    </div>
                                    <span class="brand-name" x-text="brand.name"></span>
                                    <span class="brand-count"
                                          x-show="!selectedBrand || selectedBrand === brand.name"
                                          x-text="brand.count + ' ITEM' + (brand.count !== 1 ? 'S' : '')"></span>
                                </button>
                            </template>
                        </div>
                    </section>

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

                            <div class="product-grid">
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
                                                    <span class="price-value"
                                                          x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)">
                                                    </span>
                                                    <template x-if="product.product_type === 'voucher'">
                                                        <span class="stock-line"
                                                              :class="product.in_stock ? (product.stock <= 3 ? 'low' : 'ok') : 'none'"
                                                              x-text="product.in_stock ? (product.stock + ' IN STOCK') : 'OUT OF STOCK'">
                                                        </span>
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

                </div>{{-- /catalog-main --}}
            </div>{{-- /catalog-layout --}}
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

            // A deep link that scopes the catalog should land there.
            if ((group || brand || search) && !buyId) {
                this.$nextTick(() => this.scrollToCatalog());
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
            for (const p of this.products) {
                if ((p.category || 'Other') === RIDLY_GAMES_CATEGORY) continue;
                if (this.activeFilter !== 'All' && (p.category || 'Other') !== this.activeFilter) continue;
                if (!this._matchesSearch(p)) continue;
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
            this.$nextTick(() => this.scrollToCatalog());
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
            return Object.values(map);
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
            if (this.selectedBrand) this.$nextTick(() => this.scrollToCatalog());
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
</script>
</x-app-layout>
