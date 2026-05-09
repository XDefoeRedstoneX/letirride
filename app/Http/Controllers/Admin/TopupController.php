<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopupCredential;
use Illuminate\Http\Request;

class TopupController extends Controller
{
    public function index(Request $request)
    {
        $query = TopupCredential::with(['orderDetail.product', 'orderDetail.order.user'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('topup_status', $request->status);
        }

        $topups = $query->paginate(20);

        return view('admin.topups', ['topups' => $topups]);
    }

    public function updateStatus(Request $request, TopupCredential $topup)
    {
        $request->validate([
            'topup_status' => 'required|in:pending,processing,sent',
        ]);

        $data = ['topup_status' => $request->topup_status];

        if ($request->topup_status === 'sent') {
            $data['fulfilled_at'] = now();
        }

        $topup->update($data);

        return back()->with('success', 'Top-up status updated to '.$request->topup_status.'.');
    }
}
