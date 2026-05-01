<x-app-layout>
    <div class="space-y-8" x-data="{ products: {{ \Illuminate\Support\Js::from($products) }} }">
        <!-- Header -->
        <div class="flex flex-col space-y-4">
            @auth
                <div>
                    <h1 class="text-3xl font-black tracking-tighter uppercase leading-none">Welcome, <span class="text-primary">{{ Auth::user()->name }}</span></h1>
                    <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest mt-2">What are you looking for today?</p>
                </div>
            @else
                <div>
                    <h1 class="text-3xl font-black tracking-tighter uppercase leading-none">Digital <span class="text-primary">Products</span></h1>
                    <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest mt-2">Browse our collection of premium digital items and licenses.</p>
                </div>
            @endauth

            <div class="flex items-center gap-3">
                <div class="relative w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground pixel-render"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" x-model="search" placeholder="Search products..." class="w-full pl-11 pr-4 py-4 bg-card border border-border rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all text-xs font-bold uppercase tracking-widest shadow-sm">
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="flex items-center gap-3 overflow-x-auto pb-6 scrollbar-hide">
            <template x-for="cat in categories" :key="cat">
                <button @click="category = cat"
                        :class="category === cat ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/20' : 'bg-card border border-border text-foreground/70 hover:bg-foreground/5'"
                        class="px-8 py-3.5 rounded-2xl text-[10px] font-black whitespace-nowrap transition-all tracking-widest uppercase"
                        x-text="cat"></button>
            </template>
        </div>

        <!-- thingy -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="product in products" :key="product.id">
                <div class="group glass-card rounded-[2.5rem] overflow-hidden hover:shadow-2xl hover:shadow-primary/10 transition-all duration-500 hover:-translate-y-2 animate-in fade-in zoom-in border-border/50">
                    <div class="aspect-square relative overflow-hidden bg-white/5">
                        <img :src="product.image" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-700" :alt="product.name">
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
                                        class="w-10 h-10 flex items-center justify-center rounded-xl transition-all"
                                        :class="favorites.includes(product.id) ? 'bg-red-500/10 text-red-500' : 'bg-foreground/5 text-muted-foreground hover:text-foreground'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" :fill="favorites.includes(product.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </button>
                                <button @click="openBuyModal(product)"
                                        class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl font-black text-[10px] hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/20 tracking-widest uppercase">
                                    Buy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Buy Modal -->
        <div x-show="showCartModal" class="fixed inset-0 z-100 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-transition>
            <div @click.away="showCartModal = false" class="bg-card border-2 border-border rounded-[2.5rem] p-8 max-w-md w-full space-y-8 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-black uppercase tracking-tighter" x-text="selectedProduct?.name"></h2>
                        <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest" x-text="selectedProduct?.category"></p>
                    </div>
                    <button @click="showCartModal = false" class="p-2 hover:bg-foreground/5 rounded-xl transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <div class="aspect-video relative rounded-3xl overflow-hidden bg-foreground/5">
                    <img :src="selectedProduct?.image" class="w-full h-full object-contain" />
                </div>

                <div class="flex items-center justify-between p-4 bg-foreground/5 rounded-2xl border border-border">
                    <span class="text-[10px] font-black uppercase tracking-widest">Total Price</span>
                    <span class="text-2xl font-black text-primary" x-text="'Rp ' + (selectedProduct ? new Intl.NumberFormat('id-ID').format(selectedProduct.price) : 0)"></span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <button @click="addToCart()" class="py-4 bg-foreground/5 border border-border text-foreground font-black rounded-2xl hover:bg-foreground/10 transition-all uppercase tracking-widest text-[10px] flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/></svg>
                        Add to Cart
                    </button>
                    <button class="py-4 bg-primary text-primary-foreground font-black rounded-2xl hover:scale-105 transition-all shadow-lg shadow-primary/20 uppercase tracking-widest text-[10px]">
                        Buy Now
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="filteredProducts.length === 0" class="py-20 text-center space-y-4" x-transition>
            <div class="w-20 h-20 bg-card border border-border rounded-4xl flex items-center justify-center mx-auto text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <p class="text-muted-foreground font-black uppercase tracking-widest text-xs">No products found matching your search or category.</p>
        </div>
    </div>
</x-app-layout>
