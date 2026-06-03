<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Matches the Midtrans Snap expiry window set in CheckoutController::pay().
     * Pending orders older than this with no Midtrans callback are treated as
     * expired and reconciled the next time the user lands on this page.
     */
    private const EXPIRY_HOURS = 24;

    public function index()
    {
        $this->expireStalePendingOrders(Auth::id());

        $orders = Order::with(['orderDetails.product'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Order $order) {
                $firstDetail = $order->orderDetails->first();
                $itemCount = $order->orderDetails->sum('quantity');
                $productName = $firstDetail?->product?->name ?? 'Unknown';

                if ($itemCount > 1 && $order->orderDetails->count() > 1) {
                    $productName .= ' +'.($order->orderDetails->count() - 1).' more';
                }

                $displayStatus = strtoupper($order->status);
                if ($order->status === 'failed' && $order->created_at && $order->created_at->lt(now()->subHours(self::EXPIRY_HOURS))) {
                    $displayStatus = 'EXPIRED';
                }

                return [
                    'id' => $order->display_noinv,
                    'name' => $productName,
                    'amount' => $order->total_price_after_discount,
                    'status' => $displayStatus,
                    'date' => $order->created_at?->format('M d, Y') ?? '-',
                    'image' => $firstDetail?->product?->imageUrl() ?? '/products/'.Product::FALLBACK_IMAGE,
                    'order_id' => $order->id,
                    'details' => $order->orderDetails->map(fn ($d) => [
                        'product' => $d->product?->name ?? 'Unknown',
                        'quantity' => $d->quantity,
                        'total' => (float) $d->total_price_in_cart,
                    ])->values(),
                ];
            });

        return view('pages.transactions', [
            'orders' => $orders,
        ]);
    }

    /**
     * Reconcile any of this user's pending orders past the Midtrans expiry
     * window: mark them failed and free reserved keys back to the pool. Mirrors
     * CheckoutController::failOrder() — duplicated intentionally to keep this
     * bug fix surgical (see also CheckoutController::pay/finish).
     */
    private function expireStalePendingOrders(int $userId): void
    {
        $cutoff = now()->subHours(self::EXPIRY_HOURS);

        $staleIds = Order::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->pluck('id');

        foreach ($staleIds as $id) {
            DB::transaction(function () use ($id) {
                $locked = Order::whereKey($id)->lockForUpdate()->first();
                if (! $locked || $locked->status !== 'pending') {
                    return;
                }

                $locked->update(['status' => 'failed']);

                ProductKey::where('reserved_for_order_id', $locked->id)
                    ->update(['reserved_for_order_id' => null]);
            });
        }
    }

    /**
     * Cancel a pending order: mark it cancelled, drop any key reservations back
     * into the available pool. The user_discount_id reference is preserved so
     * the order keeps its audit trail; the voucher remains unused because
     * fulfillOrder() is the only path that flips is_used → true.
     */
    public function cancel(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled.'], 422);
        }

        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== 'pending') {
                return;
            }

            $locked->update(['status' => 'cancelled']);

            ProductKey::where('reserved_for_order_id', $locked->id)
                ->update(['reserved_for_order_id' => null]);
        });

        return response()->json(['message' => 'Order cancelled successfully.']);
    }
}
