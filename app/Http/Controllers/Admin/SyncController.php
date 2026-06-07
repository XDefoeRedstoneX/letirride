<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Local-only sync control panel: live status, Pause/Resume, and Sync Now.
 * Reachable only on the local node (EnsureLocalNode) by admins. See plan §9.
 */
class SyncController extends Controller
{
    private function pauseKey(): string
    {
        return (string) config('sync.pause_cache_key');
    }

    public function index()
    {
        $local = DB::connection();

        $pullState = $local->table('sync_state')
            ->where('direction', 'pull')->where('table_name', '*')->first();

        $pendingPush = $local->table('sync_changes')
            ->where('node_id', config('sync.node_id'))->where('applied', false)->count();

        $conflicts = $local->table('sync_conflicts')->orderByDesc('id')->limit(10)->get();

        // Is the CPanel peer reachable right now?
        $peerOnline = true;
        $peerError = null;
        try {
            DB::connection(config('sync.remote'))->getPdo();
        } catch (\Throwable $e) {
            $peerOnline = false;
            $peerError = $e->getMessage();
        }

        return view('admin.sync', [
            'nodeId' => config('sync.node_id'),
            'enabled' => (bool) config('sync.enabled'),
            'paused' => (bool) Cache::get($this->pauseKey()),
            'pullState' => $pullState,
            'pendingPush' => $pendingPush,
            'conflicts' => $conflicts,
            'peerOnline' => $peerOnline,
            'peerError' => $peerError,
            'remoteName' => config('sync.remote'),
        ]);
    }

    public function pause(): RedirectResponse
    {
        Cache::forever($this->pauseKey(), true);

        return back()->with('status', 'Sync paused. The scheduler will no-op until resumed.');
    }

    public function resume(): RedirectResponse
    {
        Cache::forget($this->pauseKey());

        return back()->with('status', 'Sync resumed.');
    }

    public function now(): RedirectResponse
    {
        // Run one cycle immediately (synchronous), overriding enabled/paused.
        // A cycle can take a while over a slow CPanel link; lift PHP's execution
        // cap (just above the sync:run lock TTL) so the request can't 504 and
        // leave a half-finished run. The lock still prevents overlap.
        @set_time_limit(310);

        Artisan::call('sync:run', ['--force' => true]);

        return back()->with('status', 'Sync Now: '.trim(Artisan::output()));
    }
}
