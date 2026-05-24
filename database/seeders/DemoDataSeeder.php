<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const PRODUCT_PRICES = [
        1 => 150000,  2 => 750000,  3 => 249000,  4 => 449000,
        5 => 400000,  6 => 160000,  7 => 150000,  8 => 79000,
        9 => 1499000, 10 => 259000, 11 => 158000, 12 => 314000,
        13 => 50000,  14 => 100000, 15 => 500000, 16 => 100000,
        17 => 250000, 18 => 400000, 19 => 800000, 20 => 65000,
        21 => 130000, 22 => 90000,  23 => 75000,  24 => 300000,
        25 => 1500000, 26 => 500000, 27 => 750000, 28 => 900000,
    ];

    public function run(): void
    {
        $this->seedDemoUsers();
        $this->seedDemoOrders();
    }

    private function seedDemoUsers(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $hasReferralCode = Schema::hasColumn('users', 'referral_code');

        $firstNames = ['Ariel','Budi','Citra','Dani','Eva','Fajar','Gita','Hendra','Indah',
            'Joko','Karina','Luki','Maya','Niko','Oki','Prita','Qori','Rian','Sari','Tono',
            'Udin','Vera','Wahyu','Xena','Yuli','Zara','Agus','Bela','Ciko','Dewi',
            'Eko','Fira','Gilang','Hani','Irfan','Jihan','Kevin','Lisa','Miko','Nina',
            'Omar','Putri','Rendi','Sinta','Tari','Uli','Vino','Wati','Yogi','Zul'];

        $lastNames = ['Santoso','Wijaya','Kusuma','Pratama','Sari','Nugroho','Handoko',
            'Setiawan','Rahayu','Hidayat','Lestari','Permata','Wibowo','Suryadi','Purnama'];

        $now   = Carbon::now();
        $start = $now->copy()->subDays(89)->startOfDay();

        // Build a day-by-day weight that slowly increases (simulating growth)
        $days = [];
        for ($d = 0; $d < 90; $d++) {
            $weight = 1 + ($d / 90) * 4; // ramp from 1x to 5x over 90 days
            $days[$d] = max(1, (int) round($weight + rand(-1, 2)));
        }

        $rows    = [];
        $counter = 100; // start IDs at 100 to avoid conflicts with real users

        foreach ($days as $dayOffset => $count) {
            $date = $start->copy()->addDays($dayOffset);

            for ($i = 0; $i < $count; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName  = $lastNames[array_rand($lastNames)];
                $name      = $firstName . ' ' . $lastName;
                $email     = strtolower($firstName) . $counter . '@demo.test';
                $createdAt = $date->copy()->addSeconds(rand(0, 86399));

                $row = [
                    'id'              => $counter,
                    'name'            => $name,
                    'email'           => $email,
                    'password'        => Hash::make('password'),
                    'google_id'       => null,
                    'role'            => 'buyer',
                    'points_balance'  => rand(0, 2000),
                    'created_at'      => $createdAt,
                    'updated_at'      => $createdAt,
                ];

                if ($hasReferralCode) {
                    $row['referral_code'] = strtoupper(Str::random(8));
                    $row['referred_by']   = null;
                }

                $rows[]  = $row;
                $counter++;
            }
        }

        // Upsert in batches to avoid hitting query-size limits
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('users')->upsert($chunk, ['id'], [
                'name', 'email', 'password', 'role', 'points_balance', 'created_at', 'updated_at',
            ]);
        }

        $this->command->info('Seeded ' . count($rows) . ' demo users across 90 days.');
    }

    private function seedDemoOrders(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasTable('order_details')) {
            return;
        }

        $productIds = array_keys(self::PRODUCT_PRICES);
        $userIds    = DB::table('users')
            ->where('id', '>=', 100)
            ->pluck('id')
            ->toArray();

        if (empty($userIds)) {
            return;
        }

        $now       = Carbon::now();
        $start     = $now->copy()->subDays(89)->startOfDay();
        $statuses  = ['paid', 'paid', 'paid', 'paid', 'pending', 'failed'];

        $orders       = [];
        $orderDetails = [];
        $invCounter   = 1000;
        $orderId      = 1000;

        // ~3–7 orders per day over 90 days
        for ($d = 0; $d < 90; $d++) {
            $date  = $start->copy()->addDays($d);
            $count = rand(2, 7);

            for ($i = 0; $i < $count; $i++) {
                $userId    = $userIds[array_rand($userIds)];
                $productId = $productIds[array_rand($productIds)];
                $price     = self::PRODUCT_PRICES[$productId];
                $status    = $statuses[array_rand($statuses)];
                $createdAt = $date->copy()->addSeconds(rand(0, 86399));

                $orders[] = [
                    'id'                          => $orderId,
                    'noinv'                        => 'DEMO-' . str_pad($invCounter, 5, '0', STR_PAD_LEFT),
                    'user_id'                      => $userId,
                    'user_discount_id'             => null,
                    'subtotal'                     => $price,
                    'discount_amount'              => 0,
                    'total_price_after_discount'   => $price,
                    'payment_gateway_ref'          => $status === 'paid' ? 'demo_' . Str::random(8) : null,
                    'status'                       => $status,
                    'created_at'                   => $createdAt,
                ];

                $orderDetails[] = [
                    'order_id'            => $orderId,
                    'product_id'          => $productId,
                    'quantity'            => 1,
                    'total_price_in_cart' => $price,
                ];

                $orderId++;
                $invCounter++;
            }
        }

        foreach (array_chunk($orders, 100) as $chunk) {
            DB::table('orders')->upsert($chunk, ['id'], [
                'noinv', 'user_id', 'subtotal', 'discount_amount',
                'total_price_after_discount', 'payment_gateway_ref', 'status', 'created_at',
            ]);
        }

        foreach (array_chunk($orderDetails, 200) as $chunk) {
            DB::table('order_details')->upsert($chunk, ['order_id', 'product_id'], [
                'quantity', 'total_price_in_cart',
            ]);
        }

        $this->command->info('Seeded ' . count($orders) . ' demo orders across 90 days.');
    }
}
