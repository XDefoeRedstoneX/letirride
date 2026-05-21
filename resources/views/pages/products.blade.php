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

{{-- ── Browse Hub ───────────────────────────────────────────────── --}}
<div class="ridly-products"
     x-data="browseHub({{ \Illuminate\Support\Js::from($products) }})"
     x-cloak>

    <div class="page-inner">

        {{-- ── Popular Products ──────────────────────────────────── --}}
        <section class="popular-section" x-show="popularProducts.length > 0">
            <div class="popular-head">
                <h2 class="popular-title">
                    <span class="pop">POPULAR</span>
                </h2>
                <span class="popular-badge">✨ HOT RIGHT NOW</span>
            </div>

            <div class="popular-grid">
                <template x-for="product in popularProducts" :key="product.id">
                    <a :href="'/all-products?buy=' + product.id" class="popular-card">
                        <div class="pc-img">
                            <img :src="product.image" alt="steam-wallet.png">
                            <span class="pc-tag" x-text="product.category"></span>
                        </div>
                        <div class="pc-body">
                            <span class="pc-name" x-text="product.name"></span>
                            <span class="pc-price"
                                  x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)"></span>
                        </div>
                    </a>
                </template>
            </div>
        </section>

        <div class="px-divider">
            <div class="px-divider-dot"></div>
            <div class="px-divider-line"></div>
            <div class="px-divider-dot"></div>
        </div>

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
                            <a :href="'/all-products?group=' + group.key" class="section-viewall">VIEW ALL →</a>
                        </div>
                    </div>
                    <div class="brand-grid">
                        <template x-for="tile in tilesFor(group)" :key="tile.key">
                            <a :href="tile.href" class="brand-tile">
                                <div class="brand-img-wrap">
                                    <img :src="tile.image" alt="steam-wallet.png" class="brand-img">
                                </div>
                                <span class="brand-name" x-text="tile.name"></span>
                                <span class="brand-count" x-show="tile.meta" x-text="tile.meta"></span>
                            </a>
                        </template>
                    </div>
                </section>
            </template>
        </div>

        {{-- ── Full Rows: Subscriptions, then Game Keys ──────────── --}}
        <template x-for="group in fullGroups" :key="group.key">
            <section class="brand-section">
                <div class="section-bar">
                    <span class="section-title">
                        <span class="cat-emoji" x-text="group.emoji"></span>
                        <span x-text="group.label"></span>
                    </span>
                    <div class="section-right">
                        <a :href="'/all-products?group=' + group.key" class="section-viewall">VIEW ALL →</a>
                    </div>
                </div>
                <div class="brand-grid">
                    <template x-for="tile in tilesFor(group)" :key="tile.key">
                        <a :href="tile.href" class="brand-tile">
                            <div class="brand-img-wrap">
                                <img :src="tile.image" alt="steam-wallet.png" class="brand-img">
                            </div>
                            <span class="brand-name" x-text="tile.name"></span>
                            <span class="brand-count" x-show="tile.meta" x-text="tile.meta"></span>
                        </a>
                    </template>
                </div>
            </section>
        </template>

    </div>{{-- /page-inner --}}
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

/* PLACEHOLDER popular picks (Steam, Netflix, Spotify, ML Diamonds, Discord
   Nitro, Valorant). Replace with a backend `is_popular` flag when ready. */
const RIDLY_POPULAR_IDS = [1, 3, 4, 7, 9, 6];

/* ── Alpine.js Browse Hub ───────────────────────────────────── */
function browseHub(initialProducts) {
    return {
        products: initialProducts,
        groups:   RIDLY_HUB_GROUPS,

        // Top row shares one split grid; the rest are full-width rows.
        get splitGroups() { return [this.groups[0], this.groups[1]]; },
        get fullGroups()  { return [this.groups[2], this.groups[3]]; },

        // Curated popular items, in the configured order, that still exist.
        get popularProducts() {
            return RIDLY_POPULAR_IDS
                .map(id => this.products.find(p => p.id == id))
                .filter(Boolean);
        },

        _productsIn(group) {
            return this.products.filter(p => group.cats.includes(p.category || 'Other'));
        },

        // Tiles for a group — brands (→ ?brand=) or individual products (→ ?buy=).
        tilesFor(group) {
            const items = this._productsIn(group);

            if (group.mode === 'product') {
                return items.map(p => ({
                    key:   'p' + p.id,
                    name:  p.name,
                    image: p.image,
                    href:  '/all-products?buy=' + p.id,
                    meta:  null,
                }));
            }

            const map = {};
            for (const p of items) {
                const name = p.subcategory || 'Other';
                if (!map[name]) {
                    map[name] = { name, image: p.image, count: 0 };
                }
                map[name].count++;
            }
            return Object.values(map).map(b => ({
                key:   'b:' + b.name,
                name:  b.name,
                image: b.image,
                href:  '/all-products?brand=' + encodeURIComponent(b.name),
                meta:  b.count + ' ITEM' + (b.count !== 1 ? 'S' : ''),
            }));
        },
    };
}
</script>
</x-app-layout>
