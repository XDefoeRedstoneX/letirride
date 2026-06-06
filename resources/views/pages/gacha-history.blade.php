<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8">
            {{-- Header --}}
            <div style="display:flex;flex-direction:column;gap:8px;text-align:center;">
                <h1 class="px-heading">My Gacha <span class="gold">History</span></h1>
                <p class="px-subheading">EVERY SPIN, EVERY PRIZE, EVERY PITY TICK</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Pity Snapshot --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" style="max-width:900px;margin:0 auto;">
                <div class="px-card-static" style="padding:18px;text-align:center;">
                    <p style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);">HARD PITY</p>
                    <p style="font-family:var(--font-sans);font-size:24px;font-weight:800;color:var(--foreground);margin-top:4px;">{{ $pity['pity_counter'] }}<span style="font-size:14px;color:var(--text-dim);"> / {{ $hardPityThreshold }}</span></p>
                    <p style="font-family:var(--font-sans);font-size:10px;color:var(--text-dim);margin-top:2px;">Epic+ guaranteed in {{ max(0, $hardPityThreshold - $pity['pity_counter']) }} spins</p>
                </div>
                <div class="px-card-static" style="padding:18px;text-align:center;">
                    <p style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);">MINI PITY</p>
                    <p style="font-family:var(--font-sans);font-size:24px;font-weight:800;color:var(--foreground);margin-top:4px;">{{ $pity['mini_pity_counter'] }}<span style="font-size:14px;color:var(--text-dim);"> / {{ $miniPityThreshold }}</span></p>
                    <p style="font-family:var(--font-sans);font-size:10px;color:var(--text-dim);margin-top:2px;">Uncommon+ in {{ max(0, $miniPityThreshold - $pity['mini_pity_counter']) }} spins</p>
                </div>
                <div class="px-card-static" style="padding:18px;text-align:center;">
                    <p style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);">TOTAL SPINS</p>
                    <p style="font-family:var(--font-sans);font-size:24px;font-weight:800;color:var(--foreground);margin-top:4px;">{{ number_format($pity['total_spins']) }}</p>
                    <p style="font-family:var(--font-sans);font-size:10px;color:var(--text-dim);margin-top:2px;">Free spins: {{ $pity['free_spins'] }}</p>
                </div>
            </div>

            {{-- History Table --}}
            <div class="px-card-static" style="padding:0;overflow:hidden;max-width:900px;margin:0 auto;width:100%;">
                @if($rows->total() === 0)
                    <div style="padding:48px 24px;text-align:center;">
                        <p style="font-family:var(--px);font-size:8px;letter-spacing:0.12em;color:var(--text-dim);">NO SPINS YET</p>
                        <p style="font-family:var(--font-sans);font-size:12px;color:var(--text-dim);margin-top:8px;">Try your luck on the carousel.</p>
                        <a href="{{ route('gacha') }}" class="px-btn-gold" style="display:inline-block;padding:12px 24px;margin-top:16px;font-size:8px;">GO TO GACHA</a>
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:rgba(245,158,11,0.05);border-bottom:2px solid var(--dark-line);">
                                    <th style="padding:12px 16px;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);text-align:left;">WHEN</th>
                                    <th style="padding:12px 16px;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);text-align:left;">PRIZE</th>
                                    <th style="padding:12px 16px;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);text-align:left;">RARITY</th>
                                    <th style="padding:12px 16px;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);text-align:left;">COST</th>
                                    <th style="padding:12px 16px;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);text-align:left;">PITY</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                    @php
                                        $pool = $row->gachaPool;
                                        $rarity = $pool?->rarity_item ?? 'common';
                                        $rarityColor = match ($rarity) {
                                            'grand_prize' => '#f43f5e',
                                            'legendary' => '#f59e0b',
                                            'epic' => '#a855f7',
                                            'rare' => '#3b82f6',
                                            'uncommon' => '#22c55e',
                                            default => '#9ca3af',
                                        };
                                    @endphp
                                    <tr style="border-bottom:1px solid var(--dark-line);">
                                        <td style="padding:12px 16px;font-family:var(--font-sans);font-size:11px;color:var(--text-dim);white-space:nowrap;">
                                            {{ $row->created_at?->format('Y-m-d H:i') ?? '—' }}
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                @include('partials.gacha-icon-tile', [
                                                    'iconSrc' => $row->image_path ?: \App\Support\GachaIconCatalog::fallbackPath(),
                                                    'rarity' => $rarity,
                                                    'label' => $pool?->prize_name ?? 'Prize',
                                                    'size' => 36,
                                                ])
                                                <div>
                                                    <p style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:var(--foreground);">{{ $pool?->prize_name ?? '—' }}</p>
                                                    @if($pool?->discountType?->name)
                                                        <p style="font-family:var(--font-sans);font-size:9px;color:var(--text-dim);">{{ $pool->discountType->name }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <span style="display:inline-block;padding:3px 8px;border:1px solid {{ $rarityColor }}40;background:{{ $rarityColor }}15;color:{{ $rarityColor }};font-family:var(--px);font-size:7px;letter-spacing:0.1em;">
                                                {{ strtoupper(str_replace('_', ' ', $rarity)) }}
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px;font-family:var(--font-sans);font-size:11px;color:var(--foreground);white-space:nowrap;">
                                            @if($row->cost_type === 'money')
                                                <span style="color:#22c55e;font-weight:800;">RP 15K</span>
                                            @else
                                                <span style="color:var(--gold);font-weight:800;">{{ number_format($row->points_spent) }} PTS</span>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @if($row->pity_triggered === 'hard')
                                                <span style="display:inline-block;padding:3px 8px;border:1px solid #f59e0b40;background:#f59e0b15;color:#f59e0b;font-family:var(--px);font-size:6px;letter-spacing:0.1em;">HARD</span>
                                            @elseif($row->pity_triggered === 'mini')
                                                <span style="display:inline-block;padding:3px 8px;border:1px solid #22c55e40;background:#22c55e15;color:#22c55e;font-family:var(--px);font-size:6px;letter-spacing:0.1em;">MINI</span>
                                            @else
                                                <span style="color:var(--text-dim);font-family:var(--font-sans);font-size:11px;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($rows->hasPages())
                        <div style="padding:16px;border-top:1px solid var(--dark-line);">
                            {{ $rows->withQueryString()->links() }}
                        </div>
                    @endif
                @endif
            </div>

            <div style="display:flex;justify-content:center;">
                <a href="{{ route('gacha') }}" class="px-btn-ghost" style="padding:14px 28px;font-size:8px;">&larr; BACK TO GACHA</a>
            </div>
        </div>
    </div>
</x-app-layout>
