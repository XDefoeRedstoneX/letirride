<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GachaPool;

class GachaController extends Controller
{
    public function showGacha()
    {
        return view('gacha');
    }

    public function roll(Request $request) {
        $user = Auth::user();
        $prizes = GachaPool::all();
        $totalChance = $prizes->sum('base_win_chance');
        $rand = mt_rand(1, $totalChance * 100) / 100; // Random float between 0 and totalChance


    }
}
