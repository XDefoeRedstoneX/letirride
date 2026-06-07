<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Routes a local customer's financial request to the CPanel authority by making
 * the CPanel DB the default connection for the duration of the request. This
 * keeps inventory/points single-authority (no double-sell) and, combined with
 * the partitioned + preserved id-space, keeps ids/relationships/auth consistent.
 *
 * Session/cache/queue are pinned to this node's own connection (see config/*),
 * so they do NOT follow the switch. On CPanel (not local) this is a no-op since
 * the default connection already IS the authority. See plan §7.4.
 */
class UseAuthorityConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('sync.is_local')) {
            return $next($request);
        }

        $original = DB::getDefaultConnection();
        DB::setDefaultConnection(config('sync.remote'));

        try {
            return $next($request);
        } finally {
            DB::setDefaultConnection($original);
        }
    }
}
