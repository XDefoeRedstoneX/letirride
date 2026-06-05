@extends('admin.layouts.admin')

@section('title', 'Gacha Pool — Admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    pool: {},
    icons: {{ Illuminate\Support\Js::from($icons->pluck('image_path', 'key')) }},
    rarityColors: { common: '#9ca3af', uncommon: '#22c55e', rare: '#3b82f6', epic: '#a855f7', legendary: '#f59e0b', grand_prize: '#f43f5e' },
    iconSrc(key) { return this.icons[key] || '/gacha-icons/coin.svg'; },
    rarityColor(r) { return this.rarityColors[r] || '#9ca3af'; },
    openAdd() {
        this.showAddModal = true;
    },
    openEdit(p) {
        this.pool = JSON.parse(JSON.stringify(p));
        this.showEditModal = true;
    },
    openDelete(p) {
        this.pool = p;
        this.showDeleteModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Gacha <span class="text-primary">Pool</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage prizes & rarities — per-prize odds auto-derived</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.gacha-rarities') }}" class="px-5 py-2.5 bg-foreground/5 border border-border rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">
                EDIT RARITY CHANCES
            </a>
            <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
                + ADD PRIZE
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/40 text-green-400 px-4 py-3 rounded-xl text-xs font-bold">{{ session('success') }}</div>
    @endif

    {{-- Rarity breakdown --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                Rarity Breakdown
            </h3>
            <span class="text-[9px] font-black uppercase tracking-widest {{ abs($rarityTotal - 100) < 0.01 ? 'text-green-500' : 'text-red-500' }}">
                Total: {{ rtrim(rtrim(number_format($rarityTotal, 2), '0'), '.') }}%
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">Rarity</th>
                        <th class="px-5 py-3">Base Chance</th>
                        <th class="px-5 py-3">Prizes</th>
                        <th class="px-5 py-3">Per Prize</th>
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
                    @foreach($rarityBreakdown as $row)
                        <tr class="hover:bg-foreground/5">
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $rarityStyles[$row['rarity']] ?? 'bg-foreground/5 text-muted-foreground' }}">{{ str_replace('_', ' ', $row['rarity']) }}</span>
                            </td>
                            <td class="px-5 py-3 text-xs font-bold">{{ rtrim(rtrim(number_format($row['base_chance'], 2), '0'), '.') }}%</td>
                            <td class="px-5 py-3 text-xs">{{ $row['prize_count'] }}</td>
                            <td class="px-5 py-3 text-xs text-muted-foreground">
                                @if ($row['prize_count'] > 0)
                                    {{ rtrim(rtrim(number_format($row['per_prize_chance'], 2), '0'), '.') }}%
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pool Prizes Table --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11V7a5 5 0 0 1 10 0v4"/><rect width="18" height="12" x="3" y="11" rx="2"/><circle cx="12" cy="17" r="1"/></svg>
                Prize Pool
                <span class="text-[10px] text-muted-foreground font-bold">({{ $pools->total() }} prizes)</span>
            </h3>
            <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">Per-prize odds = rarity chance ÷ prize count</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Coin</th>
                        <th class="px-5 py-3">Prize</th>
                        <th class="px-5 py-3">Discount</th>
                        <th class="px-5 py-3">Rarity</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($pools as $p)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $p->id }}</td>
                        <td class="px-5 py-3">
                            @include('partials.gacha-icon-tile', [
                                'iconSrc' => \App\Services\GachaIconResolver::resolve($p),
                                'rarity' => $p->rarity_item,
                                'label' => $p->prize_name,
                                'size' => 40,
                            ])
                        </td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $p->prize_name }}</td>
                        <td class="px-5 py-3 text-xs text-muted-foreground">{{ $p->discountType?->name ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $rarityStyles[$p->rarity_item] ?? 'bg-foreground/5 text-muted-foreground' }}">{{ str_replace('_', ' ', $p->rarity_item) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <button @click="openEdit({{ json_encode($p) }})" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <button @click="openDelete({{ json_encode($p) }})" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $pools->links() }}
    </div>

    <!-- Add Prize Modal -->
    <div x-show="showAddModal" @click.self="showAddModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8" style="display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW PRIZE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">ADD TO POOL</p>
                    </div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('admin.gacha.store') }}" class="flex flex-col gap-4"
                      x-data="{ formIconKey: '', formRarity: 'common', showAdvanced: false }">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">PRIZE NAME <span class="req">*</span></label>
                            <input type="text" name="prize_name" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="e.g. 10% Discount">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DISCOUNT TYPE</label>
                            <select name="discount_type_id" class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="">— None —</option>
                                @foreach(\App\Models\DiscountType::all() as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">RARITY <span class="req">*</span></label>
                            <select name="rarity_item" x-model="formRarity" required class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="common">Common</option>
                                <option value="uncommon">Uncommon</option>
                                <option value="rare">Rare</option>
                                <option value="epic">Epic</option>
                                <option value="legendary">Legendary</option>
                                <option value="grand_prize">Grand Prize</option>
                            </select>
                            <p class="text-[10px] text-muted-foreground mt-2">Win chance is auto-derived from the rarity's base chance, split evenly across all prizes in that rarity.</p>
                        </div>

                        {{-- Icon picker + live "mixmatcher" preview --}}
                        <div class="sm:col-span-2 flex items-end gap-4">
                            <div class="flex-1">
                                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ICON COIN</label>
                                <select name="icon_key" x-model="formIconKey" class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                                    @include('admin._gacha-icon-options')
                                </select>
                                <p class="text-[10px] text-muted-foreground mt-2">Pick a coin, or leave on Auto to pick by reward type / targeted brand.</p>
                            </div>
                            <span class="gacha-icon-tile" :style="'--rarity-color:' + rarityColor(formRarity) + '; width:56px; height:56px;'">
                                <img :src="iconSrc(formIconKey)" class="gacha-icon-tile-img pixel-render" alt="" />
                                <span class="gacha-card-rarity-wedge" aria-hidden="true"></span>
                            </span>
                        </div>

                        {{-- Advanced: full custom image override --}}
                        <div class="sm:col-span-2">
                            <button type="button" @click="showAdvanced = !showAdvanced" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                                <span x-text="showAdvanced ? '▾' : '▸'"></span> ADVANCED: CUSTOM IMAGE
                            </button>
                            <div x-show="showAdvanced" x-cloak class="mt-2">
                                <input type="text" name="image_path" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-mono text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="/gacha-icons/steam.svg or /storage/...">
                                <p class="text-[10px] text-muted-foreground mt-1">Overrides the coin above entirely. For one-off hero art.</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE PRIZE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Prize Modal -->
    <div x-show="showEditModal" @click.self="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8" style="display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT PRIZE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + pool.id"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ route('admin.gacha') }}/' + pool.id" class="flex flex-col gap-4"
                      x-data="{ showAdvanced: false }">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">PRIZE NAME <span class="req">*</span></label>
                            <input type="text" name="prize_name" x-model="pool.prize_name" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DISCOUNT TYPE</label>
                            <select name="discount_type_id" x-model="pool.discount_type_id" class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="">— None —</option>
                                @foreach(\App\Models\DiscountType::all() as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">RARITY <span class="req">*</span></label>
                            <select name="rarity_item" x-model="pool.rarity_item" required class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="common">Common</option>
                                <option value="uncommon">Uncommon</option>
                                <option value="rare">Rare</option>
                                <option value="epic">Epic</option>
                                <option value="legendary">Legendary</option>
                                <option value="grand_prize">Grand Prize</option>
                            </select>
                        </div>

                        {{-- Icon picker + live "mixmatcher" preview --}}
                        <div class="sm:col-span-2 flex items-end gap-4">
                            <div class="flex-1">
                                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ICON COIN</label>
                                <select name="icon_key" x-model="pool.icon_key" class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                                    @include('admin._gacha-icon-options')
                                </select>
                            </div>
                            <span class="gacha-icon-tile" :style="'--rarity-color:' + rarityColor(pool.rarity_item) + '; width:56px; height:56px;'">
                                <img :src="iconSrc(pool.icon_key)" class="gacha-icon-tile-img pixel-render" alt="" />
                                <span class="gacha-card-rarity-wedge" aria-hidden="true"></span>
                            </span>
                        </div>

                        {{-- Advanced: full custom image override --}}
                        <div class="sm:col-span-2">
                            <button type="button" @click="showAdvanced = !showAdvanced" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                                <span x-text="showAdvanced ? '▾' : '▸'"></span> ADVANCED: CUSTOM IMAGE
                            </button>
                            <div x-show="showAdvanced || (pool.image_path && pool.image_path.length)" x-cloak class="mt-2">
                                <input type="text" name="image_path" x-model="pool.image_path" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-mono text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="/gacha-icons/steam.svg or /storage/...">
                                <p class="text-[10px] text-muted-foreground mt-1">Overrides the coin above entirely. For one-off hero art.</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE PRIZE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Prize Modal -->
    <div x-show="showDeleteModal" @click.self="showDeleteModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8" style="display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <p class="text-sm font-sans text-foreground/70 dark:text-muted-foreground">Are you sure you want to delete <strong class="text-foreground" x-text="pool.prize_name"></strong>? This action cannot be undone.</p>
                <form method="POST" :action="'{{ route('admin.gacha') }}/' + pool.id" class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
