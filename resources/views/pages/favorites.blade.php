<x-app-layout>
    @auth
        <div class="space-y-8" x-data="{ 
            favorites: @json($favorites, JSON_HEX_QUOT),
            get favoriteProducts() {
                return this.favorites;
            },
            async toggleFavorite(id) {
                const response = await fetch('{{ url("/favorites") }}/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                        'Accept': 'application/json',
                    },
                });
                if (response.ok) {
                    this.favorites = this.favorites.filter(f => f.id !== id);
                }
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
                    <div class="group glass-card rounded-[2.5rem] overflow-hidden hover:shadow-2xl hover:shadow-primary/10 transition-all duration-500 hover:-translate-y-2 border-border/50">
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
            <div x-show="favoriteProducts.length === 0" class="py-20 text-center space-y-4">
                <div class="w-20 h-20 bg-card border border-border rounded-[2rem] flex items-center justify-center mx-auto text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </div>
                <p class="text-muted-foreground font-black uppercase tracking-widest text-xs">No favorite products yet.</p>
                <a href="{{ route('home') }}" class="inline-block px-8 py-4 bg-primary text-primary-foreground rounded-2xl font-black text-xs uppercase tracking-widest hover:scale-105 transition-all">Browse Products</a>
            </div>
        </div>
    @else
        <div class="min-h-[70vh] flex flex-col items-center justify-center text-center space-y-12 p-6">
            <div class="space-y-6 max-w-xl">
                <div class="w-24 h-24 bg-primary/10 rounded-[2.5rem] flex items-center justify-center text-primary mx-auto mb-8 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </div>
                <h1 class="text-5xl font-black tracking-tighter leading-tight ">Keep Your <span class="text-primary">Favorites</span> Ready</h1>
                <p class="text-muted-foreground text-lg font-medium leading-relaxed">Save the products you love to your favorites list and access them quickly whenever you're ready to make a purchase.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 w-full max-w-md">
                <button @click="$dispatch('open-auth-modal', { tab: 'login' })" 
                        class="flex-1 px-8 py-5 bg-primary text-primary-foreground font-black rounded-[1.5rem] shadow-2xl shadow-primary/30 hover:scale-105 active:scale-95 transition-all text-sm tracking-widest uppercase">
                    Login
                </button>
                <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" 
                        class="flex-1 px-8 py-5 glass-card font-black rounded-[1.5rem] hover:bg-white/20 hover:scale-105 active:scale-95 transition-all text-sm tracking-widest uppercase">
                    Get Started
                </button>
            </div>
        </div>
    @endauth
</x-app-layout>
