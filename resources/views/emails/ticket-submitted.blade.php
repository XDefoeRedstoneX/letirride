<x-mail::message>
# New Support Ticket #{{ $ticket->id }}

**{{ $ticket->displaySubject() }}**

<x-mail::panel>
{{ $ticket->message }}
</x-mail::panel>

| | |
|---|---|
| **From** | {{ $ticket->requesterName() }} |
| **Email** | {{ $ticket->email }} |
| **Account** | {{ $ticket->user_id ? '#'.$ticket->user_id : 'Guest (not logged in)' }} |
| **Category** | {{ ucfirst($ticket->type) }} |
| **Status** | {{ ucfirst(str_replace('_', ' ', $ticket->status)) }} |
| **Received** | {{ $ticket->created_at?->format('M d, Y H:i') }} |
| **IP** | {{ $ticket->ip_address ?? '—' }} |

<x-mail::button :url="config('app.url').'/admin/tickets'">
Open in Admin
</x-mail::button>

**Reply directly to this email** to respond to the customer — it goes straight to their inbox.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
