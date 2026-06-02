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
    /**
     * Maps a chosen subject (or custom "Other" text) to a coarse category used
     * for admin filtering.
     */
    private const SUBJECT_TYPE_MAP = [
        'Billing or payment issue' => 'billing',
        'Order or delivery problem' => 'order',
        'Voucher or game key not working' => 'technical',
        'Account or login help' => 'account',
        'Gacha or rewards question' => 'gacha',
        'General question' => 'general',
    ];

    public function show()
    {
        return view('pages.tickets', [
            'subjects' => config('support.subjects', []),
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

        $type = self::SUBJECT_TYPE_MAP[$validated['subject_choice']] ?? 'general';

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

        $this->notifyAdmin($ticket);

        return redirect()->route('tickets')->with('ticket_submitted', true);
    }

    /**
     * Email the support inbox. Never let a mail hiccup fail the submission —
     * the ticket is already persisted and visible in the admin panel.
     */
    private function notifyAdmin(Ticket $ticket): void
    {
        try {
            Mail::to(config('support.admin_address'))->send(new TicketSubmittedMail($ticket));
        } catch (\Throwable $e) {
            Log::warning('Support ticket email failed for ticket #'.$ticket->id.': '.$e->getMessage());
        }
    }
}
