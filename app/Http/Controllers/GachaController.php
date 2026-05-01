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
        $rand = mt_rand(1, 100); // Random float between 0 and 100
        $cumulative = 0;
        $wonPrize = null;
        $pricePull = 10; // Cost of one pull
        if ($user->points < $pricePull) {
            return back()->with('error', 'Not enough points to roll! You need at least ' . $pricePull . ' points.');
        }

        foreach ($prizes as $prize) {
            $cumulative += $prize->base_win_chance;
            if ($rand <= $cumulative) {
                $wonPrize = $prize;
                break;
            }
        }
        if ($wonPrize->is_grand_prize) {
            $user->save();
            return back()->with('success', "Congratulations! You won the grand prize: {$wonPrize->prize_name} and earned {$wonPrize->points_reward} points!");
        } else if (!$wonPrize->is_grand_prize) {
            $user->save();
            return back()->with('success', "You won: {$wonPrize->prize_name} and earned {$wonPrize->points_reward} points!");
        }

        return back()->with('error', 'Something went wrong. Please try again!');

    }
}
