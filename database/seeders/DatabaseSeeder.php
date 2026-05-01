<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $this->seedUsers($now);
        $this->seedCategories();
        $this->seedProducts();
        $this->seedDiscountTypes();
        $this->seedPointShopItems();
        $this->seedPointShopPurchases($now);
        
        $this->seedUserDiscounts();
        $this->seedGachaPools();
        $this->seedOrders($now);
        $this->seedOrderDetails();
        $this->seedProductKeys();
        $this->seedTickets($now);
        $this->seedFaqs();
    }

    private function seedUsers($now): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $users = [
            ['id' => 1, 'name' => 'Alice Smith', 'email' => 'alice@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 1500],
            ['id' => 2, 'name' => 'Bob Jones', 'email' => 'bob@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 0],
            ['id' => 3, 'name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 500],
            ['id' => 4, 'name' => 'Diana Prince', 'email' => 'diana@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'admin', 'points_balance' => 9999],
            ['id' => 5, 'name' => 'Ethan Hunt', 'email' => 'ethan@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 100],
            ['id' => 6, 'name' => 'Fiona Gallagher', 'email' => 'fiona@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 300],
            ['id' => 7, 'name' => 'George Costanza', 'email' => 'george@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 0],
            ['id' => 8, 'name' => 'Hannah Abbott', 'email' => 'hannah@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 2000],
            ['id' => 9, 'name' => 'Ian Malcolm', 'email' => 'ian@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 50],
            ['id' => 10, 'name' => 'Julia Child', 'email' => 'julia@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 750],
        ];

        $usersHasRole = Schema::hasColumn('users', 'role');
        $usersHasPointsBalance = Schema::hasColumn('users', 'points_balance');
        $usersHasGoogleId = Schema::hasColumn('users', 'google_id');
        $usersHasCreatedAt = Schema::hasColumn('users', 'created_at');
        $usersHasUpdatedAt = Schema::hasColumn('users', 'updated_at');

        $rows = [];
        foreach ($users as $user) {
            $row = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
            ];

            if ($usersHasGoogleId) {
                $row['google_id'] = null;
            }

            if ($usersHasRole) {
                $row['role'] = $user['role'];
            }

            if ($usersHasPointsBalance) {
                $row['points_balance'] = $user['points_balance'];
            }

            if ($usersHasCreatedAt) {
                $row['created_at'] = $now;
            }

            if ($usersHasUpdatedAt) {
                $row['updated_at'] = $now;
            }

            $rows[] = $row;
        }

        $updateColumns = ['name', 'email', 'password'];
        if ($usersHasGoogleId) {
            $updateColumns[] = 'google_id';
        }
        if ($usersHasRole) {
            $updateColumns[] = 'role';
        }
        if ($usersHasPointsBalance) {
            $updateColumns[] = 'points_balance';
        }
        if ($usersHasUpdatedAt) {
            $updateColumns[] = 'updated_at';
        }

        DB::table('users')->upsert($rows, ['id'], $updateColumns);
    }

    private function seedCategories(): void
        {
            if (! Schema::hasTable('categories')) {
                return;
            }

            DB::table('categories')->upsert([
                ['id' => 1, 'name' => 'Gaming', 'slug' => 'gaming'],
                ['id' => 2, 'name' => 'Entertainment', 'slug' => 'entertainment'],
                ['id' => 3, 'name' => 'Software & Utilities', 'slug' => 'software-utilities'],
                ['id' => 4, 'name' => 'Gift Cards', 'slug' => 'gift-cards'],
                ['id' => 5, 'name' => 'Mobile Top-Up', 'slug' => 'mobile-top-up'],
                ['id' => 6, 'name' => 'Other', 'slug' => 'other'],
            ], ['id'], ['name', 'slug']);
        }

    private function seedProducts(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $productsHasImg = Schema::hasColumn('products', 'img');

        // UPDATED: category_id values now map only to 1 through 6
        $rows = [
            ['id' => 1, 'category_id' => 1, 'name' => 'Steam Wallet $10', 'description' => 'Adds $10 to Steam', 'price' => 10.00, 'point_reward' => 100, 'is_active' => true],
            ['id' => 2, 'category_id' => 1, 'name' => 'Steam Wallet $50', 'description' => 'Adds $50 to Steam', 'price' => 50.00, 'point_reward' => 500, 'is_active' => true],
            ['id' => 3, 'category_id' => 2, 'name' => 'Netflix 1 Month (HD)', 'description' => 'Standard 1 Month', 'price' => 15.49, 'point_reward' => 150, 'is_active' => true],
            ['id' => 4, 'category_id' => 2, 'name' => 'Spotify 3 Months', 'description' => 'Premium Code', 'price' => 29.97, 'point_reward' => 300, 'is_active' => true],
            ['id' => 5, 'category_id' => 1, 'name' => 'PSN $25', 'description' => 'PS Store Credit', 'price' => 25.00, 'point_reward' => 250, 'is_active' => true],
            ['id' => 6, 'category_id' => 1, 'name' => '1000 Valorant Points', 'description' => 'Riot Games VP', 'price' => 9.99, 'point_reward' => 100, 'is_active' => true],
            ['id' => 7, 'category_id' => 5, 'name' => '500 ML Diamonds', 'description' => 'Moonton Diamonds', 'price' => 10.00, 'point_reward' => 100, 'is_active' => true],
            ['id' => 8, 'category_id' => 1, 'name' => 'Welkin Moon', 'description' => '30 Days Genshin', 'price' => 4.99, 'point_reward' => 50, 'is_active' => true],
            ['id' => 9, 'category_id' => 2, 'name' => 'Discord Nitro 1 Year', 'description' => 'Full Nitro', 'price' => 99.99, 'point_reward' => 1000, 'is_active' => true],
            ['id' => 10, 'category_id' => 1, 'name' => 'Xbox Game Pass 1 Month', 'description' => 'Ultimate Pass', 'price' => 16.99, 'point_reward' => 160, 'is_active' => true],
        ];

        if ($productsHasImg) {
            $rows = array_map(function (array $row) {
                $row['img'] = null;
                return $row;
            }, $rows);
        }

        $updateColumns = ['category_id', 'name', 'description', 'price', 'point_reward', 'is_active'];
        if ($productsHasImg) {
            $updateColumns[] = 'img';
        }

        DB::table('products')->upsert($rows, ['id'], $updateColumns);
    }

    private function seedProductKeys(): void
    {
        if (! Schema::hasTable('product_keys')) {
            return;
        }

        $hasOrderId = Schema::hasColumn('product_keys', 'order_id');

        $rows = [
            ['id' => 1, 'product_id' => 1, 'key_code' => 'STM-1234-ABCD-5678', 'status' => 'available'],
            ['id' => 2, 'product_id' => 1, 'key_code' => 'STM-9876-WXYZ-1234', 'status' => 'sold'],
            ['id' => 3, 'product_id' => 3, 'key_code' => 'NF-AAAA-BBBB-CCCC', 'status' => 'available'],
            ['id' => 4, 'product_id' => 4, 'key_code' => 'SPO-QWER-TYUI-OPAS', 'status' => 'sold'],
            ['id' => 5, 'product_id' => 5, 'key_code' => 'PSN-ZZZZ-XXXX-YYYY', 'status' => 'bugged'],
            ['id' => 6, 'product_id' => 6, 'key_code' => 'VAL-1111-2222-3333', 'status' => 'available'],
            ['id' => 7, 'product_id' => 7, 'key_code' => 'ML-9999-8888-7777', 'status' => 'available'],
            ['id' => 8, 'product_id' => 8, 'key_code' => 'GEN-5555-4444-3333', 'status' => 'available'],
            ['id' => 9, 'product_id' => 9, 'key_code' => 'DIS-0000-1111-2222', 'status' => 'available'],
            ['id' => 10, 'product_id' => 10, 'key_code' => 'XBX-ABAB-CDCD-EFEF', 'status' => 'available'],
        ];

        if ($hasOrderId) {
            $rows = array_map(function (array $row) {
                $row['order_id'] = null;
                return $row;
            }, $rows);
        }

        $updateColumns = ['product_id', 'key_code', 'status'];
        if ($hasOrderId) {
            $updateColumns[] = 'order_id';
        }

        DB::table('product_keys')->upsert($rows, ['id'], $updateColumns);

        if ($hasOrderId) {
            DB::table('product_keys')->where('id', 2)->update(['order_id' => 1]);
            DB::table('product_keys')->where('id', 4)->update(['order_id' => 3]);
        }
    }

    private function seedDiscountTypes(): void
    {
        if (! Schema::hasTable('discount_types')) {
            return;
        }

        // UPDATED: target_category_id values now map only to 1 through 6
        DB::table('discount_types')->upsert([
            ['id' => 1, 'name' => '10% Off All', 'type' => 'percent', 'value' => 10.00, 'target_category_id' => null],
            ['id' => 2, 'name' => '5% Off Steam', 'type' => 'percent', 'value' => 5.00, 'target_category_id' => 1],
            ['id' => 3, 'name' => '$2 Off Netflix', 'type' => 'percent', 'value' => 2.00, 'target_category_id' => 2],
            ['id' => 4, 'name' => '20% Off PSN', 'type' => 'percent', 'value' => 20.00, 'target_category_id' => 1],
            ['id' => 5, 'name' => '$5 Welcome Bonus', 'type' => 'percent', 'value' => 5.00, 'target_category_id' => null],
            ['id' => 6, 'name' => 'Half Price Discord', 'type' => 'percent', 'value' => 50.00, 'target_category_id' => 2],
            ['id' => 7, 'name' => '$1 Off Valorant', 'type' => 'percent', 'value' => 1.00, 'target_category_id' => 1],
            ['id' => 8, 'name' => '15% Off Xbox', 'type' => 'percent', 'value' => 15.00, 'target_category_id' => 1],
            ['id' => 9, 'name' => 'Whale Discount', 'type' => 'percent', 'value' => 25.00, 'target_category_id' => null],
            ['id' => 10, 'name' => 'Free Welkin', 'type' => 'percent', 'value' => 4.99, 'target_category_id' => 1],
        ], ['id'], ['name', 'type', 'value', 'target_category_id']);
    }

    private function seedUserDiscounts(): void
    {
        if (! Schema::hasTable('user_discounts')) {
            return;
        }

        $hasExpiresAt = Schema::hasColumn('user_discounts', 'expires_at');

        $rows = [
            ['id' => 1, 'user_id' => 1, 'discount_type_id' => 1, 'is_used' => false, 'obtained_from' => 'gacha'],
            ['id' => 2, 'user_id' => 2, 'discount_type_id' => 5, 'is_used' => true, 'obtained_from' => 'registration'],
            ['id' => 3, 'user_id' => 3, 'discount_type_id' => 2, 'is_used' => false, 'obtained_from' => 'gacha'],
            ['id' => 4, 'user_id' => 4, 'discount_type_id' => 9, 'is_used' => false, 'obtained_from' => 'compensation'],
            ['id' => 5, 'user_id' => 5, 'discount_type_id' => 3, 'is_used' => true, 'obtained_from' => 'gacha'],
            ['id' => 6, 'user_id' => 6, 'discount_type_id' => 1, 'is_used' => false, 'obtained_from' => 'event'],
            ['id' => 7, 'user_id' => 7, 'discount_type_id' => 7, 'is_used' => false, 'obtained_from' => 'gacha'],
            ['id' => 8, 'user_id' => 8, 'discount_type_id' => 4, 'is_used' => false, 'obtained_from' => 'event'],
            ['id' => 9, 'user_id' => 9, 'discount_type_id' => 6, 'is_used' => true, 'obtained_from' => 'gacha'],
            ['id' => 10, 'user_id' => 10, 'discount_type_id' => 10, 'is_used' => false, 'obtained_from' => 'gacha'],
        ];

        if ($hasExpiresAt) {
            $rows = array_map(function (array $row) {
                $row['expires_at'] = null;
                return $row;
            }, $rows);
        }

        $updateColumns = ['user_id', 'discount_type_id', 'is_used', 'obtained_from'];
        if ($hasExpiresAt) {
            $updateColumns[] = 'expires_at';
        }

        DB::table('user_discounts')->upsert($rows, ['id'], $updateColumns);
    }

    private function seedGachaPools(): void
    {
        if (! Schema::hasTable('gacha_pools')) {
            return;
        }

        DB::table('gacha_pools')->upsert([
            ['id' => 1, 'prize_name' => 'Grand Prize: 50% Off Discord', 'discount_type_id' => 6, 'rarity_item' => 'grand_prize', 'base_win_chance' => 1.5000],
            ['id' => 2, 'prize_name' => 'Epic: 20% Off PSN', 'discount_type_id' => 4, 'rarity_item' => 'epic', 'base_win_chance' => 5.0000],
            ['id' => 3, 'prize_name' => 'Rare: $5 Off Anywhere', 'discount_type_id' => 5, 'rarity_item' => 'rare', 'base_win_chance' => 10.0000],
            ['id' => 7, 'prize_name' => 'Grand Prize: Free Welkin', 'discount_type_id' => 10, 'rarity_item' => 'grand_prize', 'base_win_chance' => 1.0000],
            ['id' => 8, 'prize_name' => 'Rare: 15% Off Xbox', 'discount_type_id' => 8, 'rarity_item' => 'rare', 'base_win_chance' => 7.5000],
            ['id' => 9, 'prize_name' => 'Common: $1 Off Valo', 'discount_type_id' => 7, 'rarity_item' => 'common', 'base_win_chance' => 15.0000],
            ['id' => 10, 'prize_name' => 'Legendary: Whale Status', 'discount_type_id' => 9, 'rarity_item' => 'legendary', 'base_win_chance' => 0.5000],
        ], ['id'], ['prize_name', 'discount_type_id', 'rarity_item', 'base_win_chance']);
    }

    private function seedOrders($now): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        $ordersHasCreatedAt = Schema::hasColumn('orders', 'created_at');

        $rows = [
            ['id' => 1, 'noinv' => 'INV-2024-001', 'user_id' => 1, 'user_discount_id' => null, 'subtotal' => 10.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 10.00, 'payment_gateway_ref' => 'ch_1A2B3C4D5E', 'status' => 'paid'],
            ['id' => 2, 'noinv' => 'INV-2024-002', 'user_id' => 2, 'user_discount_id' => 2, 'subtotal' => 15.49, 'discount_amount' => 5.00, 'total_price_after_discount' => 10.49, 'payment_gateway_ref' => 'ch_9Z8Y7X6W', 'status' => 'paid'],
            ['id' => 3, 'noinv' => 'INV-2024-003', 'user_id' => 3, 'user_discount_id' => null, 'subtotal' => 29.97, 'discount_amount' => 0.00, 'total_price_after_discount' => 29.97, 'payment_gateway_ref' => 'paypal_TX123', 'status' => 'pending'],
            ['id' => 4, 'noinv' => 'INV-2024-004', 'user_id' => 5, 'user_discount_id' => 5, 'subtotal' => 15.49, 'discount_amount' => 2.00, 'total_price_after_discount' => 13.49, 'payment_gateway_ref' => 'ch_5F4G3H2J', 'status' => 'paid'],
            ['id' => 5, 'noinv' => 'INV-2024-005', 'user_id' => 9, 'user_discount_id' => 9, 'subtotal' => 99.99, 'discount_amount' => 49.99, 'total_price_after_discount' => 50.00, 'payment_gateway_ref' => 'paypal_TX999', 'status' => 'paid'],
            ['id' => 6, 'noinv' => 'INV-2024-006', 'user_id' => 4, 'user_discount_id' => null, 'subtotal' => 100.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 100.00, 'payment_gateway_ref' => null, 'status' => 'failed'],
            ['id' => 7, 'noinv' => 'INV-2024-007', 'user_id' => 8, 'user_discount_id' => null, 'subtotal' => 4.99, 'discount_amount' => 0.00, 'total_price_after_discount' => 4.99, 'payment_gateway_ref' => 'ch_11223344', 'status' => 'paid'],
            ['id' => 8, 'noinv' => 'INV-2024-008', 'user_id' => 6, 'user_discount_id' => null, 'subtotal' => 25.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 25.00, 'payment_gateway_ref' => 'paypal_TX456', 'status' => 'pending'],
            ['id' => 9, 'noinv' => 'INV-2024-009', 'user_id' => 10, 'user_discount_id' => null, 'subtotal' => 9.99, 'discount_amount' => 0.00, 'total_price_after_discount' => 9.99, 'payment_gateway_ref' => 'ch_55667788', 'status' => 'paid'],
            ['id' => 10, 'noinv' => 'INV-2024-010', 'user_id' => 7, 'user_discount_id' => null, 'subtotal' => 16.99, 'discount_amount' => 0.00, 'total_price_after_discount' => 16.99, 'payment_gateway_ref' => 'paypal_TX789', 'status' => 'failed'],
        ];

        if ($ordersHasCreatedAt) {
            $rows = array_map(function (array $row) use ($now) {
                $row['created_at'] = $now;
                return $row;
            }, $rows);
        }

        $updateColumns = [
            'noinv',
            'user_id',
            'user_discount_id',
            'subtotal',
            'discount_amount',
            'total_price_after_discount',
            'payment_gateway_ref',
            'status',
        ];

        DB::table('orders')->upsert($rows, ['id'], $updateColumns);
    }

    private function seedOrderDetails(): void
    {
        if (! Schema::hasTable('order_details')) {
            return;
        }

        DB::table('order_details')->upsert([
            ['id' => 1, 'order_id' => 1, 'product_id' => 1, 'quantity' => 1, 'total_price_in_cart' => 10.00],
            ['id' => 2, 'order_id' => 2, 'product_id' => 3, 'quantity' => 1, 'total_price_in_cart' => 15.49],
            ['id' => 3, 'order_id' => 3, 'product_id' => 4, 'quantity' => 1, 'total_price_in_cart' => 29.97],
            ['id' => 4, 'order_id' => 4, 'product_id' => 3, 'quantity' => 1, 'total_price_in_cart' => 15.49],
            ['id' => 5, 'order_id' => 5, 'product_id' => 9, 'quantity' => 1, 'total_price_in_cart' => 99.99],
            ['id' => 6, 'order_id' => 6, 'product_id' => 2, 'quantity' => 2, 'total_price_in_cart' => 100.00],
            ['id' => 7, 'order_id' => 7, 'product_id' => 8, 'quantity' => 1, 'total_price_in_cart' => 4.99],
            ['id' => 8, 'order_id' => 8, 'product_id' => 5, 'quantity' => 1, 'total_price_in_cart' => 25.00],
            ['id' => 9, 'order_id' => 9, 'product_id' => 6, 'quantity' => 1, 'total_price_in_cart' => 9.99],
            ['id' => 10, 'order_id' => 10, 'product_id' => 10, 'quantity' => 1, 'total_price_in_cart' => 16.99],
        ], ['id'], ['order_id', 'product_id', 'quantity', 'total_price_in_cart']);
    }

    private function seedTickets($now): void
    {
        if (! Schema::hasTable('tickets')) {
            return;
        }

        $hasCreatedAt = Schema::hasColumn('tickets', 'created_at');

        $rows = [
            ['id' => 1, 'user_id' => 2, 'type' => 'billing', 'message' => 'I was charged twice for Netflix!', 'status' => 'open'],
            ['id' => 2, 'user_id' => 5, 'type' => 'technical', 'message' => 'My PSN key says it is already used.', 'status' => 'resolved'],
            ['id' => 3, 'user_id' => 8, 'type' => 'general', 'message' => 'When is the next gacha event?', 'status' => 'resolved'],
            ['id' => 4, 'user_id' => 1, 'type' => 'technical', 'message' => 'Cannot login using Google.', 'status' => 'open'],
            ['id' => 5, 'user_id' => 3, 'type' => 'billing', 'message' => 'Discount code didn’t apply at checkout.', 'status' => 'open'],
            ['id' => 6, 'user_id' => 6, 'type' => 'general', 'message' => 'Do you sell Amazon gift cards?', 'status' => 'resolved'],
            ['id' => 7, 'user_id' => 9, 'type' => 'technical', 'message' => 'Website loads slowly on mobile.', 'status' => 'open'],
            ['id' => 8, 'user_id' => 10, 'type' => 'billing', 'message' => 'Refund request for order INV-2024-009.', 'status' => 'open'],
            ['id' => 9, 'user_id' => 7, 'type' => 'technical', 'message' => 'Didn’t get my verification email.', 'status' => 'resolved'],
            ['id' => 10, 'user_id' => 4, 'type' => 'admin', 'message' => 'Test ticket from admin account.', 'status' => 'resolved'],
        ];

        if ($hasCreatedAt) {
            $rows = array_map(function (array $row) use ($now) {
                $row['created_at'] = $now;
                return $row;
            }, $rows);
        }

        $updateColumns = ['user_id', 'type', 'message', 'status'];
        DB::table('tickets')->upsert($rows, ['id'], $updateColumns);
    }

    private function seedFaqs(): void
    {
        if (! Schema::hasTable('faqs')) {
            return;
        }

        DB::table('faqs')->upsert([
            ['id' => 1, 'question' => 'How long does delivery take?', 'answer' => 'Key delivery is instant upon successful payment.'],
            ['id' => 2, 'question' => 'What payment methods do you accept?', 'answer' => 'We accept Credit Cards, PayPal, and Crypto.'],
            ['id' => 3, 'question' => 'Are the keys region locked?', 'answer' => 'Yes, please check the product description for region warnings.'],
            ['id' => 4, 'question' => 'How do I use gacha points?', 'answer' => 'You can spend points in the Gacha tab to win discounts.'],
            ['id' => 5, 'question' => 'Can I get a refund?', 'answer' => 'Refunds are only issued for bugged/invalid keys verified by support.'],
            ['id' => 6, 'question' => 'Is my credit card safe?', 'answer' => 'Yes, we use Stripe and do not store your card details.'],
            ['id' => 7, 'question' => 'How do I redeem a Steam key?', 'answer' => 'Open Steam, click "Games", then "Redeem a Steam Wallet Code".'],
            ['id' => 8, 'question' => 'Do discounts expire?', 'answer' => 'Some do! Check your "My Discounts" page for expiration dates.'],
            ['id' => 9, 'question' => 'Can I stack discounts?', 'answer' => 'No, only one discount code can be used per order.'],
            ['id' => 10, 'question' => 'How do I contact support?', 'answer' => 'Open a ticket in the Support dashboard.'],
        ], ['id'], ['question', 'answer']);
    }

    private function seedPointShopItems(): void
    {
        if (! Schema::hasTable('point_shop_items')) {
            return;
        }

        DB::table('point_shop_items')->upsert([
            [
                'id' => 1, 
                'name' => 'Buy $5 Welcome Bonus', 
                'description' => 'Instantly get a $5 discount code!', 
                'point_cost' => 500, 
                'reward_type' => 'discount_code', 
                'discount_type_id' => 5, // Links to your $5 Welcome Bonus
                'img' => null, 
                'is_active' => true
            ],
            [
                'id' => 2, 
                'name' => 'Whale Status Ticket', 
                'description' => 'Unlock the massive 25% Whale Discount.', 
                'point_cost' => 5000, 
                'reward_type' => 'discount_code', 
                'discount_type_id' => 9, // Links to your Whale Discount
                'img' => null, 
                'is_active' => true
            ],
            [
                'id' => 3, 
                'name' => 'Free Welkin Pass', 
                'description' => 'Redeem your points for a free Welkin Moon code!', 
                'point_cost' => 1000, 
                'reward_type' => 'discount_code', 
                'discount_type_id' => 10, // Links to your Free Welkin discount
                'img' => null, 
                'is_active' => true
            ],
        ], ['id'], ['name', 'description', 'point_cost', 'reward_type', 'discount_type_id', 'img', 'is_active']);
    }

    private function seedPointShopPurchases($now): void
    {
        if (! Schema::hasTable('point_shop_purchases')) {
            return;
        }

        DB::table('point_shop_purchases')->upsert([
            [
                'id' => 1, 
                'user_id' => 2, // Bob Jones
                'point_shop_item_id' => 1, // Bought the $5 Welcome Bonus
                'points_spent' => 500, 
                'created_at' => clone $now->subDays(1)
            ],
            [
                'id' => 2, 
                'user_id' => 4, // Diana Prince
                'point_shop_item_id' => 2, // Bought the Whale Status Ticket
                'points_spent' => 5000, 
                'created_at' => clone $now->subHours(5)
            ],
            [
                'id' => 3, 
                'user_id' => 8, // Hannah Abbott
                'point_shop_item_id' => 3, // Bought the Free Welkin Pass
                'points_spent' => 1000, 
                'created_at' => clone $now->subMinutes(30)
            ],
        ], ['id'], ['user_id', 'point_shop_item_id', 'points_spent', 'created_at']);
    }

    private function seedFavorites($now): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        DB::table('favorites')->upsert([
            [
                'id' => 1, 
                'user_id' => 1, // Alice
                'product_id' => 3, // Netflix 1 Month
                'created_at' => clone $now->subDays(5)
            ],
            [
                'id' => 2, 
                'user_id' => 1, // Alice
                'product_id' => 9, // Discord Nitro
                'created_at' => clone $now->subDays(2)
            ],
            [
                'id' => 3, 
                'user_id' => 2, // Bob
                'product_id' => 1, // Steam Wallet $10
                'created_at' => clone $now->subHours(12)
            ],
            [
                'id' => 4, 
                'user_id' => 8, // Hannah
                'product_id' => 8, // Welkin Moon
                'created_at' => clone $now
            ],
        ], ['id'], ['user_id', 'product_id', 'created_at']);
    }
}
