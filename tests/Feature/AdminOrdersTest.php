<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('renders the admin orders page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.orders'))->assertOk();
});

it('no longer exposes an order status-edit route', function () {
    expect(Route::has('admin.orders.status'))->toBeFalse();

    // The old endpoint is gone (404), so admins can't manually flip order status.
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->patch('/admin/orders/1/status', ['status' => 'paid'])->assertNotFound();
});
