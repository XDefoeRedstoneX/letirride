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
        return view('pages.tickets', [
            'subjects' => array_keys(config('support.subjects', [])),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: bots fill the hidden "website" field. Pretend success, save nothing.
        if (filled($request->input('website'))) {
            return redirect()->route('tickets')->with('ticket_submitted', true);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'email' => [Rule::requiredIf(! $user), 'nullable', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:120'],
            'subject_choice' => ['required', 'string', 'max:120'],
            'subject_other' => ['nullable', 'required_if:subject_choice,Other', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ], [
            'subject_other.required_if' => 'Please describe your subject.',
            'message.min' => 'Please give us a little more detail (at least 10 characters).',
        ]);

        $subject = $validated['subject_choice'] === 'Other'
            ? $validated['subject_other']
            : $validated['subject_choice'];

        $type = config('support.subjects')[$validated['subject_choice']] ?? 'general';

        $ticket = Ticket::create([
            'user_id' => $user?->id,
            'email' => $user?->email ?? $validated['email'],
            'name' => $user?->name ?? ($validated['name'] ?? null),
            'type' => $type,
            'subject' => $subject,
            'message' => $validated['message'],
            'status' => 'open',
            'ip_address' => $request->ip(),
        ]);

        $this->notifyCustomer($ticket);

        return redirect()->route('tickets')->with('ticket_submitted', true);
    }

    /**
     * Send a confirmation email to the customer. Never let a mail hiccup fail
     * the submission — the ticket is already persisted in the admin panel.
     */
    private function notifyCustomer(Ticket $ticket): void
    {
        try {
            Mail::to($ticket->email)->send(new TicketSubmittedMail($ticket));
        } catch (\Throwable $e) {
            Log::warning('Support ticket confirmation email failed for ticket #'.$ticket->id.': '.$e->getMessage());
        }
    }
}
