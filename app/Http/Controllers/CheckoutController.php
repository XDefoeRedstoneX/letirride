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
        
        // Disable SSL verification for local development to fix cURL error 60 / 20
        if (!config('midtrans.is_production')) {
            Config::$curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER => [],
            ];
        }
    }

    /**
     * Process the checkout: create Order, generate Midtrans Snap token, return JSON.
     *
     * Bug 1: Block checkout if a pending order exists.
     * Bug 4: Don't mark voucher as used here — defer to fulfillOrder().
     * Bug 5: Read topup credentials from cart_items.topup_meta instead of request body.
     */
    public function process(Request $request): JsonResponse
    {   Log::info("PROCESSING");
        $request->validate([
            'user_discount_id' => 'nullable|integer|exists:user_discounts,id',
        ]);

        $user = Auth::user();
        $existingPending = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existingPending) {
            return response()->json([
                'message' => 'You have a pending transaction. Please complete or cancel it before placing a new order.',
                'order_id' => $existingPending->id,
                'invoice' => $existingPending->noinv,
            ], 422);
        }

        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
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

            // Bug 4: Prevent same voucher from being used by another pending order
            if ($voucher) {
                $voucherOnPending = Order::where('user_discount_id', $voucher->id)
                    ->where('status', 'pending')
                    ->exists();

                if ($voucherOnPending) {
                    $voucher = null; // Silently ignore — voucher is locked by another pending order
                }
            }

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
            // Create order — Bug 4: record voucher reference but don't mark it as used yet
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

            // Create order details + topup credentials (Bug 5: read from cart_items.topup_meta)
            foreach ($cartItems as $cartItem) {
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'total_price_in_cart' => $cartItem->product->price * $cartItem->quantity,
                ]);

                // Store topup credentials for direct_topup products (Bug 5: from topup_meta)
                $meta = $cartItem->topup_meta;
                if (($cartItem->product->type ?? 'voucher') === 'direct_topup' && $meta && !empty($meta['player_id'])) {
                    TopupCredential::create([
                        'order_detail_id' => $orderDetail->id,
                        'player_id' => $meta['player_id'],
                        'zone_id' => $meta['zone_id'] ?? null,
                        'server_id' => $meta['server_id'] ?? null,
                        'topup_status' => 'pending',
                        'created_at' => now(),
                    ]);
                }
            }

            // Build Midtrans item details (Bug 4: ensure integer math matches gross_amount)
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

            // Generate a unique Midtrans order_id per attempt.
            // Format: INV-YYYYMMDD-XXXXXX::1715249272
            // The '::' delimiter lets the webhook strip the suffix to find our DB order.
            $midtransOrderId = $invoiceId.'::'.time();

            // Generate Midtrans Snap token with discounted total
            $params = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => (int) $totalAfterDiscount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            // Store the snap token (no need to store the Midtrans order_id — webhook will parse it)
            $order->update(['payment_gateway_ref' => $snapToken]);

            // Bug 1: Clear cart immediately after creating the order
            CartItem::where('user_id', $user->id)->delete();

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

        // Midtrans sends back our suffixed order_id (e.g. INV-20260509-ABC123::1715249272).
        // Strip the '::timestamp' suffix to get the original DB invoice number.
        $midtransOrderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? 'accept';

        $dbInvoice = Str::contains($midtransOrderId, '::')
            ? Str::before($midtransOrderId, '::')
            : $midtransOrderId;

        $order = Order::where('noinv', $dbInvoice)->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Already finalized — don't reprocess (Bug 1: include 'cancelled')
        if (in_array($order->status, ['paid', 'failed', 'cancelled'])) {
            return response()->json(['message' => 'Already processed.']);
        }

        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            if ($fraudStatus === 'accept') {
                $this->fulfillOrder($order);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $order->update(['status' => 'failed']);

            // Bug 4: No need to release voucher here — it was never marked as used
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
     * Bug 3: Check order status (polling endpoint for checkout-result page).
     */
    public function status(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'status' => $order->status,
        ]);
    }

    /**
     * Fulfill a paid order: mark paid, mark voucher used, assign product keys, award points.
     *
     * Bug 1: Cart already cleared in process(), not here.
     * Bug 4: Mark voucher as used HERE (on confirmed payment), not in process().
     */
    private function fulfillOrder(Order $order): void
    {
        $order->update(['status' => 'paid']);

        if ($order->user_discount_id) {
            UserDiscount::where('id', $order->user_discount_id)
                ->update(['is_used' => true]);
        }

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


    public function pay(Order $order): JsonResponse
    {
        Log::info("SNAPPED");
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'This order is no longer pending.'], 422);
        }

        try {
            $midtransOrderId = $order->noinv.'::'.time();

            $params = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => (int) $order->total_price_after_discount,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email' => $order->user->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);
            
            // Update stored token
            $order->update(['payment_gateway_ref' => $snapToken]);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $order->id,
                'invoice' => $order->noinv,
            ]);
        } catch (\Exception $e) {
            Log::error('Resume payment failed: '.$e->getMessage());

            return response()->json(['message' => 'Failed to generate payment. Please try again.'], 500);
        }
    }
}
