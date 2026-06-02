<?php

use App\Models\Category;
use App\Models\DiscountType;
use App\Models\GachaPool;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dtAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function discountPayload(array $overrides = []): array
{
    return array_merge([
        'name' => '10% Off All',
        'type' => 'percent',
        'value' => 10,
        'target_scope' => 'storewide',
    ], $overrides);
}

it('forbids non-admins', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $this->actingAs($user)->get(route('admin.discounts'))->assertForbidden();
});

it('lets an admin create a storewide percent discount', function () {
    $this->actingAs(dtAdmin())->post(route('admin.discounts.store'), discountPayload())->assertRedirect();

    $d = DiscountType::first();
    expect($d->name)->toBe('10% Off All');
    expect($d->type)->toBe('percent');
    expect($d->target_category_id)->toBeNull();
    expect($d->target_subcategory_id)->toBeNull();
});

it('rejects an empty POST without creating a row', function () {
    $this->actingAs(dtAdmin())->post(route('admin.discounts.store'), [])
        ->assertSessionHasErrors(['name', 'type', 'value', 'target_scope']);

    expect(DiscountType::count())->toBe(0);
});

it('rejects a percentage discount above 100', function () {
    $this->actingAs(dtAdmin())->post(route('admin.discounts.store'), discountPayload(['value' => 150]))
        ->assertSessionHasErrors('value');

    expect(DiscountType::count())->toBe(0);
});

it('allows a fixed discount above 100 (Rp)', function () {
    $this->actingAs(dtAdmin())->post(route('admin.discounts.store'), discountPayload([
        'type' => 'fixed', 'value' => 30000, 'name' => 'Rp30.000 Off',
    ]))->assertRedirect();

    expect((float) DiscountType::first()->value)->toBe(30000.0);
});

it('persists only the chosen target scope', function () {
    $cat = Category::create(['name' => 'Subscriptions', 'slug' => 'subs']);
    $sub = Subcategory::create(['category_id' => $cat->id, 'name' => 'Netflix', 'slug' => 'netflix']);

    $this->actingAs(dtAdmin())->post(route('admin.discounts.store'), discountPayload([
        'name' => 'Netflix deal', 'target_scope' => 'subcategory',
        'target_category_id' => $cat->id, 'target_subcategory_id' => $sub->id,
    ]))->assertRedirect();

    $d = DiscountType::first();
    expect($d->target_subcategory_id)->toBe($sub->id);
    expect($d->target_category_id)->toBeNull(); // scope=subcategory wipes category
});

it('requires a subcategory when scope is subcategory', function () {
    $this->actingAs(dtAdmin())->post(route('admin.discounts.store'), discountPayload([
        'target_scope' => 'subcategory',
    ]))->assertSessionHasErrors('target_subcategory_id');
});

it('blocks deleting a discount that is in use by a gacha prize', function () {
    $d = DiscountType::create(['name' => 'X', 'type' => 'percent', 'value' => 10]);
    GachaPool::create(['prize_name' => 'P', 'rarity_item' => 'rare', 'reward_type' => 'discount', 'discount_type_id' => $d->id]);

    $this->actingAs(dtAdmin())->delete(route('admin.discounts.destroy', $d))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(DiscountType::find($d->id))->not->toBeNull();
});

it('deletes an unused discount', function () {
    $d = DiscountType::create(['name' => 'Unused', 'type' => 'percent', 'value' => 5]);

    $this->actingAs(dtAdmin())->delete(route('admin.discounts.destroy', $d))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(DiscountType::find($d->id))->toBeNull();
});
