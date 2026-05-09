<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\UserDiscount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Show the user's order/transaction history.
     */
    public function index()
    {
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

                return [
                    'id' => $order->noinv,
                    'name' => $productName,
                    'amount' => $order->total_price_after_discount,
                    'status' => strtoupper($order->status),
                    'date' => $order->created_at?->format('M d, Y') ?? '-',
                    'image' => '/products/'.ltrim($firstDetail?->product?->image ?: 'soundcloud.svg', '/'),
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
     * Bug 1: Cancel a pending order. Releases the voucher if one was attached.
     */
    public function cancel(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled.'], 422);
        }

        $order->update(['status' => 'cancelled']);

        // Voucher was never marked as used (Bug 4 fix), so no release needed.
        // But clear the reference so the voucher isn't "locked" by this order.
        if ($order->user_discount_id) {
            $order->update(['user_discount_id' => null]);
        }

        return response()->json([
            'message' => 'Order cancelled successfully.',
        ]);
    }
}
