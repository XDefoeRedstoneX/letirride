<x-mail::message>
# We've received your message

Hi {{ $ticket->requesterName() }}, thanks for reaching out. We've logged your request and will get back to you as soon as possible.

<x-mail::panel>
{{ $ticket->message }}
</x-mail::panel>

| | |
|---|---|
| **Ticket #** | {{ $ticket->id }} |
| **Subject** | {{ $ticket->displaySubject() }} |
| **Category** | {{ ucfirst($ticket->type) }} |
| **Submitted** | {{ $ticket->created_at?->format('M d, Y H:i') }} |

Simply reply to this email if you'd like to add more details — we'll see it right away.

Thanks,<br>
{{ config('app.name') }} Support
</x-mail::message>
