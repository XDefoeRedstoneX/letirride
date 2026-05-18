<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('orders')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.users', ['users' => $users]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:buyer,admin',
            'points_balance' => 'required|integer|min:0',
        ]);

        $user->update([
            'role' => $request->role,
            'points_balance' => $request->points_balance,
        ]);

        return back()->with('success', $user->name.' updated.');
    }
}
