<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-12" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
        <div class="flex items-center justify-between" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-700" x-transition:enter-start="md:opacity-0 md:translate-y-4" x-transition:enter-end="md:opacity-100 md:translate-y-0">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase">Transaction <span class="text-primary">History</span></h1>
                <p class="text-muted-foreground font-medium">Keep track of your purchases and point redemptions.</p>
            </div>
        </div>

        @if($orders->count() > 0)
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
                            <th class="px-8 py-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($orders as $trx)
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-8 py-6 text-xs font-mono font-black text-muted-foreground group-hover:text-foreground transition-colors">{{ $trx['id'] }}</td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center p-2 group-hover:scale-110 transition-transform duration-500">
                                        <img src="{{ $trx['image'] }}" class="w-full h-full object-contain pixel-render" />
                                    </div>
                                    <span class="font-black text-sm group-hover:text-primary transition-colors">{{ $trx['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 font-black text-sm tracking-tight">Rp {{ number_format($trx['amount'], 0, ',', '.') }}</td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black tracking-widest border
                                    @if($trx['status'] === 'PAID') bg-green-500/10 text-green-500 border-green-500/20
                                    @elseif($trx['status'] === 'PENDING') bg-amber-500/10 text-amber-500 border-amber-500/20
                                    @else bg-red-500/10 text-red-500 border-red-500/20
                                    @endif">
                                    {{ $trx['status'] }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-[11px] font-bold text-muted-foreground">{{ $trx['date'] }}</td>
                            <td class="px-8 py-6">
                                <a href="{{ route('checkout.finish', $trx['order_id']) }}"
                                   class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-colors
                                       @if($trx['status'] === 'PENDING') bg-amber-500/10 text-amber-500 hover:bg-amber-500/20
                                       @else bg-foreground/5 text-foreground hover:bg-foreground/10
                                       @endif">
                                    {{ $trx['status'] === 'PENDING' ? 'Resume' : 'View' }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="py-20 text-center space-y-4">
            <div class="w-20 h-20 bg-card border border-border rounded-[2.5rem] flex items-center justify-center mx-auto text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/><path d="M2 12h20"/><path d="m7 7-5 5 5 5"/><path d="m17 7 5 5-5 5"/></svg>
            </div>
            <p class="text-muted-foreground font-black uppercase tracking-widest text-xs">No transactions yet.</p>
            <a href="{{ route('home') }}" class="inline-block mt-2 text-primary font-black uppercase tracking-widest text-[10px] hover:underline">Start Shopping</a>
        </div>
        @endif
    </div>
</x-app-layout>
