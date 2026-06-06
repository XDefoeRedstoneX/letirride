@php
    use \Illuminate\Support\Js;
@endphp
<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8" x-data="cartPage({
            items: {{ Js::from($cartItems ?? []) }},
            discounts: {{ Js::from($userDiscounts ?? []) }},
            csrfToken: '{{ csrf_token() }}',
            midtransClientKey: '{{ $midtransClientKey ?? '' }}'
        })" x-init="init()">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div><h1 class="px-heading">Order <span class="gold">Receipt</span></h1><p class="px-subheading">REVIEW YOUR ITEMS BEFORE CHECKOUT</p></div>
                <div style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;background:rgba(245,158,11,0.1);border:2px solid rgba(245,158,11,0.25);color:var(--gold-text);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/></svg>
                </div>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <template x-for="item in items" :key="item.id">
                        <div class="px-card" style="padding:20px;display:flex;align-items:center;gap:20px;">
                            <div class="cart-img-box" style="width:80px;height:80px;background:var(--dark-card2);border:2px solid var(--dark-line);display:flex;align-items:center;justify-content:center;padding:12px;flex-shrink:0;"><img :src="item.image" class="w-full h-full object-contain pixel-render" /></div>
                            <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                        <span class="px-badge px-badge-gold" x-text="item.category"></span>
                                        <template x-if="item.subcategory"><span class="px-badge px-badge-blue" x-text="item.subcategory"></span></template>
                                    </div>
                                    <button @click="removeItem(item)" style="color:var(--text-dim);cursor:pointer;background:none;border:none;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='var(--text-dim)'"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></button>
                                </div>
                                <h3 style="font-family:var(--font-sans);font-size:14px;font-weight:800;color:var(--foreground);" x-text="item.name"></h3>
                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <button @click="updateQty(item,-1)" class="px-btn-ghost" style="width:28px;height:28px;padding:0;display:flex;align-items:center;justify-content:center;font-size:14px;font-family:var(--font-sans);">−</button>
                                        <span style="font-family:var(--font-sans);font-size:14px;font-weight:800;color:var(--foreground);width:24px;text-align:center;" x-text="item.quantity"></span>
                                        <button @click="updateQty(item,1)" class="px-btn-ghost" style="width:28px;height:28px;padding:0;display:flex;align-items:center;justify-content:center;font-size:14px;font-family:var(--font-sans);">+</button>
                                    </div>
                                    <p style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--gold-text);" x-text="formatRp(item.price * item.quantity)"></p>
                                </div>
                                <template x-if="item.product_type === 'direct_topup'">
                                    <div style="margin-top:8px;padding:14px;background:rgba(245,158,11,0.08);border:2px solid rgba(245,158,11,0.2);">
                                        <div style="display:flex;align-items:center;gap:6px;color:var(--gold);margin-bottom:10px;"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg><span style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;">DIRECT TOP-UP — ENTER CREDENTIALS</span></div>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <input type="text" placeholder="Player ID *" :value="topupCredentials[item.id]?.player_id || ''" @input="setCredential(item.id, 'player_id', $event.target.value)" class="px-input" style="padding:8px 12px;font-size:12px;" />
                                            <input type="text" placeholder="Zone ID" :value="topupCredentials[item.id]?.zone_id || ''" @input="setCredential(item.id, 'zone_id', $event.target.value)" class="px-input" style="padding:8px 12px;font-size:12px;" />
                                            <input type="text" placeholder="Server ID" :value="topupCredentials[item.id]?.server_id || ''" @input="setCredential(item.id, 'server_id', $event.target.value)" class="px-input" style="padding:8px 12px;font-size:12px;" />
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div x-show="items.length === 0" class="px-empty-state" style="border:3px dashed var(--border);"><div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/></svg></div><p class="empty-text">YOUR CART IS EMPTY</p><a href="{{ route('home') }}" class="empty-link">START SHOPPING →</a></div>
                </div>

                <div class="space-y-6">
                    <div class="px-card-static" style="padding:28px;position:sticky;top:80px;">
                        <div class="px-section-header"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div><span class="section-title">SUMMARY</span></div>

                        <div style="display:flex;flex-direction:column;gap:14px;">
                            <div style="display:flex;justify-content:space-between;">
                                <span style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:var(--text-dim);">SUBTOTAL</span>
                                <span style="font-family:var(--font-sans);font-size:14px;font-weight:700;color:var(--foreground);" x-text="formatRp(subtotal)"></span>
                            </div>

                            {{-- ============ CUSTOM VOUCHER COMBOBOX ============ --}}
                            <template x-if="discounts.length > 0">
                                <div x-data="{ open: false, query: '', activeIdx: 0 }"
                                     @keydown.escape.window="open = false"
                                     @click.outside="open = false"
                                     style="position:relative;display:flex;flex-direction:column;gap:8px;">
                                    <label style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:var(--text-dim);">APPLY VOUCHER</label>

                                    {{-- Trigger button (shows selected voucher or placeholder) --}}
                                    <button type="button"
                                            @click="open = !open; if(open) $nextTick(() => $refs.search?.focus())"
                                            class="voucher-combo-btn"
                                            :class="{ 'is-open': open, 'has-value': selectedDiscount }">
                                        <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
                                            <div class="voucher-combo-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M20 12V8a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v0a2 2 0 0 1-2 2v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4a2 2 0 0 1-2-2v0a2 2 0 0 1 2-2Z"/><path d="M9 11V8"/><path d="M9 18v-2"/></svg>
                                            </div>
                                            <template x-if="selectedDiscount">
                                                <div style="flex:1;min-width:0;text-align:left;">
                                                    <div style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:var(--foreground);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="selectedDiscount.name"></div>
                                                    <div style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;color:var(--gold-text);margin-top:2px;" x-text="targetLabel(selectedDiscount)"></div>
                                                </div>
                                            </template>
                                            <template x-if="!selectedDiscount">
                                                <span style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);">SELECT A VOUCHER</span>
                                            </template>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" :style="open ? 'transform:rotate(180deg)' : ''" style="transition:transform 0.15s ease;flex-shrink:0;color:var(--text-dim);"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>

                                    {{-- Popover panel --}}
                                    <div x-show="open"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 -translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         class="voucher-combo-panel">
                                        <div class="voucher-combo-search">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" style="color:var(--text-dim);flex-shrink:0;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                            <input type="text"
                                                   x-ref="search"
                                                   x-model="query"
                                                   @keydown.arrow-down.prevent="activeIdx = Math.min(activeIdx + 1, filteredDiscounts(query).length - 1)"
                                                   @keydown.arrow-up.prevent="activeIdx = Math.max(activeIdx - 1, 0)"
                                                   @keydown.enter.prevent="
                                                        const list = filteredDiscounts(query);
                                                        const pick = list[activeIdx];
                                                        if (pick && voucherEligible(pick)) { selectDiscount(pick); open = false; }
                                                   "
                                                   placeholder="Search vouchers..." />
                                            <template x-if="selectedDiscount">
                                                <button type="button" @click="selectedDiscount = null" class="voucher-combo-clear">CLEAR</button>
                                            </template>
                                        </div>

                                        <div class="voucher-combo-list">
                                            <template x-for="(d, idx) in filteredDiscounts(query)" :key="d.id">
                                                <button type="button"
                                                        @click="if (voucherEligible(d)) { selectDiscount(d); open = false; }"
                                                        @mouseenter="activeIdx = idx"
                                                        :class="{
                                                            'is-active': activeIdx === idx,
                                                            'is-selected': selectedDiscount?.id === d.id,
                                                            'is-disabled': !voucherEligible(d)
                                                        }"
                                                        class="voucher-combo-option">
                                                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                                                        <div style="display:flex;flex-direction:column;gap:6px;flex:1;min-width:0;">
                                                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                                                <span x-show="d.type === 'percent'" class="px-badge px-badge-gold" x-text="d.value + '%'"></span>
                                                                <span x-show="d.type !== 'percent'" class="px-badge px-badge-amber" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(d.value)"></span>
                                                                <span class="voucher-target-chip"
                                                                      :class="d.target_subcategory_id ? 'is-brand' : (d.target_category_id ? 'is-cat' : 'is-store')"
                                                                      x-text="targetLabel(d)"></span>
                                                            </div>
                                                            <div style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:var(--foreground);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="d.name"></div>
                                                            <template x-if="!voucherEligible(d)">
                                                                <div style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;color:#f87171;" x-text="ineligibleReason(d)"></div>
                                                            </template>
                                                        </div>
                                                        <template x-if="selectedDiscount?.id === d.id">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" style="color:var(--gold);flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                                                        </template>
                                                    </div>
                                                </button>
                                            </template>
                                            <div x-show="filteredDiscounts(query).length === 0" style="padding:24px 16px;text-align:center;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);">
                                                NO VOUCHERS MATCH "<span x-text="query"></span>"
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            {{-- ============ END COMBOBOX ============ --}}

                            <template x-if="selectedDiscount && discountAmount > 0">
                                <div style="background:rgba(34,197,94,0.1);border:2px solid rgba(34,197,94,0.25);padding:12px;">
                                    <div style="display:flex;justify-content:space-between;">
                                        <div style="display:flex;align-items:center;gap:6px;"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" style="color:#22c55e;"><polyline points="20 6 9 17 4 12"/></svg><span style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;color:#22c55e;">VOUCHER APPLIED</span></div>
                                        <button @click="selectedDiscount=null" style="color:var(--text-dim);cursor:pointer;background:none;border:none;"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                                    </div>
                                    <p style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#4ade80;margin-top:4px;" x-text="selectedDiscount.name"></p>
                                    <p style="font-family:var(--font-sans);font-size:11px;color:rgba(34,197,94,0.7);margin-top:2px;" x-text="'Applies to: ' + targetLabel(selectedDiscount)"></p>
                                </div>
                            </template>

                            <template x-if="discountAmount > 0">
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:#22c55e;">DISCOUNT</span>
                                    <span style="font-family:var(--font-sans);font-size:14px;font-weight:700;color:#22c55e;" x-text="'- ' + formatRp(discountAmount)"></span>
                                </div>
                            </template>

                            <div style="height:2px;background:var(--border);"></div>
                            <div style="display:flex;justify-content:space-between;">
                                <span style="font-family:var(--px);font-size:8px;letter-spacing:0.1em;color:var(--foreground);">GRAND TOTAL</span>
                                <span style="font-family:var(--font-sans);font-size:22px;font-weight:800;color:var(--gold-text);" x-text="formatRp(total)"></span>
                            </div>
                        </div>

                        <button @click="pay()" :disabled="items.length === 0 || paying" class="px-btn-gold" style="width:100%;padding:18px;margin-top:20px;font-size:8px;display:flex;align-items:center;justify-content:center;gap:8px;">
                            <template x-if="paying"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></template>
                            <span x-text="paying ? 'PROCESSING...' : 'PAY NOW'"></span>
                        </button>
                        <p style="font-family:var(--px);font-size:6px;text-align:center;color:var(--text-dim);letter-spacing:0.1em;margin-top:12px;">SECURE ENCRYPTED CHECKOUT VIA MIDTRANS</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(config('midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ $midtransClientKey ?? '' }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $midtransClientKey ?? '' }}"></script>
    @endif

    <style>
        [x-cloak] { display: none !important; }

        .voucher-combo-btn {
            width: 100%;
            padding: 12px 14px;
            background: var(--muted);
            border: 2px solid var(--border);
            color: var(--foreground);
            font-family: var(--font-sans);
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: border-color 0.15s ease, background 0.15s ease;
            text-align: left;
        }
        .voucher-combo-btn:hover { border-color: rgba(245,158,11,0.4); }
        .voucher-combo-btn.is-open { border-color: var(--gold); background: rgba(245,158,11,0.05); }
        .voucher-combo-btn.has-value { border-color: rgba(34,197,94,0.4); }

        .voucher-combo-icon {
            width: 28px; height: 28px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(245,158,11,0.1);
            border: 2px solid rgba(245,158,11,0.25);
            color: var(--gold);
            flex-shrink: 0;
        }

        .voucher-combo-panel {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 6px;
            background: var(--popover);
            border: 2px solid var(--gold);
            box-shadow: 0 12px 32px rgba(0,0,0,0.5), 0 0 0 1px rgba(245,158,11,0.2);
            z-index: 50;
            display: flex;
            flex-direction: column;
            max-height: 360px;
        }

        .voucher-combo-search {
            padding: 12px;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--muted);
        }
        .voucher-combo-search input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            font-family: var(--font-sans);
            font-size: 12px;
            color: var(--foreground);
        }
        .voucher-combo-search input::placeholder { color: var(--text-dim); font-family: var(--px); font-size: 9px; letter-spacing: 0.08em; }

        .voucher-combo-clear {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171;
            padding: 4px 8px;
            font-family: var(--px);
            font-size: 6px;
            letter-spacing: 0.1em;
            cursor: pointer;
        }
        .voucher-combo-clear:hover { background: rgba(239,68,68,0.2); }

        .voucher-combo-list {
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            padding: 4px;
        }

        .voucher-combo-option {
            display: block;
            width: 100%;
            text-align: left;
            background: transparent;
            border: 2px solid transparent;
            padding: 10px 12px;
            cursor: pointer;
            transition: background 0.1s ease, border-color 0.1s ease;
            font-family: inherit;
        }
        .voucher-combo-option.is-active:not(.is-disabled) {
            background: rgba(245,158,11,0.08);
            border-color: rgba(245,158,11,0.3);
        }
        .voucher-combo-option.is-selected {
            background: rgba(245,158,11,0.12);
            border-color: rgba(245,158,11,0.5);
        }
        .voucher-combo-option.is-disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .voucher-target-chip {
            font-family: var(--px);
            font-size: 6px;
            letter-spacing: 0.1em;
            padding: 3px 6px;
            border: 1px solid;
        }
        .voucher-target-chip.is-store { color: #a78bfa; border-color: rgba(167,139,250,0.4); background: rgba(167,139,250,0.08); }
        .voucher-target-chip.is-cat   { color: #60a5fa; border-color: rgba(96,165,250,0.4); background: rgba(96,165,250,0.08); }
        .voucher-target-chip.is-brand { color: #f59e0b; border-color: rgba(245,158,11,0.4); background: rgba(245,158,11,0.08); }

        /* Product thumbnail: white in light mode, dark navy in dark mode */
        .cart-img-box { background: #ffffff !important; border-color: #e2e8f0 !important; }
        .dark .cart-img-box { background: var(--dark-card2) !important; border-color: var(--dark-line) !important; }

        /* Restore the dark voucher surfaces in dark mode (base rules above are
           light-mode defaults). */
        .dark .voucher-combo-btn { background: var(--dark-card2); border-color: var(--dark-line); color: #e8f0ff; }
        .dark .voucher-combo-panel { background: var(--dark-card); }
        .dark .voucher-combo-search { background: var(--dark-card2); border-bottom-color: var(--dark-line); }
        .dark .voucher-combo-search input { color: #e8f0ff; }
    </style>

    <script>
        function cartPage(opts) {
            return {
                items: opts.items,
                discounts: opts.discounts,
                csrfToken: opts.csrfToken,
                midtransClientKey: opts.midtransClientKey,
                selectedDiscount: null,
                topupCredentials: {},
                paying: false,

                init() {
                    this.items.forEach(item => {
                        if (item.product_type === 'direct_topup') {
                            this.topupCredentials[item.id] = item.topup_meta ? { ...item.topup_meta } : {};
                        }
                    });
                },

                setCredential(itemId, field, value) {
                    this.topupCredentials[itemId] = { ...(this.topupCredentials[itemId] || {}), [field]: value };
                },

                get cartCategoryIds()    { return [...new Set(this.items.map(i => i.category_id))]; },
                get cartSubcategoryIds() { return [...new Set(this.items.map(i => i.subcategory_id).filter(Boolean))]; },
                get subtotal() { return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0); },

                voucherEligible(d) {
                    if (d.target_subcategory_id) return this.cartSubcategoryIds.includes(d.target_subcategory_id);
                    if (d.target_category_id)    return this.cartCategoryIds.includes(d.target_category_id);
                    return true;
                },

                ineligibleReason(d) {
                    if (d.target_subcategory_id) return 'ADD A ' + (d.target_subcategory_name || 'TARGET').toUpperCase() + ' ITEM TO USE';
                    if (d.target_category_id)    return 'ADD A ' + (d.target_category_name || 'TARGET').toUpperCase() + ' ITEM TO USE';
                    return '';
                },

                targetLabel(d) {
                    if (d.target_subcategory_id) return (d.target_subcategory_name || 'BRAND') + ' ONLY';
                    if (d.target_category_id)    return (d.target_category_name || 'CATEGORY') + ' ONLY';
                    return 'ALL PRODUCTS';
                },

                filteredDiscounts(query) {
                    const q = (query || '').trim().toLowerCase();
                    let list = this.discounts;
                    if (q) {
                        list = list.filter(d =>
                            d.name.toLowerCase().includes(q)
                            || (d.target_category_name || '').toLowerCase().includes(q)
                            || (d.target_subcategory_name || '').toLowerCase().includes(q)
                        );
                    }
                    // Eligible first, ineligible last.
                    return list.slice().sort((a, b) => {
                        return (this.voucherEligible(b) ? 1 : 0) - (this.voucherEligible(a) ? 1 : 0);
                    });
                },

                selectDiscount(d) {
                    this.selectedDiscount = d;
                },

                get discountAmount() {
                    const d = this.selectedDiscount;
                    if (!d || !this.voucherEligible(d)) return 0;

                    let eligible;
                    if (d.target_subcategory_id) {
                        eligible = this.items
                            .filter(i => i.subcategory_id === d.target_subcategory_id)
                            .reduce((s, i) => s + (i.price * i.quantity), 0);
                    } else if (d.target_category_id) {
                        eligible = this.items
                            .filter(i => i.category_id === d.target_category_id)
                            .reduce((s, i) => s + (i.price * i.quantity), 0);
                    } else {
                        eligible = this.subtotal;
                    }

                    if (eligible <= 0) return 0;
                    if (d.type === 'percent') return eligible * (d.value / 100);
                    return Math.min(d.value, eligible);
                },

                get total() { return Math.max(0, this.subtotal - this.discountAmount); },

                async updateQty(item, delta) {
                    const newQty = item.quantity + delta;
                    if (newQty < 1) { this.removeItem(item); return; }
                    const r = await fetch(`/cart/${item.id}`, {
                        method: 'PATCH',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfToken },
                        body: JSON.stringify({ quantity: newQty })
                    });
                    if (r.ok) {
                        item.quantity = newQty;
                        const data = await r.json();
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
                    }
                },

                async removeItem(item) {
                    const r = await fetch(`/cart/${item.id}`, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfToken }
                    });
                    if (r.ok) {
                        this.items = this.items.filter(i => i.id !== item.id);
                        const data = await r.json();
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
                        if (this.selectedDiscount && !this.voucherEligible(this.selectedDiscount)) {
                            this.selectedDiscount = null;
                        }
                    }
                },

                async pay() {
                    if (this.items.length === 0 || this.paying) return;
                    this.paying = true;

                    const topupItems = this.items.filter(i => i.product_type === 'direct_topup');

                    for (const item of topupItems) {
                        const creds = this.topupCredentials[item.id];
                        if (!creds || !creds.player_id || creds.player_id.trim() === '') {
                            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Please enter your Player ID for ' + item.name + '.', type: 'error' } }));
                            this.paying = false;
                            return;
                        }
                    }

                    // Persist any credential edits made on the cart page back to the DB.
                    if (topupItems.length > 0) {
                        try {
                            await Promise.all(topupItems.map(item =>
                                fetch(`/cart/${item.id}`, {
                                    method: 'PATCH',
                                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfToken },
                                    body: JSON.stringify({ topup_meta: this.topupCredentials[item.id] })
                                })
                            ));
                        } catch (e) {
                            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Failed to save credentials. Please try again.', type: 'error' } }));
                            this.paying = false;
                            return;
                        }
                    }

                    try {
                        const response = await fetch('/checkout', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfToken },
                            body: JSON.stringify({ user_discount_id: this.selectedDiscount?.id || null })
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            if (response.status === 422 && data.order_id) {
                                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message + ` <a href='/checkout/finish/${data.order_id}' class='underline font-black'>View Order</a>`, type: 'warning', duration: 8000 } }));
                            } else {
                                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Checkout failed.', type: 'error' } }));
                            }
                            this.paying = false;
                            return;
                        }

                        if (typeof window.snap === 'undefined') {
                            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment gateway not loaded. Please refresh.', type: 'error' } }));
                            this.paying = false;
                            return;
                        }

                        const csrfToken = this.csrfToken, orderId = data.order_id, self = this;
                        // Always reconcile with Midtrans (via verify) on any exit
                        // — including closing the popup with the X — so a completed
                        // payment is detected even when onSuccess never fires.
                        const reconcile = async function() {
                            try { await fetch('/checkout/verify/' + orderId, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } }); } catch(err) {}
                        };
                        window.snap.pay(data.snap_token, {
                            onSuccess: async function() { await reconcile(); self.paying = false; window.location.href = '/transactions'; },
                            onPending: async function() { await reconcile(); self.paying = false; window.location.href = '/transactions'; },
                            onError:   function() { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Payment failed.', type: 'error' } })); self.paying = false; },
                            onClose:   async function() { await reconcile(); self.paying = false; window.location.href = '/transactions'; }
                        });
                    } catch (e) {
                        this.paying = false;
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error.', type: 'error' } }));
                    }
                },

                formatRp(amount) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(amount)); }
            };
        }
    </script>
</x-app-layout>
