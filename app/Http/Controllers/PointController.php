<?php

namespace App\Http\Controllers;

use App\Models\PointShopItem;
use App\Models\PointShopPurchase;
use App\Models\UserDiscount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    /**
     * Display the point shop page with DB items.
     */
    public function index()
    {
        $items = PointShopItem::with('discountType')
            ->where('is_active', true)
            ->orderBy('point_cost')
            ->get()
            ->map(fn (PointShopItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'point_cost' => $item->point_cost,
                'reward_type' => $item->reward_type,
                'image' => $item->img ? '/point-shop-assets/' . $item->img : '/gacha-assets/voucher.svg',
                'discount_name' => $item->discountType?->name ?? '',
            ]);

        return view('pages.point-shop', [
            'shopItems' => $items,
        ]);
    }

    /**
     * Redeem a point shop item (AJAX).
     */
    public function redeem(PointShopItem $item): JsonResponse
    {
        if (! $item->is_active) {
            return response()->json(['message' => 'This item is no longer available.'], 404);
        }

        $user = Auth::user();

        if ($user->points_balance < $item->point_cost) {
            return response()->json(['message' => 'Not enough points. You need '.number_format($item->point_cost).' points.'], 422);
        }

        DB::beginTransaction();

        try {
            // Deduct points
            $user->decrement('points_balance', $item->point_cost);

            // Record purchase
            PointShopPurchase::create([
                'user_id' => $user->id,
                'point_shop_item_id' => $item->id,
                'points_spent' => $item->point_cost,
            ]);

            // Create the discount voucher for the user
            if ($item->discount_type_id) {
                UserDiscount::create([
                    'user_id' => $user->id,
                    'discount_type_id' => $item->discount_type_id,
                    'is_used' => false,
                    'obtained_from' => 'point_shop',
                    'expires_at' => now()->addDays(30),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Successfully redeemed '.$item->name.'!',
                'new_balance' => $user->fresh()->points_balance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Redemption failed. Please try again.'], 500);
        }
    }
}
