<x-admin::layouts.admin>
    <div class="space-y-8">
        <h1 class="text-3xl font-black tracking-tighter uppercase">Support Tickets</h1>

        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Message</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($tickets as $ticket)
                        <tr class="hover:bg-foreground/5 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono font-bold text-muted-foreground">#{{ $ticket->id }}</td>
                            <td class="px-6 py-4 text-xs font-bold">{{ $ticket->user?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-xs font-bold">{{ $ticket->type }}</td>
                            <td class="px-6 py-4 text-xs text-muted-foreground max-w-xs truncate">{{ $ticket->message }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest
                                    @if($ticket->status === 'open') bg-amber-500/10 text-amber-500
                                    @elseif($ticket->status === 'in_progress') bg-blue-500/10 text-blue-500
                                    @else bg-green-500/10 text-green-500
                                    @endif">{{ str_replace('_', ' ', $ticket->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-[10px] text-muted-foreground">{{ $ticket->created_at?->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}" class="flex gap-1">
                                    @csrf @method('PATCH')
                                    <select name="status" class="bg-foreground/5 border border-border rounded-lg px-2 py-1 text-[10px] font-bold outline-none">
                                        @foreach(['open', 'in_progress', 'closed'] as $s)
                                            <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1 bg-primary text-primary-foreground rounded-lg text-[10px] font-black">Save</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center">
            {{ $tickets->links() }}
        </div>
    </div>
</x-admin::layouts.admin>
