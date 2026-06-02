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
        ->assertJsonPath('spendingAnalytics.labels', []);
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
