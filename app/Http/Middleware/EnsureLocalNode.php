<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the sync control panel to the local node only. On CPanel (SYNC_IS_LOCAL
 * false) these routes return 404 — the panel literally does not exist there, so
 * Pause/Sync Now can never be triggered in production. See plan §9.
 */
class EnsureLocalNode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('sync.is_local')) {
            abort(404);
        }

        return $next($request);
    }
}
