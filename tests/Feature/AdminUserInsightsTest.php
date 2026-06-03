<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function paidOrder(User $user, float $total, string $when): Order
{
    $order = new Order([
        'noinv' => 'INV-'.uniqid(),
        'user_id' => $user->id,
        'subtotal' => $total,
        'discount_amount' => 0,
        'total_price_after_discount' => $total,
        'status' => 'paid',
    ]);
    // created_at isn't fillable; set it dirty so Eloquent keeps our value.
    $order->created_at = $when;
    $order->save();

    return $order;
}

it('forbids non-admins from the insights endpoint but allows admins', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);

    $this->actingAs($user)->getJson(route('admin.users.insights', $user))->assertForbidden();
    $this->actingAs($admin)->getJson(route('admin.users.insights', $user))->assertOk();
});

it('returns 200 with zeroed overview and no chart labels for a user with no paid orders', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);

    $this->actingAs($admin)->getJson(route('admin.users.insights', $user))
        ->assertOk()
        ->assertJsonPath('overview.totalOrders', 0)
        ->assertJsonPath('overview.totalSpent', 0)
        ->assertJsonPath('overview.averageOrderValue', 0)
        ->assertJsonPath('overview.lastPurchaseDate', null)
        ->assertJsonPath('spendingAnalytics.labels', [])
        ->assertJsonPath('recentOrders', []);
});

it('aggregates paid orders into monthly chart buckets (regression for DATE_TRUNC)', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);

    // Jan: 2 orders, Feb: 1 order.
    paidOrder($user, 100000, '2026-01-05 10:00:00');
    paidOrder($user, 50000, '2026-01-20 10:00:00');
    paidOrder($user, 30000, '2026-02-10 10:00:00');

    $res = $this->actingAs($admin)->getJson(route('admin.users.insights', $user))->assertOk();

    $res->assertJsonPath('overview.totalOrders', 3);
    $res->assertJsonPath('overview.totalSpent', 180000);
    $res->assertJsonPath('overview.averageOrderValue', 60000);
    $res->assertJsonPath('spendingAnalytics.labels', ['Jan 2026', 'Feb 2026']);
    $res->assertJsonPath('spendingAnalytics.monthlySpending', [150000, 30000]);
    $res->assertJsonPath('spendingAnalytics.monthlyOrderCounts', [2, 1]);
});

it('ignores non-paid orders in the aggregates', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);

    paidOrder($user, 100000, '2026-01-05 10:00:00');
    // A pending order that must not be counted.
    $pending = new Order([
        'noinv' => 'INV-pending', 'user_id' => $user->id, 'subtotal' => 999999,
        'discount_amount' => 0, 'total_price_after_discount' => 999999, 'status' => 'pending',
    ]);
    $pending->created_at = '2026-01-06 10:00:00';
    $pending->save();

    $this->actingAs($admin)->getJson(route('admin.users.insights', $user))
        ->assertOk()
        ->assertJsonPath('overview.totalOrders', 1)
        ->assertJsonPath('overview.totalSpent', 100000);
});

it('reports the top product and category for a customer', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);

    $category = Category::create(['name' => 'Wallet', 'slug' => 'wallet']);
    $product = Product::create([
        'category_id' => $category->id, 'name' => 'Steam Wallet', 'description' => 'x',
        'price' => 50000, 'point_multiplier' => 1, 'is_active' => true, 'image' => 'steam-wallet.png',
    ]);

    $order = paidOrder($user, 100000, '2026-01-05 10:00:00');
    OrderDetail::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'total_price_in_cart' => 100000]);

    $this->actingAs($admin)->getJson(route('admin.users.insights', $user))
        ->assertOk()
        ->assertJsonPath('overview.topProduct.name', 'Steam Wallet')
        ->assertJsonPath('overview.topProduct.categoryName', 'Wallet');
});

it('returns up to 25 recent paid orders, newest first (feeds the History modal)', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);

    // 27 paid orders; we expect 25 returned, newest first.
    foreach (range(1, 27) as $i) {
        paidOrder($user, 1000 * $i, sprintf('2026-01-%02d 10:00:00', $i));
    }

    $res = $this->actingAs($admin)->getJson(route('admin.users.insights', $user))->assertOk();
    $recent = $res->json('recentOrders');

    expect($recent)->toHaveCount(25);
    expect((float) $recent[0]['total'])->toBe(27000.0); // newest is day 27
    expect((float) $recent[24]['total'])->toBe(3000.0); // 25th-newest is day 3
});

it('counts only paid orders in the customer list column (matches Insights)', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);

    paidOrder($user, 50000, '2026-01-01 10:00:00');
    paidOrder($user, 70000, '2026-01-02 10:00:00');
    // Non-paid orders that must not bump the column.
    foreach (['pending', 'failed', 'cancelled'] as $s) {
        $o = new Order([
            'noinv' => 'INV-'.$s, 'user_id' => $user->id,
            'subtotal' => 1, 'discount_amount' => 0, 'total_price_after_discount' => 1, 'status' => $s,
        ]);
        $o->created_at = '2026-01-03 10:00:00';
        $o->save();
    }

    $response = $this->actingAs($admin)->get(route('admin.users'));
    $response->assertOk();

    $row = $response->viewData('users')->firstWhere('id', $user->id);
    expect($row->orders_count)->toBe(2);
});
