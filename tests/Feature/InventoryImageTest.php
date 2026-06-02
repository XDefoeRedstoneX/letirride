<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves a real product image to its /products path', function () {
    expect((new Product(['image' => 'adobe.png']))->imageUrl())->toBe('/products/adobe.png');
});

it('falls back to the default when image is null', function () {
    expect((new Product)->imageUrl())->toBe('/products/'.Product::FALLBACK_IMAGE);
});

it('falls back when the image file does not exist (the inventory bug)', function () {
    // soundcloud.png was the old broken fallback — it is not a real file.
    expect((new Product(['image' => 'soundcloud.png']))->imageUrl())->toBe('/products/'.Product::FALLBACK_IMAGE);
});

it('strips a leading slash from the stored image', function () {
    expect((new Product(['image' => '/adobe.png']))->imageUrl())->toBe('/products/adobe.png');
});

it('renders inventory product images via imageUrl (no broken soundcloud fallback)', function () {
    $user = User::factory()->create();

    $order = new Order([
        'noinv' => 'INV-INVTEST', 'user_id' => $user->id, 'subtotal' => 50000,
        'discount_amount' => 0, 'total_price_after_discount' => 50000, 'status' => 'paid',
    ]);
    $order->save();

    $category = Category::create(['name' => 'Games', 'slug' => 'games']);

    // Product with no image → must fall back to the real default, not soundcloud.png.
    $product = Product::create([
        'category_id' => $category->id, 'type' => 'voucher', 'name' => 'Mystery Item',
        'description' => 'x', 'price' => 50000, 'point_multiplier' => 1, 'is_active' => true,
    ]);

    ProductKey::create([
        'product_id' => $product->id, 'key_code' => 'ABC-123', 'status' => 'sold', 'order_id' => $order->id,
    ]);

    $response = $this->actingAs($user)->get(route('inventory'));

    $response->assertOk();
    $items = $response->viewData('items');
    $key = $items->firstWhere('item_type', 'voucher_key');

    expect($key['image'])->toBe('/products/'.Product::FALLBACK_IMAGE);
    expect($items->pluck('image')->filter(fn ($i) => str_contains((string) $i, 'soundcloud')))->toBeEmpty();
});
