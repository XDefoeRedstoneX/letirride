@extends('admin.layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Support <span class="text-primary">Tickets</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Reply by email — each ticket links straight to the customer's inbox</p>
        </div>
        <form method="GET" action="{{ route('admin.tickets') }}" class="flex gap-1.5">
            @if($activeStatus !== 'all')<input type="hidden" name="status" value="{{ $activeStatus }}">@endif
            <input type="text" name="q" value="{{ $search }}" placeholder="Search subject, message, email…"
                   class="px-3 py-2 bg-foreground/5 border border-border rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary w-56">
            <button type="submit" class="px-4 py-2 bg-foreground/5 border border-border rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary">Search</button>
        </form>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/40 text-green-400 px-4 py-3 rounded-xl text-xs font-bold">{{ session('success') }}</div>
    @endif

    {{-- Status filter chips with counts --}}
    <div class="flex gap-1.5 flex-wrap">
        @foreach(['all' => 'All', 'open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed'] as $key => $label)
            <a href="{{ route('admin.tickets', array_filter(['status' => $key === 'all' ? null : $key, 'q' => $search])) }}"
               class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all
                      {{ $activeStatus === $key ? 'bg-primary text-primary-foreground' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }}">
                {{ $label }} <span class="opacity-60">({{ $counts[$key] ?? 0 }})</span>
            </a>
        @endforeach
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Requester</th>
                        <th class="px-5 py-3">Subject &amp; message</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($tickets as $ticket)
                    @php
                        $mailtoBody = "Hi ".$ticket->requesterName().",\n\n\n\n-----\nYour message:\n".$ticket->message;
                        $mailto = 'mailto:'.$ticket->email
                            .'?subject='.rawurlencode('Re: [Ridly Support #'.$ticket->id.'] '.$ticket->displaySubject())
                            .'&body='.rawurlencode($mailtoBody);
                    @endphp
                    <tr class="hover:bg-foreground/5 align-top">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $ticket->id }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-foreground/10 flex items-center justify-center text-[10px] font-black uppercase shrink-0">
                                    {{ strtoupper(substr($ticket->requesterName(), 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold truncate">{{ $ticket->requesterName() }}</p>
                                    <a href="mailto:{{ $ticket->email }}" class="text-[10px] text-muted-foreground hover:text-primary truncate block">{{ $ticket->email }}</a>
                                    <span class="text-[8px] font-black uppercase tracking-widest text-muted-foreground/70">{{ $ticket->user_id ? 'Member' : 'Guest' }} · {{ $ticket->type }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="max-w-sm">
                                <p class="text-xs font-bold text-foreground">{{ $ticket->displaySubject() }}</p>
                                <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">{{ $ticket->message }}</p>
                                <button type="button" onclick="document.getElementById('ticketMsg{{ $ticket->id }}').classList.toggle('hidden')" class="text-[9px] text-primary font-black uppercase tracking-widest hover:underline mt-1">Read full</button>
                                <div id="ticketMsg{{ $ticket->id }}" class="hidden mt-2 p-3 bg-foreground/5 rounded-xl">
                                    <p class="text-xs text-muted-foreground whitespace-pre-line">{{ $ticket->message }}</p>
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
                        <td class="px-5 py-3 text-[10px] text-muted-foreground whitespace-nowrap">{{ $ticket->created_at?->format('M d, Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-col gap-1.5">
                                <a href="{{ $mailto }}" class="px-3 py-1.5 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest text-center hover:bg-primary/90">Reply via email</a>
                                <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}" class="flex gap-1">
                                    @csrf @method('PATCH')
                                    <select name="status" class="bg-background border border-border rounded-lg px-2 py-1.5 text-[10px] font-bold text-foreground outline-none">
                                        @foreach(['open', 'in_progress', 'closed'] as $s)
                                            <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 bg-foreground/10 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-foreground/20">Set</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-xs text-muted-foreground">No tickets match this view.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $tickets->links() }}
    </div>
</div>
@endsection
