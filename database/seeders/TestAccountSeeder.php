<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserDiscount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestAccountSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Create or Update Test User
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Master',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'points_balance' => 50000,
                'referral_code' => 'TESTMASTER99',
            ]
        );

        // Clear existing test user data
        CartItem::where('user_id', $user->id)->delete();
        Favorite::where('user_id', $user->id)->delete();
        UserDiscount::where('user_id', $user->id)->delete();
        Ticket::where('user_id', $user->id)->delete();
        $orders = Order::where('user_id', $user->id)->get();
        foreach($orders as $order) {
            ProductKey::where('order_id', $order->id)->update(['order_id' => null, 'status' => 'available']);
            $order->orderDetails()->delete();
            $order->delete();
        }

        $products = Product::where('is_active', true)->get();
        if ($products->isEmpty()) {
            return;
        }

        // 2. Add to Cart
        foreach ($products->random(3) as $product) {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => rand(1, 3)
            ]);
        }

        // 3. Add to Favorites
        foreach ($products->random(4) as $product) {
            Favorite::create([
                'user_id' => $user->id,
                'product_id' => $product->id
            ]);
        }

        // 4. Give User Discounts (Active and Expired/Used)
        UserDiscount::create([
            'user_id' => $user->id,
            'discount_type_id' => 1, // 10% Off All
            'is_used' => false,
            'obtained_from' => 'admin_gift',
            'expires_at' => $now->copy()->addDays(7),
        ]);
        UserDiscount::create([
            'user_id' => $user->id,
            'discount_type_id' => 5, // Rp75.000 Welcome Bonus
            'is_used' => true,
            'obtained_from' => 'registration',
        ]);
        UserDiscount::create([
            'user_id' => $user->id,
            'discount_type_id' => 10, // Free Welkin
            'is_used' => false,
            'obtained_from' => 'point_shop',
            'expires_at' => $now->copy()->addDays(30),
        ]);

        // 6. Support Tickets
        Ticket::create([
            'user_id' => $user->id,
            'type' => 'Issue with payment',
            'message' => 'My payment failed yesterday, what happened?',
            'status' => 'closed',
            'created_at' => $now->copy()->subDays(5),
        ]);
        Ticket::create([
            'user_id' => $user->id,
            'type' => 'Requesting a refund',
            'message' => 'I accidentally bought the wrong code.',
            'status' => 'open',
            'created_at' => $now->copy()->subHours(2),
        ]);

        // 7. Orders & Transactions
        $this->createOrder($user, $products, 'paid', $now->copy()->subDays(10));
        $this->createOrder($user, $products, 'paid', $now->copy()->subDays(3));
        $this->createOrder($user, $products, 'failed', $now->copy()->subDays(1));
        $this->createOrder($user, $products, 'pending', $now->copy()->subMinutes(15));
    }

    private function createOrder($user, $products, $status, $date)
    {
        $orderProducts = $products->random(rand(1, 3));
        $subtotal = 0;
        
        $order = Order::create([
            'noinv' => 'INV-' . $date->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'user_id' => $user->id,
            'subtotal' => 0, // updated below
            'discount_amount' => 0,
            'total_price_after_discount' => 0,
            'payment_gateway_ref' => 'mock_' . Str::random(10),
            'status' => $status,
            'created_at' => $date,
        ]);

        foreach ($orderProducts as $product) {
            $qty = rand(1, 2);
            $totalPrice = $product->price * $qty;
            $subtotal += $totalPrice;

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'total_price_in_cart' => $totalPrice,
            ]);

            if ($status === 'paid') {
                // Assign product keys
                $keys = ProductKey::where('product_id', $product->id)
                    ->where('status', 'available')
                    ->limit($qty)
                    ->get();
                    
                foreach ($keys as $key) {
                    $key->update([
                        'status' => 'sold',
                        'order_id' => $order->id,
                    ]);
                }
            }
        }

        $order->update([
            'subtotal' => $subtotal,
            'total_price_after_discount' => $subtotal, // Simplifying discount for test order
        ]);
    }
}
