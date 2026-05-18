<x-app-layout>
    @auth
        <div class="px-page">
            <div class="px-page-inner space-y-8"
                 x-data="pointShopPage({{ \Illuminate\Support\Js::from($shopItems ?? []) }}, {{ Auth::user()->points_balance }}, '{{ csrf_token() }}')"
                 x-init="setTimeout(() => show = true, 50)">

                {{-- Header --}}
                <div>
                    <h1 class="px-heading">Point <span class="gold">Shop</span></h1>
                    <p class="px-subheading">EXCHANGE YOUR HARD-EARNED POINTS FOR EXCLUSIVE REWARDS</p>
                </div>
                <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

                {{-- Points Balance Card --}}
                <div class="px-card-static" style="padding:20px;display:flex;align-items:center;gap:16px;border-color:rgba(245,158,11,0.3);">
                    <div style="width:56px;height:56px;background:rgba(245,158,11,0.15);border:2px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                    </div>
                    <div>
                        <p style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);">AVAILABLE POINTS</p>
                        <p style="font-family:var(--font-sans);font-size:32px;font-weight:800;color:var(--gold);" x-text="formatNum(userPoints)"></p>
                    </div>
                </div>

                {{-- Shop Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="px-card" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                            <div style="aspect-ratio:1;background:var(--dark-card2);border:2px solid var(--dark-line);display:flex;align-items:center;justify-content:center;position:relative;">
                                <img :src="item.image" style="max-width:80px;max-height:80px;object-fit:contain;image-rendering:pixelated;" />
                                <span class="px-badge px-badge-gold" style="position:absolute;top:8px;right:8px;" x-text="item.reward_type"></span>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                <h3 style="font-family:var(--font-sans);font-size:14px;font-weight:800;color:#e8f0ff;" x-text="item.name"></h3>
                                <p style="font-family:var(--font-sans);font-size:11px;color:var(--text-dim);line-height:1.5;" x-text="item.description"></p>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:10px;border-top:2px solid var(--dark-line);">
                                <div>
                                    <p style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;color:var(--text-dim);">COST</p>
                                    <div style="display:flex;align-items:center;gap:4px;color:var(--gold);">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                                        <span style="font-family:var(--font-sans);font-size:18px;font-weight:800;" x-text="formatNum(item.point_cost)"></span>
                                    </div>
                                </div>
                                <button @click="openConfirm(item)"
                                        :disabled="userPoints < item.point_cost || redeeming === item.id"
                                        class="px-btn-gold" style="padding:10px 18px;font-size:6px;"
                                        :style="userPoints < item.point_cost ? 'opacity:0.4;cursor:not-allowed;' : ''">
                                    <span x-text="redeeming === item.id ? 'REDEEMING...' : (userPoints < item.point_cost ? 'INSUFFICIENT' : 'REDEEM')"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Empty State --}}
                <div x-show="items.length === 0" class="px-empty-state">
                    <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/></svg></div>
                    <p class="empty-text">NO ITEMS AVAILABLE RIGHT NOW</p>
                </div>

                {{-- Confirm Modal --}}
                <div x-show="confirmItem" class="px-modal-overlay"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     @click.self="closeConfirm()">
                    <div class="px-modal-box" style="text-align:center;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                        <div style="width:64px;height:64px;background:rgba(245,158,11,0.15);border:2px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);margin:0 auto 16px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                        </div>
                        <h3 style="font-family:var(--font-sans);font-size:18px;font-weight:800;color:#e8f0ff;">Confirm Redemption</h3>
                        <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);margin-top:6px;">Are you sure you want to redeem:</p>
                        <p style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--gold);margin-top:4px;" x-text="confirmItem ? confirmItem.name : ''"></p>

                        <div style="background:var(--dark-card2);border:2px solid var(--dark-line);padding:14px;margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                            <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;color:var(--text-dim);">COST</span><span style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:var(--gold);" x-text="confirmItem ? formatNum(confirmItem.point_cost) + ' PTS' : ''"></span></div>
                            <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--px);font-size:6px;letter-spacing:0.1em;color:var(--text-dim);">BALANCE AFTER</span><span style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#e8f0ff;" x-text="confirmItem ? formatNum(userPoints - confirmItem.point_cost) + ' PTS' : ''"></span></div>
                        </div>

                        <div style="display:flex;gap:8px;margin-top:16px;">
                            <button @click="closeConfirm()" class="px-btn-ghost" style="flex:1;padding:14px;">CANCEL</button>
                            <button @click="confirmRedeem()" class="px-btn-gold" style="flex:1;padding:14px;">CONFIRM</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="px-page">
            <div class="px-empty-state" style="min-height:70vh;">
                <div style="width:80px;height:80px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                </div>
                <h1 class="px-heading" style="text-align:center;">Elevate Your Experience with <span class="gold">Points</span></h1>
                <p style="font-family:var(--font-sans);font-size:14px;color:var(--text-dim);text-align:center;max-width:480px;line-height:1.7;">Join our community to start earning points from every transaction and redeem them for premium digital assets.</p>
                <div style="display:flex;gap:12px;">
                    <button @click="$dispatch('open-auth-modal', { tab: 'login' })" class="px-btn-gold" style="padding:16px 28px;font-size:8px;">LOGIN</button>
                    <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" class="px-btn-ghost" style="padding:16px 28px;font-size:8px;">SIGN UP</button>
                </div>
            </div>
        </div>
    @endauth

    <script>
    function pointShopPage(initialItems, initialPoints, csrfToken) {
        return {
            show: false,
            items: initialItems,
            userPoints: initialPoints,
            redeeming: null,
            confirmItem: null,
            csrfToken,

            openConfirm(item) {
                if (this.userPoints < item.point_cost) return;
                this.confirmItem = item;
            },

            closeConfirm() {
                this.confirmItem = null;
            },

            async confirmRedeem() {
                const item = this.confirmItem;
                if (!item || this.redeeming) return;
                this.redeeming = item.id;
                this.confirmItem = null;

                try {
                    const response = await fetch(`/point-shop/redeem/${item.id}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.userPoints = data.new_balance;
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: data.message, type: 'success' }
                        }));
                    } else {
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: data.message || 'Failed.', type: 'error' }
                        }));
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'Network error.', type: 'error' }
                    }));
                }
                this.redeeming = null;
            },

            formatNum(n) {
                return new Intl.NumberFormat('id-ID').format(n);
            }
        };
    }
    </script>
</x-app-layout>
