<?php

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderDetail;
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

it('favorites resolve product images via imageUrl too', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'Games', 'slug' => 'games']);

    // A product whose stored image file does not exist → must fall back.
    $product = Product::create([
        'category_id' => $category->id, 'type' => 'voucher', 'name' => 'Ghost',
        'description' => 'x', 'price' => 1000, 'point_multiplier' => 1, 'is_active' => true,
        'image' => 'does-not-exist.png',
    ]);
    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $favorites = $this->actingAs($user)->get(route('favorites'))->viewData('favorites');

    expect($favorites->first()['image'])->toBe('/products/'.Product::FALLBACK_IMAGE);
});

it('cart resolves product images via imageUrl (no broken soundcloud fallback)', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'Games', 'slug' => 'games']);

    $product = Product::create([
        'category_id' => $category->id, 'type' => 'voucher', 'name' => 'Ghost',
        'description' => 'x', 'price' => 1000, 'point_multiplier' => 1, 'is_active' => true,
        'image' => null,
    ]);

    CartItem::create([
        'user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1,
    ]);

    $items = $this->actingAs($user)->get(route('cart'))->viewData('cartItems');
    expect($items->first()['image'])->toBe('/products/'.Product::FALLBACK_IMAGE);
});

it('transactions render product image via imageUrl when present', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'Games', 'slug' => 'games']);

    $product = Product::create([
        'category_id' => $category->id, 'type' => 'voucher', 'name' => 'Mystery',
        'description' => 'x', 'price' => 1000, 'point_multiplier' => 1, 'is_active' => true,
        'image' => 'bogus.png', // missing file → must fall back
    ]);

    $order = new Order([
        'noinv' => 'INV-T1', 'user_id' => $user->id, 'subtotal' => 1000,
        'discount_amount' => 0, 'total_price_after_discount' => 1000, 'status' => 'paid',
    ]);
    $order->save();

    OrderDetail::create([
        'order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 1,
        'total_price_in_cart' => 1000,
    ]);

    $response = $this->actingAs($user)->get(route('transactions'));
    $response->assertOk();
    $orders = $response->viewData('orders');
    expect($orders->first()['image'])->toBe('/products/'.Product::FALLBACK_IMAGE);
});

it('handles non-PNG product images (svg/jpg/webp) via existence check', function () {
    // Drop a temporary SVG into public/products/ so the existence check passes.
    $tmp = public_path('products/_test_svg_marker.svg');
    @file_put_contents($tmp, '<svg/>');

    try {
        $product = new Product(['image' => '_test_svg_marker.svg']);
        expect($product->imageUrl())->toBe('/products/_test_svg_marker.svg');
    } finally {
        @unlink($tmp);
    }
});

it('strips path traversal from a malicious image filename', function () {
    $product = new Product(['image' => '../../../etc/passwd']);
    expect($product->imageUrl())->toBe('/products/'.Product::FALLBACK_IMAGE);
});
