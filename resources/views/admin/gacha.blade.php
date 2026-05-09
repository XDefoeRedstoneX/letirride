<x-admin::layouts.admin>
    <div class="space-y-8">
        <h1 class="text-3xl font-black tracking-tighter uppercase">Gacha Pool</h1>

        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Prize</th>
                        <th class="px-6 py-4">Discount</th>
                        <th class="px-6 py-4">Rarity</th>
                        <th class="px-6 py-4">Win Chance</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($pools as $pool)
                        <tr class="hover:bg-foreground/5 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono font-bold text-muted-foreground">#{{ $pool->id }}</td>
                            <td class="px-6 py-4 text-xs font-bold">{{ $pool->prize_name }}</td>
                            <td class="px-6 py-4 text-xs text-muted-foreground">{{ $pool->discountType?->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $rarityColors = [
                                        'common' => 'text-gray-400 bg-gray-500/10',
                                        'uncommon' => 'text-green-400 bg-green-500/10',
                                        'rare' => 'text-blue-400 bg-blue-500/10',
                                        'epic' => 'text-purple-400 bg-purple-500/10',
                                        'grand_prize' => 'text-yellow-400 bg-yellow-500/10',
                                        'legendary' => 'text-primary bg-primary/10',
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $rarityColors[$pool->rarity_item] ?? '' }}">{{ str_replace('_', ' ', $pool->rarity_item) }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold">{{ $pool->base_win_chance }}%</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.gacha.destroy', $pool) }}" onsubmit="return confirm('Delete this prize?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-500/10 text-red-500 rounded-lg text-[10px] font-black hover:bg-red-500/20 transition-colors">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts.admin>
