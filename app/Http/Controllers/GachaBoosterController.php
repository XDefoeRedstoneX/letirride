<?php

namespace App\Http\Controllers;

use App\Models\GachaBooster;
use App\Models\UserActiveBooster;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GachaBoosterController extends Controller
{
    /**
     * Activate (or top up) a luck booster for the authenticated user.
     * Boosters are charge-based: activating grants `rolls_granted` charges, one
     * consumed per spin while active.
     */
    public function activate(GachaBooster $booster): JsonResponse
    {
        if (! $booster->is_active) {
            return response()->json(['message' => 'This booster is not available.'], 422);
        }

        $user = Auth::user();

        if ($user->points_balance < $booster->point_cost) {
            return response()->json([
                'message' => 'Not enough points to activate '.$booster->name.'.',
            ], 422);
        }

        $active = DB::transaction(function () use ($user, $booster) {
            $user->decrement('points_balance', $booster->point_cost);

            $existing = UserActiveBooster::where('user_id', $user->id)
                ->where('gacha_booster_id', $booster->id)
                ->where('rolls_remaining', '>', 0)
                ->first();

            if ($existing) {
                $existing->rolls_remaining = ((int) $existing->rolls_remaining) + (int) $booster->rolls_granted;
                $existing->save();

                return $existing;
            }

            return UserActiveBooster::create([
                'user_id' => $user->id,
                'gacha_booster_id' => $booster->id,
                'rolls_remaining' => (int) $booster->rolls_granted,
            ]);
        });

        return response()->json([
            'message' => $booster->name.' activated.',
            'new_balance' => $user->fresh()->points_balance,
            'active_booster' => [
                'id' => $active->id,
                'booster_id' => $booster->id,
                'key' => $booster->key,
                'name' => $booster->name,
                'rarity_floor' => $booster->rarity_floor,
                'bonus_percent' => (float) $booster->bonus_percent,
                'rolls_remaining' => (int) $active->rolls_remaining,
            ],
        ]);
    }
}
