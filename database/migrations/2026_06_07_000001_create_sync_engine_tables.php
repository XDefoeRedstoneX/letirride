<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sync engine bookkeeping tables. These are policy = NEVER (excluded from sync).
 * See docs/database-sync-plan.md §6.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Append-only outbox: one row per create/update/delete of a synced model.
        Schema::create('sync_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('node_id', 32);                 // origin node: local | cpanel
            $table->string('table_name', 64);
            $table->char('row_ulid', 26);                  // logical identity of the affected row
            $table->string('op', 10);                      // insert | update | delete
            $table->json('payload')->nullable();           // synced columns; FKs stored as ULIDs
            $table->unsignedBigInteger('row_version')->default(1);
            $table->timestamp('occurred_at')->useCurrent(); // source updated_at / event time
            $table->boolean('applied')->default(false);     // has this been shipped to the peer
            $table->timestamp('created_at')->useCurrent();

            $table->index(['applied', 'node_id', 'id']);    // PUSH scan
            $table->index(['node_id', 'id']);               // PULL scan by watermark
            $table->index(['table_name', 'row_ulid']);      // history per row
        });

        // Watermarks + last-run status, one row per (direction, table).
        Schema::create('sync_state', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('direction', 8);                 // pull | push
            $table->string('table_name', 64);
            $table->unsignedBigInteger('last_change_id')->default(0); // peer sync_changes.id watermark
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_status', 32)->nullable();  // ok | error | running
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['direction', 'table_name']);
        });

        // Audit log of every change discarded by conflict resolution.
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('table_name', 64);
            $table->char('row_ulid', 26);
            $table->string('winner_node', 32)->nullable();
            $table->string('loser_node', 32)->nullable();
            $table->unsignedBigInteger('winning_change_id')->nullable();
            $table->json('losing_payload')->nullable();
            $table->string('reason', 64)->nullable();       // lww | column_override | authority
            $table->timestamp('created_at')->useCurrent();

            $table->index(['table_name', 'row_ulid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_conflicts');
        Schema::dropIfExists('sync_state');
        Schema::dropIfExists('sync_changes');
    }
};
