<x-mail::message>
# Your ticket has been received!

Hi {{ $ticket->requesterName() }},

Thank you for reaching out to {{ config('app.name') }} Support. We've received your message and our customer service team will get back to you as soon as possible.

In the meantime, please wait patiently — there's no need to submit another ticket for the same issue.

<x-mail::panel>
**Your message:**

{{ $ticket->message }}
</x-mail::panel>

| | |
|---|---|
| **Ticket #** | #{{ $ticket->id }} |
| **Subject** | {{ $ticket->displaySubject() }} |
| **Category** | {{ ucfirst($ticket->type) }} |
| **Submitted** | {{ $ticket->created_at?->format('M d, Y \a\t H:i') }} UTC |

Thanks for your patience,<br>
{{ config('app.name') }} Support Team
</x-mail::message>
