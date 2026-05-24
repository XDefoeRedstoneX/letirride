<?php

namespace App\Http\Controllers;

use App\Models\GachaBooster;
use App\Models\GachaHistory;
use App\Models\GachaPool;
use App\Services\GachaRollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class GachaController extends Controller
{
    public const SPIN_COST_POINTS = 200;

    public function __construct(private readonly GachaRollService $roller) {}

    /**
     * Show the gacha page with server-provided prize pool + per-user pity state.
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
                'reward_type' => $pool->reward_type,
                'points_amount' => $pool->points_amount,
                'rate' => (float) $pool->base_win_chance,
                'discount_name' => $pool->discountType?->name ?? '',
                'image' => $this->resolveImage($pool),
            ]);

        $snapshot = Auth::check() ? $this->roller->snapshotFor(Auth::user()) : null;

        $boosters = GachaBooster::where('is_active', true)
            ->orderBy('point_cost')
            ->get()
            ->map(fn (GachaBooster $b) => [
                'id' => $b->id,
                'key' => $b->key,
                'name' => $b->name,
                'description' => $b->description,
                'point_cost' => (int) $b->point_cost,
                'rarity_floor' => $b->rarity_floor,
                'bonus_percent' => (float) $b->bonus_percent,
                'duration_minutes' => (int) $b->duration_minutes,
            ]);

        return view('pages.gacha', [
            'prizes' => $prizes,
            'spinCost' => self::SPIN_COST_POINTS,
            'midtransClientKey' => config('midtrans.client_key'),
            'midtransIsProduction' => config('midtrans.is_production'),
            'paidSpinPrice' => GachaPaymentController::SPIN_PRICE,
            'pity' => $snapshot,
            'boosters' => $boosters,
            'hardPityThreshold' => GachaRollService::HARD_PITY_THRESHOLD,
            'miniPityThreshold' => GachaRollService::MINI_PITY_THRESHOLD,
        ]);
    }

    /**
     * Points-only / free-spin roll. Money rolls go through GachaPaymentController.
     */
    public function roll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cost_type' => 'sometimes|in:points,free_spin',
        ]);

        $costType = $validated['cost_type'] ?? 'points';
        $user = Auth::user();

        if ($costType === 'points' && $user->points_balance < self::SPIN_COST_POINTS) {
            return response()->json([
                'message' => 'Not enough points! You need at least '.self::SPIN_COST_POINTS.' points.',
            ], 422);
        }

        if ($costType === 'free_spin') {
            $state = $this->roller->snapshotFor($user);
            if ($state['free_spins'] < 1) {
                return response()->json(['message' => 'No free spin tokens available.'], 422);
            }
        }

        try {
            $result = DB::transaction(function () use ($user, $costType) {
                $pointsSpent = 0;

                if ($costType === 'points') {
                    $user->decrement('points_balance', self::SPIN_COST_POINTS);
                    $pointsSpent = self::SPIN_COST_POINTS;
                } else {
                    $user->gachaState()->decrement('free_spins');
                }

                $outcome = $this->roller->roll($user);
                $prize = $outcome['prize'];

                $this->roller->dispatchReward($user, $prize);

                GachaHistory::create([
                    'user_id' => $user->id,
                    'gacha_pool_id' => $prize->id,
                    'points_spent' => $pointsSpent,
                    'cost_type' => 'points',
                    'pity_triggered' => $outcome['pity_triggered'],
                    'reward_type' => $prize->reward_type,
                    'image_path' => $this->resolveImage($prize),
                ]);

                return [
                    'prize' => $prize,
                    'pity_triggered' => $outcome['pity_triggered'],
                ];
            });
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Spin failed. Please try again.'], 500);
        }

        $fresh = $user->fresh();
        $snapshot = $this->roller->snapshotFor($fresh);
        $prize = $result['prize'];

        return response()->json([
            'prize' => [
                'id' => $prize->id,
                'name' => $prize->prize_name,
                'rarity' => $prize->rarity_item,
                'reward_type' => $prize->reward_type,
                'points_amount' => $prize->points_amount,
                'image' => $this->resolveImage($prize),
                'discount_name' => $prize->discountType?->name ?? '',
            ],
            'pity_triggered' => $result['pity_triggered'],
            'new_balance' => $fresh->points_balance,
            'pity' => $snapshot,
        ]);
    }

    /**
     * Paginated list of the authenticated user's spins.
     */
    public function history()
    {
        $user = Auth::user();

        $rows = GachaHistory::with('gachaPool.discountType')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate(20);

        $snapshot = $this->roller->snapshotFor($user);

        return view('pages.gacha-history', [
            'rows' => $rows,
            'pity' => $snapshot,
            'hardPityThreshold' => GachaRollService::HARD_PITY_THRESHOLD,
            'miniPityThreshold' => GachaRollService::MINI_PITY_THRESHOLD,
        ]);
    }

    /**
     * Per-prize `image_path` wins, else rarity fallback, else the global placeholder.
     */
    public static function resolveImageFor(GachaPool $prize): string
    {
        if ($prize->image_path) {
            return $prize->image_path;
        }

        return match ($prize->rarity_item) {
            'grand_prize', 'legendary' => '/gacha-assets/jackpot.png',
            'epic', 'rare' => '/gacha-assets/voucher.png',
            'uncommon' => '/gacha-assets/points.png',
            default => '/alt/logo.png',
        };
    }

    private function resolveImage(GachaPool $prize): string
    {
        return self::resolveImageFor($prize);
    }
}
