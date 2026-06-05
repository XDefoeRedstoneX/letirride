@extends('admin.layouts.admin')

@section('title', 'Referrals — Admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Referral <span class="text-primary">Program</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Tune rewards · monitor referral activity</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-xs font-bold">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Totals --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-card border border-border rounded-2xl p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Total Referrals</p>
            <p class="text-2xl font-black mt-1">{{ number_format($totals['total_referrals']) }}</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Rewarded</p>
            <p class="text-2xl font-black mt-1 text-green-500">{{ number_format($totals['rewarded']) }}</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Pending</p>
            <p class="text-2xl font-black mt-1 text-yellow-500">{{ number_format($totals['pending']) }}</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Voided</p>
            <p class="text-2xl font-black mt-1 text-red-500">{{ number_format($totals['void']) }}</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Points Paid</p>
            <p class="text-2xl font-black mt-1">{{ number_format($totals['total_points_paid']) }}</p>
        </div>
    </div>

    {{-- Config --}}
    <form method="POST" action="{{ route('admin.referrals.config') }}" class="bg-card border border-border rounded-2xl overflow-hidden">
        @csrf @method('PATCH')
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-sm font-black uppercase tracking-widest">Reward Config</h3>
            <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest mt-1">Tier A = first-purchase bonus · Tier B = ongoing commission (disabled when % = 0)</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">REFEREE WELCOME POINTS</label>
                <input type="number" name="referee_welcome_points" value="{{ old('referee_welcome_points', $config->referee_welcome_points) }}" min="0" max="100000" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                <p class="text-[10px] text-muted-foreground mt-1">Given to the new user when they claim a code.</p>
            </div>
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">REFERRER FIRST-PURCHASE BONUS</label>
                <input type="number" name="referrer_first_purchase_pts" value="{{ old('referrer_first_purchase_pts', $config->referrer_first_purchase_pts) }}" min="0" max="100000" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                <p class="text-[10px] text-muted-foreground mt-1">Paid to referrer on referee's first paid order.</p>
            </div>
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">COMMISSION %</label>
                <input type="number" step="0.01" name="commission_percent" value="{{ old('commission_percent', $config->commission_percent) }}" min="0" max="100" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                <p class="text-[10px] text-muted-foreground mt-1">Set 0 to disable Tier B entirely.</p>
            </div>
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">COMMISSION PER-ORDER CAP (PTS)</label>
                <input type="number" name="commission_per_order_cap" value="{{ old('commission_per_order_cap', $config->commission_per_order_cap) }}" min="0" max="1000000" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">COMMISSION LIFETIME CAP (PTS)</label>
                <input type="number" name="commission_lifetime_cap" value="{{ old('commission_lifetime_cap', $config->commission_lifetime_cap) }}" min="0" max="10000000" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                <p class="text-[10px] text-muted-foreground mt-1">Per referee, lifetime.</p>
            </div>
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">REFERRER MIN ACCOUNT AGE (HOURS)</label>
                <input type="number" name="referrer_min_account_age_h" value="{{ old('referrer_min_account_age_h', $config->referrer_min_account_age_h) }}" min="0" max="8760" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                <p class="text-[10px] text-muted-foreground mt-1">Anti-fraud: block payouts if referrer is younger than this.</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-border flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE CONFIG</button>
        </div>
    </form>

    {{-- Leaderboard --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-sm font-black uppercase tracking-widest">Top Referrers</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Code</th>
                        <th class="px-5 py-3">Referrals</th>
                        <th class="px-5 py-3">Rewarded</th>
                        <th class="px-5 py-3">Points Earned</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($leaderboard as $i => $row)
                        <tr class="hover:bg-foreground/5">
                            <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 text-xs font-bold">{{ $row->name }}</td>
                            <td class="px-5 py-3 text-xs font-mono">{{ $row->referral_code ?? '—' }}</td>
                            <td class="px-5 py-3 text-xs">{{ number_format((int) $row->referrals_count) }}</td>
                            <td class="px-5 py-3 text-xs text-green-500">{{ number_format((int) $row->rewarded_count) }}</td>
                            <td class="px-5 py-3 text-xs font-bold">{{ number_format((int) $row->points_earned) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-xs text-center text-muted-foreground">No referrers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent referrals --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-sm font-black uppercase tracking-widest">All Referrals</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Referrer</th>
                        <th class="px-5 py-3">Referee</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">First Order</th>
                        <th class="px-5 py-3">Commission Paid</th>
                        <th class="px-5 py-3">Joined</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($referrals as $ref)
                        <tr class="hover:bg-foreground/5">
                            <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $ref->id }}</td>
                            <td class="px-5 py-3 text-xs">{{ $ref->referrer?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-xs">{{ $ref->referredUser?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-xs">
                                @if ($ref->status === \App\Models\Referral::STATUS_FIRST_PURCHASE_REWARDED)
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-green-500/10 text-green-400">REWARDED</span>
                                @elseif ($ref->status === \App\Models\Referral::STATUS_VOID)
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-red-500/10 text-red-400">VOID</span>
                                @else
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-yellow-500/10 text-yellow-400">PENDING</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs">{{ $ref->first_purchase_order_id ? '#'.$ref->first_purchase_order_id : '—' }}</td>
                            <td class="px-5 py-3 text-xs">{{ number_format((int) $ref->total_commission_paid) }}</td>
                            <td class="px-5 py-3 text-xs text-muted-foreground">{{ $ref->created_at?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($ref->status !== \App\Models\Referral::STATUS_VOID)
                                    <form method="POST" action="{{ route('admin.referrals.void', $ref) }}" onsubmit="return confirm('Void this referral? Future commission payouts will stop. Already-paid points are kept.');">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Void</button>
                                    </form>
                                @else
                                    <span class="text-[9px] text-muted-foreground uppercase">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-8 text-xs text-center text-muted-foreground">No referrals recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($referrals->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $referrals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
