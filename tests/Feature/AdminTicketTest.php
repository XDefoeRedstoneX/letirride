<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function ticket(array $overrides = []): Ticket
{
    return Ticket::create(array_merge([
        'user_id' => null,
        'email' => 'cust@example.com',
        'name' => 'Cust',
        'type' => 'billing',
        'subject' => 'Billing or payment issue',
        'message' => 'Something went wrong with my payment.',
        'status' => 'open',
        'ip_address' => '127.0.0.1',
    ], $overrides));
}

it('forbids non-admins from the tickets dashboard', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->get(route('admin.tickets'))->assertForbidden();
});

it('lists tickets with status counts for an admin', function () {
    ticket(['status' => 'open']);
    ticket(['status' => 'closed']);

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.tickets'));

    $response->assertOk();
    expect($response->viewData('counts')['all'])->toBe(2);
    expect($response->viewData('counts')['open'])->toBe(1);
    expect($response->viewData('counts')['closed'])->toBe(1);
});

it('filters by status', function () {
    ticket(['status' => 'open', 'subject' => 'Open one']);
    ticket(['status' => 'closed', 'subject' => 'Closed one']);

    $admin = User::factory()->create(['role' => 'admin']);

    $rows = $this->actingAs($admin)->get(route('admin.tickets', ['status' => 'closed']))->viewData('tickets');

    expect($rows->total())->toBe(1);
    expect($rows->first()->subject)->toBe('Closed one');
});

it('searches across subject, message and email', function () {
    ticket(['email' => 'needle@example.com', 'subject' => 'Findme']);
    ticket(['email' => 'other@example.com', 'subject' => 'Nope']);

    $admin = User::factory()->create(['role' => 'admin']);

    $rows = $this->actingAs($admin)->get(route('admin.tickets', ['q' => 'needle']))->viewData('tickets');

    expect($rows->total())->toBe(1);
    expect($rows->first()->email)->toBe('needle@example.com');
});

it('updates a ticket status to closed', function () {
    $t = ticket(['status' => 'open']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.tickets.status', $t), ['status' => 'closed'])
        ->assertRedirect();

    expect($t->fresh()->status)->toBe('closed');
});
