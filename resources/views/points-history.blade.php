<x-app-layout>
    @auth
        <div class="px-page">
            <div class="px-page-inner space-y-8">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h1 class="px-heading">Points <span class="gold">History</span></h1>
                        <p class="px-subheading">TRACK EVERY POINT EARNED & SPENT</p>
                    </div>
                </div>

                <div class="px-divider">
                    <div class="px-divider-dot"></div>
                    <div class="px-divider-line"></div>
                    <div class="px-divider-dot"></div>
                </div>

                {{-- Summary --}}
                <div class="px-card-static" style="padding:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <div style="min-width:220px;flex:1;">
                        <p style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);">CURRENT POINTS</p>
                        <p style="font-family:var(--font-sans);font-size:32px;font-weight:800;color:var(--gold);">{{ number_format($currentBalance) }}</p>
                    </div>

                    <div style="min-width:220px;flex:1;border-left:2px solid var(--dark-line);padding-left:16px;">
                        <p style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);">TOTAL EARNED</p>
                        <p style="font-family:var(--font-sans);font-size:24px;font-weight:800;color:#22c55e;">+{{ number_format($totalEarned) }}</p>
                    </div>

                    <div style="min-width:220px;flex:1;border-left:2px solid var(--dark-line);padding-left:16px;">
                        <p style="font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);">TOTAL SPENT</p>
                        <p style="font-family:var(--font-sans);font-size:24px;font-weight:800;color:#ef4444;">-{{ number_format($totalSpent) }}</p>
                    </div>
                </div>

                {{-- Tabs: Earned / Spent --}}
                <div class="px-card" style="padding:20px;">
                    <div class="flex items-center gap-2 mb-4 flex-wrap">
                        <a href="#" data-tab="earned" class="px-tab px-tab-active" style="padding:10px 16px;font-size:6.5px;font-weight:800;">Points Earned (+)</a>
                        <a href="#" data-tab="spent" class="px-tab" style="padding:10px 16px;font-size:6.5px;font-weight:800;opacity:0.8;">Points Spent (-)</a>
                    </div>

                    <div id="points-tab-earned" class="tab-panel">
                        <div class="overflow-x-auto">
                            <table style="width:100%;border-collapse:separate;border-spacing:0;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);font-weight:800;padding:10px 6px;border-bottom:2px solid var(--dark-line);">DATE</th>
                                        <th style="text-align:left;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);font-weight:800;padding:10px 6px;border-bottom:2px solid var(--dark-line);">ACTIVITY</th>
                                        <th style="text-align:left;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);font-weight:800;padding:10px 6px;border-bottom:2px solid var(--dark-line);">CATEGORY</th>
                                        <th style="text-align:right;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);font-weight:800;padding:10px 6px;border-bottom:2px solid var(--dark-line);">POINTS EARNED</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($earnedActivities as $a)
                                        @php $val = (int) $a['points_change']; @endphp
                                        <tr>
                                            <td style="padding:12px 6px;border-bottom:1px solid rgba(255,255,255,0.06);">
                                                <span style="font-family:var(--font-sans);font-size:12px;font-weight:700;color:var(--text-dim);">{{ optional($a['date'])->format('M d, Y') }}</span>
                                            </td>
                                            <td style="padding:12px 6px;border-bottom:1px solid rgba(255,255,255,0.06);">
                                                <span style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:var(--foreground);">{{ $a['description'] }}</span>
                                            </td>
                                            <td style="padding:12px 6px;border-bottom:1px solid rgba(255,255,255,0.06);">
                                                <span style="font-family:var(--font-sans);font-size:12px;font-weight:700;color:var(--text-dim);">{{ $a['category'] }}</span>
                                            </td>
                                            <td style="padding:12px 6px;border-bottom:1px solid rgba(255,255,255,0.06);text-align:right;">
                                                <span style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:#22c55e;">+{{ number_format($val) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="padding:28px 6px;text-align:center;">
                                                <p class="px-text-dim" style="font-family:var(--font-sans);font-size:14px;">No point-earned activities found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="points-tab-spent" class="tab-panel" style="display:none;">
                        <div class="overflow-x-auto">
                            <table style="width:100%;border-collapse:separate;border-spacing:0;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);font-weight:800;padding:10px 6px;border-bottom:2px solid var(--dark-line);">DATE</th>
                                        <th style="text-align:left;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);font-weight:800;padding:10px 6px;border-bottom:2px solid var(--dark-line);">ACTIVITY</th>
                                        <th style="text-align:left;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);font-weight:800;padding:10px 6px;border-bottom:2px solid var(--dark-line);">CATEGORY</th>
                                        <th style="text-align:right;font-family:var(--px);font-size:7px;letter-spacing:0.12em;color:var(--text-dim);font-weight:800;padding:10px 6px;border-bottom:2px solid var(--dark-line);">POINTS SPENT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($spentActivities as $a)
                                        @php $val = (int) $a['points_change']; @endphp
                                        <tr>
                                            <td style="padding:12px 6px;border-bottom:1px solid rgba(255,255,255,0.06);">
                                                <span style="font-family:var(--font-sans);font-size:12px;font-weight:700;color:var(--text-dim);">{{ optional($a['date'])->format('M d, Y') }}</span>
                                            </td>
                                            <td style="padding:12px 6px;border-bottom:1px solid rgba(255,255,255,0.06);">
                                                <span style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:var(--foreground);">{{ $a['description'] }}</span>
                                            </td>
                                            <td style="padding:12px 6px;border-bottom:1px solid rgba(255,255,255,0.06);">
                                                <span style="font-family:var(--font-sans);font-size:12px;font-weight:700;color:var(--text-dim);">{{ $a['category'] }}</span>
                                            </td>
                                            <td style="padding:12px 6px;border-bottom:1px solid rgba(255,255,255,0.06);text-align:right;">
                                                <span style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:#ef4444;">{{ $val < 0 ? '' : '-' }}{{ number_format(abs($val)) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="padding:28px 6px;text-align:center;">
                                                <p class="px-text-dim" style="font-family:var(--font-sans);font-size:14px;">No point-spent activities found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Simple pagination (manual) shared for both tabs --}}
                    @if($total > $pageSize)
                        <div style="margin-top:16px;display:flex;justify-content:center;gap:8px;">
                            @php
                                $lastPage = (int) ceil($total / $pageSize);
                                $prevPage = max(1, $page - 1);
                                $nextPage = min($lastPage, $page + 1);
                            @endphp
                            <a href="{{ route('points.history', ['page' => $prevPage]) }}" class="px-btn-ghost" style="padding:10px 16px;font-size:6.5px;{{ $page <= 1 ? 'opacity:0.4;pointer-events:none;' : '' }}">PREV</a>
                            <span style="font-family:var(--font-sans);font-size:12px;font-weight:800;color:var(--text-dim);padding:10px 0;">Page {{ $page }} of {{ $lastPage }}</span>
                            <a href="{{ route('points.history', ['page' => $nextPage]) }}" class="px-btn-ghost" style="padding:10px 16px;font-size:6.5px;{{ $page >= $lastPage ? 'opacity:0.4;pointer-events:none;' : '' }}">NEXT</a>
                        </div>
                    @endif
                </div>

<script>
    // Minimal local tab switcher (does not require Bootstrap JS)
    (function () {
        const earned = document.getElementById('points-tab-earned');
        const spent = document.getElementById('points-tab-spent');
        const links = Array.from(document.querySelectorAll('[data-tab]'));
        links.forEach(l => {
            l.addEventListener('click', (e) => {
                e.preventDefault();
                const tab = l.getAttribute('data-tab');
                if (!earned || !spent) return;
                const isEarn = tab === 'earned';
                earned.style.display = isEarn ? '' : 'none';
                spent.style.display = isEarn ? 'none' : '';
                links.forEach(x => x.classList.remove('px-tab-active'));
                l.classList.add('px-tab-active');
            });
        });
    })();
</script>



            </div>
        </div>
    @else
        <div class="px-page" x-data="{}">
            <div class="px-empty-state" style="min-height:70vh;">
                <h1 class="px-heading" style="text-align:center;">Please login to view your points history</h1>
            </div>
        </div>
    @endauth
</x-app-layout>

