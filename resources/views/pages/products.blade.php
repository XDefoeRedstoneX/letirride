<x-app-layout>
    <div class="space-y-8" x-data="{ 
        search: '', 
        category: 'All Items',
        products: [
            { name: 'Steam Wallet Rp 100.000', price: 110000, category: 'Games', image: '/products/steam-wallet.svg' },
            { name: 'Spotify Premium 1 Month', price: 54900, category: 'Music', image: '/products/spotify.svg' },
            { name: 'Netflix Standard', price: 186000, category: 'Media', image: '/products/netflix.svg' },
            { name: 'YouTube Premium', price: 59000, category: 'Media', image: '/products/youtube.svg' },
            { name: 'Canva Pro 1 Year', price: 120000, category: 'Productivity', image: '/products/canva.svg' },
            { name: 'Xbox Game Pass', price: 149000, category: 'Games', image: '/products/xbox.svg' },
            { name: 'Google Play Gift Card', price: 50000, category: 'Games', image: '/products/google-play.svg' },
            { name: 'Notion Personal Pro', price: 75000, category: 'Productivity', image: '/products/notion.svg' },
        ],
        get filteredProducts() {
            return this.products.filter(p => {
                const matchesSearch = p.name.toLowerCase().includes(this.search.toLowerCase());
                const matchesCategory = this.category === 'All Items' || p.category === this.category;
                return matchesSearch && matchesCategory;
            });
        }
    }">
        <!-- Header -->
        <div class="flex flex-col space-y-4">
            @auth
                <div>
                    <p class="text-lg font-bold">Welcome, {{ Auth::user()->username }}</p>
                </div>
            @else
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Digital Products</h1>
                    <p class="text-muted-foreground">Browse our collection of premium digital items and licenses.</p>
                </div>
            @endauth
            
            <div class="flex items-center gap-2">
                <div class="relative w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" x-model="search" placeholder="Search products..." class="w-full pl-9 pr-4 py-3 bg-card border border-border rounded-xl focus:ring-2 focus:ring-primary/50 outline-none transition-all text-sm shadow-sm">
                </div>
                <button class="p-3 bg-card border border-border rounded-xl hover:bg-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M7 12h10"/><path d="M10 18h4"/></svg>
                </button>
            </div>
        </div>

        <!-- Categories -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <template x-for="cat in ['All Items', 'Games', 'Music', 'Media', 'Productivity']">
                <button @click="category = cat" 
                        :class="category === cat ? 'bg-primary text-primary-foreground' : 'bg-card border border-border text-foreground hover:bg-accent'"
                        class="px-5 py-2.5 rounded-full text-sm font-bold whitespace-nowrap transition-all"
                        x-text="cat"></button>
            </template>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="product in filteredProducts" :key="product.name">
                <div class="group bg-card border border-border rounded-2xl overflow-hidden hover:shadow-xl transition-all hover:-translate-y-1 animate-in fade-in zoom-in duration-300">
                    <div class="aspect-video relative overflow-hidden bg-muted">
                        <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
                        <div class="absolute top-3 left-3 px-2 py-1 rounded-md bg-background/80 backdrop-blur-sm text-[10px] font-bold border border-border/50 uppercase tracking-wider" x-text="product.category">
                        </div>
                    </div>
                    <div class="p-4 space-y-4">
                        <h3 class="font-bold text-sm line-clamp-1" x-text="product.name"></h3>
                        <div class="flex items-center justify-between">
                            <span class="text-primary font-bold text-lg" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)"></span>
                            <div class="flex items-center gap-1">
                                <button class="p-2 hover:bg-accent rounded-lg text-muted-foreground transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </button>
                                <button class="px-4 py-2 bg-primary text-primary-foreground rounded-lg font-bold text-xs hover:opacity-90 transition-all flex items-center gap-1.5">
                                    Buy
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredProducts.length === 0" class="py-20 text-center space-y-4" x-transition>
            <div class="w-20 h-20 bg-muted rounded-full flex items-center justify-center mx-auto text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <p class="text-muted-foreground font-medium">No products found matching your search or category.</p>
        </div>
    </div>
</x-app-layout>
