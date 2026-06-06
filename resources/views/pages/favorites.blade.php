<x-app-layout>
    @auth
    <div class="px-page">
        <div class="px-page-inner space-y-8"
             x-data="favoritesPage({{ \Illuminate\Support\Js::from($favorites ?? []) }}, '{{ csrf_token() }}')">
            <div>
                <h1 class="px-heading">My <span class="gold">Favorites</span></h1>
                <p class="px-subheading">PRODUCTS YOU'VE SAVED FOR LATER</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
                <template x-for="product in favorites" :key="product.id">
                    <div class="px-card fav-card" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                        <div class="fav-img-box" style="aspect-ratio:1;background:var(--dark-card2);border:2px solid var(--dark-line);display:flex;align-items:center;justify-content:center;padding:24px;position:relative;">
                            <img :src="product.image" style="max-width:80px;max-height:80px;width:100%;height:100%;object-fit:contain;image-rendering:pixelated;" />
                            <span class="px-badge px-badge-gold fav-badge" style="position:absolute;top:8px;right:8px;" x-text="product.category"></span>
                        </div>
                        <div class="fav-info" style="display:flex;flex-direction:column;gap:6px;">
                            <h3 class="fav-name" style="font-family:var(--font-sans);font-size:14px;font-weight:800;color:var(--foreground);" x-text="product.name"></h3>
                            <p class="fav-price" style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--gold);" x-text="formatRp(product.price)"></p>
                        </div>
                        <div class="fav-actions" style="display:flex;gap:6px;margin-top:auto;">
                            <a :href="'/?buy=' + product.id" class="px-btn-gold" style="flex:1;text-align:center;padding:10px;font-size:6px;text-decoration:none;">BUY</a>
                            <button @click="removeFavorite(product.id)" :disabled="removing === product.id" class="px-btn-danger fav-remove" style="padding:10px;font-size:6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="favorites.length === 0" class="px-empty-state">
                <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg></div>
                <p class="empty-text">NO FAVORITES YET</p>
                <a href="{{ route('home') }}" class="empty-link">BROWSE PRODUCTS →</a>
            </div>
        </div>
    </div>
    @else
        <div class="px-page" x-data="{}">
            <div class="px-empty-state" style="min-height:70vh;">
                <div style="width:80px;height:80px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </div>
                <h1 class="px-heading" style="text-align:center;">Save Your Favorite <span class="gold">Products</span></h1>
                <p style="font-family:var(--font-sans);font-size:14px;color:var(--text-dim);text-align:center;max-width:480px;line-height:1.7;">Sign in to bookmark products and keep them handy for later. Your favorites stay synced across every visit.</p>
                <div style="display:flex;gap:12px;">
                    <button @click="$dispatch('open-auth-modal', { tab: 'login' })" class="px-btn-gold" style="padding:16px 28px;font-size:8px;">LOGIN</button>
                    <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" class="px-btn-ghost" style="padding:16px 28px;font-size:8px;">SIGN UP</button>
                </div>
            </div>
        </div>
    @endauth

    <script>
    function favoritesPage(initialFavorites, csrfToken) {
        return {
            favorites: initialFavorites,
            removing: null,
            csrfToken,

            async removeFavorite(productId) {
                if (this.removing) return;
                this.removing = productId;
                try {
                    const r = await fetch('/favorites/' + productId, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfToken
                        }
                    });
                    if (r.ok) {
                        this.favorites = this.favorites.filter(f => f.id !== productId);
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: 'Removed from favorites.', type: 'success' }
                        }));
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'Failed to remove.', type: 'error' }
                    }));
                }
                this.removing = null;
            },

            formatRp(amount) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
            }
        };
    }
    </script>
</x-app-layout>
