<?php

use App\Models\GachaIcon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('strips script tags from an uploaded SVG before persisting', function () {
    Storage::fake('public');

    $payload = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><circle r="10"/></svg>';
    $upload = UploadedFile::fake()->createWithContent('coin.svg', $payload);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.gacha-icons.store'), [
        'key' => 'evil-coin',
        'label' => 'Evil Coin',
        'category' => 'special',
        'image_file' => $upload,
    ])->assertRedirect()->assertSessionHasNoErrors();

    $icon = GachaIcon::where('key', 'evil-coin')->first();
    expect($icon)->not->toBeNull();

    $relative = str_replace('/storage/', '', $icon->image_path);
    $stored = Storage::disk('public')->get($relative);

    expect($stored)->not->toContain('<script')->not->toContain('alert');
});

it('uses the server-detected MIME for the stored extension', function () {
    Storage::fake('public');

    // Real PNG upload — server-detected extension should be png.
    $png = UploadedFile::fake()->image('coin.png', 64, 64);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.gacha-icons.store'), [
        'key' => 'realpng',
        'label' => 'Real PNG',
        'category' => 'special',
        'image_file' => $png,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(GachaIcon::where('key', 'realpng')->first()->image_path)->toEndWith('.png');
});
