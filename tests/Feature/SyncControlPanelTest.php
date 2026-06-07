<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Treat this test process as the local node, and keep the peer check offline
    // by pointing the "remote" at the in-memory sqlite connection.
    config(['sync.is_local' => true, 'sync.remote' => 'sqlite', 'sync.enabled' => false]);
    Cache::forget(config('sync.pause_cache_key'));
});

function syncAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

it('shows the control panel to a local-node admin', function () {
    $this->actingAs(syncAdmin())
        ->get(route('admin.sync'))
        ->assertOk()
        ->assertSee('Sync Control');
});

it('forbids non-admins', function () {
    $this->actingAs(User::factory()->create(['role' => 'buyer']))
        ->get(route('admin.sync'))
        ->assertForbidden();
});

it('returns 404 when not the local node (e.g. on CPanel)', function () {
    config(['sync.is_local' => false]);

    $this->actingAs(syncAdmin())
        ->get(route('admin.sync'))
        ->assertNotFound();
});

it('pauses and resumes via the controls', function () {
    $admin = syncAdmin();

    $this->actingAs($admin)->post(route('admin.sync.pause'))->assertRedirect();
    expect(Cache::get(config('sync.pause_cache_key')))->toBeTrue();

    $this->actingAs($admin)->post(route('admin.sync.resume'))->assertRedirect();
    expect(Cache::get(config('sync.pause_cache_key')))->toBeNull();
});

it('runs a one-off cycle via Sync Now', function () {
    // The engine runs against the sqlite "peer"; just assert the cycle completes.
    $this->actingAs(syncAdmin())
        ->post(route('admin.sync.now'))
        ->assertRedirect();
});
