<?php

use App\Models\Order;
use App\Models\ProductKey;
use App\Models\User;
use Database\Seeders\DemoTransactionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

it('seeds demo data the admin dashboard can render meaningfully', function () {
    // Need products/discounts in place first.
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
    (new DemoTransactionsSeeder)->run();

    $paid = Order::where('status', 'paid')->count();
    expect($paid)->toBeGreaterThan(150); // ~78% of 250 ≈ 195

    expect((float) Order::where('status', 'paid')->sum('total_price_after_discount'))->toBeGreaterThan(0);

    // Sparkline sanity: paid orders must exist in both halves of the recent
    // window so a 10-day trend line isn't a single bar.
    $recent = Order::where('status', 'paid')
        ->where('created_at', '>=', now()->subDays(3))
        ->count();
    $older = Order::where('status', 'paid')
        ->where('created_at', '<', now()->subDays(3))
        ->where('created_at', '>=', now()->subDays(10))
        ->count();
    expect($recent)->toBeGreaterThan(0);
    expect($older)->toBeGreaterThan(0);

    // Test user (id 1) gets a populated transaction page.
    if ($u = User::find(1)) {
        expect($u->orders()->count())->toBeGreaterThan(3);
        $paidOrderIds = $u->orders()->where('status', 'paid')->pluck('id');
        expect(ProductKey::whereIn('order_id', $paidOrderIds)->count())->toBeGreaterThan(0);
    }
});

it('is idempotent — re-running drops old demo rows before reseeding', function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);

    (new DemoTransactionsSeeder)->run();
    $first = Order::where('noinv', 'like', 'INV-DEMO-%')->count();

    (new DemoTransactionsSeeder)->run();
    $second = Order::where('noinv', 'like', 'INV-DEMO-%')->count();

    expect($second)->toBe($first);
});
