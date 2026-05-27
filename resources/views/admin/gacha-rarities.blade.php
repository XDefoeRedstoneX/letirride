@extends('admin.layouts.admin')

@section('title', 'Gacha Rarity Chances — Admin')

@section('content')
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
<div class="space-y-8" x-data="rarityForm({{ $rarities->toJson() }})">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Rarity <span class="text-primary">Chances</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Per-rarity base weights — must sum to exactly 100%</p>
        </div>
        <a href="{{ route('admin.gacha') }}" class="px-5 py-2.5 bg-foreground/5 border border-border rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">
            &larr; BACK TO POOL
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/40 text-green-400 px-4 py-3 rounded-xl text-xs font-bold">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-xs font-bold">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.gacha-rarities.update') }}" class="bg-card border border-border rounded-2xl overflow-hidden">
        @csrf @method('PATCH')
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest">Rarity Weights</h3>
            <span class="text-[10px] font-black uppercase tracking-widest"
                  :class="Math.abs(total - 100) < 0.01 ? 'text-green-500' : 'text-red-500'">
                Total: <span x-text="total.toFixed(2)"></span>%
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">Rarity</th>
                        <th class="px-5 py-3">Base Chance (%)</th>
                        <th class="px-5 py-3">Prizes</th>
                        <th class="px-5 py-3">Per Prize (preview)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($rarities as $row)
                        <tr>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $rarityStyles[$row['rarity']] ?? 'bg-foreground/5 text-muted-foreground' }}">{{ str_replace('_', ' ', $row['rarity']) }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <input type="number" step="0.01" min="0" max="100"
                                       name="chances[{{ $row['rarity'] }}]"
                                       x-model.number="values['{{ $row['rarity'] }}']"
                                       @input="recompute()"
                                       class="w-32 px-3 py-2 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                            </td>
                            <td class="px-5 py-3 text-xs">{{ $row['prize_count'] }}</td>
                            <td class="px-5 py-3 text-xs text-muted-foreground">
                                @if ($row['prize_count'] > 0)
                                    <span x-text="(values['{{ $row['rarity'] }}'] / {{ $row['prize_count'] }}).toFixed(2) + '%'"></span>
                                @else
                                    <span class="text-red-500">no prizes</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-border flex items-center justify-end gap-3">
            <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest mr-auto">
                Per-prize chance auto-derives from these values
            </p>
            <button type="submit"
                    :disabled="Math.abs(total - 100) > 0.01"
                    :class="Math.abs(total - 100) > 0.01 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary/90'"
                    class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest transition-colors">
                SAVE
            </button>
        </div>
    </form>
</div>

<script>
function rarityForm(initial) {
    const values = {};
    initial.forEach(r => { values[r.rarity] = Number(r.base_chance); });
    return {
        values,
        total: 0,
        init() { this.recompute(); },
        recompute() {
            this.total = Object.values(this.values).reduce((s, v) => s + (Number(v) || 0), 0);
        },
    };
}
</script>
@endsection
