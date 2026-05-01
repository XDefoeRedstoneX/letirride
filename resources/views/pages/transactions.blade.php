<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-12" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
        <div class="flex items-center justify-between" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-700" x-transition:enter-start="md:opacity-0 md:translate-y-4" x-transition:enter-end="md:opacity-100 md:translate-y-0">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase">Transaction <span class="text-primary">History</span></h1>
                <p class="text-muted-foreground font-medium">Keep track of your purchases and point redemptions.</p>
            </div>
            <button class="w-12 h-12 flex items-center justify-center rounded-2xl glass-card hover:bg-white/10 transition-all text-muted-foreground hover:text-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
            </button>
        </div>

        <div class="glass-card rounded-[2.5rem] overflow-hidden" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-200" x-transition:enter-start="md:opacity-0 md:scale-95" x-transition:enter-end="md:opacity-100 md:scale-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-foreground/5 text-[10px] font-black uppercase tracking-widest text-muted-foreground border-b border-white/5">
                            <th class="px-8 py-6">Transaction ID</th>
                            <th class="px-8 py-6">Product / Item</th>
                            <th class="px-8 py-6">Amount</th>
                            <th class="px-8 py-6">Status</th>
                            <th class="px-8 py-6">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @php
                            $trxs = [
                                ['id' => '#TRX-9921-001', 'name' => 'Steam Wallet Rp 100.000', 'amount' => 'Rp 110.000', 'status' => 'SUCCESS', 'date' => 'May 01, 2026', 'img' => '/products/steam-wallet.svg'],
                                ['id' => '#TRX-8812-045', 'name' => '5% Discount Voucher', 'amount' => '500 Points', 'status' => 'SUCCESS', 'date' => 'Apr 28, 2026', 'img' => '/gacha/voucher.svg'],
                                ['id' => '#TRX-7754-012', 'name' => 'Netflix Standard', 'amount' => 'Rp 186.000', 'status' => 'PENDING', 'date' => 'Apr 25, 2026', 'img' => '/products/netflix.svg'],
                            ];
                        @endphp
                        @foreach($trxs as $trx)
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-8 py-6 text-xs font-mono font-black text-muted-foreground group-hover:text-foreground transition-colors">{{ $trx['id'] }}</td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center p-2 group-hover:scale-110 transition-transform duration-500">
                                        <img src="{{ $trx['img'] }}" class="w-full h-full object-contain pixel-render" />
                                    </div>
                                    <span class="font-black text-sm group-hover:text-primary transition-colors">{{ $trx['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 font-black text-sm tracking-tight">{{ $trx['amount'] }}</td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black tracking-widest border {{ $trx['status'] === 'SUCCESS' ? 'bg-green-500/10 text-green-500 border-green-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20' }}">
                                    {{ $trx['status'] }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-[11px] font-bold text-muted-foreground">{{ $trx['date'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
