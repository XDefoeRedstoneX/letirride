<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the full management panel to the admin-authority node only (CPanel by
 * default, config('sync.admin_node')). The catalog/config that admins edit is
 * owned by a single node, so the panel should exist on that node alone.
 *
 * On the local node the only admin surface is the Sync control panel, so any
 * management route is bounced there; on any other non-admin node it 404s. The
 * inverse of EnsureLocalNode (which gates the Sync panel to local). See plan §9.
 */
class EnsureAdminNode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('sync.node_id') !== config('sync.admin_node')) {
            if (config('sync.is_local')) {
                return redirect()->route('admin.sync');
            }

            abort(404);
        }

        return $next($request);
    }
}
