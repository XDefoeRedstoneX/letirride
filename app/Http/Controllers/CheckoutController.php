<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\TopupCredential;
use App\Models\UserDiscount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Process the checkout: create Order, generate Midtrans Snap token, return JSON.
     */
    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'user_discount_id' => 'nullable|integer|exists:user_discounts,id',
            'topup_credentials' => 'nullable|array',
            'topup_credentials.*.product_id' => 'required_with:topup_credentials|integer',
            'topup_credentials.*.player_id' => 'required_with:topup_credentials|string|max:100',
            'topup_credentials.*.zone_id' => 'nullable|string|max:50',
            'topup_credentials.*.server_id' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $cartItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        // If user already has a pending order, return its snap token to resume
        $existingPending = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('payment_gateway_ref')
            ->latest()
            ->first();

        if ($existingPending) {
            return response()->json([
                'snap_token' => $existingPending->payment_gateway_ref,
                'order_id' => $existingPending->id,
                'invoice' => $existingPending->noinv,
            ]);
        }

        // Calculate subtotal
        $subtotal = $cartItems->sum(fn (CartItem $item) => $item->product->price * $item->quantity);

        // Apply discount voucher (if any)
        $discountAmount = 0;
        $voucherId = $request->user_discount_id;
        $voucher = null;

        if ($voucherId) {
            $voucher = UserDiscount::with('discountType')
                ->where('id', $voucherId)
                ->where('user_id', $user->id)
                ->where('is_used', false)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($voucher) {
                $discount = $voucher->discountType;

                // If the voucher targets a specific category, validate that the cart
                // contains at least one item from that category and only apply to those items.
                if ($discount->target_category_id) {
                    $eligibleSubtotal = $cartItems
                        ->filter(fn (CartItem $item) => $item->product->category_id === $discount->target_category_id)
                        ->sum(fn (CartItem $item) => $item->product->price * $item->quantity);

                    if ($eligibleSubtotal <= 0) {
                        // No eligible items — ignore the voucher silently
                        $voucher = null;
                    } else {
                        if ($discount->type === 'percent') {
                            $discountAmount = $eligibleSubtotal * ($discount->value / 100);
                        } else {
                            $discountAmount = min($discount->value, $eligibleSubtotal);
                        }
                    }
                } else {
                    // Universal voucher — apply to full subtotal
                    if ($discount->type === 'percent') {
                        $discountAmount = $subtotal * ($discount->value / 100);
                    } else {
                        $discountAmount = min($discount->value, $subtotal);
                    }
                }
            }
        }

        $totalAfterDiscount = max(0, $subtotal - $discountAmount);
        $invoiceId = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));

        DB::beginTransaction();

        try {
            // Create order
            $order = Order::create([
                'noinv' => $invoiceId,
                'user_id' => $user->id,
                'user_discount_id' => $voucher?->id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_price_after_discount' => $totalAfterDiscount,
                'payment_gateway_ref' => null,
                'status' => 'pending',
            ]);

            // Create order details + topup credentials
            foreach ($cartItems as $cartItem) {
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'total_price_in_cart' => $cartItem->product->price * $cartItem->quantity,
                ]);

                // Store topup credentials for direct_topup products
                if (($cartItem->product->type ?? 'voucher') === 'direct_topup' && $topupCredentials->has($cartItem->product_id)) {
                    $creds = $topupCredentials->get($cartItem->product_id);
                    TopupCredential::create([
                        'order_detail_id' => $orderDetail->id,
                        'player_id' => $creds['player_id'],
                        'zone_id' => $creds['zone_id'] ?? null,
                        'server_id' => $creds['server_id'] ?? null,
                        'topup_status' => 'pending',
                        'created_at' => now(),
                    ]);
                }
            }

            // Mark voucher as used
            if ($voucher) {
                $voucher->update(['is_used' => true]);
            }

            // Build Midtrans item details
            $itemDetails = $cartItems->map(fn (CartItem $item) => [
                'id' => (string) $item->product_id,
                'price' => (int) $item->product->price,
                'quantity' => $item->quantity,
                'name' => substr($item->product->name, 0, 50),
            ])->values()->toArray();

            // Add discount as negative line item if applicable
            if ($discountAmount > 0) {
                $itemDetails[] = [
                    'id' => 'DISCOUNT',
                    'price' => (int) -$discountAmount,
                    'quantity' => 1,
                    'name' => 'Discount Voucher',
                ];
            }

            // Generate Midtrans Snap token
            $params = [
                'transaction_details' => [
                    'order_id' => $invoiceId,
                    'gross_amount' => (int) $totalAfterDiscount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            // Store the snap token reference
            $order->update(['payment_gateway_ref' => $snapToken]);

            DB::commit();

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $order->id,
                'invoice' => $invoiceId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => 'Checkout failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Midtrans server-to-server notification webhook.
     * No auth middleware — Midtrans calls this directly.
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            $notification = new Notification;
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: '.$e->getMessage());

            return response()->json(['message' => 'Invalid notification.'], 400);
        }

        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? 'accept';
        $paymentType = $notification->payment_type ?? '';

        $order = Order::where('noinv', $orderId)->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Already finalized — don't reprocess
        if (in_array($order->status, ['paid', 'failed'])) {
            return response()->json(['message' => 'Already processed.']);
        }

        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            if ($fraudStatus === 'accept') {
                $this->fulfillOrder($order);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $order->update(['status' => 'failed']);

            // Release the voucher if one was used
            if ($order->user_discount_id) {
                UserDiscount::where('id', $order->user_discount_id)
                    ->update(['is_used' => false]);
            }
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * User returns from Midtrans — show the order result page.
     */
    public function finish(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['orderDetails.product', 'productKeys.product']);

        return view('pages.checkout-result', [
            'order' => $order,
        ]);
    }

    /**
     * Fulfill a paid order: mark paid, assign product keys, award points, clear cart.
     */
    private function fulfillOrder(Order $order): void
    {
        $order->update(['status' => 'paid']);

        // Clear only cart items that belong to this order's products
        $orderedProductIds = $order->orderDetails->pluck('product_id')->toArray();
        CartItem::where('user_id', $order->user_id)
            ->whereIn('product_id', $orderedProductIds)
            ->delete();

        $totalPointsAwarded = 0;

        // Assign available product keys to each order detail
        foreach ($order->orderDetails as $detail) {
            $product = Product::find($detail->product_id);

            if (! $product) {
                continue;
            }

            // Grab available keys for this product
            $keys = ProductKey::where('product_id', $detail->product_id)
                ->where('status', 'available')
                ->limit($detail->quantity)
                ->get();

            foreach ($keys as $key) {
                $key->update([
                    'status' => 'sold',
                    'order_id' => $order->id,
                ]);
            }

            // Award points
            $totalPointsAwarded += ($product->point_reward ?? 0) * $detail->quantity;
        }

        // Credit points to user
        if ($totalPointsAwarded > 0) {
            $order->user()->increment('points_balance', $totalPointsAwarded);
        }
    }

    /**
     * Resume payment for a pending order — returns the stored snap token.
     */
    public function pay(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'This order is no longer pending.'], 422);
        }

        $snapToken = $order->payment_gateway_ref;

        if (! $snapToken) {
            return response()->json(['message' => 'Payment token not found. Please contact support.'], 422);
        }

        return response()->json([
            'snap_token' => $snapToken,
            'order_id' => $order->id,
            'invoice' => $order->noinv,
        ]);
    }
}
