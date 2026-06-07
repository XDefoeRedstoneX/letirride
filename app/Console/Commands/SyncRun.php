<?php

namespace App\Console\Commands;

use App\Services\Sync\SyncEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * One bidirectional sync cycle (pull peer changes, then push local changes).
 * Scheduled every minute on the local node; also runnable on demand from the
 * control panel's "Sync Now" button. See docs/database-sync-plan.md §7, §9.
 */
class SyncRun extends Command
{
    protected $signature = 'sync:run {--force : Run even if SYNC_ENABLED is false or sync is paused}';

    protected $description = 'Run one local<->CPanel synchronization cycle.';

    public function handle(SyncEngine $engine): int
    {
        if (! config('sync.is_local')) {
            $this->warn('Not the local node (SYNC_IS_LOCAL=false). Nothing to do.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');

        if (! $force && ! config('sync.enabled')) {
            $this->warn('Sync is disabled (SYNC_ENABLED=false). Use --force to override.');

            return self::SUCCESS;
        }

        if (! $force && Cache::get(config('sync.pause_cache_key'))) {
            $this->warn('Sync is paused via the control panel. Use --force to override.');

            return self::SUCCESS;
        }

        // Never let two cycles overlap.
        $lock = Cache::lock('sync:run', 300);
        if (! $lock->get()) {
            $this->warn('Another sync cycle is already running.');

            return self::SUCCESS;
        }

        try {
            $stats = $engine->runCycle();
        } catch (\Throwable $e) {
            Log::error('sync: cycle failed: '.$e->getMessage(), ['exception' => $e]);
            $this->error('Sync cycle failed: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            $lock->release();
        }

        $this->info(sprintf(
            'pull: %d applied, %d conflicts, %d deferred, %d skipped | push: %d applied, %d conflicts, %d deferred, %d skipped',
            $stats['pull']['applied'], $stats['pull']['conflicts'], $stats['pull']['deferred'], $stats['pull']['skipped'],
            $stats['push']['applied'], $stats['push']['conflicts'], $stats['push']['deferred'], $stats['push']['skipped'],
        ));

        return self::SUCCESS;
    }
}
