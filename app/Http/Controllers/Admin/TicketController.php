<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('q');

        $tickets = Ticket::with('user')
            ->status($status)
            ->search($search)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $rawCounts = Ticket::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'all'         => $rawCounts->sum(),
            'open'        => $rawCounts->get('open', 0),
            'in_progress' => $rawCounts->get('in_progress', 0),
            'closed'      => $rawCounts->get('closed', 0),
        ];

        return view('admin.tickets', [
            'tickets' => $tickets,
            'counts' => $counts,
            'activeStatus' => in_array($status, Ticket::STATUSES, true) ? $status : 'all',
            'search' => $search,
        ]);
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => ['required', Rule::in(Ticket::STATUSES)],
        ]);

        $ticket->update(['status' => $request->status]);

        return back()->with('success', 'Ticket status updated.');
    }
}
