<?php

use App\Models\DiscountType;
use App\Models\PointShopItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function psAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

it('forbids non-admins from the point-shop admin', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $this->actingAs($user)->get(route('admin.point-shop'))->assertForbidden();
});

it('creates a discount-voucher item', function () {
    $d = DiscountType::create(['name' => '5% Off', 'type' => 'percent', 'value' => 5]);

    $this->actingAs(psAdmin())->post(route('admin.point-shop.store'), [
        'name' => 'Voucher Item', 'point_cost' => 500, 'reward_type' => 'discount',
        'discount_type_id' => $d->id, 'is_active' => '1',
    ])->assertRedirect();

    $item = PointShopItem::first();
    expect($item->reward_type)->toBe('discount');
    expect($item->discount_type_id)->toBe($d->id);
    expect($item->points_amount)->toBeNull();
});

it('creates a cashback item', function () {
    $this->actingAs(psAdmin())->post(route('admin.point-shop.store'), [
        'name' => 'Cashback 400', 'point_cost' => 500, 'reward_type' => 'cashback',
        'points_amount' => 400, 'is_active' => '1',
    ])->assertRedirect();

    $item = PointShopItem::first();
    expect($item->reward_type)->toBe('cashback');
    expect($item->points_amount)->toBe(400);
    expect($item->discount_type_id)->toBeNull();
});

it('rejects an empty POST without creating a row', function () {
    $this->actingAs(psAdmin())->post(route('admin.point-shop.store'), [])
        ->assertSessionHasErrors(['name', 'point_cost', 'reward_type']);

    expect(PointShopItem::count())->toBe(0);
});

it('requires a discount when reward_type is discount', function () {
    $this->actingAs(psAdmin())->post(route('admin.point-shop.store'), [
        'name' => 'X', 'point_cost' => 100, 'reward_type' => 'discount',
    ])->assertSessionHasErrors('discount_type_id');

    expect(PointShopItem::count())->toBe(0);
});

it('requires points_amount when reward_type is cashback', function () {
    $this->actingAs(psAdmin())->post(route('admin.point-shop.store'), [
        'name' => 'X', 'point_cost' => 100, 'reward_type' => 'cashback',
    ])->assertSessionHasErrors('points_amount');

    expect(PointShopItem::count())->toBe(0);
});

it('credits points when a customer redeems a cashback item', function () {
    $user = User::factory()->create(['points_balance' => 1000]);
    $item = PointShopItem::create([
        'name' => 'Cashback', 'point_cost' => 500, 'reward_type' => 'cashback',
        'points_amount' => 400, 'is_active' => true,
    ]);

    $this->actingAs($user)->postJson(route('point-shop.redeem', $item))->assertOk();

    // Spent 500, got 400 cashback → net -100 (1000 - 500 + 400 = 900).
    expect($user->fresh()->points_balance)->toBe(900);
});

it('grants a voucher when a customer redeems a discount item', function () {
    $d = DiscountType::create(['name' => '5% Off', 'type' => 'percent', 'value' => 5]);
    $user = User::factory()->create(['points_balance' => 1000]);
    $item = PointShopItem::create([
        'name' => 'Voucher', 'point_cost' => 500, 'reward_type' => 'discount',
        'discount_type_id' => $d->id, 'is_active' => true,
    ]);

    $this->actingAs($user)->postJson(route('point-shop.redeem', $item))->assertOk();

    expect($user->fresh()->points_balance)->toBe(500);
    expect($user->userDiscounts()->where('discount_type_id', $d->id)->exists())->toBeTrue();
});
