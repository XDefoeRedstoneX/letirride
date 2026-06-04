<?php

namespace Database\Seeders;

use App\Support\GachaIconCatalog;
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

        $this->call([
            UserSeeder::class,
            TestAccountSeeder::class,
        ]);

        $this->seedCategories();
        $this->seedSubcategories();
        $this->seedProducts();
        $this->seedProductKeys();
        $this->seedDiscountTypes();
        $this->seedUserDiscounts();
        $this->seedPointShopItems();
        $this->seedPointShopPurchases($now);
        $this->seedGachaRarityChances();
        $this->seedGachaIcons();
        $this->seedGachaPools();
        $this->seedGachaBoosters();
        $this->seedNews();
        $this->seedFaqs();
        $this->seedTickets($now);
        $this->seedFavorites($now);
        $this->seedCartItems($now);
        $this->seedReferrals($now);
        $this->seedReferralConfig($now);
        $this->seedReferralTiers($now);
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
            // Games (PC game codes)
            ['id' => 1, 'category_id' => 1, 'name' => 'PC Games', 'slug' => 'pc-games'],

            // Wallet Top-Ups
            ['id' => 2, 'category_id' => 2, 'name' => 'Steam', 'slug' => 'steam'],
            ['id' => 3, 'category_id' => 2, 'name' => 'PlayStation', 'slug' => 'playstation'],
            ['id' => 4, 'category_id' => 2, 'name' => 'Nintendo', 'slug' => 'nintendo'],

            // In-Game Currency
            ['id' => 5, 'category_id' => 3, 'name' => 'Riot Games / Valorant', 'slug' => 'riot-games-valorant'],
            ['id' => 6, 'category_id' => 3, 'name' => 'Mobile Legends', 'slug' => 'mobile-legends'],
            ['id' => 7, 'category_id' => 3, 'name' => 'Fortnite', 'slug' => 'fortnite'],
            // Genshin's primary product is Genesis Crystals (in-game currency), not a subscription.
            ['id' => 9, 'category_id' => 3, 'name' => 'Genshin Impact', 'slug' => 'genshin-impact'],

            // Gift Cards
            ['id' => 8, 'category_id' => 4, 'name' => 'Roblox', 'slug' => 'roblox'],

            // Subscriptions
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
            ['id' => 5, 'category_id' => 2, 'subcategory_id' => 3, 'type' => 'voucher', 'name' => 'PSN Rp400.000', 'description' => 'PS Store Credit', 'price' => 400000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'playstation.png'],
            ['id' => 9, 'category_id' => 5, 'subcategory_id' => 12, 'type' => 'voucher', 'name' => 'Discord Nitro 1 Year', 'description' => 'Full Nitro', 'price' => 1499000.00, 'point_multiplier' => 2.00, 'is_active' => true, 'image' => 'discord.png'],
            ['id' => 10, 'category_id' => 5, 'subcategory_id' => 13, 'type' => 'voucher', 'name' => 'Xbox Game Pass 1 Month', 'description' => 'Ultimate Pass', 'price' => 259000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'xbox.png'],
            ['id' => 11, 'category_id' => 2, 'subcategory_id' => 4, 'type' => 'voucher', 'name' => 'Nintendo EShop $10', 'description' => '$10 Gift Card for Nintendo EShop', 'price' => 158000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'nintendo.png'],
            ['id' => 12, 'category_id' => 2, 'subcategory_id' => 4, 'type' => 'voucher', 'name' => 'Nintendo EShop $20', 'description' => '$20 Gift Card for Nintendo EShop', 'price' => 314000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'nintendo.png'],
            ['id' => 13, 'category_id' => 4, 'subcategory_id' => 8, 'type' => 'voucher', 'name' => 'Roblox Gift Card Rp.50000', 'description' => 'Rp.50000 Gift card for Robux', 'price' => 50000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'roblox.png'],
            ['id' => 14, 'category_id' => 4, 'subcategory_id' => 8, 'type' => 'voucher', 'name' => 'Roblox Gift Card Rp.100000', 'description' => 'Rp.100000 Gift card for Robux', 'price' => 100000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'roblox.png'],
            ['id' => 15, 'category_id' => 4, 'subcategory_id' => 8, 'type' => 'voucher', 'name' => 'Roblox Gift Card Rp.500000', 'description' => 'Rp.500000 Gift card for Robux', 'price' => 500000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'roblox.png'],
            ['id' => 16, 'category_id' => 3, 'subcategory_id' => 7, 'type' => 'voucher', 'name' => 'Fortnite V-Bucks 1000', 'description' => 'Adds 1000 V-Bucks', 'price' => 100000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'fortnite.png'],
            ['id' => 17, 'category_id' => 3, 'subcategory_id' => 7, 'type' => 'voucher', 'name' => 'Fortnite V-Bucks 2500', 'description' => 'Adds 2500 V-Bucks', 'price' => 250000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'fortnite.png'],
            ['id' => 18, 'category_id' => 3, 'subcategory_id' => 7, 'type' => 'voucher', 'name' => 'Fortnite V-Bucks 5000', 'description' => 'Adds 5000 V-Bucks', 'price' => 400000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'fortnite.png'],
            ['id' => 19, 'category_id' => 3, 'subcategory_id' => 7, 'type' => 'voucher', 'name' => 'Fortnite V-Bucks 12500', 'description' => 'Adds 12500 V-Bucks', 'price' => 800000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'fortnite.png'],
            ['id' => 20, 'category_id' => 5, 'subcategory_id' => 14, 'type' => 'voucher', 'name' => 'Youtube Premium Individual', 'description' => '1 Month Premium for 1 User', 'price' => 65000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'youtube.png'],
            ['id' => 21, 'category_id' => 5, 'subcategory_id' => 14, 'type' => 'voucher', 'name' => 'Youtube Premium Family', 'description' => '1 Month Premium for up to 5 Users', 'price' => 130000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'youtube.png'],
            ['id' => 22, 'category_id' => 5, 'subcategory_id' => 15, 'type' => 'voucher', 'name' => 'Canva Pro', 'description' => '1 Month Pro for 1 User', 'price' => 90000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'canva.png'],
            ['id' => 23, 'category_id' => 5, 'subcategory_id' => 16, 'type' => 'voucher', 'name' => 'Chatgpt Go', 'description' => '1 Month Go tier', 'price' => 75000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'chatgpt.png'],
            ['id' => 24, 'category_id' => 5, 'subcategory_id' => 16, 'type' => 'voucher', 'name' => 'Chatgpt Plus', 'description' => '1 Month Plus tier', 'price' => 300000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'chatgpt.png'],
            ['id' => 25, 'category_id' => 5, 'subcategory_id' => 16, 'type' => 'voucher', 'name' => 'Chatgpt Pro', 'description' => '1 Month Pro tier', 'price' => 1500000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'chatgpt.png'],
            ['id' => 26, 'category_id' => 5, 'subcategory_id' => 17, 'type' => 'voucher', 'name' => 'Adobe Creative Cloud Pro', 'description' => '1 Month Pro Subscription', 'price' => 500000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'adobe.png'],
            ['id' => 27, 'category_id' => 1, 'subcategory_id' => 1, 'type' => 'voucher', 'name' => 'Pragmata', 'description' => 'Steam code for Pragmata', 'price' => 750000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 28, 'category_id' => 1, 'subcategory_id' => 1, 'type' => 'voucher', 'name' => 'Resident Evil Requiem', 'description' => 'Steam code for Resident Evil Requiem', 'price' => 900000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],

            // ===== DIRECT TOP-UP products (buyer inputs Player ID) =====
            ['id' => 6, 'category_id' => 3, 'subcategory_id' => 5, 'type' => 'direct_topup', 'name' => '1000 Valorant Points', 'description' => 'Riot Games VP Ã¢â‚¬â€ requires Riot ID', 'price' => 160000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'valorant.png'],
            ['id' => 7, 'category_id' => 3, 'subcategory_id' => 6, 'type' => 'direct_topup', 'name' => '500 ML Diamonds', 'description' => 'Mobile Legends Ã¢â‚¬â€ requires Player ID & Zone ID', 'price' => 150000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'mobile-legends.png'],
            ['id' => 8, 'category_id' => 3, 'subcategory_id' => 9, 'type' => 'direct_topup', 'name' => 'Genshin Welkin Moon', 'description' => 'Genshin Impact - Blessing of the Welkin Moon, requires UID', 'price' => 79000.00, 'point_multiplier' => 0.50, 'is_active' => true, 'image' => 'google-play.png'],

            // ===== EXPANDED LADDERS — more SKUs per brand =====
            // --- Steam Wallet ---
            ['id' => 29, 'category_id' => 2, 'subcategory_id' => 2, 'type' => 'voucher', 'name' => 'Steam Wallet Rp40.000', 'description' => 'Adds Rp40.000 to Steam', 'price' => 40000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 30, 'category_id' => 2, 'subcategory_id' => 2, 'type' => 'voucher', 'name' => 'Steam Wallet Rp60.000', 'description' => 'Adds Rp60.000 to Steam', 'price' => 60000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 31, 'category_id' => 2, 'subcategory_id' => 2, 'type' => 'voucher', 'name' => 'Steam Wallet Rp400.000', 'description' => 'Adds Rp400.000 to Steam', 'price' => 400000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'steam-wallet.png'],
            // --- PlayStation / PSN ---
            ['id' => 32, 'category_id' => 2, 'subcategory_id' => 3, 'type' => 'voucher', 'name' => 'PSN Rp100.000', 'description' => 'PS Store Credit', 'price' => 100000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'playstation.png'],
            ['id' => 33, 'category_id' => 2, 'subcategory_id' => 3, 'type' => 'voucher', 'name' => 'PSN Rp200.000', 'description' => 'PS Store Credit', 'price' => 200000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'playstation.png'],
            ['id' => 34, 'category_id' => 2, 'subcategory_id' => 3, 'type' => 'voucher', 'name' => 'PSN Rp750.000', 'description' => 'PS Store Credit', 'price' => 750000.00, 'point_multiplier' => 2.00, 'is_active' => true, 'image' => 'playstation.png'],
            // --- Nintendo eShop ---
            ['id' => 35, 'category_id' => 2, 'subcategory_id' => 4, 'type' => 'voucher', 'name' => 'Nintendo eShop $5', 'description' => '$5 Gift Card for Nintendo eShop', 'price' => 80000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'nintendo.png'],
            ['id' => 36, 'category_id' => 2, 'subcategory_id' => 4, 'type' => 'voucher', 'name' => 'Nintendo eShop $50', 'description' => '$50 Gift Card for Nintendo eShop', 'price' => 780000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'nintendo.png'],
            // --- Netflix ---
            ['id' => 37, 'category_id' => 5, 'subcategory_id' => 10, 'type' => 'voucher', 'name' => 'Netflix Mobile 1 Month', 'description' => 'Mobile plan, 1 Month', 'price' => 65000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'netflix.png'],
            ['id' => 38, 'category_id' => 5, 'subcategory_id' => 10, 'type' => 'voucher', 'name' => 'Netflix Premium 1 Month', 'description' => '4K Ultra HD, 1 Month', 'price' => 186000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'netflix.png'],
            ['id' => 39, 'category_id' => 5, 'subcategory_id' => 10, 'type' => 'voucher', 'name' => 'Netflix Standard 3 Months', 'description' => 'Standard HD, 3 Months', 'price' => 690000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'netflix.png'],
            // --- Spotify ---
            ['id' => 40, 'category_id' => 5, 'subcategory_id' => 11, 'type' => 'voucher', 'name' => 'Spotify Premium 1 Month', 'description' => 'Premium Individual, 1 Month', 'price' => 65000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'spotify.png'],
            ['id' => 41, 'category_id' => 5, 'subcategory_id' => 11, 'type' => 'voucher', 'name' => 'Spotify Premium 6 Months', 'description' => 'Premium Individual, 6 Months', 'price' => 850000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'spotify.png'],
            ['id' => 42, 'category_id' => 5, 'subcategory_id' => 11, 'type' => 'voucher', 'name' => 'Spotify Premium 12 Months', 'description' => 'Premium Individual, 12 Months', 'price' => 1599000.00, 'point_multiplier' => 2.00, 'is_active' => true, 'image' => 'spotify.png'],
            // --- Discord Nitro ---
            ['id' => 43, 'category_id' => 5, 'subcategory_id' => 12, 'type' => 'voucher', 'name' => 'Discord Nitro Basic 1 Month', 'description' => 'Nitro Basic, 1 Month', 'price' => 35000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'discord.png'],
            ['id' => 44, 'category_id' => 5, 'subcategory_id' => 12, 'type' => 'voucher', 'name' => 'Discord Nitro 1 Month', 'description' => 'Full Nitro, 1 Month', 'price' => 75000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'discord.png'],
            // --- Xbox Game Pass ---
            ['id' => 45, 'category_id' => 5, 'subcategory_id' => 13, 'type' => 'voucher', 'name' => 'Xbox Game Pass Ultimate 3 Months', 'description' => 'Ultimate, 3 Months', 'price' => 720000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'xbox.png'],
            ['id' => 46, 'category_id' => 5, 'subcategory_id' => 13, 'type' => 'voucher', 'name' => 'Xbox Game Pass Ultimate 12 Months', 'description' => 'Ultimate, 12 Months', 'price' => 1990000.00, 'point_multiplier' => 2.00, 'is_active' => true, 'image' => 'xbox.png'],
            // --- YouTube Premium ---
            ['id' => 47, 'category_id' => 5, 'subcategory_id' => 14, 'type' => 'voucher', 'name' => 'Youtube Premium Individual 3 Months', 'description' => '3 Months Premium for 1 User', 'price' => 180000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'youtube.png'],
            // --- Canva ---
            ['id' => 48, 'category_id' => 5, 'subcategory_id' => 15, 'type' => 'voucher', 'name' => 'Canva Pro 12 Months', 'description' => '12 Months Pro for 1 User', 'price' => 750000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'canva.png'],
            ['id' => 49, 'category_id' => 5, 'subcategory_id' => 15, 'type' => 'voucher', 'name' => 'Canva Teams 1 Month', 'description' => '1 Month Teams (up to 5 users)', 'price' => 150000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'canva.png'],
            // --- ChatGPT / OpenAI ---
            ['id' => 50, 'category_id' => 5, 'subcategory_id' => 16, 'type' => 'voucher', 'name' => 'Chatgpt Team', 'description' => '1 Month Team tier (per seat)', 'price' => 600000.00, 'point_multiplier' => 1.50, 'is_active' => true, 'image' => 'chatgpt.png'],
            // --- Adobe ---
            ['id' => 51, 'category_id' => 5, 'subcategory_id' => 17, 'type' => 'voucher', 'name' => 'Adobe Photography Plan', 'description' => '1 Month Photography (Lightroom + Photoshop)', 'price' => 250000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'adobe.png'],
            ['id' => 52, 'category_id' => 5, 'subcategory_id' => 17, 'type' => 'voucher', 'name' => 'Adobe Single App', 'description' => '1 Month single Creative Cloud app', 'price' => 350000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'adobe.png'],
            // --- Roblox ---
            ['id' => 53, 'category_id' => 4, 'subcategory_id' => 8, 'type' => 'voucher', 'name' => 'Roblox Gift Card Rp.200000', 'description' => 'Rp.200000 Gift card for Robux', 'price' => 200000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'roblox.png'],
            // --- PC Games ---
            ['id' => 65, 'category_id' => 1, 'subcategory_id' => 1, 'type' => 'voucher', 'name' => 'Monster Hunter Wilds', 'description' => 'Steam code for Monster Hunter Wilds', 'price' => 800000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],
            ['id' => 66, 'category_id' => 1, 'subcategory_id' => 1, 'type' => 'voucher', 'name' => 'Street Fighter 6', 'description' => 'Steam code for Street Fighter 6', 'price' => 600000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'steam-wallet.png'],

            // --- Valorant Points (direct top-up) ---
            ['id' => 54, 'category_id' => 3, 'subcategory_id' => 5, 'type' => 'direct_topup', 'name' => '475 Valorant Points', 'description' => 'Riot Games VP - requires Riot ID', 'price' => 80000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'valorant.png'],
            ['id' => 55, 'category_id' => 3, 'subcategory_id' => 5, 'type' => 'direct_topup', 'name' => '2050 Valorant Points', 'description' => 'Riot Games VP - requires Riot ID', 'price' => 320000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'valorant.png'],
            ['id' => 56, 'category_id' => 3, 'subcategory_id' => 5, 'type' => 'direct_topup', 'name' => '3650 Valorant Points', 'description' => 'Riot Games VP - requires Riot ID', 'price' => 550000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'valorant.png'],
            // --- Mobile Legends Diamonds (direct top-up) ---
            ['id' => 57, 'category_id' => 3, 'subcategory_id' => 6, 'type' => 'direct_topup', 'name' => '86 ML Diamonds', 'description' => 'Mobile Legends - requires Player ID & Zone ID', 'price' => 28000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'mobile-legends.png'],
            ['id' => 58, 'category_id' => 3, 'subcategory_id' => 6, 'type' => 'direct_topup', 'name' => '172 ML Diamonds', 'description' => 'Mobile Legends - requires Player ID & Zone ID', 'price' => 55000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'mobile-legends.png'],
            ['id' => 59, 'category_id' => 3, 'subcategory_id' => 6, 'type' => 'direct_topup', 'name' => '257 ML Diamonds', 'description' => 'Mobile Legends - requires Player ID & Zone ID', 'price' => 80000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'mobile-legends.png'],
            ['id' => 60, 'category_id' => 3, 'subcategory_id' => 6, 'type' => 'direct_topup', 'name' => '706 ML Diamonds', 'description' => 'Mobile Legends - requires Player ID & Zone ID', 'price' => 200000.00, 'point_multiplier' => 1.25, 'is_active' => true, 'image' => 'mobile-legends.png'],
            // --- Genshin Genesis Crystals (direct top-up) ---
            ['id' => 61, 'category_id' => 3, 'subcategory_id' => 9, 'type' => 'direct_topup', 'name' => '60 Genesis Crystals', 'description' => 'Genshin Impact - requires UID', 'price' => 16000.00, 'point_multiplier' => 0.50, 'is_active' => true, 'image' => 'google-play.png'],
            ['id' => 62, 'category_id' => 3, 'subcategory_id' => 9, 'type' => 'direct_topup', 'name' => '330 Genesis Crystals', 'description' => 'Genshin Impact - requires UID', 'price' => 80000.00, 'point_multiplier' => 0.50, 'is_active' => true, 'image' => 'google-play.png'],
            ['id' => 63, 'category_id' => 3, 'subcategory_id' => 9, 'type' => 'direct_topup', 'name' => '980 Genesis Crystals', 'description' => 'Genshin Impact - requires UID', 'price' => 250000.00, 'point_multiplier' => 0.75, 'is_active' => true, 'image' => 'google-play.png'],
            ['id' => 64, 'category_id' => 3, 'subcategory_id' => 9, 'type' => 'direct_topup', 'name' => '1980 Genesis Crystals', 'description' => 'Genshin Impact - requires UID', 'price' => 480000.00, 'point_multiplier' => 1.00, 'is_active' => true, 'image' => 'google-play.png'],
        ];

        if (! $hasType) {
            $rows = array_map(function (array $row) {
                unset($row['type']);

                return $row;
            }, $rows);
        }

        // Overlay storefront copy from the single source of truth so fresh seeds
        // match what ProductDescriptionSeeder applies to existing databases.
        $descriptions = ProductDescriptionSeeder::DESCRIPTIONS;
        $rows = array_map(function (array $row) use ($descriptions) {
            if (isset($descriptions[$row['id']])) {
                $row['description'] = $descriptions[$row['id']];
            }

            return $row;
        }, $rows);

        DB::table('products')->upsert($rows, ['id']);
    }

    private function seedProductKeys(): void
    {
        if (! Schema::hasTable('product_keys')) {
            return;
        }

        $hasOrderId = Schema::hasColumn('product_keys', 'order_id');
        $hasReservedId = Schema::hasColumn('product_keys', 'reserved_for_order_id');

        // Voucher-type products only Ã¢â‚¬â€ direct_topup products (6, 7, 8) use Player ID, not keys
        $voucherProductIds = [
            1, 2, 3, 4, 5, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28,
            // Expanded-ladder voucher SKUs (direct_topup ids 54-64 still use Player ID, no keys).
            29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 65, 66,
        ];

        $rows = [];
        $keyId = 1;

        foreach ($voucherProductIds as $productId) {
            for ($seq = 1; $seq <= 30; $seq++) {
                $hash = strtoupper(substr(md5("p{$productId}-k{$seq}"), 0, 12));
                $keyCode = sprintf('P%03d-%s-%s-%s', $productId,
                    substr($hash, 0, 4), substr($hash, 4, 4), substr($hash, 8, 4));

                $row = [
                    'id' => $keyId++,
                    'product_id' => $productId,
                    'key_code' => $keyCode,
                    'status' => 'available',
                ];

                if ($hasOrderId) {
                    $row['order_id'] = null;
                }
                if ($hasReservedId) {
                    $row['reserved_for_order_id'] = null;
                }

                $rows[] = $row;
            }
        }

        $updateColumns = ['product_id', 'key_code', 'status'];
        if ($hasOrderId) {
            $updateColumns[] = 'order_id';
        }
        if ($hasReservedId) {
            $updateColumns[] = 'reserved_for_order_id';
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('product_keys')->upsert($chunk, ['id'], $updateColumns);
        }
    }

    private function seedDiscountTypes(): void
    {
        if (! Schema::hasTable('discount_types')) {
            return;
        }

        $hasSubcategoryTarget = Schema::hasColumn('discount_types', 'target_subcategory_id');

        // Brand-targeted vouchers pin to a subcategory (Steam, Netflix, PSN, Ã¢â‚¬Â¦);
        // category-only vouchers stay on target_category_id; storewide leaves both null.
        $rows = [
            ['id' => 1,  'name' => '10% Off All',               'type' => 'percent', 'value' => 10.00,    'target_category_id' => null, 'target_subcategory_id' => null],
            ['id' => 2,  'name' => '5% Off Steam',              'type' => 'percent', 'value' => 5.00,     'target_category_id' => null, 'target_subcategory_id' => 2],   // Steam
            ['id' => 3,  'name' => 'Rp30.000 Off Netflix',      'type' => 'fixed',   'value' => 30000.00, 'target_category_id' => null, 'target_subcategory_id' => 10],  // Netflix
            ['id' => 4,  'name' => '20% Off PSN',               'type' => 'percent', 'value' => 20.00,    'target_category_id' => null, 'target_subcategory_id' => 3],   // PlayStation
            ['id' => 5,  'name' => 'Rp75.000 Welcome Bonus',    'type' => 'fixed',   'value' => 75000.00, 'target_category_id' => null, 'target_subcategory_id' => null],
            ['id' => 6,  'name' => 'Half Price Discord',        'type' => 'percent', 'value' => 50.00,    'target_category_id' => null, 'target_subcategory_id' => 12],  // Discord
            ['id' => 7,  'name' => 'Rp15.000 Off Valorant',     'type' => 'fixed',   'value' => 15000.00, 'target_category_id' => null, 'target_subcategory_id' => 5],   // Valorant
            ['id' => 8,  'name' => '15% Off Xbox',              'type' => 'percent', 'value' => 15.00,    'target_category_id' => null, 'target_subcategory_id' => 13],  // Xbox
            ['id' => 9,  'name' => 'Whale Discount',            'type' => 'percent', 'value' => 25.00,    'target_category_id' => null, 'target_subcategory_id' => null],
            ['id' => 10, 'name' => 'Free Welkin',               'type' => 'fixed',   'value' => 79000.00, 'target_category_id' => null, 'target_subcategory_id' => 9],   // Genshin
            ['id' => 11, 'name' => '20% Off All Subscriptions', 'type' => 'percent', 'value' => 20.00,    'target_category_id' => 5,    'target_subcategory_id' => null], // Subscriptions category
            ['id' => 12, 'name' => '20% Off Steam',             'type' => 'percent', 'value' => 20.00,    'target_category_id' => null, 'target_subcategory_id' => 2],   // Steam — point shop
            ['id' => 13, 'name' => '25% Off Fortnite V-Bucks',  'type' => 'percent', 'value' => 25.00,    'target_category_id' => null, 'target_subcategory_id' => 7],   // Fortnite — point shop
            ['id' => 14, 'name' => '10% Off ChatGPT',           'type' => 'percent', 'value' => 10.00,    'target_category_id' => null, 'target_subcategory_id' => 16],  // OpenAI — point shop
            ['id' => 15, 'name' => '15% off Netflix',           'type' => 'percent', 'value' => 15.00,    'target_category_id' => null, 'target_subcategory_id' => 10],  // Netflix — point shop
            ['id' => 16, 'name' => '10% off Spotify',           'type' => 'percent', 'value' => 10.00,    'target_category_id' => null, 'target_subcategory_id' => 11],  // Spotify — point shop
            ['id' => 17, 'name' => '15% off Valorant Points',   'type' => 'percent', 'value' => 15.00,    'target_category_id' => null, 'target_subcategory_id' => 5],   // Valorant — point shop
            ['id' => 18, 'name' => '25% off Genshin products',  'type' => 'percent', 'value' => 25.00,    'target_category_id' => null, 'target_subcategory_id' => 9],   // Genshin — point shop
            ['id' => 19, 'name' => '10% off Nintendo eShop',    'type' => 'percent', 'value' => 10.00,    'target_category_id' => null, 'target_subcategory_id' => 4],  // Nintendo — point shop
            ['id' => 20, 'name' => '15% off Game keys',         'type' => 'percent', 'value' => 15.00,    'target_category_id' => 1, 'target_subcategory_id' => null],  // Game Keys — point shop
            ['id' => 21, 'name' => '25% off Subscriptions',     'type' => 'percent', 'value' => 25.00,    'target_category_id' => 5, 'target_subcategory_id' => null],  // Subscriptions — point shop
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

        $existingUserIds = DB::table('users')->pluck('id')->toArray();

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

        $rows = array_values(array_filter($rows, fn ($r) => in_array($r['user_id'], $existingUserIds)));

        if (empty($rows)) {
            return;
        }

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

    private function seedGachaRarityChances(): void
    {
        if (! Schema::hasTable('gacha_rarity_chances')) {
            return;
        }

        $now = now();

        // Per-rarity base chances that sum to exactly 100%. Per-prize odds are
        // auto-derived at runtime as base_chance / prize_count_in_rarity.
        DB::table('gacha_rarity_chances')->upsert([
            ['rarity' => 'grand_prize', 'base_chance' => 0.50,  'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['rarity' => 'legendary',   'base_chance' => 2.50,  'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['rarity' => 'epic',        'base_chance' => 7.00,  'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['rarity' => 'rare',        'base_chance' => 15.00, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['rarity' => 'uncommon',    'base_chance' => 25.00, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['rarity' => 'common',      'base_chance' => 50.00, 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
        ], ['rarity'], ['base_chance', 'sort_order', 'updated_at']);
    }

    private function seedGachaIcons(): void
    {
        if (! Schema::hasTable('gacha_icons')) {
            return;
        }

        $now = now();

        $rows = array_map(fn (array $def) => [
            'key' => $def['key'],
            'label' => $def['label'],
            'category' => $def['category'],
            'image_path' => $def['image_path'],
            'sort_order' => $def['sort_order'],
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], GachaIconCatalog::all());

        DB::table('gacha_icons')->upsert(
            $rows,
            ['key'],
            ['label', 'category', 'image_path', 'sort_order', 'is_active', 'updated_at']
        );
    }

    private function seedGachaPools(): void
    {
        if (! Schema::hasTable('gacha_pools')) {
            return;
        }

        // Rebalanced pool: 20 prizes across 6 rarities (incl. 1 "no prize" filler).
        // Per-rarity chances live in gacha_rarity_chances; per-prize odds are
        // rarity_chance / count_of_prizes_in_rarity (computed at runtime). Value
        // roughly tracks rarity now: real brand vouchers outrank points bundles.
        // NOTE: this destructively replaces the pool. gacha_histories cascades on delete.
        DB::table('gacha_pools')->delete();

        // Default cap (unlimited unless overridden) keeps every row's keys aligned.
        $rows = array_map(fn (array $r) => $r + ['max_per_user' => null, 'image_path' => null], [
            // === Grand Prize (0.50%) ===
            ['id' => 1, 'prize_name' => 'Free Steam Wallet Rp250.000', 'rarity_item' => 'grand_prize', 'reward_type' => 'discount', 'icon_key' => 'steam', 'discount_type_id' => 12, 'points_amount' => null],

            // === Legendary (2.50%) ===
            ['id' => 2, 'prize_name' => 'Rp100.000 Cash Voucher', 'rarity_item' => 'legendary', 'reward_type' => 'discount', 'icon_key' => 'cash', 'discount_type_id' => 13, 'points_amount' => null],
            ['id' => 3, 'prize_name' => '1000 Points Bundle', 'rarity_item' => 'legendary', 'reward_type' => 'points', 'icon_key' => 'points-stack', 'discount_type_id' => null, 'points_amount' => 1000],

            // === Epic (7.00%) ===
            ['id' => 4, 'prize_name' => '50% Off Discord Nitro', 'rarity_item' => 'epic', 'reward_type' => 'discount', 'icon_key' => 'discord', 'discount_type_id' => 6, 'points_amount' => null],
            ['id' => 5, 'prize_name' => 'Rp30.000 Off Netflix', 'rarity_item' => 'epic', 'reward_type' => 'discount', 'icon_key' => 'netflix', 'discount_type_id' => 3, 'points_amount' => null],
            ['id' => 6, 'prize_name' => '300 Points Stack', 'rarity_item' => 'epic', 'reward_type' => 'points', 'icon_key' => 'points-stack', 'discount_type_id' => null, 'points_amount' => 300],

            // === Rare (15.00%) ===
            ['id' => 7, 'prize_name' => '20% Off PSN', 'rarity_item' => 'rare', 'reward_type' => 'discount', 'icon_key' => 'playstation', 'discount_type_id' => 4, 'points_amount' => null],
            ['id' => 8, 'prize_name' => '15% Off Xbox', 'rarity_item' => 'rare', 'reward_type' => 'discount', 'icon_key' => 'xbox', 'discount_type_id' => 8, 'points_amount' => null],
            ['id' => 9, 'prize_name' => 'Free Spin Token', 'rarity_item' => 'rare', 'reward_type' => 'free_spin', 'icon_key' => 'free-spin', 'discount_type_id' => null, 'points_amount' => null],
            ['id' => 10, 'prize_name' => '200 Points', 'rarity_item' => 'rare', 'reward_type' => 'points', 'icon_key' => 'points-coin', 'discount_type_id' => null, 'points_amount' => 200],

            // === Uncommon (25.00%) ===
            // Welcome Bonus is a one-time sweetener — capped to a single win per user.
            ['id' => 11, 'prize_name' => 'Rp75.000 Welcome Bonus', 'rarity_item' => 'uncommon', 'reward_type' => 'discount', 'icon_key' => 'cash', 'discount_type_id' => 5, 'points_amount' => null, 'max_per_user' => 1],
            ['id' => 12, 'prize_name' => '10% Off Storewide', 'rarity_item' => 'uncommon', 'reward_type' => 'discount', 'icon_key' => 'voucher', 'discount_type_id' => 1, 'points_amount' => null],
            ['id' => 13, 'prize_name' => '5% Off Steam', 'rarity_item' => 'uncommon', 'reward_type' => 'discount', 'icon_key' => 'steam', 'discount_type_id' => 2, 'points_amount' => null],
            ['id' => 14, 'prize_name' => 'Rp15.000 Off Valorant', 'rarity_item' => 'uncommon', 'reward_type' => 'discount', 'icon_key' => 'valorant', 'discount_type_id' => 7, 'points_amount' => null],
            ['id' => 15, 'prize_name' => '100 Points', 'rarity_item' => 'uncommon', 'reward_type' => 'points', 'icon_key' => 'points-coin', 'discount_type_id' => null, 'points_amount' => 100],

            // === Common (50.00%) ===
            ['id' => 16, 'prize_name' => '50 Points', 'rarity_item' => 'common', 'reward_type' => 'points', 'icon_key' => 'points-coin', 'discount_type_id' => null, 'points_amount' => 50],
            ['id' => 17, 'prize_name' => '50 Points', 'rarity_item' => 'common', 'reward_type' => 'points', 'icon_key' => 'points-coin', 'discount_type_id' => null, 'points_amount' => 50],
            ['id' => 18, 'prize_name' => '25 Points', 'rarity_item' => 'common', 'reward_type' => 'points', 'icon_key' => 'points-coin', 'discount_type_id' => null, 'points_amount' => 25],
            ['id' => 19, 'prize_name' => '10 Points', 'rarity_item' => 'common', 'reward_type' => 'points', 'icon_key' => 'points-coin', 'discount_type_id' => null, 'points_amount' => 10],

            // === Filler (still common rarity, just a no-prize slot) ===
            ['id' => 20, 'prize_name' => 'Better Luck Next Time', 'rarity_item' => 'common', 'reward_type' => 'nothing', 'icon_key' => 'nothing', 'discount_type_id' => null, 'points_amount' => null],
        ]);

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
                'description' => '+5% Rare+ chance for 10 rolls.',
                'point_cost' => 500,
                'rarity_floor' => 'rare',
                'bonus_percent' => 5.00,
                'rolls_granted' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'key' => 'golden_touch',
                'name' => 'Golden Touch',
                'description' => '+10% Epic+ chance for 5 rolls.',
                'point_cost' => 2000,
                'rarity_floor' => 'epic',
                'bonus_percent' => 10.00,
                'rolls_granted' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], ['key', 'name', 'description', 'point_cost', 'rarity_floor', 'bonus_percent', 'rolls_granted', 'is_active', 'updated_at']);
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

        $existingUserIds = DB::table('users')->pluck('id')->toArray();
        $emails = DB::table('users')->pluck('email', 'id');

        // Member tickets (user_id set; email derived from the account).
        $member = [
            ['id' => 1, 'user_id' => 2, 'type' => 'billing', 'subject' => 'Billing or payment issue', 'message' => 'I was charged twice for Netflix!', 'status' => 'open'],
            ['id' => 2, 'user_id' => 5, 'type' => 'technical', 'subject' => 'Voucher or game key not working', 'message' => 'My PSN key says it is already used.', 'status' => 'closed'],
            ['id' => 3, 'user_id' => 8, 'type' => 'gacha', 'subject' => 'Gacha or rewards question', 'message' => 'When is the next gacha event?', 'status' => 'closed'],
            ['id' => 4, 'user_id' => 1, 'type' => 'account', 'subject' => 'Account or login help', 'message' => 'Cannot login using Google.', 'status' => 'in_progress'],
            ['id' => 5, 'user_id' => 3, 'type' => 'billing', 'subject' => 'Billing or payment issue', 'message' => "Discount code didn't apply at checkout.", 'status' => 'open'],
            ['id' => 6, 'user_id' => 6, 'type' => 'general', 'subject' => 'General question', 'message' => 'Do you sell Amazon gift cards?', 'status' => 'closed'],
            ['id' => 7, 'user_id' => 9, 'type' => 'technical', 'subject' => 'Other', 'message' => 'Website loads slowly on mobile.', 'status' => 'open'],
            ['id' => 8, 'user_id' => 10, 'type' => 'order', 'subject' => 'Order or delivery problem', 'message' => 'Refund request for order INV-2024-009.', 'status' => 'open'],
            ['id' => 9, 'user_id' => 7, 'type' => 'account', 'subject' => 'Account or login help', 'message' => "Didn't get my verification email.", 'status' => 'closed'],
        ];

        $rows = [];
        foreach ($member as $r) {
            if (! in_array($r['user_id'], $existingUserIds, true)) {
                continue;
            }
            $rows[] = [
                'id' => $r['id'],
                'user_id' => $r['user_id'],
                'email' => $emails[$r['user_id']] ?? 'member@ridly.example',
                'name' => null,
                'type' => $r['type'],
                'subject' => $r['subject'],
                'message' => $r['message'],
                'status' => $r['status'],
                'ip_address' => '127.0.0.1',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Guest tickets (no account) to exercise the guest path.
        $rows[] = [
            'id' => 10, 'user_id' => null, 'email' => 'guest.dewi@example.com', 'name' => 'Dewi',
            'type' => 'order', 'subject' => 'Order or delivery problem',
            'message' => 'I paid for a Steam wallet code but never received the email. Order placed as a guest.',
            'status' => 'open', 'ip_address' => '203.0.113.10', 'created_at' => $now, 'updated_at' => $now,
        ];
        $rows[] = [
            'id' => 11, 'user_id' => null, 'email' => 'guest.arif@example.com', 'name' => 'Arif',
            'type' => 'general', 'subject' => 'General question',
            'message' => 'Do I need an account to buy, or can I check out as a guest?',
            'status' => 'closed', 'ip_address' => '203.0.113.22', 'created_at' => $now, 'updated_at' => $now,
        ];

        DB::table('tickets')->upsert($rows, ['id'], [
            'user_id', 'email', 'name', 'type', 'subject', 'message', 'status', 'ip_address', 'updated_at',
        ]);
    }

    private function seedNews(): void
    {
        if (! Schema::hasTable('news')) {
            return;
        }

        DB::table('news')->upsert([
            ['id' => 1, 'name' => 'News 1', 'image' => 'news/1.jpg', 'sort_order' => 1, 'is_active' => true],
            ['id' => 2, 'name' => 'News 2', 'image' => 'news/2.jpg', 'sort_order' => 2, 'is_active' => true],
            ['id' => 3, 'name' => 'News 3', 'image' => 'news/3.jpg', 'sort_order' => 3, 'is_active' => true],
            ['id' => 4, 'name' => 'News 4', 'image' => 'news/4.jpg', 'sort_order' => 4, 'is_active' => true],
        ], ['id'], ['name', 'image', 'sort_order', 'is_active']);
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
                'reward_type' => 'discount',
                'discount_type_id' => 12,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],
            [
                'id' => 2,
                'name' => 'Fortnite V-Bucks Frenzy',
                'description' => 'Receive 25% off every Fortnite V-Bucks package.',
                'point_cost' => 5000,
                'reward_type' => 'discount',
                'discount_type_id' => 13,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],
            [
                'id' => 3,
                'name' => 'ChatGPT Premium Saver',
                'description' => 'Enjoy 10% off all ChatGPT subscription plans.',
                'point_cost' => 1000,
                'reward_type' => 'discount',
                'discount_type_id' => 14,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],
            [
                'id' => 4,
                'name' => 'Netflix Saver',
                'description' => '15% off Netflix subscriptions.',
                'point_cost' => 750,
                'reward_type' => 'discount_code',
                'discount_type_id' => 15,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],
            [
                'id' => 5,
                'name' => 'Spotify Fan Pass',
                'description' => '10% off Spotify subscriptions.',
                'point_cost' => 500,
                'reward_type' => 'discount_code',
                'discount_type_id' => 16,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],

            [
                'id' => 6,
                'name' => 'Valorant Sharpshooter',
                'description' => '15% off Valorant Points.',
                'point_cost' => 1200,
                'reward_type' => 'discount_code',
                'discount_type_id' => 17,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],

            [
                'id' => 7,
                'name' => 'Genshin Blessing',
                'description' => '25% off Genshin products.',
                'point_cost' => 2000,
                'reward_type' => 'discount_code',
                'discount_type_id' => 18,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],

            [
                'id' => 8,
                'name' => 'Nintendo Club',
                'description' => '10% off Nintendo eShop vouchers.',
                'point_cost' => 1000,
                'reward_type' => 'discount_code',
                'discount_type_id' => 19,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],

            [
                'id' => 9,
                'name' => 'Game Hunter',
                'description' => '15% off any game key.',
                'point_cost' => 3000,
                'reward_type' => 'discount_code',
                'discount_type_id' => 20,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],

            [
                'id' => 10,
                'name' => 'Subscription King',
                'description' => '25% off any subscription product.',
                'point_cost' => 5000,
                'reward_type' => 'discount_code',
                'discount_type_id' => 21,
                'points_amount' => null,
                'img' => null,
                'is_active' => true,
            ],
            [
                'id' => 11,
                'name' => 'Loyalty Cashback',
                'description' => 'Trade in points for a 400-point loyalty cashback.',
                'point_cost' => 500,
                'reward_type' => 'cashback',
                'discount_type_id' => null,
                'points_amount' => 400,
                'img' => null,
                'is_active' => true,
            ],
        ], ['id'], ['name', 'description', 'point_cost', 'reward_type', 'discount_type_id', 'points_amount', 'img', 'is_active']);
    }

    private function seedPointShopPurchases($now): void
    {
        if (! Schema::hasTable('point_shop_purchases')) {
            return;
        }

        $existingUserIds = DB::table('users')->pluck('id')->toArray();

        $rows = array_values(array_filter([
            ['id' => 1, 'user_id' => 2, 'point_shop_item_id' => 1, 'points_spent' => 500,  'created_at' => (clone $now)->subDays(1)],
            ['id' => 2, 'user_id' => 4, 'point_shop_item_id' => 2, 'points_spent' => 5000, 'created_at' => (clone $now)->subHours(5)],
            ['id' => 3, 'user_id' => 8, 'point_shop_item_id' => 3, 'points_spent' => 1000, 'created_at' => (clone $now)->subMinutes(30)],
        ], fn ($r) => in_array($r['user_id'], $existingUserIds)));

        if (! empty($rows)) {
            DB::table('point_shop_purchases')->upsert($rows, ['id'], ['user_id', 'point_shop_item_id', 'points_spent', 'created_at']);
        }
    }

    private function seedFavorites($now): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        $existingUserIds = DB::table('users')->pluck('id')->toArray();

        $rows = array_values(array_filter([
            ['id' => 1, 'user_id' => 1, 'product_id' => 3, 'created_at' => (clone $now)->subDays(5)],
            ['id' => 2, 'user_id' => 1, 'product_id' => 9, 'created_at' => (clone $now)->subDays(2)],
            ['id' => 3, 'user_id' => 2, 'product_id' => 1, 'created_at' => (clone $now)->subHours(12)],
            ['id' => 4, 'user_id' => 8, 'product_id' => 8, 'created_at' => clone $now],
        ], fn ($r) => in_array($r['user_id'], $existingUserIds)));

        if (! empty($rows)) {
            DB::table('favorites')->upsert($rows, ['id'], ['user_id', 'product_id', 'created_at']);
        }
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

        // Need users with IDs 1 and 2 specifically to seed referrals
        if (! DB::table('users')->where('id', 1)->exists() || ! DB::table('users')->where('id', 2)->exists()) {
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

        // Upsert the referral record using the post-migration schema.
        $exists = DB::table('referrals')
            ->where('referrer_id', $alice)
            ->where('referred_user_id', $bob)
            ->exists();

        if (! $exists) {
            DB::table('referrals')->insert([
                'referrer_id' => $alice,
                'referred_user_id' => $bob,
                'status' => 'first_purchase_rewarded',
                'first_purchase_rewarded_at' => $now,
                'total_commission_paid' => 0,
                'created_at' => $now,
            ]);
        }
    }

    private function seedReferralConfig($now): void
    {
        if (! Schema::hasTable('referral_configs')) {
            return;
        }

        DB::table('referral_configs')->upsert([
            [
                'id' => 1,
                'referee_welcome_points' => 100,
                'referrer_first_purchase_pts' => 500,
                'commission_percent' => 0,
                'commission_per_order_cap' => 5000,
                'commission_lifetime_cap' => 50000,
                'referrer_min_account_age_h' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], [
            'referee_welcome_points',
            'referrer_first_purchase_pts',
            'commission_percent',
            'commission_per_order_cap',
            'commission_lifetime_cap',
            'referrer_min_account_age_h',
            'updated_at',
        ]);
    }

    private function seedReferralTiers($now): void
    {
        if (! Schema::hasTable('referral_tiers')) {
            return;
        }

        // Look up a few discount type ids defensively — they only exist after
        // seedDiscountTypes has run, which it has by this point.
        $steam5 = DB::table('discount_types')->where('id', 2)->value('id');      // 5% Off Steam
        $netflix30k = DB::table('discount_types')->where('id', 3)->value('id');  // Rp30.000 Off Netflix
        $steamWallet = DB::table('discount_types')->where('id', 12)->value('id'); // Free Steam Wallet Rp250.000

        DB::table('referral_tiers')->upsert([
            [
                'id' => 1,
                'threshold' => 1,
                'title' => 'First Catch',
                'description' => 'One friend in. Welcome to the rewards ladder.',
                'points_reward' => 200,
                'discount_type_id' => null,
                'free_spins_reward' => 0,
                'icon' => 'sparkles',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'threshold' => 3,
                'title' => 'Triple Threat',
                'description' => 'Three paying friends. Have a free spin on the house.',
                'points_reward' => 500,
                'discount_type_id' => null,
                'free_spins_reward' => 1,
                'icon' => 'flame',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'threshold' => 5,
                'title' => 'Hot Streak',
                'description' => 'Five and counting. Big points + a Steam voucher.',
                'points_reward' => 1000,
                'discount_type_id' => $steam5,
                'free_spins_reward' => 2,
                'icon' => 'zap',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'threshold' => 10,
                'title' => 'Squad Leader',
                'description' => 'Ten friends locked in. A heavyweight package.',
                'points_reward' => 2500,
                'discount_type_id' => $netflix30k,
                'free_spins_reward' => 5,
                'icon' => 'shield',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'threshold' => 25,
                'title' => 'Hall of Fame',
                'description' => 'Twenty-five paying friends. You\'re carrying us.',
                'points_reward' => 10000,
                'discount_type_id' => $steamWallet,
                'free_spins_reward' => 10,
                'icon' => 'crown',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], [
            'threshold', 'title', 'description', 'points_reward', 'discount_type_id',
            'free_spins_reward', 'icon', 'sort_order', 'is_active', 'updated_at',
        ]);
    }
}
