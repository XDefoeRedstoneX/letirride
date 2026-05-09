<?php

namespace App\Http\Controllers;

use App\Models\GachaHistory;
use App\Models\GachaPool;
use App\Models\UserDiscount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GachaController extends Controller
{
    private const SPIN_COST = 200;

    /**
     * Show the gacha page with server-provided prize pool.
     */
    public function showGacha()
    {
        $prizes = GachaPool::with('discountType')
            ->orderByDesc('base_win_chance')
            ->get()
            ->map(fn (GachaPool $pool) => [
                'id' => $pool->id,
                'name' => $pool->prize_name,
                'rarity' => $pool->rarity_item,
                'rate' => (float) $pool->base_win_chance,
                'discount_name' => $pool->discountType?->name ?? '',
                'image' => $this->imageForRarity($pool->rarity_item),
            ]);

        return view('pages.gacha', [
            'prizes' => $prizes,
            'spinCost' => self::SPIN_COST,
        ]);
    }

    /**
     * Server-side spin — weighted RNG, deduct points, create voucher.
     */
    public function roll(): JsonResponse
    {
        $user = Auth::user();

        if ($user->points_balance < self::SPIN_COST) {
            return response()->json([
                'message' => 'Not enough points! You need at least '.self::SPIN_COST.' points.',
            ], 422);
        }

        $prizes = GachaPool::all();

        if ($prizes->isEmpty()) {
            return response()->json(['message' => 'No prizes available.'], 404);
        }

        // Server-side weighted RNG
        $totalWeight = $prizes->sum('base_win_chance');
        $random = mt_rand(0, (int) ($totalWeight * 100)) / 100;
        $cumulative = 0;
        $wonPrize = null;

        foreach ($prizes as $prize) {
            $cumulative += $prize->base_win_chance;
            if ($random <= $cumulative) {
                $wonPrize = $prize;

                break;
            }
        }

        // Fallback to last prize if none matched (float precision)
        if (! $wonPrize) {
            $wonPrize = $prizes->last();
        }

        DB::beginTransaction();

        try {
            // Deduct points
            $user->decrement('points_balance', self::SPIN_COST);

            // Record history
            GachaHistory::create([
                'user_id' => $user->id,
                'gacha_pool_id' => $wonPrize->id,
                'points_spent' => self::SPIN_COST,
            ]);

            // Create discount voucher for the user
            if ($wonPrize->discount_type_id) {
                UserDiscount::create([
                    'user_id' => $user->id,
                    'discount_type_id' => $wonPrize->discount_type_id,
                    'is_used' => false,
                    'obtained_from' => 'gacha',
                    'expires_at' => now()->addDays(14),
                ]);
            }

            DB::commit();

            return response()->json([
                'prize' => [
                    'id' => $wonPrize->id,
                    'name' => $wonPrize->prize_name,
                    'rarity' => $wonPrize->rarity_item,
                    'image' => $this->imageForRarity($wonPrize->rarity_item),
                ],
                'new_balance' => $user->fresh()->points_balance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Spin failed. Please try again.'], 500);
        }
    }

    /**
     * Map rarity to an image path.
     */
    private function imageForRarity(string $rarity): string
    {
        return match ($rarity) {
            'legendary' => '/gacha/jackpot.svg',
            'grand_prize' => '/gacha/jackpot.svg',
            'epic' => '/gacha/voucher.svg',
            'rare' => '/gacha/voucher.svg',
            'uncommon' => '/gacha/points.svg',
            default => '/gacha/points.svg',
        };
    }
}
