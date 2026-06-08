<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/** Rows recorded in the outbox for a table+op since the last reset. */
function outbox(string $table, string $op): int
{
    return DB::table('sync_changes')->where('table_name', $table)->where('op', $op)->count();
}

function resetOutbox(): void
{
    DB::table('sync_changes')->delete();
}

it('records a bulk update on a synced model (the bug: events do not fire for bulk writes)', function () {
    $a = Category::create(['name' => 'Alpha', 'slug' => 'alpha']);
    $b = Category::create(['name' => 'Bravo', 'slug' => 'bravo']);
    resetOutbox();

    Category::whereIn('id', [$a->id, $b->id])->update(['name' => 'Renamed']);

    expect(outbox('categories', 'update'))->toBe(2);
});

it('records a bulk delete on a synced model', function () {
    $a = Category::create(['name' => 'Alpha', 'slug' => 'alpha']);
    $b = Category::create(['name' => 'Bravo', 'slug' => 'bravo']);
    resetOutbox();

    Category::whereIn('id', [$a->id, $b->id])->delete();

    expect(outbox('categories', 'delete'))->toBe(2);
});

it('records a bulk increment on a synced model (points/free-spins path)', function () {
    $u = User::factory()->create(['points_balance' => 0]);
    resetOutbox();

    User::where('id', $u->id)->increment('points_balance', 50);

    expect(outbox('users', 'update'))->toBe(1);
    expect((int) User::find($u->id)->points_balance)->toBe(50);
});

it('records an instance update exactly once (no double recording)', function () {
    $c = Category::create(['name' => 'Alpha', 'slug' => 'alpha']);
    resetOutbox();

    $c->update(['name' => 'Renamed']);

    expect(outbox('categories', 'update'))->toBe(1);
});

it('records a create via the model event, not the builder', function () {
    resetOutbox();

    Category::create(['name' => 'Alpha', 'slug' => 'alpha']);

    expect(outbox('categories', 'insert'))->toBe(1);
    expect(outbox('categories', 'update'))->toBe(0);
});
