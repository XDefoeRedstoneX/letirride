<x-app-layout>
    <div class="space-y-8" x-data="{ 
        favorites: [1, 3], // Mocked initial favorites
        products: [
            { id: 1, name: 'Steam Wallet Rp 100.000', price: 110000, category: 'Games', image: '/products/steam-wallet.svg' },
            { id: 2, name: 'Spotify Premium 1 Month', price: 54900, category: 'Music', image: '/products/spotify.svg' },
            { id: 3, name: 'Netflix Standard', price: 186000, category: 'Media', image: '/products/netflix.svg' },
            { id: 4, name: 'YouTube Premium', price: 59000, category: 'Media', image: '/products/youtube.svg' },
            { id: 5, name: 'Canva Pro 1 Year', price: 120000, category: 'Productivity', image: '/products/canva.svg' },
            { id: 6, name: 'Xbox Game Pass', price: 149000, category: 'Games', image: '/products/xbox.svg' },
            { id: 7, name: 'Google Play Gift Card', price: 50000, category: 'Games', image: '/products/google-play.svg' },
            { id: 8, name: 'Notion Personal Pro', price: 75000, category: 'Productivity', image: '/products/notion.svg' },
        ],
        get favoriteProducts() {
            return this.products.filter(p => this.favorites.includes(p.id));
        },
        toggleFavorite(id) {
            this.favorites = this.favorites.filter(favId => favId !== id);
        }
    }">
        <!-- Header -->
        <div class="flex flex-col space-y-2">
            <h1 class="text-3xl font-black tracking-tighter uppercase leading-none">Your <span class="text-primary">Favorites</span></h1>
            <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest mt-2">All the items you've liked in one place.</p>
        </div>

        <!-- Favorites Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="product in favoriteProducts" :key="product.id">
                <div class="group glass-card rounded-[2.5rem] overflow-hidden hover:shadow-2xl hover:shadow-primary/10 transition-all duration-500 hover:-translate-y-2 animate-in fade-in zoom-in duration-300 border-border/50">
                    <div class="aspect-square relative overflow-hidden bg-white/5">
                        <img :src="product.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" :alt="product.name">
                        <div class="absolute top-5 left-5 px-3 py-1 rounded-full bg-black/40 backdrop-blur-md text-[8px] font-black border border-white/10 uppercase tracking-widest text-white" x-text="product.category">
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <h3 class="font-black text-sm leading-tight tracking-tight uppercase group-hover:text-primary transition-colors h-10 overflow-hidden" x-text="product.name"></h3>
                        <div class="flex items-center justify-between pt-2">
                            <div class="flex flex-col">
                                <span class="text-[8px] font-black text-muted-foreground uppercase tracking-widest">Price</span>
                                <span class="text-lg font-black text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="toggleFavorite(product.id)" 
                                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-500/10 text-red-500 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </button>
                                <button class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl font-black text-[10px] hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/20 tracking-widest uppercase">
                                    Buy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="favoriteProducts.length === 0" class="py-20 text-center space-y-4" x-transition>
            <div class="w-20 h-20 bg-card border border-border rounded-[2rem] flex items-center justify-center mx-auto text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
            </div>
            <p class="text-muted-foreground font-black uppercase tracking-widest text-xs">No favorite products yet.</p>
            <a href="{{ route('home') }}" class="inline-block px-8 py-4 bg-primary text-primary-foreground rounded-2xl font-black text-xs uppercase tracking-widest hover:scale-105 transition-all">Browse Products</a>
        </div>
    </div>
</x-app-layout>