@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Support <span class="text-primary">Tickets</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage customer support requests</p>
        </div>
        <div class="flex gap-1.5">
            @foreach(['all' => 'All', 'open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed'] as $key => $label)
                <a href="{{ route('admin.tickets', $key === 'all' ? [] : ['status' => $key]) }}"
                   class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all
                          {{ (request('status') ?? 'all') === $key ? 'bg-primary text-primary-foreground' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Message</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($tickets as $ticket)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $ticket->id }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-foreground/10 flex items-center justify-center text-[10px] font-black uppercase shrink-0">
                                    {{ substr($ticket->user?->name ?? '??', 0, 2) }}
                                </div>
                                <span class="text-xs font-bold">{{ $ticket->user?->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground">{{ $ticket->type ?? 'General' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="max-w-xs">
                                <p class="text-xs text-muted-foreground truncate">{{ $ticket->message }}</p>
                                <button onclick="document.getElementById('ticketMsg{{ $ticket->id }}').classList.toggle('hidden')" class="text-[9px] text-primary font-black uppercase tracking-widest hover:underline">Read More</button>
                                <div id="ticketMsg{{ $ticket->id }}" class="hidden mt-2 p-3 bg-foreground/5 rounded-xl">
                                    <p class="text-xs text-muted-foreground">{{ $ticket->message }}</p>
                                    <div class="mt-3">
                                        <textarea rows="2" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none" placeholder="Type a reply..."></textarea>
                                        <button class="mt-1.5 px-4 py-1.5 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Send Reply</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest
                                @if($ticket->status === 'open') bg-amber-500/10 text-amber-500
                                @elseif($ticket->status === 'in_progress') bg-blue-500/10 text-blue-500
                                @else bg-green-500/10 text-green-500
                                @endif">{{ str_replace('_', ' ', $ticket->status) }}</span>
                        </td>
                        <td class="px-5 py-3 text-[10px] text-muted-foreground">{{ $ticket->created_at?->format('M d, Y') }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}" class="flex gap-1">
                                @csrf @method('PATCH')
                                <select name="status" class="bg-background border border-border rounded-lg px-2 py-1.5 text-[10px] font-bold text-foreground outline-none">
                                    @foreach(['open', 'in_progress', 'closed'] as $s)
                                        <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Update</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $tickets->links() }}
    </div>
</div>
@endsection
