@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Direct <span class="text-primary">Top-Ups</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Fulfill direct top-up requests</p>
        </div>
        <div class="flex gap-1.5">
            <a href="{{ route('admin.topups') }}" class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all {{ !request('status') ? 'bg-primary text-primary-foreground' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }}">All</a>
            <a href="{{ route('admin.topups', ['status' => 'pending']) }}" class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all {{ request('status') === 'pending' ? 'bg-yellow-500 text-black' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }}">Pending</a>
            <a href="{{ route('admin.topups', ['status' => 'processing']) }}" class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all {{ request('status') === 'processing' ? 'bg-blue-500 text-white' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }}">Processing</a>
            <a href="{{ route('admin.topups', ['status' => 'sent']) }}" class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all {{ request('status') === 'sent' ? 'bg-green-500 text-white' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }}">Sent</a>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                    <tr>
                        <th class="px-5 py-3">Order</th>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Player ID</th>
                        <th class="px-5 py-3">Zone / Server</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Fulfilled</th>
                        <th class="px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topups as $topup)
                    <tr class="border-t border-border hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold">{{ $topup->orderDetail?->order?->noinv ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $topup->orderDetail?->product?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $topup->orderDetail?->order?->user?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs font-black text-primary">{{ $topup->player_id }}</td>
                        <td class="px-5 py-3 text-xs text-muted-foreground font-bold">{{ $topup->zone_id ?? '—' }} / {{ $topup->server_id ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest
                                @if($topup->topup_status === 'sent') bg-green-500/10 text-green-500
                                @elseif($topup->topup_status === 'processing') bg-blue-500/10 text-blue-500
                                @else bg-yellow-500/10 text-yellow-500
                                @endif">{{ $topup->topup_status }}</span>
                        </td>
                        <td class="px-5 py-3 text-[10px] text-muted-foreground">
                            @if($topup->fulfilled_at)
                                {{ $topup->fulfilled_at->format('M d, Y H:i') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <form action="{{ route('admin.topups.status', $topup) }}" method="POST" class="flex gap-1">
                                @csrf @method('PATCH')
                                <select name="topup_status" class="px-2 py-1.5 bg-background border border-border rounded-lg text-[10px] font-bold text-foreground outline-none">
                                    <option value="pending" {{ $topup->topup_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $topup->topup_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="sent" {{ $topup->topup_status === 'sent' ? 'selected' : '' }}>Sent</option>
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Update</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-xs font-bold text-muted-foreground uppercase tracking-widest">No top-up requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $topups->links() }}
    </div>
</div>
@endsection
