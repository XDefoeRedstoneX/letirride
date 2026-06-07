@extends('admin.layouts.admin')

@section('content')
<div class="space-y-6 max-w-4xl">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-1.5 mb-1">
                <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Ridly Admin</span>
                <span class="text-muted-foreground/30 select-none">/</span>
                <span class="text-[9px] font-black uppercase tracking-widest">Database Sync</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight">Sync Control</h1>
            <p class="text-xs text-muted-foreground mt-1">
                Node <span class="font-mono font-bold">{{ $nodeId }}</span> ⇄ peer <span class="font-mono font-bold">{{ $remoteName }}</span>
            </p>
        </div>

        {{-- Live state pill --}}
        <div class="text-right">
            @if ($paused)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-500/10 text-amber-600 text-xs font-black uppercase tracking-widest">● Paused</span>
            @elseif ($enabled)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-600 text-xs font-black uppercase tracking-widest">● Active</span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-muted text-muted-foreground text-xs font-black uppercase tracking-widest">● Disabled</span>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="bg-primary/10 border border-primary/20 text-foreground rounded-xl px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    {{-- Controls --}}
    <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
        <h2 class="text-xs font-black uppercase tracking-widest text-muted-foreground mb-4">Controls</h2>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.sync.now') }}">
                @csrf
                <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-primary text-primary-foreground text-sm font-black uppercase tracking-widest hover:opacity-90 transition">
                    ⟳ Sync Now
                </button>
            </form>

            @if ($paused)
                <form method="POST" action="{{ route('admin.sync.resume') }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 text-sm font-black uppercase tracking-widest hover:bg-emerald-500/20 transition">
                        ▶ Resume
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.sync.pause') }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2.5 rounded-xl bg-amber-500/10 text-amber-600 text-sm font-black uppercase tracking-widest hover:bg-amber-500/20 transition">
                        ⏸ Pause
                    </button>
                </form>
            @endif
        </div>
        @unless ($enabled)
            <p class="text-[11px] text-muted-foreground mt-3">
                Continuous sync is off (<span class="font-mono">SYNC_ENABLED=false</span>). “Sync Now” still runs a one-off cycle.
            </p>
        @endunless
    </div>

    {{-- Status grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Peer</div>
            <div class="text-lg font-black mt-1 {{ $peerOnline ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $peerOnline ? 'Online' : 'Offline' }}
            </div>
        </div>
        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Pending Push</div>
            <div class="text-lg font-black mt-1">{{ $pendingPush }}</div>
        </div>
        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Pull Watermark</div>
            <div class="text-lg font-black mt-1">{{ $pullState->last_change_id ?? 0 }}</div>
        </div>
        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Last Sync</div>
            <div class="text-sm font-bold mt-1">{{ $pullState->last_synced_at ?? '—' }}</div>
        </div>
    </div>

    @unless ($peerOnline)
        <div class="bg-red-500/10 border border-red-500/20 text-red-700 rounded-xl px-4 py-3 text-xs font-mono">
            Peer unreachable: {{ $peerError }}
        </div>
    @endunless

    {{-- Recent conflicts --}}
    <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
        <h2 class="text-xs font-black uppercase tracking-widest text-muted-foreground mb-4">Recent Conflicts</h2>
        @if ($conflicts->isEmpty())
            <p class="text-sm text-muted-foreground">No conflicts recorded. 🎉</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-left text-muted-foreground uppercase tracking-widest text-[10px] font-black border-b border-border">
                            <th class="py-2 pr-4">Table</th>
                            <th class="py-2 pr-4">ULID</th>
                            <th class="py-2 pr-4">Winner</th>
                            <th class="py-2 pr-4">Loser</th>
                            <th class="py-2 pr-4">Reason</th>
                            <th class="py-2">When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($conflicts as $c)
                            <tr class="border-b border-border/40">
                                <td class="py-2 pr-4 font-mono">{{ $c->table_name }}</td>
                                <td class="py-2 pr-4 font-mono">{{ \Illuminate\Support\Str::limit($c->row_ulid, 12, '…') }}</td>
                                <td class="py-2 pr-4">{{ $c->winner_node }}</td>
                                <td class="py-2 pr-4">{{ $c->loser_node }}</td>
                                <td class="py-2 pr-4">{{ $c->reason }}</td>
                                <td class="py-2 text-muted-foreground">{{ $c->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
