<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-8" x-data="{
        show: false,
        search: '',
        category: 'All Items',
        items: {{ \Illuminate\Support\Js::from($items ?? []) }},
        copiedIndex: null,
        selectedItem: null,

        get filteredItems() {
            return this.items.filter(item => {
                const matchesSearch = item.name.toLowerCase().includes(this.search.toLowerCase()) || (item.code && item.code.toLowerCase().includes(this.search.toLowerCase()));
                const matchesCategory = this.category === 'All Items' || item.type === this.category.slice(0, -1);
                return matchesSearch && matchesCategory;
            });
        },

        copyCode(code, index) {
            navigator.clipboard.writeText(code);
            this.copiedIndex = index;
            setTimeout(() => this.copiedIndex = null, 2000);
        },

        openDetail(item) {
            this.selectedItem = item;
        },

        closeDetail() {
            this.selectedItem = null;
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

        <!-- Inventory Tabs -->
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
                <div @click="openDetail(item)" class="glass-card rounded-[2.5rem] p-6 flex gap-6 items-center group hover:shadow-2xl hover:shadow-primary/5 transition-all duration-500 border-border/50 cursor-pointer"
                     x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     :style="'transition-delay: ' + ((index + 3) * 100) + 'ms'">
                    <div class="w-20 h-20 bg-foreground/5 rounded-2xl flex items-center justify-center p-4 shrink-0 group-hover:bg-primary/5 transition-colors">
                        <img :src="item.image" class="w-full h-full object-contain pixel-render group-hover:scale-110 transition-transform duration-500" />
                    </div>
                    <div class="flex-1 min-w-0 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-primary" x-text="item.type"></span>
                                <!-- Type Badge -->
                                <template x-if="item.item_type === 'direct_topup'">
                                    <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-widest bg-amber-500/15 text-amber-500 border border-amber-500/20">Top-Up</span>
                                </template>
                                <template x-if="item.item_type === 'voucher_key'">
                                    <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-widest bg-blue-500/15 text-blue-500 border border-blue-500/20">Code</span>
                                </template>
                            </div>
                            <span class="text-[9px] font-black text-muted-foreground uppercase" x-text="item.date"></span>
                        </div>
                        <h3 class="font-black text-sm uppercase leading-tight truncate group-hover:text-primary transition-colors" x-text="item.name"></h3>

                        <!-- Status Indicators -->
                        <template x-if="item.item_type === 'voucher_key' || item.item_type === 'discount'">
                            <div class="flex items-center gap-2">
                                <code class="px-3 py-2 bg-foreground/5 rounded-xl text-[10px] font-mono font-black text-foreground/60 border border-border truncate max-w-[200px]" x-text="item.code"></code>
                                <span class="text-[9px] font-bold text-muted-foreground">Tap to view</span>
                            </div>
                        </template>
                        <template x-if="item.item_type === 'direct_topup'">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest"
                                      :class="item.topup_status === 'sent' ? 'bg-green-500/10 text-green-500 border border-green-500/20' : (item.topup_status === 'processing' ? 'bg-blue-500/10 text-blue-500 border border-blue-500/20' : 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20')"
                                      x-text="item.topup_status === 'sent' ? '✓ Sent' : (item.topup_status === 'processing' ? '⟳ Processing' : '⏳ Pending')"></span>
                                <span class="text-[9px] font-bold text-muted-foreground">Tap for details</span>
                            </div>
                        </template>
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
            <a href="{{ route('home') }}" class="inline-block mt-2 text-primary font-black uppercase tracking-widest text-[10px] hover:underline">Start Shopping</a>
        </div>

        <!-- Detail Modal -->
        <div x-show="selectedItem" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="closeDetail()">
            <div class="bg-card border-2 border-border rounded-[2rem] p-8 max-w-md w-full space-y-6 shadow-2xl"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>

                <!-- Close Button -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-foreground/5 rounded-xl flex items-center justify-center p-2">
                            <img :src="selectedItem?.image" class="w-full h-full object-contain pixel-render" />
                        </div>
                        <div>
                            <h3 class="font-black text-sm uppercase leading-tight" x-text="selectedItem?.name"></h3>
                            <span class="text-[9px] font-black uppercase tracking-widest text-primary" x-text="selectedItem?.type"></span>
                        </div>
                    </div>
                    <button @click="closeDetail()" class="w-10 h-10 rounded-xl bg-foreground/5 hover:bg-foreground/10 flex items-center justify-center transition-colors text-muted-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <!-- Voucher Key Content -->
                <template x-if="selectedItem && (selectedItem.item_type === 'voucher_key' || selectedItem.item_type === 'discount')">
                    <div class="space-y-4">
                        <div class="bg-foreground/5 border border-border rounded-xl p-5 text-center space-y-3">
                            <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Redeem Code</p>
                            <code class="block text-lg font-mono font-black text-primary tracking-wider break-all" x-text="selectedItem.code"></code>
                        </div>
                        <button @click="navigator.clipboard.writeText(selectedItem.code); $dispatch('show-toast', { message: 'Code copied!', type: 'success' })"
                                class="w-full py-4 bg-primary text-primary-foreground font-black rounded-xl hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/20 uppercase tracking-widest text-xs flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            Copy Code
                        </button>
                        <p class="text-[9px] font-bold text-muted-foreground text-center uppercase tracking-widest" x-text="selectedItem.item_type === 'discount' ? 'Expires: ' + selectedItem.date : 'Purchased: ' + selectedItem.date"></p>
                    </div>
                </template>

                <!-- Direct Top-Up Content -->
                <template x-if="selectedItem && selectedItem.item_type === 'direct_topup'">
                    <div class="space-y-4">
                        <!-- Status Badge -->
                        <div class="rounded-xl p-5 text-center space-y-2"
                             :class="selectedItem.topup_status === 'sent' ? 'bg-green-500/10 border border-green-500/20' : (selectedItem.topup_status === 'processing' ? 'bg-blue-500/10 border border-blue-500/20' : 'bg-yellow-500/10 border border-yellow-500/20')">
                            <div class="w-14 h-14 mx-auto rounded-full flex items-center justify-center"
                                 :class="selectedItem.topup_status === 'sent' ? 'bg-green-500/20 text-green-500' : (selectedItem.topup_status === 'processing' ? 'bg-blue-500/20 text-blue-500' : 'bg-yellow-500/20 text-yellow-500')">
                                <template x-if="selectedItem.topup_status === 'sent'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </template>
                                <template x-if="selectedItem.topup_status === 'processing'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                </template>
                                <template x-if="selectedItem.topup_status === 'pending'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </template>
                            </div>
                            <p class="text-sm font-black uppercase tracking-widest"
                               :class="selectedItem.topup_status === 'sent' ? 'text-green-500' : (selectedItem.topup_status === 'processing' ? 'text-blue-500' : 'text-yellow-500')"
                               x-text="selectedItem.topup_status === 'sent' ? 'Delivered' : (selectedItem.topup_status === 'processing' ? 'Processing' : 'Pending')"></p>
                            <p class="text-[10px] font-bold text-muted-foreground"
                               x-text="selectedItem.topup_status === 'sent' ? 'The top-up has been sent to your account.' : (selectedItem.topup_status === 'processing' ? 'Admin is processing your top-up.' : 'Awaiting admin to process your top-up.')"></p>
                        </div>

                        <!-- Submitted Credentials -->
                        <div class="bg-foreground/5 border border-border rounded-xl p-4 space-y-3">
                            <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Your Submitted Credentials</p>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Player ID</span>
                                    <span class="text-xs font-black" x-text="selectedItem.player_id || '—'"></span>
                                </div>
                                <template x-if="selectedItem.zone_id">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Zone ID</span>
                                        <span class="text-xs font-black" x-text="selectedItem.zone_id"></span>
                                    </div>
                                </template>
                                <template x-if="selectedItem.server_id">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Server ID</span>
                                        <span class="text-xs font-black" x-text="selectedItem.server_id"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <p class="text-[9px] font-bold text-muted-foreground text-center uppercase tracking-widest" x-text="'Purchased: ' + selectedItem.date"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
