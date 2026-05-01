<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-8" x-data="{ 
        show: false,
        search: '',
        category: 'All Items',
        items: [
            { name: 'Steam Wallet Code', code: 'PX-ST-1234-5678', type: 'Product', date: '2025-05-01', image: '/products/steam-wallet.svg' },
            { name: '10% Off Everything', code: 'DISC10OFF', type: 'Voucher', date: '2025-04-28', image: '/gacha/voucher.svg' },
            { name: 'Spotify Premium Code', code: 'PX-SP-8888-9999', type: 'Product', date: '2025-04-25', image: '/products/spotify.svg' },
        ],
        get filteredItems() {
            return this.items.filter(item => {
                const matchesSearch = item.name.toLowerCase().includes(this.search.toLowerCase()) || item.code.toLowerCase().includes(this.search.toLowerCase());
                const matchesCategory = this.category === 'All Items' || item.type === this.category.slice(0, -1); // Remove 's' from Vouchers/Products
                return matchesSearch && matchesCategory;
            });
        }
    }" x-init="setTimeout(() => show = true, 50)">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6" x-show="show" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="space-y-2">
                <h1 class="text-4xl font-black tracking-tighter uppercase leading-none">My <span class="text-primary">Inventory</span></h1>
                <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest mt-2">Digital licenses, vouchers, and rewards in one place.</p>
            </div>
            
            <div class="relative w-full md:w-80">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground pixel-render"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" x-model="search" placeholder="Search inventory..." class="w-full pl-11 pr-4 py-4 bg-card border border-border rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all text-xs font-bold uppercase tracking-widest shadow-sm">
            </div>
        </div>

        <!-- Professional Inventory Tabs -->
        <div class="flex gap-2 p-1.5 bg-foreground/5 rounded-2xl w-max" x-show="show" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <template x-for="cat in ['All Items', 'Vouchers', 'Products']">
                <button @click="category = cat" 
                        :class="category === cat ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20' : 'text-muted-foreground hover:text-foreground hover:bg-white/10'"
                        class="px-8 py-2.5 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all"
                        x-text="cat"></button>
            </template>
        </div>

        <!-- Inventory Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <template x-for="(item, index) in filteredItems" :key="index">
                <div class="glass-card rounded-[2.5rem] p-6 flex gap-6 items-center group hover:shadow-2xl hover:shadow-primary/5 transition-all duration-500 border-border/50"
                     x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     :style="'transition-delay: ' + ((index + 3) * 100) + 'ms'">
                    <div class="w-20 h-20 bg-foreground/5 rounded-2xl flex items-center justify-center p-4 shrink-0 group-hover:bg-primary/5 transition-colors">
                        <img :src="item.image" class="w-full h-full object-contain pixel-render group-hover:scale-110 transition-transform duration-500" />
                    </div>
                    <div class="flex-1 min-w-0 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase tracking-widest text-primary" x-text="item.type"></span>
                            <span class="text-[9px] font-black text-muted-foreground uppercase" x-text="item.date"></span>
                        </div>
                        <h3 class="font-black text-sm uppercase leading-tight truncate group-hover:text-primary transition-colors" x-text="item.name"></h3>
                        <div class="flex items-center gap-3">
                            <code class="px-3 py-2 bg-foreground/5 rounded-xl text-[10px] font-mono font-black text-foreground/60 border border-border" x-text="item.code"></code>
                            <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-foreground/5 hover:bg-primary/10 hover:text-primary transition-all text-muted-foreground group/btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render group-active/btn:scale-90 transition-transform"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredItems.length === 0" class="py-20 text-center space-y-4" x-transition>
            <div class="w-20 h-20 bg-card border border-border rounded-[2.5rem] flex items-center justify-center mx-auto text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
            </div>
            <p class="text-muted-foreground font-black uppercase tracking-widest text-xs">No items found in your inventory.</p>
        </div>
    </div>
</x-app-layout>
