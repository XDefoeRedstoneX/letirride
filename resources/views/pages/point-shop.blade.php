<x-app-layout>
    @auth
        <div class="space-y-8" x-data="{
            show: false,
            items: {{ \Illuminate\Support\Js::from($shopItems ?? []) }},
            userPoints: {{ Auth::user()->points_balance }},
            redeeming: null,
            confirmItem: null,
            csrfToken: '{{ csrf_token() }}',

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
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message, type: 'success' } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Failed.', type: 'error' } }));
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error.', type: 'error' } }));
                }

                this.redeeming = null;
            },

            formatNum(n) {
                return new Intl.NumberFormat('id-ID').format(n);
            }
        }" x-init="setTimeout(() => show = true, 50)">
            <!-- Header -->
            <div class="text-center md:text-left" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-500" x-transition:enter-start="md:opacity-0 md:translate-y-4" x-transition:enter-end="md:opacity-100 md:translate-y-0">
                <h1 class="text-4xl font-black tracking-tighter uppercase">Point <span class="text-primary">Shop</span></h1>
                <p class="text-muted-foreground font-medium">Exchange your hard-earned points for exclusive rewards and vouchers.</p>
            </div>

            <!-- User Points Card -->
            <div class="bg-gradient-to-r from-yellow-500/20 to-amber-500/20 border border-yellow-500/30 rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-6"
                 x-show="show" x-transition:enter="md:transition md:ease-out md:duration-500 md:delay-100" x-transition:enter-start="md:opacity-0 md:translate-y-4" x-transition:enter-end="md:opacity-100 md:translate-y-0">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-yellow-500/20 rounded-full flex items-center justify-center text-yellow-500 shadow-[0_0_20px_rgba(234,179,8,0.2)]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-widest">Available Points</p>
                        <p class="text-4xl font-black text-yellow-600 dark:text-yellow-500" x-text="formatNum(userPoints)"></p>
                    </div>
                </div>
            </div>

            <!-- Shop Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <template x-for="(item, index) in items" :key="item.id">
                    <div class="group glass-card rounded-[2rem] p-6 space-y-6 hover:shadow-2xl hover:shadow-primary/10 transition-all duration-500 hover:-translate-y-2"
                         x-show="show" x-transition:enter="md:transition md:ease-out md:duration-500" x-transition:enter-start="md:opacity-0 md:scale-95" x-transition:enter-end="md:opacity-100 md:scale-100"
                         :style="'transition-delay: ' + ((index + 3) * 100) + 'ms'">
                        <div class="aspect-square relative rounded-2xl bg-white/5 overflow-hidden flex items-center justify-center group-hover:bg-primary/5 transition-colors duration-500">
                            <img :src="item.image" class="w-24 h-24 object-contain group-hover:scale-110 group-hover:rotate-3 transition-transform duration-700 pixel-render" />
                            <div class="absolute top-3 right-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-[9px] font-black border border-white/20 uppercase tracking-widest" x-text="item.reward_type"></span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-black text-base leading-tight" x-text="item.name"></h3>
                            <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest leading-relaxed" x-text="item.description"></p>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Cost</span>
                                <div class="flex items-center gap-1.5 text-yellow-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                                    <span class="font-black text-lg" x-text="formatNum(item.point_cost)"></span>
                                </div>
                            </div>
                            <button @click="openConfirm(item)"
                                    :disabled="userPoints < item.point_cost || redeeming === item.id"
                                    :class="userPoints < item.point_cost ? 'opacity-50 cursor-not-allowed' : 'hover:scale-105 active:scale-95'"
                                    class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl font-black text-xs transition-all shadow-lg shadow-primary/20 tracking-widest uppercase">
                                <span x-text="redeeming === item.id ? 'Redeeming...' : (userPoints < item.point_cost ? 'Insufficient' : 'Redeem')"></span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="items.length === 0" class="py-20 text-center space-y-4">
                <p class="text-muted-foreground font-black uppercase tracking-widest text-xs">No items available in the shop right now.</p>
            </div>

            <!-- Confirmation Modal -->
            <div x-show="confirmItem" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click.self="closeConfirm()">
                <div class="bg-card border-2 border-primary/30 rounded-[2rem] p-8 max-w-sm w-full text-center space-y-6 shadow-2xl shadow-primary/20"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <!-- Icon -->
                    <div class="w-20 h-20 bg-yellow-500/10 rounded-full flex items-center justify-center mx-auto text-yellow-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                    </div>

                    <!-- Details -->
                    <div class="space-y-2">
                        <h3 class="text-xl font-black uppercase tracking-tight">Confirm Redemption</h3>
                        <p class="text-sm text-muted-foreground">Are you sure you want to redeem:</p>
                        <p class="text-lg font-black text-primary" x-text="confirmItem ? confirmItem.name : ''"></p>
                    </div>

                    <!-- Cost Summary -->
                    <div class="bg-foreground/5 border border-border rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-muted-foreground uppercase tracking-widest">Cost</span>
                            <span class="font-black text-yellow-500" x-text="confirmItem ? formatNum(confirmItem.point_cost) + ' PTS' : ''"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-muted-foreground uppercase tracking-widest">Balance After</span>
                            <span class="font-black" x-text="confirmItem ? formatNum(userPoints - confirmItem.point_cost) + ' PTS' : ''"></span>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button @click="closeConfirm()" class="flex-1 py-3.5 bg-foreground/5 border border-border text-foreground font-black rounded-xl hover:bg-foreground/10 transition-all uppercase tracking-widest text-[10px]">
                            Cancel
                        </button>
                        <button @click="confirmRedeem()" class="flex-1 py-3.5 bg-primary text-primary-foreground font-black rounded-xl hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/20 uppercase tracking-widest text-[10px]">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="min-h-[70vh] flex flex-col items-center justify-center text-center space-y-12 p-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
            <div class="space-y-6 max-w-xl" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000" x-transition:enter-start="md:opacity-0 md:scale-95 md:translate-y-12" x-transition:enter-end="md:opacity-100 md:scale-100 md:translate-y-0">
                <div class="w-24 h-24 bg-primary/10 rounded-[2.5rem] flex items-center justify-center text-primary mx-auto mb-8 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                </div>
                <h1 class="text-5xl font-black tracking-tighter leading-tight">Elevate Your Experience with <span class="text-primary">Points</span></h1>
                <p class="text-muted-foreground text-lg font-medium leading-relaxed">Join our community to start earning points from every transaction and redeem them for premium digital assets and exclusive vouchers.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 w-full max-w-md" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-300" x-transition:enter-start="md:opacity-0 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:translate-y-0">
                <button @click="$dispatch('open-auth-modal', { tab: 'login' })"
                        class="flex-1 px-8 py-5 bg-primary text-primary-foreground font-black rounded-[1.5rem] shadow-2xl shadow-primary/30 hover:scale-105 active:scale-95 transition-all text-sm tracking-widest uppercase">
                    Login to Access
                </button>
                <button @click="$dispatch('open-auth-modal', { tab: 'signup' })"
                        class="flex-1 px-8 py-5 glass-card font-black rounded-[1.5rem] hover:bg-white/20 hover:scale-105 active:scale-95 transition-all text-sm tracking-widest uppercase">
                    Create Account
                </button>
            </div>
        </div>
    @endauth
</x-app-layout>
