<?php

use App\Models\Category;
use App\Models\DiscountType;
use App\Models\GachaIcon;
use App\Models\GachaPool;
use App\Models\GachaRarityChance;
use App\Models\Subcategory;
use App\Models\User;
use App\Services\GachaIconResolver;
use App\Support\GachaIconCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    GachaIcon::flushCache();
    // A representative slice of the catalog so byKey() lookups have rows.
    foreach (['coin', 'points-coin', 'free-spin', 'nothing', 'voucher', 'netflix', 'steam'] as $i => $key) {
        GachaIcon::create([
            'key' => $key,
            'label' => ucfirst($key),
            'category' => 'special',
            'image_path' => GachaIconCatalog::ASSET_DIR.'/'.$key.'.svg',
            'sort_order' => $i,
            'is_active' => true,
        ]);
    }
});

function makePrize(array $attrs): GachaPool
{
    return GachaPool::create(array_merge([
        'prize_name' => 'P',
        'rarity_item' => 'common',
        'reward_type' => 'points',
        'discount_type_id' => null,
        'points_amount' => null,
        'image_path' => null,
        'icon_key' => null,
    ], $attrs));
}

it('uses the explicit icon_key when set', function () {
    $prize = makePrize(['reward_type' => 'discount', 'icon_key' => 'netflix']);

    expect(GachaIconResolver::iconKeyFor($prize))->toBe('netflix');
    expect(GachaIconResolver::resolve($prize))->toBe('/gacha-icons/netflix.svg');
});

it('lets a full-custom image_path override everything', function () {
    $prize = makePrize(['icon_key' => 'netflix', 'image_path' => '/hero/whale.png']);

    expect(GachaIconResolver::resolve($prize))->toBe('/hero/whale.png');
});

it('auto-picks a coin by reward type when no icon_key is set', function () {
    expect(GachaIconResolver::iconKeyFor(makePrize(['reward_type' => 'points'])))->toBe('points-coin');
    expect(GachaIconResolver::iconKeyFor(makePrize(['reward_type' => 'free_spin'])))->toBe('free-spin');
    expect(GachaIconResolver::iconKeyFor(makePrize(['reward_type' => 'nothing'])))->toBe('nothing');
});

it('maps a discount prize to its brand coin via the targeted subcategory', function () {
    Category::insert(['id' => 5, 'name' => 'Subscriptions', 'slug' => 'subscriptions']);
    Subcategory::insert(['id' => 10, 'category_id' => 5, 'name' => 'Netflix', 'slug' => 'netflix']);
    DiscountType::insert([
        ['id' => 3, 'name' => 'Rp30.000 Off Netflix', 'type' => 'fixed', 'value' => 30000, 'target_category_id' => null, 'target_subcategory_id' => 10],
    ]);

    $prize = makePrize(['reward_type' => 'discount', 'discount_type_id' => 3]);

    expect(GachaIconResolver::iconKeyFor($prize))->toBe('netflix');
});

it('falls back to the generic voucher coin for a brandless discount', function () {
    DiscountType::insert([
        ['id' => 1, 'name' => '10% Off All', 'type' => 'percent', 'value' => 10, 'target_category_id' => null, 'target_subcategory_id' => null],
    ]);

    $prize = makePrize(['reward_type' => 'discount', 'discount_type_id' => 1]);

    expect(GachaIconResolver::iconKeyFor($prize))->toBe('voucher');
});

it('resolves to the on-disk coin path even with no catalog rows present', function () {
    GachaIcon::query()->delete();
    GachaIcon::flushCache();

    $prize = makePrize(['reward_type' => 'points']);

    expect(GachaIconResolver::resolve($prize))->toBe('/gacha-icons/points-coin.svg');
});

it('excludes inactive coins from the active scope and byKey lookup', function () {
    GachaIcon::where('key', 'netflix')->update(['is_active' => false]);
    GachaIcon::flushCache();

    expect(GachaIcon::byKey('netflix'))->toBeNull();
    expect(GachaIcon::active()->pluck('key'))->not->toContain('netflix');
});

it('includes icon_key in the roll response payload', function () {
    GachaRarityChance::create(['rarity' => 'common', 'base_chance' => 100.00, 'sort_order' => 0]);
    makePrize(['id' => 700, 'reward_type' => 'points', 'rarity_item' => 'common', 'points_amount' => 50, 'icon_key' => 'points-coin']);

    $user = User::factory()->create(['points_balance' => 500]);

    $this->actingAs($user)->postJson(route('gacha.roll'))
        ->assertOk()
        ->assertJsonPath('prize.icon_key', 'points-coin');
});
