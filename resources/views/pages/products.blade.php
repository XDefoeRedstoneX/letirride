<x-app-layout>
<style>
    /* Ensure html, body and content wrapper are transparent so the parallax background shows through */
    html, body, .ridly-products {
        background-color: transparent !important;
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
    <script>window._ridlyNews = {!! json_encode(array_values($newsImages), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};</script>
    <div class="hero-frame-grid relative group"
         x-data="{
             imgs: window._ridlyNews || [],
             i: 0,
             interval: null,
             touchStartX: 0,
             init() {
                 this.startSlide();
             },
             handleTouchStart(e) {
                 this.stopSlide();
                 this.touchStartX = e.changedTouches[0].screenX;
             },
             handleTouchEnd(e) {
                 const dx = e.changedTouches[0].screenX - this.touchStartX;
                 if (Math.abs(dx) > 40) {
                     dx < 0 ? this.nextSlide() : this.prevSlide();
                 }
                 this.startSlide();
             },
             startSlide() {
                 if (this.imgs.length > 1 && !this.interval) {
                     this.interval = setInterval(() => this.nextSlide(), 4000);
                 }
             },
             stopSlide() {
                 if (this.interval) {
                     clearInterval(this.interval);
                     this.interval = null;
                 }
             },
             nextSlide() {
                 if (this.imgs.length > 0) {
                     this.i = (this.i + 1) % this.imgs.length;
                 }
             },
             prevSlide() {
                 if (this.imgs.length > 0) {
                     this.i = (this.i - 1 + this.imgs.length) % this.imgs.length;
                 }
             }
         }"
         @mouseenter="stopSlide()"
         @mouseleave="startSlide()"
         @touchstart.passive="handleTouchStart($event)"
         @touchend.passive="handleTouchEnd($event)">
         
        {{-- Frame border image --}}
        <img src="{{ asset('components/frame/news.png') }}" class="hero-frame-img pixel-render" alt="Hero Frame">

        <div class="hero-frame-content">
            <template x-for="(src, idx) in imgs" :key="idx">
                <img :src="src"
                     x-show="i === idx"
                     x-transition.opacity.duration.500ms
                     class="hero-news-img"
                     alt="">
            </template>
        </div>

        {{-- Left Arrow --}}
        <button type="button" @click.prevent="prevSlide()"
                class="absolute left-[5%] md:left-[3.5%] top-1/2 -translate-y-1/2 z-40 w-8 h-8 md:w-12 md:h-12 flex items-center justify-center bg-[#08152a] border-[3px] border-[#122044] text-[#5a7aaa] opacity-50 group-hover:opacity-100 transition-all duration-200 hover:border-[#f59e0b] hover:text-[#f59e0b] hover:scale-110"
                style="image-rendering: pixelated; box-shadow: inset -2px -2px 0 rgba(0,0,0,0.5), inset 2px 2px 0 rgba(255,255,255,0.1), 4px 4px 0 rgba(0,0,0,0.5);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="square" stroke-linejoin="miter" class="w-4 h-4 md:w-6 md:h-6"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        
        {{-- Right Arrow --}}
        <button type="button" @click.prevent="nextSlide()"
                class="absolute right-[5%] md:right-[3.5%] top-1/2 -translate-y-1/2 z-40 w-8 h-8 md:w-12 md:h-12 flex items-center justify-center bg-[#08152a] border-[3px] border-[#122044] text-[#5a7aaa] opacity-50 group-hover:opacity-100 transition-all duration-200 hover:border-[#f59e0b] hover:text-[#f59e0b] hover:scale-110"
                style="image-rendering: pixelated; box-shadow: inset -2px -2px 0 rgba(0,0,0,0.5), inset 2px 2px 0 rgba(255,255,255,0.1), 4px 4px 0 rgba(0,0,0,0.5);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="square" stroke-linejoin="miter" class="w-4 h-4 md:w-6 md:h-6"><path d="m9 18 6-6-6-6"/></svg>
        </button>

        {{-- Dot indicators (mobile) --}}
        <div class="hero-dots" x-show="imgs.length > 1">
            <template x-for="(src, idx) in imgs" :key="'dot' + idx">
                <button type="button" @click="i = idx"
                        class="hero-dot" :class="i === idx ? 'is-active' : ''"
                        :aria-label="'Go to slide ' + (idx + 1)"></button>
            </template>
        </div>
    </div>
</section>

{{-- ── Store (hub + full catalog, one Alpine component) ─────────── --}}
<div class="ridly-products"
     x-data="ridlyStore({{ \Illuminate\Support\Js::from($products) }}, {{ \Illuminate\Support\Js::from($favoriteIds ?? []) }}, {{ Auth::check() ? 'true' : 'false' }}, '{{ csrf_token() }}')"
     @brand-centered.window="autoSelectBrand($event.detail)"
     x-cloak>

    <div class="page-inner">

        <section class="popular-section popular-placeholder">
        </section>

        <div class="catalog-merged" x-ref="catalog">

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
                           class="w-full bg-card border-2 border-border rounded-xl px-10 py-2.5 sm:px-12 sm:py-3.5 text-xs sm:text-sm font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all shadow-sm">
                </div>

                <div class="flex items-center justify-between gap-2 sm:gap-4">
                    <div class="flex gap-2 sm:gap-3 overflow-x-auto scrollbar-hide py-1 w-full min-w-0">
                        <button @click="setFilter('All')"
                                :class="activeFilter === 'All' ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-muted-foreground border-border hover:border-primary/50 hover:text-primary'"
                                class="whitespace-nowrap px-3 sm:px-6 py-1.5 sm:py-2.5 rounded-xl font-black text-[8px] sm:text-[10px] tracking-widest border-2 shadow-sm transition-all flex-shrink-0">
                            ALL
                        </button>
                        <template x-for="cat in categories.filter(c => c !== 'All')" :key="cat">
                            <button @click="setFilter(cat)"
                                    :class="activeFilter === cat ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-muted-foreground border-border hover:border-primary/50 hover:text-primary'"
                                    class="whitespace-nowrap px-3 sm:px-6 py-1.5 sm:py-2.5 rounded-xl font-black text-[8px] sm:text-[10px] tracking-widest border-2 shadow-sm transition-all flex-shrink-0"
                                    x-text="cat">
                            </button>
                        </template>
                    </div>

                    <div class="relative flex-shrink-0" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="bg-card text-muted-foreground border-border hover:border-primary/50 hover:text-primary whitespace-nowrap px-4 py-2.5 rounded-xl font-black text-[10px] tracking-widest border-2 shadow-sm transition-all flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            SORT
                        </button>
                        <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-card border-2 border-border rounded-xl shadow-xl z-50 overflow-hidden">
                            <button @click="sortBy = 'default'; open = false" class="w-full text-left px-4 py-3 text-[10px] font-bold tracking-widest text-foreground hover:bg-muted transition-colors border-b border-border" :class="sortBy === 'default' ? 'text-primary' : ''">DEFAULT</button>
                            <button @click="sortBy = 'price_asc'; open = false" class="w-full text-left px-4 py-3 text-[10px] font-bold tracking-widest text-foreground hover:bg-muted transition-colors border-b border-border" :class="sortBy === 'price_asc' ? 'text-primary' : ''">PRICE: LOW TO HIGH</button>
                            <button @click="sortBy = 'price_desc'; open = false" class="w-full text-left px-4 py-3 text-[10px] font-bold tracking-widest text-foreground hover:bg-muted transition-colors border-b border-border" :class="sortBy === 'price_desc' ? 'text-primary' : ''">PRICE: HIGH TO LOW</button>
                            <button @click="sortBy = 'name_asc'; open = false" class="w-full text-left px-4 py-3 text-[10px] font-bold tracking-widest text-foreground hover:bg-muted transition-colors border-b border-border" :class="sortBy === 'name_asc' ? 'text-primary' : ''">NAME: A - Z</button>
                            <button @click="sortBy = 'name_desc'; open = false" class="w-full text-left px-4 py-3 text-[10px] font-bold tracking-widest text-foreground hover:bg-muted transition-colors" :class="sortBy === 'name_desc' ? 'text-primary' : ''">NAME: Z - A</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Full Width Main Catalog ── --}}
            <div class="w-full">

                {{-- Browse By Brand (3D Coverflow) --}}
                <div x-show="brands.length > 0" class="mb-2">
                    <div x-data="brandSlider()" x-init="initSlider()" 
                         class="relative w-full h-[130px] sm:h-[320px] py-1 sm:py-2 overflow-hidden" 
                         style="perspective: 1200px; -webkit-mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent); mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);"
                         @wheel.prevent="handleWheel"
                         @mousedown="startDrag" @mouseleave="endDrag" @mouseup="endDrag" @mousemove="doDrag"
                         @touchstart="startDrag" @touchend="endDrag" @touchmove="doDrag">
                        
                        <template x-for="(brand, index) in brands" :key="brand.name">
                            <div class="w-28 sm:w-56 cursor-pointer group relative"
                                 @click="if(!isDragging) { scrollTo(index); }"
                                 :style="getCardStyle(index)">

                                     <div class="flex flex-col bg-card shadow-2xl rounded-xl h-full transition-all duration-300 border-2 pointer-events-none select-none overflow-hidden"
                                          :class="selectedBrand === brand.name ? 'border-primary ring-4 ring-primary/20' : 'border-border'">

                                         {{-- Full Frame Thumbnail Area --}}
                                         <div class="w-full aspect-[4/3] flex items-center justify-center p-1.5 sm:p-4 bg-gradient-to-br from-white to-slate-100 dark:from-[#0a1020] dark:to-[#040812]">
                                             <template x-if="brand.image">
                                                <img :src="brand.image" draggable="false" class="w-3/4 h-3/4 object-contain drop-shadow-xl transition-transform duration-500 group-hover:scale-110 pixel-render">
                                             </template>
                                             <template x-if="!brand.image">
                                                <div class="text-2xl sm:text-4xl font-black uppercase tracking-widest text-primary drop-shadow-[0_0_10px_rgba(245,158,11,0.5)]">ALL</div>
                                             </template>
                                         </div>

                                         {{-- Text Area Below --}}
                                         <div class="flex flex-col items-center p-1.5 sm:p-3 bg-card border-t border-border/50">
                                             <span class="text-[8px] sm:text-xs font-black uppercase tracking-widest text-foreground text-center" x-text="brand.name"></span>
                                             <span class="text-[6px] sm:text-[9px] font-bold text-muted-foreground mt-0.5 sm:mt-1 tracking-widest" x-text="brand.count + ' ITEM' + (brand.count !== 1 ? 'S' : '')"></span>
                                         </div>

                                     </div>
                                </div>
                            </template>
                    </div>
                </div>

                <div id="product-list" style="scroll-margin-top: 140px;"></div>

                {{-- Product Groups --}}
                <template x-for="group in displayGroups" :key="group.key">
                    <section class="products-section">

                        <div class="mb-4 mt-8">
                            <h2 class="text-xl sm:text-2xl font-black uppercase tracking-widest text-[var(--gold)] dark:text-white transition-colors duration-200" style="font-family: var(--px, 'Press Start 2P', monospace);">
                                <span x-text="group.label"></span>
                            </h2>
                        </div>

                        <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-4 md:gap-6 pb-8">
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
<span class="badge-sale"
                                                      x-show="product.discount_label"
                                                      x-text="product.discount_pct ? product.discount_label + ' ' + product.discount_pct + '%' : product.discount_label">
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
                                                    <span class="price-label" x-text="product.discount_label ? product.discount_label : 'PRICE'"></span>
                                                    <template x-if="product.discount_label && product.original_price !== product.price">
                                                        <span class="price-original"
                                                              x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.original_price)">
                                                        </span>
                                                    </template>
                                                    <span class="price-value"
                                                          :class="[!product.in_stock ? '!text-gray-500' : '', product.discount_label ? '!text-rose-500' : '']"
                                                          x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)">
                                                    </span>
                                                    <template x-if="product.product_type === 'voucher' && product.in_stock">
                                                        <span class="stock-line card-stock-line" :class="product.stock <= 3 ? 'low' : 'ok'" x-text="product.stock + ' IN STOCK'"></span>
                                                    </template>
                                                </div>
                                                <div class="card-actions" @click.stop>
                                                    <button @click="toggleFavorite(product.id)"
                                                            :class="favorites.includes(product.id) ? 'active' : ''"
                                                            class="fav-btn card-fav-btn"
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
             class="px-frame mx-4 sm:mx-auto"
             style="max-width: 480px; width: calc(100% - 32px); sm:width: 100%;"
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

                {{-- Description --}}
                <template x-if="selectedProduct?.description">
                    <div>
                        <p class="field-label" style="margin-bottom: 6px;">DESCRIPTION</p>
                        <p style="font-family: var(--font-sans); font-size: 13px; line-height: 1.7; color: var(--text-dim, var(--muted-foreground)); white-space: pre-line; max-height: 160px; overflow-y: auto;"
                           x-text="selectedProduct.description"></p>
                    </div>
                </template>

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

            {{-- Discount Banner (shown when product has an active discount) --}}
            <template x-if="selectedProduct?.discount_label">
                <div class="modal-discount-banner">
                    <div class="modal-discount-left">
                        <span class="modal-discount-tag" x-text="selectedProduct.discount_label"></span>
                        <span class="modal-discount-desc"
                              x-text="selectedProduct.discount_pct
                                  ? selectedProduct.discount_pct + '% off'
                                  : 'Rp ' + new Intl.NumberFormat('id-ID').format(selectedProduct.discount_fixed) + ' off'">
                        </span>
                    </div>
                    <div class="modal-discount-savings">
                        <span class="modal-discount-was"
                              x-text="'Was Rp ' + new Intl.NumberFormat('id-ID').format(selectedProduct.original_price)">
                        </span>
                        <span class="modal-discount-saves"
                              x-text="'Save Rp ' + new Intl.NumberFormat('id-ID').format(selectedProduct.original_price - selectedProduct.price)">
                        </span>
                    </div>
                </div>
            </template>

            {{-- Price Row --}}
            <div class="modal-price-row">
                <span class="modal-price-label">TOTAL PRICE</span>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:2px;">
                    <template x-if="selectedProduct?.discount_label">
                        <span class="modal-price-original"
                              x-text="'Rp ' + (selectedProduct ? new Intl.NumberFormat('id-ID').format(selectedProduct.original_price) : 0)">
                        </span>
                    </template>
                    <span class="modal-price-val"
                          :class="selectedProduct?.discount_label ? '!text-rose-500' : ''"
                          x-text="'Rp ' + (selectedProduct ? new Intl.NumberFormat('id-ID').format(selectedProduct.price) : 0)">
                    </span>
                </div>
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

const RIDLY_HUB_GROUPS = [
    { key: 'topups',   label: 'GAME TOP-UPS',          emoji: '💎', cats: ['In-Game Currency'],            mode: 'brand'   },
    { key: 'vouchers', label: 'VOUCHERS & GIFT CARDS', emoji: '👛', cats: ['Wallet Top-Ups', 'Gift Cards'], mode: 'brand'  },
    { key: 'subs',     label: 'SUBSCRIPTIONS',         emoji: '📺', cats: ['Subscriptions'],               mode: 'brand'   },
    { key: 'games',    label: 'GAME KEYS',             emoji: '🎮', cats: ['Games'],                       mode: 'product' },
];

const RIDLY_GAMES_CATEGORY = 'Games';

const RIDLY_GROUP_CATS = {
    topups:   ['In-Game Currency'],
    vouchers: ['Wallet Top-Ups', 'Gift Cards'],
    subs:     ['Subscriptions'],
    games:    ['Games'],
};

function ridlyStore(initialProducts, initialFavorites, isAuthenticated, csrfToken) {
    return {
        products:         initialProducts,
        groups:           RIDLY_HUB_GROUPS,
        search:           '',
        sortBy:           'default',
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

            if ((group || brand || search) && !buyId) {
                this.$nextTick(() => this.scrollToProducts());
            }

            if (search || group || brand || buyId) {
                window.history.replaceState({}, '', window.location.pathname);
            }
        },

        get splitGroups() { return [this.groups[0], this.groups[1]]; },
        get fullGroups()  { return [this.groups[2], this.groups[3]]; },

        _productsIn(group) {
            return this.products.filter(p => group.cats.includes(p.category || 'Other'));
        },

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

        onTileClick(tile) {
            if (tile.product) {
                if (tile.product.in_stock) this.openBuyModal(tile.product);
                return;
            }
            this.goToBrand(tile.name);
        },

        viewGroup(key) {
            this.activeFilter  = 'All';
            this.selectedBrand = null;
            this.groupCats     = RIDLY_GROUP_CATS[key] || null;
            this.$nextTick(() => this.scrollToProducts());
        },

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

        get uniqueCategories() {
            return [...new Set(this.products.map(p => p.category || 'Other'))];
        },

        get categories() {
            return ['All', ...this.uniqueCategories];
        },

        get visibleCategories() {
            return this.uniqueCategories.filter(c => this._inScope(c));
        },

        get brands() {
            const map = {};
            let totalCount = 0;
            for (const p of this.products) {
                if ((p.category || 'Other') === RIDLY_GAMES_CATEGORY) continue;
                if (!this._inScope(p.category || 'Other')) continue;
                if (!this._matchesSearch(p)) continue;
                const name = p.subcategory || 'Other';
                if (!map[name]) {
                    map[name] = { name, category: p.category || 'Other', image: p.image, count: 0 };
                }
                map[name].count++;
                totalCount++;
            }
            const sortedBrands = Object.values(map).sort((a, b) => a.name.localeCompare(b.name));
            if (sortedBrands.length > 0) {
                sortedBrands.unshift({
                    name: 'ALL',
                    category: 'All',
                    image: null,
                    count: totalCount
                });
            }
            return sortedBrands;
        },

        get displayGroups() {
            let groups = [];
            if (this.selectedBrand) {
                groups = [{
                    key: 'brand::' + this.selectedBrand,
                    label: this.selectedBrand,
                    emoji: '',
                    isBrand: true,
                    products: this.productsForBrand(this.selectedBrand),
                }];
            } else {
                groups = this.visibleCategories
                    .map(cat => ({
                        key: 'cat::' + cat,
                        label: cat,
                        emoji: this.categoryEmoji(cat),
                        isBrand: false,
                        products: this.productsForCategory(cat),
                    }))
                    .filter(g => g.products.length > 0);
            }

            groups.forEach(group => {
                group.products.sort((a, b) => {
                    if (this.sortBy === 'price_asc') return a.price - b.price;
                    if (this.sortBy === 'price_desc') return b.price - a.price;
                    if (this.sortBy === 'name_asc') return a.name.localeCompare(b.name);
                    if (this.sortBy === 'name_desc') return b.name.localeCompare(a.name);
                    return 0; 
                });
            });

            return groups;
        },

        get hasResults() {
            return this.brands.length > 0 || this.displayGroups.length > 0;
        },

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

        autoSelectBrand(name) {
            if (name === 'ALL') {
                this.selectedBrand = null;
            } else {
                this.selectedBrand = name;
            }
        },

        setFilter(cat) {
            this.activeFilter = cat;
            this.selectedBrand = null;
            this.groupCats = null;
        },

        selectBrand(name) {
            this.selectedBrand = (this.selectedBrand === name) ? null : name;
            if (this.selectedBrand) this.$nextTick(() => this.scrollToProducts());
        },

        openBuyModal(product) {
            this.selectedProduct = product;
            this.topupPlayerId   = '';
            this.topupZoneId     = '';
            this.topupServerId   = '';
            this.showCartModal   = true;
        },

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
        currentOffset: 0,
        isDown: false,
        startX: 0,
        startOffset: 0,
        brandsCount: 0,
        isDragging: false,
        accumulatedDelta: 0,
        lastNotifiedIdx: -1,
        initSlider() {
            this.$watch('brands', (val) => {
                this.brandsCount = val.length;
                this.currentOffset = 0;
                this.lastNotifiedIdx = -1;
                this.$nextTick(() => this.notifySelected());
            });
            this.brandsCount = this.brands.length;
            this.$nextTick(() => this.notifySelected());
        },
        notifySelected() {
            if (this.brandsCount === 0) return;
            let currentMod = Math.round(this.currentOffset) % this.brandsCount;
            if (currentMod < 0) currentMod += this.brandsCount;
            
            if (this.lastNotifiedIdx === currentMod) return;
            this.lastNotifiedIdx = currentMod;
            
            const centeredBrand = this.brands[currentMod];
            if (centeredBrand) {
                this.$dispatch('brand-centered', centeredBrand.name);
            }
        },
        handleWheel(e) {
            if (this.brandsCount === 0) return;
            
            this.accumulatedDelta += e.deltaY;
            
            if (Math.abs(this.accumulatedDelta) > 100) {
                if (this.accumulatedDelta > 0) {
                    this.currentOffset += 1;
                } else {
                    this.currentOffset -= 1;
                }
                this.accumulatedDelta = 0;
                this.snapToNearest();
            }
        },
        snapToNearest() {
            this.currentOffset = Math.round(this.currentOffset);
            this.notifySelected();
        },
        isActive(index) {
            if (this.brandsCount === 0) return false;
            let currentMod = Math.round(this.currentOffset) % this.brandsCount;
            if (currentMod < 0) currentMod += this.brandsCount;
            return index === currentMod;
        },
        startDrag(e) {
            this.isDown = true;
            this.isDragging = false;
            const pageX = e.pageX || (e.touches && e.touches[0].pageX);
            this.startX = pageX;
            this.startOffset = this.currentOffset;
        },
        endDrag() {
            if (!this.isDown) return;
            this.isDown = false;
            this.snapToNearest();
            setTimeout(() => { this.isDragging = false; }, 50);
        },
        doDrag(e) {
            if (!this.isDown) return;
            e.preventDefault();
            this.isDragging = true;
            const pageX = e.pageX || (e.touches && e.touches[0].pageX);
            const deltaX = pageX - this.startX;
            this.currentOffset = this.startOffset - (deltaX / 150); 
        },
        scrollTo(index) {
            let currentMod = this.currentOffset % this.brandsCount;
            if (currentMod < 0) currentMod += this.brandsCount;
            
            let diff = index - currentMod;
            if (diff > this.brandsCount / 2) diff -= this.brandsCount;
            if (diff < -this.brandsCount / 2) diff += this.brandsCount;
            
            this.currentOffset += diff;
            this.snapToNearest();
        },
        getCardStyle(index) {
            const count = this.brandsCount;
            if (count === 0) return 'display: none;';
            
            let diff = index - this.currentOffset;
            let normDiff = diff % count;
            if (normDiff > count / 2) normDiff -= count;
            if (normDiff < -count / 2) normDiff += count;

            const absDiff = Math.abs(normDiff);
            const zIndex = 50 - Math.round(absDiff * 10);

            const scale = Math.max(0.6, 1 - (absDiff * 0.15));
            const rotateY = 0; 
            
            const spacing = window.innerWidth < 640 ? 90 : 150;
            const translateZ = -absDiff * 40; 
            const transform = `translate(calc(-50% + ${normDiff * spacing}px), -50%) translateZ(${translateZ}px) rotateY(${rotateY}deg) scale(${scale})`;

            const opacity = absDiff > 4 ? 0 : (1 - (absDiff * 0.15));
            const pointerEvents = absDiff < 0.5 ? 'auto' : 'none'; 

            return `
                position: absolute;
                left: 50%;
                top: 50%;
                z-index: ${zIndex};
                opacity: ${opacity};
                pointer-events: ${pointerEvents};
                transform: ${transform};
                transition: ${this.isDown ? 'none' : 'transform 0.4s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.4s'};
            `;
        }
    };
}
</script>
</x-app-layout>