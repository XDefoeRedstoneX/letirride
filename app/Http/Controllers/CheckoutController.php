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
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Transaction;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        if (! config('midtrans.is_production')) {
            Config::$curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER => [],
            ];
        }
    }

    public function process(Request $request): JsonResponse
    {
        Log::info('PROCESSING');
        $request->validate([
            'user_discount_id' => 'nullable|integer|exists:user_discounts,id',
        ]);

        $user = Auth::user();

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
                if (($cartItem->product->type ?? 'voucher') === 'direct_topup' && $meta && ! empty($meta['player_id'])) {
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

            $midtransOrderId = $invoiceId.'::'.time();

            // Generate Midtrans Snap token with discounted total
            $finishUrl = route('checkout.finish', $order->id);
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
                'callbacks' => [
                    'finish' => $finishUrl,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            // Store the snap token AND the Midtrans order_id (with ::timestamp suffix).
            // The ::timestamp suffix is required for verify() to query Midtrans status correctly.
            // The callback() method already strips it to find the DB order.
            $order->update([
                'payment_gateway_ref' => $snapToken,
                'noinv' => $midtransOrderId,
            ]);

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
            Log::error('Checkout failed: '.$e->getMessage(), [
                'file' => $e->getFile().':'.$e->getLine(),
                'previous' => $e->getPrevious()?->getMessage(),
            ]);

            return response()->json([
                'message' => 'Checkout failed. Please try again.',
            ], 500);
        }
    }

    public function callback(Request $request): JsonResponse
    {
        try {
            $notification = new Notification;
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: '.$e->getMessage());

            return response()->json(['message' => 'Invalid notification.'], 400);
        }

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

    private function fulfillOrder(Order $order): void
    {
        $order->update(['status' => 'paid']);

        if ($order->user_discount_id) {
            UserDiscount::where('id', $order->user_discount_id)
                ->update(['is_used' => true]);
        }

        $totalPointsAwarded = 0.0;

        $orderSubtotal = (float) $order->subtotal;
        $orderTotal = (float) $order->total_price_after_discount;
        $discountRatio = $orderSubtotal > 0 ? ($orderTotal / $orderSubtotal) : 1.0;

        foreach ($order->orderDetails as $detail) {
            $product = Product::find($detail->product_id);

            if (! $product) {
                continue;
            }
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

            // Calculate final fiat price for this specific item (accounting for order-level discounts)
            $itemOriginalPrice = (float) $detail->total_price_in_cart;
            $itemFinalPrice = $itemOriginalPrice * $discountRatio;

            // Award points
            $totalPointsAwarded += $product->calculatePoints($itemFinalPrice);
        }

        $finalPoints = (int) floor($totalPointsAwarded);

        // Credit points to user
        if ($finalPoints > 0) {
            $order->user()->increment('points_balance', $finalPoints);
        }

        // Send order confirmation email
        try {
            Mail::to($order->user->email)->send(new OrderConfirmation($order));
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed: ' . $e->getMessage(), ['order_id' => $order->id]);
        }
    }

    public function verify(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (in_array($order->status, ['paid', 'failed', 'cancelled'])) {
            return response()->json(['status' => $order->status]);
        }

        try {
            $midtransStatus = Transaction::status($order->noinv);

            $txStatus = $midtransStatus->transaction_status ?? null;
            $fraudStatus = $midtransStatus->fraud_status ?? 'accept';

            if (in_array($txStatus, ['capture', 'settlement']) && $fraudStatus === 'accept') {
                $this->fulfillOrder($order);
            } elseif (in_array($txStatus, ['cancel', 'deny', 'expire'])) {
                $order->update(['status' => 'failed']);
            }
            $order->save();
        } catch (\Exception $e) {
            Log::warning('Midtrans verify failed: '.$e->getMessage(), ['order' => $order->noinv]);
        }

        $order->refresh();

        return response()->json(['status' => $order->status]);
    }

    public function pay(Order $order): JsonResponse
    {
        Log::info('SNAPPED');
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'This order is no longer pending.'], 422);
        }

        try {
            // If we already have a token, reuse it — Midtrans tokens are valid for 24h.
            if ($order->payment_gateway_ref) {
                return response()->json([
                    'snap_token' => $order->payment_gateway_ref,
                    'order_id' => $order->id,
                    'invoice' => $order->noinv,
                ]);
            }

            // No token stored yet — create a fresh one (first-time pay from cart)
            $params = [
                'transaction_details' => [
                    'order_id' => $order->noinv.'::'.time(),
                    'gross_amount' => (int) $order->total_price_after_discount,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'callbacks' => [
                    'finish' => url('/transactions'),
                ],
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s O'),
                    'unit' => 'hours',
                    'duration' => 24,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);
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
