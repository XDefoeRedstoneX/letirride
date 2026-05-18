<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.tickets', ['tickets' => $tickets]);
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $ticket->update(['status' => $request->status]);

        return back()->with('success', 'Ticket status updated.');
    }
}
