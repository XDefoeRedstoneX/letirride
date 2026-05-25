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

        // NOTE: seedUsers() and seedOrders() are intentionally NOT called here.
        // Running them on a live server overwrites real user accounts and orders.
        // To seed test data locally, call them manually:
        //   $this->seedUsers($now);
        //   $this->seedOrders($now);
        //   $this->seedOrderDetails();

        $this->seedCategories();
        $this->seedSubcategories();
        $this->seedProducts();
        $this->seedDiscountTypes();
        $this->seedPointShopItems();
        $this->seedGachaPools();
        $this->seedGachaBoosters();
        $this->seedFaqs();
    }

    private function seedUsers($now): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $users = [
            ['id' => 1, 'name' => 'nig', 'email' => 'text@example.com', 'password' => 'password', 'role' => 'buyer', 'points_balance' => 9999],
            ['id' => 2, 'name' => 'Bob Jones', 'email' => 'bob@example.com', 'password' => '$2y$10$dummyhashplaceholder', 'role' => 'buyer', 'points_balance' => 0],
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
            ['id' => 1, 'name' => 'Games', 'slug' => 'games'],
            ['id' => 2, 'name' => 'Wallet Top-Ups', 'slug' => 'wallet-top-ups'],
            ['id' => 3, 'name' => 'In-Game Currency', 'slug' => 'in-game-currency'],
            ['id' => 4, 'name' => 'Gift Cards', 'slug' => 'gift-cards'],
            ['id' => 5, 'name' => 'Subscriptions', 'slug' => 'subscriptions'],
        ], ['id'], ['name', 'slug']);
    }

    private function seedSubcategories(): void
    {
        if (! Schema::hasTable('subcategories')) {
            return;
        }

        DB::table('subcategories')->upsert([
            // Games
            ['id' => 1, 'category_id' => 1, 'name' => 'Capcom', 'slug' => 'capcom'],

            // Wallet Top-Ups
            ['id' => 2, 'category_id' => 2, 'name' => 'Steam', 'slug' => 'steam'],
            ['id' => 3, 'category_id' => 2, 'name' => 'PlayStation', 'slug' => 'playstation'],
            ['id' => 4, 'category_id' => 2, 'name' => 'Nintendo', 'slug' => 'nintendo'],

            // In-Game Currency
            ['id' => 5, 'category_id' => 3, 'name' => 'Riot Games / Valorant', 'slug' => 'riot-games-valorant'],
            ['id' => 6, 'category_id' => 3, 'name' => 'Mobile Legends', 'slug' => 'mobile-legends'],
            ['id' => 7, 'category_id' => 3, 'name' => 'Fortnite', 'slug' => 'fortnite'],

            // Gift Cards
            ['id' => 8, 'category_id' => 4, 'name' => 'Roblox', 'slug' => 'roblox'],

            // Subscriptions
            ['id' => 9, 'category_id' => 5, 'name' => 'Genshin Impact', 'slug' => 'genshin-impact'],
            ['id' => 10, 'category_id' => 5, 'name' => 'Netflix', 'slug' => 'netflix'],
            ['id' => 11, 'category_id' => 5, 'name' => 'Spotify', 'slug' => 'spotify'],
            ['id' => 12, 'category_id' => 5, 'name' => 'Discord', 'slug' => 'discord'],
            ['id' => 13, 'category_id' => 5, 'name' => 'Xbox', 'slug' => 'xbox'],
            ['id' => 14, 'category_id' => 5, 'name' => 'YouTube', 'slug' => 'youtube'],
            ['id' => 15, 'category_id' => 5, 'name' => 'Canva', 'slug' => 'canva'],
            ['id' => 16, 'category_id' => 5, 'name' => 'OpenAI', 'slug' => 'openai'],
            ['id' => 17, 'category_id' => 5, 'name' => 'Adobe', 'slug' => 'adobe'],

        ], ['id'], ['category_id', 'name', 'slug']);
    }

    private function seedProducts(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $hasType = Schema::hasColumn('products', 'type');

        $rows = [
            // ===== VOUCHER CODE products (auto-delivery key) =====
            ['id' => 1, 'category_id' => 2, 'subcategory_id' => 2, 'type' => 'voucher', 'name' => 'Steam Wallet Rp150.000', 'description' => 'Adds Rp150.000 to Steam', 'price' => 150000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 2, 'category_id' => 2, 'subcategory_id' => 2, 'type' => 'voucher', 'name' => 'Steam Wallet Rp750.000', 'description' => 'Adds Rp750.000 to Steam', 'price' => 750000.00, 'point_multiplier' => 2.00, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 3, 'category_id' => 5, 'subcategory_id' => 10, 'type' => 'voucher', 'name' => 'Netflix 1 Month (HD)', 'description' => 'Standard 1 Month', 'price' => 249000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'netflix.png'],
            ['id' => 4, 'category_id' => 5, 'subcategory_id' => 11, 'type' => 'voucher', 'name' => 'Spotify 3 Months', 'description' => 'Premium Code', 'price' => 449000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'spotify.png'],
            ['id' => 5, 'category_id' => 2, 'subcategory_id' => 3, 'type' => 'voucher', 'name' => 'PSN Rp400.000', 'description' => 'PS Store Credit', 'price' => 400000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'google-play.png'],
            ['id' => 9, 'category_id' => 5, 'subcategory_id' => 12, 'type' => 'voucher', 'name' => 'Discord Nitro 1 Year', 'description' => 'Full Nitro', 'price' => 1499000.00, 'point_multiplier' => 2.00, 'is_active' => true, 'image' => 'discord.png'],
            ['id' => 10, 'category_id' => 5, 'subcategory_id' => 13, 'type' => 'voucher', 'name' => 'Xbox Game Pass 1 Month', 'description' => 'Ultimate Pass', 'price' => 259000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 11, 'category_id' => 2, 'subcategory_id' => 4, 'type' => 'voucher', 'name' => 'Nintendo EShop $10', 'description' => '$10 Gift Card for Nintendo EShop', 'price' => 158000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 12, 'category_id' => 2, 'subcategory_id' => 4, 'type' => 'voucher', 'name' => 'Nintendo EShop $20', 'description' => '$20 Gift Card for Nintendo EShop', 'price' => 314000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 13, 'category_id' => 4, 'subcategory_id' => 8, 'type' => 'voucher', 'name' => 'Roblox Gift Card Rp.50000', 'description' => 'Rp.50000 Gift card for Robux', 'price' => 50000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 14, 'category_id' => 4, 'subcategory_id' => 8, 'type' => 'voucher', 'name' => 'Roblox Gift Card Rp.100000', 'description' => 'Rp.100000 Gift card for Robux', 'price' => 100000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 15, 'category_id' => 4, 'subcategory_id' => 8, 'type' => 'voucher', 'name' => 'Roblox Gift Card Rp.500000', 'description' => 'Rp.500000 Gift card for Robux', 'price' => 500000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 16, 'category_id' => 3, 'subcategory_id' => 7, 'type' => 'voucher', 'name' => 'Fortnite V-Bucks 1000', 'description' => 'Adds 1000 V-Bucks', 'price' => 100000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 17, 'category_id' => 3, 'subcategory_id' => 7, 'type' => 'voucher', 'name' => 'Fortnite V-Bucks 2500', 'description' => 'Adds 2500 V-Bucks', 'price' => 250000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 18, 'category_id' => 3, 'subcategory_id' => 7, 'type' => 'voucher', 'name' => 'Fortnite V-Bucks 5000', 'description' => 'Adds 5000 V-Bucks', 'price' => 400000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 19, 'category_id' => 3, 'subcategory_id' => 7, 'type' => 'voucher', 'name' => 'Fortnite V-Bucks 12500', 'description' => 'Adds 12500 V-Bucks', 'price' => 800000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 20, 'category_id' => 5, 'subcategory_id' => 14, 'type' => 'voucher', 'name' => 'Youtube Premium Individual', 'description' => '1 Month Premium for 1 User', 'price' => 65000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'youtube.png'],
            ['id' => 21, 'category_id' => 5, 'subcategory_id' => 14, 'type' => 'voucher', 'name' => 'Youtube Premium Family', 'description' => '1 Month Premium for up to 5 Users', 'price' => 130000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'youtube.png'],
            ['id' => 22, 'category_id' => 5, 'subcategory_id' => 15, 'type' => 'voucher', 'name' => 'Canva Pro', 'description' => '1 Month Pro for 1 User', 'price' => 90000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 23, 'category_id' => 5, 'subcategory_id' => 16, 'type' => 'voucher', 'name' => 'Chatgpt Go', 'description' => '1 Month Go tier', 'price' => 75000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 24, 'category_id' => 5, 'subcategory_id' => 16, 'type' => 'voucher', 'name' => 'Chatgpt Plus', 'description' => '1 Month Plus tier', 'price' => 300000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 25, 'category_id' => 5, 'subcategory_id' => 16, 'type' => 'voucher', 'name' => 'Chatgpt Pro', 'description' => '1 Month Pro tier', 'price' => 1500000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 26, 'category_id' => 5, 'subcategory_id' => 17, 'type' => 'voucher', 'name' => 'Adobe Creative Cloud Pro', 'description' => '1 Month Pro Subscription', 'price' => 500000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 27, 'category_id' => 1, 'subcategory_id' => 1, 'type' => 'voucher', 'name' => 'Pragmata', 'description' => 'Steam code for Pragmata', 'price' => 750000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 28, 'category_id' => 1, 'subcategory_id' => 1, 'type' => 'voucher', 'name' => 'Resident Evil Requiem', 'description' => 'Steam code for Resident Evil Requiem', 'price' => 900000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],

            // ===== DIRECT TOP-UP products (buyer inputs Player ID) =====
            ['id' => 6, 'category_id' => 3, 'subcategory_id' => 5, 'type' => 'direct_topup', 'name' => '1000 Valorant Points', 'description' => 'Riot Games VP — requires Riot ID', 'price' => 160000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 7, 'category_id' => 3, 'subcategory_id' => 6, 'type' => 'direct_topup', 'name' => '500 ML Diamonds', 'description' => 'Mobile Legends — requires Player ID & Zone ID', 'price' => 150000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'google-play.png'],
            ['id' => 8, 'category_id' => 5, 'subcategory_id' => 9, 'type' => 'direct_topup', 'name' => 'Welkin Moon', 'description' => 'Genshin Impact — requires UID', 'price' => 79000.00, 'point_multiplier' => 0.50, 'is_active' => true, 'image' => 'youtube.png'],
        ];

        if (! $hasType) {
            $rows = array_map(function (array $row) {
                unset($row['type']);

                return $row;
            }, $rows);
        }

        DB::table('products')->upsert($rows, ['id']);
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
            ['id' => 5, 'product_id' => 5, 'key_code' => 'PSN-ZZZZ-XXXX-YYYY', 'status' => 'sold'],
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

        $hasSubcategoryTarget = Schema::hasColumn('discount_types', 'target_subcategory_id');

        // Brand-targeted vouchers pin to a subcategory (Steam, Netflix, PSN, …);
        // category-only vouchers stay on target_category_id; storewide leaves both null.
        $rows = [
            ['id' => 1,  'name' => '10% Off All',            'type' => 'percent', 'value' => 10.00,    'target_category_id' => null, 'target_subcategory_id' => null],
            ['id' => 2,  'name' => '5% Off Steam',           'type' => 'percent', 'value' => 5.00,     'target_category_id' => null, 'target_subcategory_id' => 2],   // Steam
            ['id' => 3,  'name' => 'Rp30.000 Off Netflix',   'type' => 'fixed',   'value' => 30000.00, 'target_category_id' => null, 'target_subcategory_id' => 10],  // Netflix
            ['id' => 4,  'name' => '20% Off PSN',            'type' => 'percent', 'value' => 20.00,    'target_category_id' => null, 'target_subcategory_id' => 3],   // PlayStation
            ['id' => 5,  'name' => 'Rp75.000 Welcome Bonus', 'type' => 'fixed',   'value' => 75000.00, 'target_category_id' => null, 'target_subcategory_id' => null],
            ['id' => 6,  'name' => 'Half Price Discord',     'type' => 'percent', 'value' => 50.00,    'target_category_id' => null, 'target_subcategory_id' => 12],  // Discord
            ['id' => 7,  'name' => 'Rp15.000 Off Valorant',  'type' => 'fixed',   'value' => 15000.00, 'target_category_id' => null, 'target_subcategory_id' => 5],   // Valorant
            ['id' => 8,  'name' => '15% Off Xbox',           'type' => 'percent', 'value' => 15.00,    'target_category_id' => null, 'target_subcategory_id' => 13],  // Xbox
            ['id' => 9,  'name' => 'Whale Discount',         'type' => 'percent', 'value' => 25.00,    'target_category_id' => null, 'target_subcategory_id' => null],
            ['id' => 10, 'name' => 'Free Welkin',            'type' => 'fixed',   'value' => 79000.00, 'target_category_id' => null, 'target_subcategory_id' => 9],   // Genshin
            ['id' => 11, 'name' => '20% Off All Subscriptions', 'type' => 'percent', 'value' => 20.00, 'target_category_id' => 5,    'target_subcategory_id' => null], // category-level
        ];

        if (! $hasSubcategoryTarget) {
            $rows = array_map(function (array $row) {
                unset($row['target_subcategory_id']);

                return $row;
            }, $rows);
        }

        $updateColumns = ['name', 'type', 'value', 'target_category_id'];
        if ($hasSubcategoryTarget) {
            $updateColumns[] = 'target_subcategory_id';
        }

        DB::table('discount_types')->upsert($rows, ['id'], $updateColumns);
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

        // Revised pool: 18 prizes across 6 rarities + 1 "no prize" filler.
        // Odds sum to 100% — no implicit re-normalization. Top tiers: Grand 0.5%, Legendary 2%, Epic 7%.
        // NOTE: this destructively replaces the pool. gacha_histories cascades on delete.
        DB::table('gacha_pools')->delete();

        $hasNewColumns = Schema::hasColumn('gacha_pools', 'reward_type');

        $rows = [
            // === Grand Prize (0.5%) ===
            ['id' => 1, 'prize_name' => 'Whale Status', 'rarity_item' => 'grand_prize', 'reward_type' => 'discount', 'discount_type_id' => 9, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 0.5000],

            // === Legendary (2.0%) ===
            ['id' => 2, 'prize_name' => 'Free Welkin Moon', 'rarity_item' => 'legendary', 'reward_type' => 'discount', 'discount_type_id' => 10, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 1.0000],
            ['id' => 3, 'prize_name' => '1000 Points Bundle', 'rarity_item' => 'legendary', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 1000, 'image_path' => null, 'base_win_chance' => 1.0000],

            // === Epic (7.0%) ===
            ['id' => 4, 'prize_name' => '50% Off Discord Nitro', 'rarity_item' => 'epic', 'reward_type' => 'discount', 'discount_type_id' => 6, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 2.5000],
            ['id' => 5, 'prize_name' => '20% Off PSN', 'rarity_item' => 'epic', 'reward_type' => 'discount', 'discount_type_id' => 4, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 2.5000],
            ['id' => 6, 'prize_name' => '500 Points Stack', 'rarity_item' => 'epic', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 500, 'image_path' => null, 'base_win_chance' => 2.0000],

            // === Rare (15.0%) ===
            ['id' => 7, 'prize_name' => 'Rp75.000 Welcome Bonus', 'rarity_item' => 'rare', 'reward_type' => 'discount', 'discount_type_id' => 5, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 4.0000],
            ['id' => 8, 'prize_name' => 'Rp30.000 Off Netflix', 'rarity_item' => 'rare', 'reward_type' => 'discount', 'discount_type_id' => 3, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 4.0000],
            ['id' => 9, 'prize_name' => '15% Off Xbox', 'rarity_item' => 'rare', 'reward_type' => 'discount', 'discount_type_id' => 8, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 4.0000],
            ['id' => 10, 'prize_name' => 'Free Spin Token', 'rarity_item' => 'rare', 'reward_type' => 'free_spin', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 3.0000],

            // === Uncommon (25.5%) ===
            ['id' => 11, 'prize_name' => '10% Off Storewide', 'rarity_item' => 'uncommon', 'reward_type' => 'discount', 'discount_type_id' => 1, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 7.0000],
            ['id' => 12, 'prize_name' => '5% Off Steam', 'rarity_item' => 'uncommon', 'reward_type' => 'discount', 'discount_type_id' => 2, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 6.5000],
            ['id' => 13, 'prize_name' => 'Rp15.000 Off Valorant', 'rarity_item' => 'uncommon', 'reward_type' => 'discount', 'discount_type_id' => 7, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 6.0000],
            ['id' => 14, 'prize_name' => '200 Points', 'rarity_item' => 'uncommon', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 200, 'image_path' => null, 'base_win_chance' => 6.0000],

            // === Common (30.0%) ===
            ['id' => 15, 'prize_name' => '100 Points', 'rarity_item' => 'common', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 100, 'image_path' => null, 'base_win_chance' => 7.5000],
            ['id' => 16, 'prize_name' => '50 Points', 'rarity_item' => 'common', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 50, 'image_path' => null, 'base_win_chance' => 7.5000],
            ['id' => 17, 'prize_name' => '50 Points', 'rarity_item' => 'common', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 50, 'image_path' => null, 'base_win_chance' => 7.5000],
            ['id' => 18, 'prize_name' => '25 Points', 'rarity_item' => 'common', 'reward_type' => 'points', 'discount_type_id' => null, 'points_amount' => 25, 'image_path' => null, 'base_win_chance' => 7.5000],

            // === No Prize (20.0%) ===
            ['id' => 19, 'prize_name' => 'Better Luck Next Time', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'discount_type_id' => null, 'points_amount' => null, 'image_path' => null, 'base_win_chance' => 20.0000],
        ];

        if (! $hasNewColumns) {
            $rows = array_map(function (array $row) {
                unset($row['reward_type'], $row['points_amount'], $row['image_path']);

                return $row;
            }, $rows);
        }

        DB::table('gacha_pools')->insert($rows);
    }

    private function seedGachaBoosters(): void
    {
        if (! Schema::hasTable('gacha_boosters')) {
            return;
        }

        $now = now();

        DB::table('gacha_boosters')->upsert([
            [
                'id' => 1,
                'key' => 'lucky_charm',
                'name' => 'Lucky Charm',
                'description' => '+5% Rare+ chance for 30 minutes.',
                'point_cost' => 500,
                'rarity_floor' => 'rare',
                'bonus_percent' => 5.00,
                'duration_minutes' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'key' => 'golden_touch',
                'name' => 'Golden Touch',
                'description' => '+10% Epic+ chance for 15 minutes.',
                'point_cost' => 2000,
                'rarity_floor' => 'epic',
                'bonus_percent' => 10.00,
                'duration_minutes' => 15,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], ['key', 'name', 'description', 'point_cost', 'rarity_floor', 'bonus_percent', 'duration_minutes', 'is_active', 'updated_at']);
    }

    private function seedOrders($now): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        $ordersHasCreatedAt = Schema::hasColumn('orders', 'created_at');

        $rows = [
            ['id' => 1, 'noinv' => 'INV-2024-001', 'user_id' => 1, 'user_discount_id' => null, 'subtotal' => 150000.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 150000.00, 'payment_gateway_ref' => 'ch_1A2B3C4D5E', 'status' => 'paid'],
            ['id' => 2, 'noinv' => 'INV-2024-002', 'user_id' => 2, 'user_discount_id' => 2, 'subtotal' => 249000.00, 'discount_amount' => 75000.00, 'total_price_after_discount' => 174000.00, 'payment_gateway_ref' => 'ch_9Z8Y7X6W', 'status' => 'paid'],
            ['id' => 3, 'noinv' => 'INV-2024-003', 'user_id' => 3, 'user_discount_id' => null, 'subtotal' => 449000.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 449000.00, 'payment_gateway_ref' => 'paypal_TX123', 'status' => 'pending'],
            ['id' => 4, 'noinv' => 'INV-2024-004', 'user_id' => 5, 'user_discount_id' => 5, 'subtotal' => 249000.00, 'discount_amount' => 30000.00, 'total_price_after_discount' => 219000.00, 'payment_gateway_ref' => 'ch_5F4G3H2J', 'status' => 'paid'],
            ['id' => 5, 'noinv' => 'INV-2024-005', 'user_id' => 9, 'user_discount_id' => 9, 'subtotal' => 1499000.00, 'discount_amount' => 749500.00, 'total_price_after_discount' => 749500.00, 'payment_gateway_ref' => 'paypal_TX999', 'status' => 'paid'],
            ['id' => 6, 'noinv' => 'INV-2024-006', 'user_id' => 4, 'user_discount_id' => null, 'subtotal' => 1500000.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 1500000.00, 'payment_gateway_ref' => null, 'status' => 'failed'],
            ['id' => 7, 'noinv' => 'INV-2024-007', 'user_id' => 8, 'user_discount_id' => null, 'subtotal' => 79000.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 79000.00, 'payment_gateway_ref' => 'ch_11223344', 'status' => 'paid'],
            ['id' => 8, 'noinv' => 'INV-2024-008', 'user_id' => 6, 'user_discount_id' => null, 'subtotal' => 400000.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 400000.00, 'payment_gateway_ref' => 'paypal_TX456', 'status' => 'pending'],
            ['id' => 9, 'noinv' => 'INV-2024-009', 'user_id' => 10, 'user_discount_id' => null, 'subtotal' => 160000.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 160000.00, 'payment_gateway_ref' => 'ch_55667788', 'status' => 'paid'],
            ['id' => 10, 'noinv' => 'INV-2024-010', 'user_id' => 7, 'user_discount_id' => null, 'subtotal' => 259000.00, 'discount_amount' => 0.00, 'total_price_after_discount' => 259000.00, 'payment_gateway_ref' => 'paypal_TX789', 'status' => 'failed'],
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
            ['id' => 1, 'order_id' => 1, 'product_id' => 1, 'quantity' => 1, 'total_price_in_cart' => 150000.00],
            ['id' => 2, 'order_id' => 2, 'product_id' => 3, 'quantity' => 1, 'total_price_in_cart' => 249000.00],
            ['id' => 3, 'order_id' => 3, 'product_id' => 4, 'quantity' => 1, 'total_price_in_cart' => 449000.00],
            ['id' => 4, 'order_id' => 4, 'product_id' => 3, 'quantity' => 1, 'total_price_in_cart' => 249000.00],
            ['id' => 5, 'order_id' => 5, 'product_id' => 9, 'quantity' => 1, 'total_price_in_cart' => 1499000.00],
            ['id' => 6, 'order_id' => 6, 'product_id' => 2, 'quantity' => 2, 'total_price_in_cart' => 1500000.00],
            ['id' => 7, 'order_id' => 7, 'product_id' => 8, 'quantity' => 1, 'total_price_in_cart' => 79000.00],
            ['id' => 8, 'order_id' => 8, 'product_id' => 5, 'quantity' => 1, 'total_price_in_cart' => 400000.00],
            ['id' => 9, 'order_id' => 9, 'product_id' => 6, 'quantity' => 1, 'total_price_in_cart' => 160000.00],
            ['id' => 10, 'order_id' => 10, 'product_id' => 10, 'quantity' => 1, 'total_price_in_cart' => 259000.00],
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
                'name' => 'Steam Master Discount',
                'description' => 'Get 20% off all Steam products and wallet top-ups.',
                'point_cost' => 500,
                'reward_type' => 'discount_code',
                'discount_type_id' => 5,
                'img' => null,
                'is_active' => true,
            ],
            [
                'id' => 2,
                'name' => 'Fortnite V-Bucks Frenzy',
                'description' => 'Receive 25% off every Fortnite V-Bucks package.',
                'point_cost' => 5000,
                'reward_type' => 'discount_code',
                'discount_type_id' => 9,
                'img' => null,
                'is_active' => true,
            ],
            [
                'id' => 3,
                'name' => 'ChatGPT Premium Saver',
                'description' => 'Enjoy 10% off all ChatGPT subscription plans.',
                'point_cost' => 1000,
                'reward_type' => 'discount_code',
                'discount_type_id' => 10,
                'img' => null,
                'is_active' => true,
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
                'user_id' => 2,
                'point_shop_item_id' => 1,
                'points_spent' => 500,
                'created_at' => (clone $now)->subDays(1),
            ],
            [
                'id' => 2,
                'user_id' => 4,
                'point_shop_item_id' => 2,
                'points_spent' => 5000,
                'created_at' => (clone $now)->subHours(5),
            ],
            [
                'id' => 3,
                'user_id' => 8,
                'point_shop_item_id' => 3,
                'points_spent' => 1000,
                'created_at' => (clone $now)->subMinutes(30),
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
                'user_id' => 1,
                'product_id' => 3,
                'created_at' => (clone $now)->subDays(5),
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'product_id' => 9,
                'created_at' => (clone $now)->subDays(2),
            ],
            [
                'id' => 3,
                'user_id' => 2,
                'product_id' => 1,
                'created_at' => (clone $now)->subHours(12),
            ],
            [
                'id' => 4,
                'user_id' => 8,
                'product_id' => 8,
                'created_at' => clone $now,
            ],
        ], ['id'], ['user_id', 'product_id', 'created_at']);
    }

    private function seedCartItems($now): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        // Skip if cart items already exist
        if (DB::table('cart_items')->exists()) {
            return;
        }

        $userIds = DB::table('users')->pluck('id');
        $productIds = DB::table('products')->pluck('id');

        if ($userIds->isEmpty() || $productIds->isEmpty()) {
            return;
        }

        DB::table('cart_items')->insert([
            [
                'user_id' => $userIds->first(),
                'product_id' => $productIds->random(),
                'quantity' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userIds->first(),
                'product_id' => $productIds->random(),
                'quantity' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function seedReferrals($now): void
    {
        if (! Schema::hasTable('referrals')) {
            return;
        }

        $userIds = DB::table('users')->pluck('id');
        $discountIds = DB::table('user_discounts')->pluck('id');

        if ($userIds->count() < 2) {
            return;
        }

        $alice = 1;
        $bob = 2;

        // Only set referral codes if they don't already have one
        $aliceCode = DB::table('users')->where('id', $alice)->value('referral_code');
        if (! $aliceCode) {
            DB::table('users')->where('id', $alice)->update(['referral_code' => 'ALICE-2026']);
        }

        $bobCode = DB::table('users')->where('id', $bob)->value('referral_code');
        if (! $bobCode) {
            DB::table('users')->where('id', $bob)->update([
                'referral_code' => 'BOB-2026',
                'referred_by' => $alice,
            ]);
        }

        // Upsert the referral record
        $exists = DB::table('referrals')
            ->where('referrer_id', $alice)
            ->where('referred_user_id', $bob)
            ->exists();

        if (! $exists && $discountIds->isNotEmpty()) {
            DB::table('referrals')->insert([
                'referrer_id' => $alice,
                'referred_user_id' => $bob,
                'reward_discount_id' => $discountIds->first(),
                'status' => 'rewarded',
                'created_at' => $now,
            ]);
        }
    }
}
