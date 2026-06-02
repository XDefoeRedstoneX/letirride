<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket) {}

    public function envelope(): Envelope
    {
        // Reply-To is the customer, so the admin can answer from their own
        // mail client and the reply lands directly in the customer's inbox.
        return new Envelope(
            subject: "[Ridly Support #{$this->ticket->id}] ".$this->ticket->displaySubject(),
            replyTo: [new Address($this->ticket->email, $this->ticket->requesterName())],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ticket-submitted',
            with: ['ticket' => $this->ticket],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
