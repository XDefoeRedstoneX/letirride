@extends('admin.layouts.admin')
@section('title', 'Top-Up Management')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-black tracking-tighter uppercase">Direct <span class="text-primary">Top-Ups</span></h1>

        <div class="flex gap-2">
            <a href="{{ route('admin.topups') }}" class="px-4 py-2 text-xs font-black uppercase tracking-widest rounded-lg {{ !request('status') ? 'bg-primary text-primary-foreground' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }} transition-all">All</a>
            <a href="{{ route('admin.topups', ['status' => 'pending']) }}" class="px-4 py-2 text-xs font-black uppercase tracking-widest rounded-lg {{ request('status') === 'pending' ? 'bg-yellow-500 text-black' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }} transition-all">Pending</a>
            <a href="{{ route('admin.topups', ['status' => 'processing']) }}" class="px-4 py-2 text-xs font-black uppercase tracking-widest rounded-lg {{ request('status') === 'processing' ? 'bg-blue-500 text-white' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }} transition-all">Processing</a>
            <a href="{{ route('admin.topups', ['status' => 'sent']) }}" class="px-4 py-2 text-xs font-black uppercase tracking-widest rounded-lg {{ request('status') === 'sent' ? 'bg-green-500 text-white' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }} transition-all">Sent</a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-border">
        <table class="w-full text-left">
            <thead class="bg-foreground/5 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                <tr>
                    <th class="px-6 py-4">Order</th>
                    <th class="px-6 py-4">Product</th>
                    <th class="px-6 py-4">User</th>
                    <th class="px-6 py-4">Player ID</th>
                    <th class="px-6 py-4">Zone</th>
                    <th class="px-6 py-4">Server</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topups as $topup)
                <tr class="border-t border-border hover:bg-foreground/5 transition-colors">
                    <td class="px-6 py-4 text-xs font-black">{{ $topup->orderDetail?->order?->noinv ?? '—' }}</td>
                    <td class="px-6 py-4 text-xs font-bold">{{ $topup->orderDetail?->product?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-xs font-bold">{{ $topup->orderDetail?->order?->user?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-xs font-black text-primary">{{ $topup->player_id }}</td>
                    <td class="px-6 py-4 text-xs font-bold text-muted-foreground">{{ $topup->zone_id ?? '—' }}</td>
                    <td class="px-6 py-4 text-xs font-bold text-muted-foreground">{{ $topup->server_id ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @if($topup->topup_status === 'sent')
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-green-500/15 text-green-500">Sent</span>
                        @elseif($topup->topup_status === 'processing')
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-blue-500/15 text-blue-500">Processing</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-yellow-500/15 text-yellow-500">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('admin.topups.status', $topup) }}" method="POST" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="topup_status" class="px-3 py-1.5 bg-foreground/5 border border-border rounded-lg text-xs font-bold">
                                <option value="pending" {{ $topup->topup_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $topup->topup_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="sent" {{ $topup->topup_status === 'sent' ? 'selected' : '' }}>Sent</option>
                            </select>
                            <button type="submit" class="px-3 py-1.5 bg-primary text-primary-foreground rounded-lg text-[10px] font-black uppercase hover:scale-105 transition-all">Update</button>
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

    {{ $topups->links() }}
</div>
@endsection
