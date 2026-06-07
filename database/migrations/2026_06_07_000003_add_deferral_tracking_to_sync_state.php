<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks how many cycles the pull stream has been stuck on a single un-resolvable
 * change (a missing FK dependency). After SyncEngine::MAX_DEFERRALS the change is
 * dead-lettered to sync_conflicts and skipped, so one poison row can no longer
 * wedge inbound replication. See docs/database-sync-plan.md §7.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sync_state', function (Blueprint $table) {
            if (! Schema::hasColumn('sync_state', 'deferred_change_id')) {
                $table->unsignedBigInteger('deferred_change_id')->nullable()->after('last_change_id');
            }
            if (! Schema::hasColumn('sync_state', 'deferred_attempts')) {
                $table->unsignedInteger('deferred_attempts')->default(0)->after('deferred_change_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sync_state', function (Blueprint $table) {
            foreach (['deferred_change_id', 'deferred_attempts'] as $column) {
                if (Schema::hasColumn('sync_state', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
