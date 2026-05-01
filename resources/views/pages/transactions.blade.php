<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Transaction History</h1>
                <p class="text-muted-foreground">Keep track of your purchases and point redemptions.</p>
            </div>
            <button class="p-2 bg-card border border-border rounded-xl hover:bg-accent transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
            </button>
        </div>

        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-muted/50 text-[10px] font-black uppercase tracking-widest text-muted-foreground border-b border-border">
                            <th class="px-6 py-4">Transaction ID</th>
                            <th class="px-6 py-4">Product / Item</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr class="hover:bg-accent/30 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono">#TRX-9921-001</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-primary/10 flex items-center justify-center p-1">
                                        <img src="/products/steam-wallet.svg" class="w-full h-full object-contain pixel-render" />
                                    </div>
                                    <span class="font-bold text-sm">Steam Wallet Rp 100.000</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-sm">Rp 110.000</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full bg-green-500/10 text-green-500 text-[10px] font-bold border border-green-500/20">SUCCESS</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-muted-foreground">May 01, 2025</td>
                        </tr>
                        <tr class="hover:bg-accent/30 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono">#TRX-8812-045</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-yellow-500/10 flex items-center justify-center p-1">
                                        <img src="/gacha/voucher.svg" class="w-full h-full object-contain pixel-render" />
                                    </div>
                                    <span class="font-bold text-sm">5% Discount Voucher</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-sm">500 Points</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full bg-green-500/10 text-green-500 text-[10px] font-bold border border-green-500/20">SUCCESS</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-muted-foreground">Apr 28, 2025</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
