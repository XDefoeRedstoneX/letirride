<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">My Inventory</h1>
                <p class="text-muted-foreground">Manage your purchased items, vouchers, and gacha rewards.</p>
            </div>
            <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
            </div>
        </div>

        <!-- Inventory Tabs -->
        <div class="flex border-b border-border">
            <button class="px-6 py-3 text-sm font-bold border-b-2 border-primary text-primary">All Items</button>
            <button class="px-6 py-3 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Vouchers</button>
            <button class="px-6 py-3 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Products</button>
        </div>

        <!-- Inventory Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @php
                $items = [
                    ['name' => 'Steam Wallet Code', 'code' => 'PX-ST-1234-5678', 'type' => 'Product', 'date' => '2025-05-01', 'image' => '/products/steam-wallet.svg'],
                    ['name' => '10% Off Everything', 'code' => 'DISC10OFF', 'type' => 'Voucher', 'date' => '2025-04-28', 'image' => '/gacha/voucher.svg'],
                    ['name' => 'Spotify Premium Code', 'code' => 'PX-SP-8888-9999', 'type' => 'Product', 'date' => '2025-04-25', 'image' => '/products/spotify.svg'],
                ];
            @endphp

            @foreach($items as $item)
                <div class="bg-card border border-border rounded-2xl p-4 flex gap-4 items-center group hover:border-primary/30 transition-all">
                    <div class="w-16 h-16 bg-muted rounded-xl flex items-center justify-center p-2 shrink-0">
                        <img src="{{ $item['image'] }}" class="w-full h-full object-contain pixel-render" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-primary">{{ $item['type'] }}</span>
                            <span class="text-[10px] text-muted-foreground">{{ $item['date'] }}</span>
                        </div>
                        <h3 class="font-bold text-sm truncate">{{ $item['name'] }}</h3>
                        <div class="mt-2 flex items-center gap-2">
                            <code class="px-2 py-1 bg-muted rounded text-[10px] font-mono font-bold text-foreground/70">{{ $item['code'] }}</code>
                            <button class="p-1 hover:text-primary transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            </button>
                        </div>
                    </div>
                    <button class="p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground hover:text-foreground"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Empty State (Hidden) -->
        <div class="hidden py-20 text-center space-y-4">
            <div class="w-20 h-20 bg-muted rounded-full mx-auto flex items-center justify-center text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
            </div>
            <div>
                <h3 class="font-bold">Inventory is empty</h3>
                <p class="text-sm text-muted-foreground">You don't have any items yet. Start shopping or try gacha!</p>
            </div>
            <a href="{{ route('products') }}" class="inline-block px-6 py-2 bg-primary text-primary-foreground font-bold rounded-xl">Go to Shop</a>
        </div>
    </div>
</x-app-layout>
