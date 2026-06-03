<?php

namespace App\Http\Controllers;

use App\Mail\TicketSubmittedMail;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function show()
    {
        return view('pages.tickets');
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: bots fill the hidden "website" field. Pretend success, save nothing.
        if (filled($request->input('website'))) {
            return redirect()->route('tickets')->with('ticket_submitted', true);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'email'   => [Rule::requiredIf(! $user), 'nullable', 'email', 'max:255'],
            'name'    => ['nullable', 'string', 'max:120'],
            'subject' => ['required', 'string', 'min:3', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ], [
            'message.min' => 'Please give us a little more detail (at least 10 characters).',
        ]);

        $ticket = Ticket::create([
            'user_id' => $user?->id,
            'email'   => $user?->email ?? $validated['email'],
            'name'    => $user?->name ?? ($validated['name'] ?? null),
            'type'    => 'general',
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status'  => 'open',
            'ip_address' => $request->ip(),
        ]);

        $this->notifyCustomer($ticket);

        return redirect()->route('tickets')->with('ticket_submitted', true);
    }

    private function notifyCustomer(Ticket $ticket): void
    {
        try {
            Mail::to($ticket->email)->send(new TicketSubmittedMail($ticket));
        } catch (\Throwable $e) {
            Log::warning('Support ticket confirmation email failed for ticket #'.$ticket->id.': '.$e->getMessage());
        }
    }
}
