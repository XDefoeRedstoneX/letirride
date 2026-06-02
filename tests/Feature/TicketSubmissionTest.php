<?php

use App\Mail\TicketSubmittedMail;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => Mail::fake());

function ticketPayload(array $overrides = []): array
{
    return array_merge([
        'email' => 'guest@example.com',
        'name' => 'Guest Gary',
        'subject_choice' => 'Billing or payment issue',
        'message' => 'I was double charged for my order, please help.',
    ], $overrides);
}

it('lets a guest submit a ticket and emails the support inbox', function () {
    config(['support.admin_address' => 'support@ridly.example']);

    $this->post(route('tickets.store'), ticketPayload())
        ->assertRedirect(route('tickets'))
        ->assertSessionHas('ticket_submitted');

    $ticket = Ticket::first();
    expect($ticket)->not->toBeNull();
    expect($ticket->user_id)->toBeNull();
    expect($ticket->email)->toBe('guest@example.com');
    expect($ticket->status)->toBe('open');
    expect($ticket->type)->toBe('billing');
    expect($ticket->subject)->toBe('Billing or payment issue');

    Mail::assertSent(TicketSubmittedMail::class, function (TicketSubmittedMail $mail) use ($ticket) {
        return $mail->hasTo('support@ridly.example')
            && $mail->hasReplyTo('guest@example.com')
            && $mail->ticket->is($ticket);
    });
});

it('uses the account email for a logged-in user and ignores any posted email', function () {
    $user = User::factory()->create(['email' => 'member@example.com']);

    $this->actingAs($user)->post(route('tickets.store'), ticketPayload([
        'email' => 'attacker@evil.example',
    ]))->assertRedirect(route('tickets'));

    $ticket = Ticket::first();
    expect($ticket->user_id)->toBe($user->id);
    expect($ticket->email)->toBe('member@example.com');
});

it('stores the custom subject when Other is chosen', function () {
    $this->post(route('tickets.store'), ticketPayload([
        'subject_choice' => 'Other',
        'subject_other' => 'Partnership inquiry',
    ]))->assertRedirect(route('tickets'));

    expect(Ticket::first()->subject)->toBe('Partnership inquiry');
});

it('requires a custom subject when Other is chosen', function () {
    $this->post(route('tickets.store'), ticketPayload([
        'subject_choice' => 'Other',
        'subject_other' => '',
    ]))->assertSessionHasErrors('subject_other');

    expect(Ticket::count())->toBe(0);
});

it('requires an email from guests and a long-enough message', function () {
    $this->post(route('tickets.store'), ticketPayload(['email' => '']))
        ->assertSessionHasErrors('email');

    $this->post(route('tickets.store'), ticketPayload(['message' => 'too short']))
        ->assertSessionHasErrors('message');

    expect(Ticket::count())->toBe(0);
});

it('silently drops honeypot (bot) submissions without persisting or emailing', function () {
    $this->post(route('tickets.store'), ticketPayload(['website' => 'http://spam.example']))
        ->assertRedirect(route('tickets'))
        ->assertSessionHas('ticket_submitted');

    expect(Ticket::count())->toBe(0);
    Mail::assertNothingSent();
});

it('throttles excessive submissions from the same IP', function () {
    foreach (range(1, 3) as $i) {
        $this->post(route('tickets.store'), ticketPayload(['email' => "a{$i}@example.com"]))
            ->assertRedirect();
    }

    $this->post(route('tickets.store'), ticketPayload(['email' => 'a4@example.com']))
        ->assertStatus(429);
});
