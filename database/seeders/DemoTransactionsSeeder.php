<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\TopupCredential;
use App\Models\User;
use App\Models\UserDiscount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Demo traffic for the admin dashboard showcase.
 *
 * Populates ~60 customers + ~250 orders across the last 10 days (heavy on
 * the most recent 3-4), so the dashboard sparklines, revenue totals,
 * recent-orders feed and customer-insights modal all tell a real story.
 * Also seeds product keys / topup credentials for paid orders so the
 * Transactions/Inventory pages look fulfilled.
 *
 * NOT wired into DatabaseSeeder::run() — call manually before the demo:
 *
 *     php artisan db:seed --class=DemoTransactionsSeeder
 *
 * Idempotent: clears the rows it created in prior runs (invoice prefix
 *
 * INV-DEMO-, emails matching demo+*@ridly.local) before re-inserting,
 * so it's safe to re-run.
 */
class DemoTransactionsSeeder extends Seeder
{
    private const INVOICE_PREFIX = 'INV-DEMO-';

    private const EMAIL_PATTERN = 'demo+%@ridly.local';

    private const WINDOW_DAYS = 10;

    private const DEMO_CUSTOMERS = 60;

    private const ORDERS = 250;

    /**
     * Status mix for the order weighting. Sums to 100; tweak to taste.
     *
     * @var array<string, int>
     */
    private const STATUS_WEIGHTS = [
        'paid' => 78,
        'pending' => 12,
        'failed' => 8,
        'cancelled' => 2,
    ];

    public function run(): void
    {
        $this->wipePreviousRun();

        $now = CarbonImmutable::now();
        $users = $this->seedCustomers($now);

        // Tilt the demo so the resident test user (id=1, if present) owns a
        // healthy chunk of orders — Transactions/Inventory look populated when
        // logging in as them for the showcase.
        $testUser = User::find(1);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            $this->command?->warn('No active products — run the main DatabaseSeeder first.');

            return;
        }

        // Brand-popularity weighting so the "top products" chart on the
        // customer-insights modal looks realistic.
        $weighted = $this->weightedProducts($products);

        $createdOrderIds = [];
        for ($i = 1; $i <= self::ORDERS; $i++) {
            $owner = $this->pickOwner($users, $testUser, $i);
            $createdOrderIds[] = $this->createOrder($owner, $weighted, $now, $i);
        }

        $this->command?->info(sprintf(
            'Seeded %d demo customers and %d orders across the last %d days.',
            count($users) + ($testUser ? 1 : 0),
            count($createdOrderIds),
            self::WINDOW_DAYS,
        ));
    }

    /**
     * Drop any rows from a prior demo run before we re-seed.
     */
    private function wipePreviousRun(): void
    {
        $orderIds = DB::table('orders')
            ->where('noinv', 'like', self::INVOICE_PREFIX.'%')
            ->pluck('id');

        if ($orderIds->isNotEmpty()) {
            // Free reserved/sold product keys back to the available pool.
            DB::table('product_keys')->whereIn('order_id', $orderIds)
                ->update(['order_id' => null, 'status' => 'available']);
            DB::table('product_keys')->whereIn('reserved_for_order_id', $orderIds)
                ->update(['reserved_for_order_id' => null]);

            // Kill dependent records and the orders themselves.
            $detailIds = DB::table('order_details')->whereIn('order_id', $orderIds)->pluck('id');
            DB::table('topup_credentials')->whereIn('order_detail_id', $detailIds)->delete();
            DB::table('user_discounts')->whereIn('order_id', $orderIds)
                ->update(['is_used' => false, 'used_at' => null, 'order_id' => null]);
            DB::table('order_details')->whereIn('order_id', $orderIds)->delete();
            DB::table('orders')->whereIn('id', $orderIds)->delete();
        }

        DB::table('users')->where('email', 'like', str_replace('%', '%', self::EMAIL_PATTERN))->delete();
    }

    /**
     * Spin up the synthetic customer pool, with join dates spread across the
     * recent window so the dashboard's new-users metric has variation.
     *
     * @return array<int, User>
     */
    private function seedCustomers(CarbonImmutable $now): array
    {
        $created = [];
        $rng = mt_rand(...); // alias

        for ($i = 1; $i <= self::DEMO_CUSTOMERS; $i++) {
            $joinedDaysAgo = $rng(0, self::WINDOW_DAYS);
            $joinedAt = $now->subDays($joinedDaysAgo)->subMinutes($rng(0, 24 * 60));

            $user = new User([
                'name' => 'Demo Customer '.$i,
                'email' => sprintf('demo+%03d@ridly.local', $i),
                'password' => Hash::make('demo'),
                'role' => 'buyer',
                'points_balance' => $rng(0, 2500),
                'referral_code' => 'DEMO-'.strtoupper(Str::random(6)),
            ]);
            $user->created_at = $joinedAt;
            $user->updated_at = $joinedAt;
            $user->save();

            $created[] = $user;
        }

        return $created;
    }

    /**
     * Tilt order creation toward the test user, then toward repeat customers,
     * then everyone else — so insights/customer pages have real depth.
     *
     * @param  array<int, User>  $pool
     */
    private function pickOwner(array $pool, ?User $testUser, int $orderIndex): User
    {
        // Give the test user roughly 12 orders so logging in as them shows a
        // populated transactions/inventory page on demo day.
        if ($testUser && $orderIndex % 22 === 0) {
            return $testUser;
        }

        return $pool[array_rand($pool)];
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return array<int, Product> ID-weighted bag (popular products appear more often).
     */
    private function weightedProducts($products): array
    {
        // Subcategory-id heuristic for "popular brand" weighting. Steam (2),
        // Netflix (10), Discord (12) appear 4x; PSN (3), Xbox (13), Spotify (11) 2x.
        $heavy = [2, 10, 12];
        $medium = [3, 11, 13];

        $bag = [];
        foreach ($products as $p) {
            $weight = in_array($p->subcategory_id, $heavy, true) ? 4
                : (in_array($p->subcategory_id, $medium, true) ? 2 : 1);
            for ($i = 0; $i < $weight; $i++) {
                $bag[] = $p;
            }
        }

        return $bag;
    }

    /**
     * @param  array<int, Product>  $weightedProducts
     */
    private function createOrder(User $owner, array $weightedProducts, CarbonImmutable $now, int $i): int
    {
        // Tilt order dates toward the most recent ~3 days so the 7d sparkline
        // is busy, with a long tail back to day 10.
        $hoursAgo = mt_rand(0, self::WINDOW_DAYS * 24);
        if (mt_rand(1, 100) <= 55) {
            $hoursAgo = mt_rand(0, 72);
        }
        $createdAt = $now->subHours($hoursAgo);

        $status = $this->weightedStatus();
        $itemCount = mt_rand(1, 3);
        $picked = [];
        for ($j = 0; $j < $itemCount; $j++) {
            $picked[] = $weightedProducts[array_rand($weightedProducts)];
        }

        $subtotal = 0.0;
        foreach ($picked as $product) {
            $subtotal += (float) $product->price;
        }

        // 20% of paid/pending orders use a voucher for variety.
        $discountAmount = 0.0;
        $voucherId = null;
        if (in_array($status, ['paid', 'pending'], true) && mt_rand(1, 100) <= 20) {
            $discountAmount = round($subtotal * (mt_rand(5, 25) / 100), -2);
            $voucherId = $this->reserveVoucher($owner, $createdAt);
        }

        $order = new Order([
            'noinv' => self::INVOICE_PREFIX.sprintf('%05d', $i),
            'user_id' => $owner->id,
            'user_discount_id' => $voucherId,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_price_after_discount' => max(0.0, $subtotal - $discountAmount),
            'payment_gateway_ref' => $status === 'paid' ? 'MID-'.strtoupper(Str::random(10)) : null,
            'status' => $status,
        ]);
        $order->created_at = $createdAt;
        $order->save();

        // Order details.
        foreach ($picked as $product) {
            $detail = OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'total_price_in_cart' => (float) $product->price,
            ]);

            if ($status === 'paid') {
                $this->fulfillItem($product, $detail);
            }
        }

        if ($status === 'paid' && $voucherId) {
            DB::table('user_discounts')->where('id', $voucherId)->update([
                'is_used' => true,
                'used_at' => $createdAt,
                'order_id' => $order->id,
            ]);
        }

        return $order->id;
    }

    private function weightedStatus(): string
    {
        $roll = mt_rand(1, 100);
        $cumulative = 0;
        foreach (self::STATUS_WEIGHTS as $status => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $status;
            }
        }

        return 'paid';
    }

    /**
     * Reserve a random active discount for the user. Falls back to null silently
     * if no discounts exist or all are expired.
     */
    private function reserveVoucher(User $owner, CarbonImmutable $createdAt): ?int
    {
        $discountId = DB::table('discount_types')->inRandomOrder()->value('id');
        if (! $discountId) {
            return null;
        }

        $ud = UserDiscount::create([
            'user_id' => $owner->id,
            'discount_type_id' => $discountId,
            'is_used' => false,
            'obtained_from' => 'demo_seed',
            'expires_at' => $createdAt->addDays(30),
        ]);

        return $ud->id;
    }

    /**
     * Voucher products consume a real ProductKey; direct-topup products get a
     * 'sent' TopupCredential so the inventory page shows a fulfilled order.
     */
    private function fulfillItem(Product $product, OrderDetail $detail): void
    {
        if (($product->type ?? 'voucher') === 'direct_topup') {
            TopupCredential::create([
                'order_detail_id' => $detail->id,
                'player_id' => 'PID-'.mt_rand(100000, 999999),
                'zone_id' => mt_rand(1000, 9999),
                'server_id' => null,
                'topup_status' => 'sent',
                'fulfilled_at' => $detail->order?->created_at,
            ]);

            return;
        }

        // Grab a free key; mint one if the pool is dry so seeding can't stall.
        $key = ProductKey::where('product_id', $product->id)
            ->where('status', 'available')
            ->whereNull('reserved_for_order_id')
            ->whereNull('order_id')
            ->first();

        if (! $key) {
            $key = ProductKey::create([
                'product_id' => $product->id,
                'key_code' => 'DEMO-'.strtoupper(Str::random(12)),
                'status' => 'available',
            ]);
        }

        $key->update([
            'status' => 'sold',
            'order_id' => $detail->order_id,
            'reserved_for_order_id' => null,
        ]);
    }
}
