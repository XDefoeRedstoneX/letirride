<?php

use App\Models\GachaIcon;
use App\Models\GachaPool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function admin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

it('blocks non-admins from the icon manager', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->get(route('admin.gacha-icons'))->assertForbidden();
});

it('lets an admin view the icon manager', function () {
    GachaIcon::create(['key' => 'steam', 'label' => 'Steam', 'category' => 'wallet', 'image_path' => '/gacha-icons/steam.svg']);

    $this->actingAs(admin())->get(route('admin.gacha-icons'))
        ->assertOk()
        ->assertSee('Steam', false);
});

it('creates a coin icon', function () {
    $this->actingAs(admin())->post(route('admin.gacha-icons.store'), [
        'key' => 'my-coin',
        'label' => 'My Coin',
        'category' => 'special',
        'image_url' => '/gacha-icons/coin.svg',
        'sort_order' => 3,
        'is_active' => '1',
    ])->assertRedirect();

    $icon = GachaIcon::where('key', 'my-coin')->first();
    expect($icon)->not->toBeNull();
    expect($icon->label)->toBe('My Coin');
    expect($icon->is_active)->toBeTrue();
});

it('rejects a duplicate key', function () {
    GachaIcon::create(['key' => 'steam', 'label' => 'Steam', 'category' => 'wallet', 'image_path' => '/x.svg']);

    $this->actingAs(admin())->post(route('admin.gacha-icons.store'), [
        'key' => 'steam',
        'label' => 'Steam Two',
        'category' => 'wallet',
    ])->assertSessionHasErrors('key');

    expect(GachaIcon::where('key', 'steam')->count())->toBe(1);
});

it('rejects an invalid key format', function () {
    $this->actingAs(admin())->post(route('admin.gacha-icons.store'), [
        'key' => 'Bad Key!',
        'label' => 'Bad',
        'category' => 'special',
    ])->assertSessionHasErrors('key');
});

it('updates a coin icon', function () {
    $icon = GachaIcon::create(['key' => 'steam', 'label' => 'Steam', 'category' => 'wallet', 'image_path' => '/x.svg']);

    $this->actingAs(admin())->patch(route('admin.gacha-icons.update', $icon), [
        'key' => 'steam',
        'label' => 'Steam Wallet',
        'category' => 'wallet',
        'image_url' => '/x.svg',
        'is_active' => '1',
    ])->assertRedirect();

    expect($icon->fresh()->label)->toBe('Steam Wallet');
});

it('deactivating a coin hides it from the active scope', function () {
    $icon = GachaIcon::create(['key' => 'steam', 'label' => 'Steam', 'category' => 'wallet', 'image_path' => '/x.svg']);

    $this->actingAs(admin())->patch(route('admin.gacha-icons.update', $icon), [
        'key' => 'steam',
        'label' => 'Steam',
        'category' => 'wallet',
        'image_url' => '/x.svg',
        // is_active omitted → unchecked
    ])->assertRedirect();

    GachaIcon::flushCache();
    expect($icon->fresh()->is_active)->toBeFalse();
    expect(GachaIcon::byKey('steam'))->toBeNull();
});

it('deletes a coin icon', function () {
    $icon = GachaIcon::create(['key' => 'steam', 'label' => 'Steam', 'category' => 'wallet', 'image_path' => '/x.svg']);

    $this->actingAs(admin())->delete(route('admin.gacha-icons.destroy', $icon))->assertRedirect();

    expect(GachaIcon::find($icon->id))->toBeNull();
});

it('stores an uploaded image on the public disk', function () {
    Storage::fake('public');

    $this->actingAs(admin())->post(route('admin.gacha-icons.store'), [
        'key' => 'uploaded',
        'label' => 'Uploaded',
        'category' => 'special',
        'image_file' => UploadedFile::fake()->image('coin.png', 64, 64),
    ])->assertRedirect();

    $icon = GachaIcon::where('key', 'uploaded')->first();
    expect($icon->image_path)->toStartWith('/storage/gacha-icons/uploads/');

    $relative = str_replace('/storage/', '', $icon->image_path);
    Storage::disk('public')->assertExists($relative);
});

it('accepts icon_key and custom image_path when saving a prize', function () {
    GachaIcon::create(['key' => 'netflix', 'label' => 'Netflix', 'category' => 'subscription', 'image_path' => '/gacha-icons/netflix.svg']);

    $this->actingAs(admin())->post(route('admin.gacha.store'), [
        'prize_name' => 'Netflix Prize',
        'rarity_item' => 'rare',
        'icon_key' => 'netflix',
    ])->assertRedirect();

    $prize = GachaPool::where('prize_name', 'Netflix Prize')->first();
    expect($prize->icon_key)->toBe('netflix');
});
