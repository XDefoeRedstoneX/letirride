<x-app-layout>

{{-- ── News Ticker ─────────────────────────────────────────────── --}}
<div class="news-ticker" aria-label="News ticker">
    <span class="news-label">⚡ RIDLY NEWS</span>
    <div class="news-track-wrap">
        <div class="news-track">
            {{-- Double the content for seamless loop --}}
            <span>★ NEW: Steam Wallet Rp1.000.000 now available &nbsp; ✦ &nbsp; Netflix 4K UHD coming soon &nbsp; ✦ &nbsp; Use code RIDLY10 for 10% off your first purchase &nbsp; ✦ &nbsp; Mobile Top-Up now live — instant delivery &nbsp; ✦ &nbsp; Spotify Family Plan added &nbsp; ✦ &nbsp; Weekend Sale: up to 20% off Gaming vouchers &nbsp; ✦ &nbsp; New: Xbox Game Pass Ultimate &nbsp; ✦ &nbsp; Direct Top-Up now supports Mobile Legends &nbsp; ✦</span>
            <span>★ NEW: Steam Wallet Rp1.000.000 now available &nbsp; ✦ &nbsp; Netflix 4K UHD coming soon &nbsp; ✦ &nbsp; Use code RIDLY10 for 10% off your first purchase &nbsp; ✦ &nbsp; Mobile Top-Up now live — instant delivery &nbsp; ✦ &nbsp; Spotify Family Plan added &nbsp; ✦ &nbsp; Weekend Sale: up to 20% off Gaming vouchers &nbsp; ✦ &nbsp; New: Xbox Game Pass Ultimate &nbsp; ✦ &nbsp; Direct Top-Up now supports Mobile Legends &nbsp; ✦</span>
        </div>
    </div>
</div>

{{-- ── Hero Image ──────────────────────────────────────────────── --}}
<img src="" alt="" aria-hidden="true" style="display:block;width:100%;height:340px;object-fit:cover;">

{{-- ── Main Products Page ──────────────────────────────────────── --}}
<div class="ridly-products"
     x-data="productsPage({{ \Illuminate\Support\Js::from($products) }}, {{ \Illuminate\Support\Js::from($favoriteIds ?? []) }}, {{ Auth::check() ? 'true' : 'false' }}, '{{ csrf_token() }}')"
     x-cloak>

    <div class="page-inner">

        {{-- ── Page Header ──────────────────────────────────────── --}}
        <div class="page-header">
            @auth
                <h1 class="page-title">
                    WELCOME, <span class="gold">{{ Auth::user()->name }}</span>
                </h1>
                <p class="page-sub">WHAT ARE YOU LOOKING FOR TODAY?</p>
            @else
                <h1 class="page-title">
                    DIGITAL <span class="gold">PRODUCTS</span>
                </h1>
                <p class="page-sub">BROWSE OUR COLLECTION OF PREMIUM DIGITAL ITEMS AND LICENSES</p>
            @endauth
        </div>

        <div class="px-divider">
            <div class="px-divider-dot"></div>
            <div class="px-divider-line"></div>
            <div class="px-divider-dot"></div>
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

        {{-- ── Category Filter Tabs ────────────────────────────── --}}
        <div class="filter-tabs">
            <template x-for="cat in categories" :key="cat">
                <button @click="setFilter(cat)"
                        :class="activeFilter === cat ? 'px-tab-active' : 'px-tab-inactive'"
                        class="px-tab"
                        x-text="cat">
                </button>
            </template>
        </div>

        {{-- ── Category Accordion Sections ─────────────────────── --}}
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <template x-for="cat in uniqueCategories" :key="cat">
                <section class="cat-section" x-show="productsForCategory(cat).length > 0">

                    {{-- Category Toggle Header --}}
                    <button class="cat-header-btn" @click="toggleCategory(cat)" type="button">
                        <div class="cat-header-left">
                            <span class="cat-emoji" x-text="categoryEmoji(cat)"></span>
                            <span class="cat-name" x-text="cat"></span>
                        </div>
                        <div class="cat-header-right">
                            <span class="cat-count"
                                  x-text="productsForCategory(cat).length + ' ITEM' + (productsForCategory(cat).length !== 1 ? 'S' : '')">
                            </span>
                            <span class="cat-arrow" :class="expandedCategories[cat] ? 'open' : ''">▼</span>
                        </div>
                    </button>

                    {{-- Product Grid --}}
                    <div x-show="expandedCategories[cat]"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-[-8px]"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">

                        <div class="product-grid">
                            <template x-for="product in productsForCategory(cat)" :key="product.id">
                                <div class="product-card px-border-card"
                                     @click="openBuyModal(product)">

                                    {{-- Image --}}
                                    <div class="card-img-wrap">
                                        <img :src="product.image"
                                             :alt="product.name"
                                             class="card-img">
                                        <div class="card-badges">
                                            <span class="badge-cat" x-text="product.category"></span>
                                            <span class="badge-topup"
                                                  x-show="product.product_type === 'direct_topup'">
                                                ⚡ TOP-UP
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Body --}}
                                    <div class="card-body">
                                        <h3 class="card-title" x-text="product.name"></h3>
                                        <div class="card-footer">
                                            <div>
                                                <span class="price-label">PRICE</span>
                                                <span class="price-value"
                                                      x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)">
                                                </span>
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
                                                <button @click="openBuyModal(product)" class="buy-btn">
                                                    BUY
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>

                    </div>
                </section>
            </template>
        </div>

        {{-- ── Empty State ─────────────────────────────────────── --}}
        <div x-show="!hasResults" class="empty-state" x-transition>
            <div class="empty-icon-box">🔍</div>
            <p class="empty-text">NO PRODUCTS FOUND</p>
            <p class="empty-sub">TRY A DIFFERENT SEARCH TERM OR CATEGORY</p>
        </div>

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
             class="modal-box px-border"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

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
                <img :src="selectedProduct?.image" :alt="selectedProduct?.name">
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

            {{-- Actions --}}
            <div class="modal-actions">
                <button @click="addToCart()" class="modal-btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/>
                    </svg>
                    ADD TO CART
                </button>
                <button @click="buyNow()" class="modal-btn-primary">
                    BUY NOW
                </button>
            </div>

        </div>
    </div>

</div>{{-- /ridly-products --}}


{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════════ --}}
<script>



/* ── Alpine.js Products Controller ──────────────────────────── */
function productsPage(initialProducts, initialFavorites, isAuthenticated, csrfToken) {
    return {
        /* ─ State ─────────────────────────────────────────────── */
        products:         initialProducts,
        search:           '',
        activeFilter:     'All',
        expandedCategories: {},
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
            this.uniqueCategories.forEach(cat => {
                this.expandedCategories[cat] = true;
            });
        },

        /* ─ Computed ──────────────────────────────────────────── */
        get uniqueCategories() {
            return [...new Set(this.products.map(p => p.category || 'Other'))];
        },

        get categories() {
            return ['All', ...this.uniqueCategories];
        },

        get hasResults() {
            return this.uniqueCategories.some(cat => this.productsForCategory(cat).length > 0);
        },

        /* ─ Helpers ───────────────────────────────────────────── */
        categoryEmoji(cat) {
            const map = {
                'Gaming':             '🎮',
                'Entertainment':      '🎬',
                'Mobile Top-Up':      '📱',
                'Software & Utilities': '💻',
                'Music':              '🎵',
                'Education':          '📚',
                'Other':              '✦',
            };
            return map[cat] || '✦';
        },

        productsForCategory(cat) {
            const kw = this.search.trim().toLowerCase();
            return this.products.filter(p => {
                const inCat    = (p.category || 'Other') === cat;
                const inSearch = !kw
                    || p.name.toLowerCase().includes(kw)
                    || (p.category || '').toLowerCase().includes(kw);
                return inCat && inSearch;
            });
        },

        /* ─ Actions ───────────────────────────────────────────── */
        setFilter(cat) {
            this.activeFilter = cat;
            if (cat === 'All') {
                this.uniqueCategories.forEach(c => this.expandedCategories[c] = true);
            } else {
                this.uniqueCategories.forEach(c => {
                    this.expandedCategories[c] = (c === cat);
                });
            }
        },

        toggleCategory(cat) {
            this.expandedCategories[cat] = !this.expandedCategories[cat];
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

        /* ─ Add to Cart ───────────────────────────────────────── */
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
