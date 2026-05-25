<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8"
             x-data="inventoryPage({{ \Illuminate\Support\Js::from($items ?? []) }})"
             x-init="setTimeout(() => show = true, 50)">
            <div style="display:flex;flex-direction:column;gap:16px;" class="md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="px-heading">My <span class="gold">Inventory</span></h1>
                    <p class="px-subheading">DIGITAL LICENSES, VOUCHERS, AND REWARDS IN ONE PLACE</p>
                </div>
                <div style="position:relative;width:100%;max-width:320px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-dim);" class="pixel-render"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" x-model="search" placeholder="Search inventory..." class="px-input" style="width:100%;padding:12px 12px 12px 36px;font-size:12px;">
                </div>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Modern Tabs --}}
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide w-full">
                <template x-for="cat in ['All Items', 'Vouchers', 'Products', 'Redeemed']">
                    <button @click="category = cat"
                            :class="category === cat ? 'bg-primary text-primary-foreground shadow-sm' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10'"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
                            x-text="cat"></button>
                </template>
            </div>

            {{-- Inventory Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <template x-for="(item, index) in filteredItems" :key="index">
                    <div @click="openDetail(item)" class="px-card" style="padding:20px;display:flex;gap:16px;align-items:center;cursor:pointer;">
                        <div style="width:64px;height:64px;background:var(--dark-card2);border:2px solid var(--dark-line);display:flex;align-items:center;justify-content:center;padding:12px;flex-shrink:0;">
                            <img :src="item.image" class="w-full h-full object-contain pixel-render" />
                        </div>
                        <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:6px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span class="px-badge px-badge-gold" x-text="item.type"></span>
                                    <template x-if="item.item_type === 'direct_topup'"><span class="px-badge px-badge-amber">TOP-UP</span></template>
                                    <template x-if="item.item_type === 'voucher_key'"><span class="px-badge px-badge-blue">CODE</span></template>
                                    <template x-if="item.item_type === 'discount_redeemed'"><span class="px-badge" style="background:rgba(148,163,184,0.12);color:#94a3b8;border-color:rgba(148,163,184,0.3);">REDEEMED</span></template>
                                </div>
                                <span style="font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.08em;" x-text="item.date"></span>
                            </div>
                            <h3 style="font-family:var(--font-sans);font-size:14px;font-weight:800;color:#e8f0ff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item.name"></h3>
                            <template x-if="item.item_type === 'voucher_key' || item.item_type === 'discount'">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="px-badge px-badge-blue">CODE</span>
                                    <span style="font-family:var(--px);font-size:5px;color:var(--text-dim);">TAP TO REVEAL</span>
                                </div>
                            </template>
                            <template x-if="item.item_type === 'direct_topup'">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="px-badge" :class="item.topup_status === 'sent' ? 'px-badge-green' : (item.topup_status === 'processing' ? 'px-badge-blue' : 'px-badge-amber')" x-text="item.topup_status === 'sent' ? '✓ SENT' : (item.topup_status === 'processing' ? '⟳ PROCESSING' : '⏳ PENDING')"></span>
                                    <span style="font-family:var(--px);font-size:5px;color:var(--text-dim);">TAP FOR DETAILS</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty State --}}
            <div x-show="filteredItems.length === 0" class="px-empty-state">
                <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg></div>
                <p class="empty-text">NO ITEMS FOUND IN YOUR INVENTORY</p>
                <a href="{{ route('home') }}" class="empty-link">START SHOPPING →</a>
            </div>

            {{-- Detail Modal --}}
            <div x-show="selectedItem" class="px-modal-overlay" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="closeDetail()">
                <div class="px-modal-box" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" @click.stop>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;background:var(--dark-card2);border:2px solid var(--dark-line);display:flex;align-items:center;justify-content:center;padding:6px;">
                                <img :src="selectedItem?.image" class="w-full h-full object-contain pixel-render" />
                            </div>
                            <div>
                                <h3 style="font-family:var(--font-sans);font-size:14px;font-weight:800;color:#e8f0ff;" x-text="selectedItem?.name"></h3>
                                <span class="px-badge px-badge-gold" x-text="selectedItem?.type"></span>
                            </div>
                        </div>
                        <button @click="closeDetail()" class="px-btn-ghost" style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                    <template x-if="selectedItem && (selectedItem.item_type === 'voucher_key' || selectedItem.item_type === 'discount')">
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            <div style="background:var(--dark-card2);border:2px solid var(--dark-line);padding:16px;text-align:center;">
                                <p style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);margin-bottom:8px;">REDEEM CODE</p>
                                <code style="display:block;font-family:var(--font-mono);font-size:16px;font-weight:800;color:var(--gold);word-break:break-all;" x-text="selectedItem.code"></code>
                            </div>
                            <button @click="navigator.clipboard.writeText(selectedItem.code); $dispatch('show-toast', { message: 'Code copied!', type: 'success' })" class="px-btn-gold" style="width:100%;padding:14px;font-size:7px;display:flex;align-items:center;justify-content:center;gap:6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                COPY CODE
                            </button>
                            <p style="font-family:var(--px);font-size:6px;color:var(--text-dim);text-align:center;letter-spacing:0.1em;" x-text="selectedItem.item_type === 'discount' ? 'EXPIRES: ' + selectedItem.date : 'PURCHASED: ' + selectedItem.date"></p>
                        </div>
                    </template>
                    <template x-if="selectedItem && selectedItem.item_type === 'discount_redeemed'">
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            <div style="background:rgba(148,163,184,0.08);border:2px solid rgba(148,163,184,0.25);padding:16px;text-align:center;">
                                <p style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:#94a3b8;margin-bottom:4px;">VOUCHER REDEEMED</p>
                                <p style="font-family:var(--font-sans);font-size:12px;color:var(--text-dim);">This voucher has been consumed and cannot be reused.</p>
                            </div>
                            <template x-if="selectedItem.redeemed_order">
                                <div style="background:var(--dark-card2);border:2px solid var(--dark-line);padding:14px;display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.12em;">APPLIED TO ORDER</span>
                                    <span style="font-family:var(--font-mono);font-size:12px;font-weight:800;color:var(--gold);" x-text="selectedItem.redeemed_order"></span>
                                </div>
                            </template>
                            <p style="font-family:var(--px);font-size:6px;color:var(--text-dim);text-align:center;letter-spacing:0.1em;" x-text="'REDEEMED: ' + selectedItem.date"></p>
                        </div>
                    </template>
                    <template x-if="selectedItem && selectedItem.item_type === 'direct_topup'">
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            <div style="padding:16px;text-align:center;border:2px solid;" :style="'border-color:' + (selectedItem.topup_status === 'sent' ? '#22c55e' : selectedItem.topup_status === 'processing' ? '#3b82f6' : '#f59e0b') + ';background:' + (selectedItem.topup_status === 'sent' ? 'rgba(34,197,94,0.1)' : selectedItem.topup_status === 'processing' ? 'rgba(59,130,246,0.1)' : 'rgba(245,158,11,0.1)')">
                                <p style="font-family:var(--px);font-size:8px;letter-spacing:0.1em;margin-bottom:4px;" :style="'color:' + (selectedItem.topup_status === 'sent' ? '#22c55e' : selectedItem.topup_status === 'processing' ? '#3b82f6' : '#f59e0b')" x-text="selectedItem.topup_status === 'sent' ? '✓ DELIVERED' : (selectedItem.topup_status === 'processing' ? '⟳ PROCESSING' : '⏳ PENDING')"></p>
                                <p style="font-family:var(--font-sans);font-size:12px;color:var(--text-dim);" x-text="selectedItem.topup_status === 'sent' ? 'Top-up has been sent to your account.' : (selectedItem.topup_status === 'processing' ? 'Admin is processing your top-up.' : 'Awaiting admin to process.')"></p>
                            </div>
                            <div style="background:var(--dark-card2);border:2px solid var(--dark-line);padding:14px;display:flex;flex-direction:column;gap:8px;">
                                <p style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">YOUR SUBMITTED CREDENTIALS</p>
                                <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:6px;color:var(--text-dim);">PLAYER ID</span><span style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#e8f0ff;" x-text="selectedItem.player_id || '—'"></span></div>
                                <template x-if="selectedItem.zone_id"><div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:6px;color:var(--text-dim);">ZONE ID</span><span style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#e8f0ff;" x-text="selectedItem.zone_id"></span></div></template>
                                <template x-if="selectedItem.server_id"><div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:6px;color:var(--text-dim);">SERVER ID</span><span style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#e8f0ff;" x-text="selectedItem.server_id"></span></div></template>
                            </div>
                            <p style="font-family:var(--px);font-size:6px;color:var(--text-dim);text-align:center;letter-spacing:0.1em;" x-text="'PURCHASED: ' + selectedItem.date"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
    function inventoryPage(initialItems) {
        return {
            show: false,
            search: '',
            category: 'All Items',
            items: initialItems,
            copiedIndex: null,
            selectedItem: null,

            get filteredItems() {
                return this.items.filter(item => {
                    const matchesSearch = item.name.toLowerCase().includes(this.search.toLowerCase())
                        || (item.code && item.code.toLowerCase().includes(this.search.toLowerCase()));

                    let matchesCategory;
                    if (this.category === 'All Items') {
                        matchesCategory = item.item_type !== 'discount_redeemed';
                    } else if (this.category === 'Redeemed') {
                        matchesCategory = item.item_type === 'discount_redeemed';
                    } else if (this.category === 'Vouchers') {
                        matchesCategory = item.type === 'Voucher' && item.item_type !== 'discount_redeemed';
                    } else { // 'Products'
                        matchesCategory = item.type === 'Product';
                    }
                    return matchesSearch && matchesCategory;
                });
            },

            copyCode(code, index) {
                navigator.clipboard.writeText(code);
                this.copiedIndex = index;
                setTimeout(() => this.copiedIndex = null, 2000);
            },

            openDetail(item) { this.selectedItem = item; },
            closeDetail() { this.selectedItem = null; }
        };
    }
    </script>
</x-app-layout>
