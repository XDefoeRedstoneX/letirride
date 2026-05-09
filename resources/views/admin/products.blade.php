<x-admin::layouts.admin>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-black tracking-tighter uppercase">Products</h1>
        </div>

        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Points</th>
                        <th class="px-6 py-4">Keys</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($products as $product)
                        <tr class="hover:bg-foreground/5 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono font-bold text-muted-foreground">#{{ $product->id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-foreground/5 rounded-lg flex items-center justify-center p-1">
                                        <img src="/products/{{ $product->image ?? 'soundcloud.svg' }}" class="w-full h-full object-contain" />
                                    </div>
                                    <span class="text-xs font-bold">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-muted-foreground">{{ $product->category?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs font-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-xs font-bold text-yellow-500">{{ $product->point_reward }}</td>
                            <td class="px-6 py-4 text-xs font-bold">{{ $product->product_keys_count }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $product->is_active ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts.admin>
