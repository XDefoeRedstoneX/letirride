<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DiscountType;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\UserDiscount;
use App\Models\VoucherCode;
use Midtrans\Snap;
use Midtrans\Config;

class StoreController extends Controller
{
    public function showStore()
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->get()
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'category' => $product->category?->name ?? 'Other',
                    'image' => $product->image ? asset('products/' . ltrim($product->image, '/')) : asset('products/soundcloud.svg'),
                ];
            })
            ->values();

        return view('pages.products', compact('products'));
    }

    public function addCart(Request $request, $productId){
        $product = Product::findOrFail($productId);
        $cart = session()->get('cart', []);
        if(isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                "name" => $product->name,
                "quantity" => 1,
                "price" => $product->price,
                "img" => $product->img
            ];
        }
        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function viewCart(){
        $cart = session()->get('cart', []);
        return view('cart', compact('cart'));
    }

    public function updateCart(Request $request, $productId){
        $cart = session()->get('cart', []);
        if(isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $request->quantity;
            if ($cart[$productId]['quantity'] <= 0) {
                unset($cart[$productId]);
            }
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Cart updated successfully!');
        }
        return redirect()->back()->with('error', 'Product not found in cart!');
    }


    public function checkout(Request $request, $discountID){
        $cart = session('cart', []);
        try {
            $voucher = UserDiscount::findorFail($discountID);
            $discount = DiscountType::findOrFail($voucher->discount_type_id);
                if ($discount->type == 'percent') {
                    $totalPrice = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
                    $discountAmount = $totalPrice * ($discount->value / 100);
                } else {
                    $discountAmount = $discount->value;
                }
        }
        catch (\Exception $e) {
            $voucher = null;
        }


        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty!');
        }


        DB::beginTransaction();
        try {
            $totalPrice = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

            $order = Order::create([
                'invoice_   id' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'user_id' => Auth::id(),
                'user_discount_id' => $voucher ? $voucher->id : null,
                'subtotal' => $totalPrice,
                'discount_amount' => $voucher ? $discountAmount : 0,
                'total_price_after_discount' => $totalPrice - ($voucher ? $discountAmount : 0),
                'payment_gateway_ref' => null,
                'status' => 'pending',
                'paid_at' => null,
            ]);

            foreach ($cart as $product_id => $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $product_id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            //Midtrans payment integration
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Prepare item details for Midtrans
            $item_details = [];
            foreach ($cart as $product_id => $item) {
                $item_details[] = [
                    'id'       => $product_id,
                    'price'    => $item['price'],
                    'quantity' => $item['quantity'],
                    'name'     => substr($item['name'], 0, 50)
                ];
            }

            // Create Midtrans Transaction
            $params = [
                'transaction_details' => [
                    'order_id' => $order->invoice_number,
                    'gross_amount' => $totalPrice,
                ],
                'item_details' => $item_details,
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
				'callbacks' => [
                    'finish' => route('payment_return', $order->id), // Auto-check status after return
                ]
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $order->payment_url = $snapToken; // Store token to use in the modal later
              $order->save();

            DB::commit();

            session()->forget('cart'); // Clear cart

            // Return to a checkout payment view that triggers the Snap popup
            return view('payment', compact('snapToken', 'order'));

            //return redirect()->route('store')->with('success', 'Checkout successful! Thank you for your purchase.');
		} catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }
}
