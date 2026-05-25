<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\TopupCredential;
use App\Models\User;
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
        $request->validate([
            'user_discount_id' => 'nullable|integer|exists:user_discounts,id',
        ]);

        $user = Auth::user();

        $cartItems = CartItem::with(['product.subcategory'])
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        // Block stacking pending orders.
        $existingPending = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return response()->json([
                'message' => 'You have a pending order. Complete or cancel it first.',
                'order_id' => $existingPending->id,
            ], 422);
        }

        $subtotal = $cartItems->sum(fn (CartItem $item) => $item->product->price * $item->quantity);
        $discountAmount = 0.0;
        $voucher = $this->resolveVoucher($request->user_discount_id, $user->id);

        if ($voucher) {
            $discountAmount = $this->computeDiscount($voucher, $cartItems, $subtotal);
            if ($discountAmount <= 0) {
                // Voucher resolved but doesn't apply to the current cart contents.
                $voucher = null;
            }
        }

        $totalAfterDiscount = max(0, $subtotal - $discountAmount);
        $invoiceId = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));

        try {
            $order = DB::transaction(function () use ($user, $voucher, $cartItems, $subtotal, $discountAmount, $totalAfterDiscount, $invoiceId) {
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

                foreach ($cartItems as $cartItem) {
                    $orderDetail = OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'total_price_in_cart' => $cartItem->product->price * $cartItem->quantity,
                    ]);

                    $productType = $cartItem->product->type ?? 'voucher';

                    // Reserve keys atomically for voucher products.
                    if ($productType === 'voucher') {
                        $keys = ProductKey::where('product_id', $cartItem->product_id)
                            ->where('status', 'available')
                            ->whereNull('reserved_for_order_id')
                            ->orderBy('id')
                            ->limit($cartItem->quantity)
                            ->lockForUpdate()
                            ->get();

                        if ($keys->count() < $cartItem->quantity) {
                            throw new \RuntimeException($cartItem->product->name.' is out of stock.');
                        }

                        ProductKey::whereIn('id', $keys->pluck('id'))->update([
                            'reserved_for_order_id' => $order->id,
                        ]);
                    }

                    $meta = $cartItem->topup_meta;
                    if ($productType === 'direct_topup' && $meta && ! empty($meta['player_id'])) {
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

                return $order;
            });
        } catch (\RuntimeException $stockErr) {
            return response()->json(['message' => $stockErr->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Checkout failed: '.$e->getMessage(), [
                'file' => $e->getFile().':'.$e->getLine(),
            ]);

            return response()->json(['message' => 'Checkout failed. Please try again.'], 500);
        }

        try {
            $itemDetails = $cartItems->map(fn (CartItem $item) => [
                'id' => (string) $item->product_id,
                'price' => (int) $item->product->price,
                'quantity' => $item->quantity,
                'name' => substr($item->product->name, 0, 50),
            ])->values()->toArray();

            if ($discountAmount > 0) {
                $itemDetails[] = [
                    'id' => 'DISCOUNT',
                    'price' => (int) -$discountAmount,
                    'quantity' => 1,
                    'name' => 'Discount Voucher',
                ];
            }

            $midtransOrderId = $invoiceId.'::'.time();
            $params = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => (int) $totalAfterDiscount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $this->midtransSafeEmail($user),
                ],
                'callbacks' => [
                    'finish' => route('checkout.finish', $order->id),
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $order->update([
                'payment_gateway_ref' => $snapToken,
                'noinv' => $midtransOrderId,
            ]);

            CartItem::where('user_id', $user->id)->delete();

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $order->id,
                'invoice' => $invoiceId,
            ]);
        } catch (\Throwable $e) {
            Log::error('Snap token failed: '.$e->getMessage());

            // Free the reservations + remove the order we just created so the
            // user isn't blocked by a phantom pending order.
            $this->releaseReservations($order);
            $order->update(['status' => 'failed']);

            return response()->json(['message' => 'Payment gateway error. Please try again.'], 500);
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

        $order = Order::where('noinv', $dbInvoice)
            ->orWhere('noinv', $midtransOrderId)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            if ($fraudStatus === 'accept') {
                $this->fulfillOrder($order);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
            $this->failOrder($order);
        }

        return response()->json(['message' => 'OK']);
    }

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

    public function status(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json(['status' => $order->status]);
    }

    public function verify(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (in_array($order->status, ['paid', 'failed', 'cancelled'], true)) {
            return response()->json(['status' => $order->status]);
        }

        try {
            $midtransStatus = Transaction::status($order->noinv);

            $txStatus = $midtransStatus->transaction_status ?? null;
            $fraudStatus = $midtransStatus->fraud_status ?? 'accept';

            if (in_array($txStatus, ['capture', 'settlement'], true) && $fraudStatus === 'accept') {
                $this->fulfillOrder($order);
            } elseif (in_array($txStatus, ['cancel', 'deny', 'expire'], true)) {
                $this->failOrder($order);
            }
        } catch (\Exception $e) {
            Log::warning('Midtrans verify failed: '.$e->getMessage(), ['order' => $order->noinv]);
        }

        $order->refresh();

        return response()->json(['status' => $order->status]);
    }

    public function pay(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'This order is no longer pending.'], 422);
        }

        try {
            if ($order->payment_gateway_ref) {
                return response()->json([
                    'snap_token' => $order->payment_gateway_ref,
                    'order_id' => $order->id,
                    'invoice' => $order->noinv,
                ]);
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $order->noinv.'::'.time(),
                    'gross_amount' => (int) $order->total_price_after_discount,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email' => $this->midtransSafeEmail($order->user),
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

    /**
     * Look up the voucher belonging to this user, and make sure no other pending
     * order has already laid claim to it.
     */
    private function resolveVoucher(?int $voucherId, int $userId): ?UserDiscount
    {
        if (! $voucherId) {
            return null;
        }

        $voucher = UserDiscount::with(['discountType.targetCategory', 'discountType.targetSubcategory'])
            ->where('id', $voucherId)
            ->where('user_id', $userId)
            ->where('is_used', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $voucher) {
            return null;
        }

        $heldByPending = Order::where('user_discount_id', $voucher->id)
            ->where('status', 'pending')
            ->exists();

        return $heldByPending ? null : $voucher;
    }

    /**
     * Apply a voucher's discount type/value to the eligible cart subtotal.
     * Subcategory targeting wins over category targeting; both null = storewide.
     */
    private function computeDiscount(UserDiscount $voucher, $cartItems, float $subtotal): float
    {
        $discount = $voucher->discountType;

        if ($discount->target_subcategory_id) {
            $eligible = $cartItems
                ->filter(fn (CartItem $i) => $i->product->subcategory_id === $discount->target_subcategory_id)
                ->sum(fn (CartItem $i) => $i->product->price * $i->quantity);
        } elseif ($discount->target_category_id) {
            $eligible = $cartItems
                ->filter(fn (CartItem $i) => $i->product->category_id === $discount->target_category_id)
                ->sum(fn (CartItem $i) => $i->product->price * $i->quantity);
        } else {
            $eligible = $subtotal;
        }

        if ($eligible <= 0) {
            return 0.0;
        }

        return $discount->type === 'percent'
            ? $eligible * ($discount->value / 100)
            : min((float) $discount->value, (float) $eligible);
    }

    private function midtransSafeEmail(User $user): string
    {
        if (is_string($user->email) && preg_match('/@[^@\s]+\.[a-zA-Z]{2,}$/', $user->email)) {
            return $user->email;
        }

        return 'user-'.$user->id.'@noreply.ridly.example';
    }

    /**
     * Idempotent fulfillment: row-lock the order, re-check status, convert reserved
     * keys to sold, mark the voucher consumed with audit stamps, award points.
     */
    private function fulfillOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();
            if (! $locked || $locked->status === 'paid') {
                return;
            }

            $locked->update(['status' => 'paid']);

            if ($locked->user_discount_id) {
                UserDiscount::where('id', $locked->user_discount_id)
                    ->where('is_used', false)
                    ->update([
                        'is_used' => true,
                        'used_at' => now(),
                        'order_id' => $locked->id,
                    ]);
            }

            $orderSubtotal = (float) $locked->subtotal;
            $orderTotal = (float) $locked->total_price_after_discount;
            $discountRatio = $orderSubtotal > 0 ? ($orderTotal / $orderSubtotal) : 1.0;

            $totalPointsAwarded = 0.0;

            foreach ($locked->orderDetails as $detail) {
                $product = Product::find($detail->product_id);
                if (! $product) {
                    continue;
                }

                // Convert reserved keys (claimed at process() time) into sold ones.
                // Fall back to any still-available keys if the reservation was lost
                // somehow (shouldn't happen with the new flow but defensive).
                $reservedIds = ProductKey::where('reserved_for_order_id', $locked->id)
                    ->where('product_id', $detail->product_id)
                    ->where('status', 'available')
                    ->pluck('id');

                if ($reservedIds->count() < $detail->quantity) {
                    $extraIds = ProductKey::where('product_id', $detail->product_id)
                        ->where('status', 'available')
                        ->whereNull('reserved_for_order_id')
                        ->lockForUpdate()
                        ->limit($detail->quantity - $reservedIds->count())
                        ->pluck('id');
                    $reservedIds = $reservedIds->merge($extraIds);
                }

                if ($reservedIds->isNotEmpty()) {
                    ProductKey::whereIn('id', $reservedIds)->update([
                        'status' => 'sold',
                        'order_id' => $locked->id,
                        'reserved_for_order_id' => null,
                    ]);
                }

                $itemFinalPrice = (float) $detail->total_price_in_cart * $discountRatio;
                $totalPointsAwarded += $product->calculatePoints($itemFinalPrice);
            }

            $finalPoints = (int) floor($totalPointsAwarded);
            if ($finalPoints > 0) {
                $locked->user()->increment('points_balance', $finalPoints);
            }
        });

        try {
            $order->refresh();
            Mail::to($order->user->email)->send(new OrderConfirmation($order));
        } catch (\Throwable $e) {
            Log::error('Order confirmation email failed: '.$e->getMessage(), ['order_id' => $order->id]);
        }
    }

    /**
     * Transition a pending order to failed and release any reservations so the
     * keys re-enter the available pool.
     */
    private function failOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();
            if (! $locked || in_array($locked->status, ['paid', 'failed', 'cancelled'], true)) {
                return;
            }

            $locked->update(['status' => 'failed']);
            $this->releaseReservations($locked);
        });
    }

    private function releaseReservations(Order $order): void
    {
        ProductKey::where('reserved_for_order_id', $order->id)
            ->update(['reserved_for_order_id' => null]);
    }
}
