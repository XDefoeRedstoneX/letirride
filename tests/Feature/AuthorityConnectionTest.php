<?php

use App\Http\Middleware\UseAuthorityConnection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // A throwaway second connection to observe the switch, no network involved.
    config([
        'sync.remote' => 'authority_test',
        'database.connections.authority_test' => array_merge(
            config('database.connections.sqlite'),
            ['database' => ':memory:'],
        ),
    ]);
});

it('switches the default connection to the authority during the request and restores after', function () {
    config(['sync.is_local' => true]);

    $original = DB::getDefaultConnection();
    $inside = null;

    (new UseAuthorityConnection())->handle(Request::create('/x'), function () use (&$inside) {
        $inside = DB::getDefaultConnection();

        return new Response('ok');
    });

    expect($inside)->toBe('authority_test');
    expect(DB::getDefaultConnection())->toBe($original); // restored
});

it('is a no-op when not the local node', function () {
    config(['sync.is_local' => false]);
    $original = DB::getDefaultConnection();
    $inside = null;

    (new UseAuthorityConnection())->handle(Request::create('/x'), function () use (&$inside) {
        $inside = DB::getDefaultConnection();

        return new Response('ok');
    });

    expect($inside)->toBe($original); // never switched
});
