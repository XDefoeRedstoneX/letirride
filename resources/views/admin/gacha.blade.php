@extends('admin.layouts.admin')

@section('title', 'Gacha Pool — Admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Gacha <span class="text-primary">Pool</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage prizes, rarities & drop rates</p>
        </div>
        <button onclick="document.getElementById('addPrizeForm').classList.toggle('hidden')" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            Add Prize
        </button>
    </div>

    <!-- Add Prize Form -->
    <div id="addPrizeForm" class="hidden bg-card border border-border rounded-2xl p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest">New Prize</h3>
            <button onclick="document.getElementById('addPrizeForm').classList.add('hidden')" class="p-1 hover:bg-foreground/5 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.gacha.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Prize Name <span class="text-red-500">*</span></label>
                <input type="text" name="prize_name" required class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Discount Type <span class="text-red-500">*</span></label>
                <select name="discount_type_id" required class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="">Select discount</option>
                    @foreach(\App\Models\DiscountType::all() as $dt)
                        <option value="{{ $dt->id }}">{{ $dt->name }} ({{ $dt->type }}: {{ $dt->value }}{{ $dt->type === 'percent' ? '%' : '' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Rarity <span class="text-red-500">*</span></label>
                <select name="rarity_item" required class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="common">Common</option>
                    <option value="uncommon">Uncommon</option>
                    <option value="rare">Rare</option>
                    <option value="epic">Epic</option>
                    <option value="legendary">Legendary</option>
                    <option value="grand_prize">Grand Prize</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Win Chance (%) <span class="text-red-500">*</span></label>
                <input type="number" name="base_win_chance" required min="0" max="100" step="0.01" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div class="md:col-span-2 lg:col-span-4 flex justify-end">
                <button type="submit" class="px-8 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest">Add Prize</button>
            </div>
        </form>
    </div>

    <!-- Pool Prizes Table -->
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11V7a5 5 0 0 1 10 0v4"/><rect width="18" height="12" x="3" y="11" rx="2"/><circle cx="12" cy="17" r="1"/></svg>
                Prize Pool
                <span class="text-[10px] text-muted-foreground font-bold">({{ $pools->count() }} prizes)</span>
            </h3>
            @php
                $totalChance = $pools->sum('base_win_chance');
            @endphp
            <span class="text-[9px] font-black uppercase tracking-widest {{ $totalChance == 100 ? 'text-green-500' : 'text-red-500' }}">
                Total: {{ $totalChance }}%
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Prize</th>
                        <th class="px-5 py-3">Discount</th>
                        <th class="px-5 py-3">Rarity</th>
                        <th class="px-5 py-3">Win Chance</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @php
                        $rarityStyles = [
                            'common' => 'bg-gray-500/10 text-gray-400',
                            'uncommon' => 'bg-green-500/10 text-green-400',
                            'rare' => 'bg-blue-500/10 text-blue-400',
                            'epic' => 'bg-purple-500/10 text-purple-400',
                            'legendary' => 'bg-primary/10 text-primary',
                            'grand_prize' => 'bg-rose-500/10 text-rose-400',
                        ];
                    @endphp
                    @foreach($pools as $pool)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $pool->id }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $pool->prize_name }}</td>
                        <td class="px-5 py-3 text-xs text-muted-foreground">{{ $pool->discountType?->name ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $rarityStyles[$pool->rarity_item] ?? 'bg-foreground/5 text-muted-foreground' }}">{{ str_replace('_', ' ', $pool->rarity_item) }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $pool->base_win_chance }}%</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <button onclick="document.getElementById('editPrize{{ $pool->id }}').classList.toggle('hidden')" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <form method="POST" action="{{ route('admin.gacha.destroy', $pool) }}" onsubmit="return confirm('Delete {{ $pool->prize_name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                                </form>
                            </div>
                            <div id="editPrize{{ $pool->id }}" class="hidden mt-3 p-4 bg-foreground/5 rounded-xl border border-border">
                                <form method="POST" action="{{ route('admin.gacha.update', $pool) }}" class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                    @csrf @method('PATCH')
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Name</label>
                                        <input type="text" name="prize_name" value="{{ $pool->prize_name }}" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Discount</label>
                                        <select name="discount_type_id" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                            @foreach(\App\Models\DiscountType::all() as $dt)
                                                <option value="{{ $dt->id }}" {{ $pool->discount_type_id == $dt->id ? 'selected' : '' }}>{{ $dt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Rarity</label>
                                        <select name="rarity_item" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                            @foreach(['common','uncommon','rare','epic','legendary','grand_prize'] as $r)
                                                <option value="{{ $r }}" {{ $pool->rarity_item === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Chance %</label>
                                        <input type="number" name="base_win_chance" value="{{ $pool->base_win_chance }}" step="0.01" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                    </div>
                                    <div class="col-span-full">
                                        <button type="submit" class="px-5 py-2 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Save Changes</button>
                                        <button type="button" onclick="document.getElementById('editPrize{{ $pool->id }}').classList.add('hidden')" class="ml-2 px-5 py-2 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
