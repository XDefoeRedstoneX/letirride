<?php

namespace App\Services\Sync;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Writes one append-only row into `sync_changes` for every create/update/delete
 * of a synced model, on the model's own connection. Foreign-key columns are
 * stored as the referenced row's ULID so the payload is portable to the peer.
 *
 * Recording is skipped while the engine is applying pulled changes (Sync guard),
 * for tables not listed in config/sync.php, and if the outbox table is absent.
 */
class SyncRecorder
{
    /** Cache of "does sync_changes exist?" keyed by connection name. */
    private array $outboxExists = [];

    public function record(Model $model, string $op): void
    {
        if (Sync::applying()) {
            return;
        }

        $table = $model->getTable();
        $config = config('sync.tables', []);
        if (! isset($config[$table])) {
            return;
        }

        // A soft delete is really an update that sets deleted_at — record it as an
        // update so the tombstone (and LWW) propagate, instead of hard-deleting
        // the peer's row. A genuine force-delete still records as 'delete'.
        if ($op === 'delete' && $this->isSoftDelete($model)) {
            $op = 'update';
        }

        $connection = $model->getConnectionName() ?: config('database.default');
        if (! $this->outboxExists($connection)) {
            return;
        }

        $ulid = $model->getAttribute('ulid');
        if (empty($ulid)) {
            return; // a synced row must have a ULID (HasUlid assigns on create)
        }

        $payload = $op === 'delete'
            ? null
            : $this->buildPayload($model, $config[$table], $connection);

        $occurredAt = $model->getAttribute('updated_at') ?: now();

        // Attribute the change to the node that OWNS the connection it lands on,
        // not the running process. This matters for authority writes: a local
        // request writing to CPanel records on cpanel.sync_changes as 'cpanel',
        // so the local PULL (node_id != 'local') brings the row back. See plan §7.4.
        $nodeId = $connection === config('sync.remote')
            ? config('sync.remote_node_id')
            : config('sync.node_id');

        DB::connection($connection)->table('sync_changes')->insert([
            'node_id' => $nodeId,
            'table_name' => $table,
            'row_ulid' => $ulid,
            'op' => $op,
            'payload' => $payload !== null ? json_encode($payload) : null,
            'occurred_at' => $occurredAt,
            'applied' => false,
            'created_at' => now(),
        ]);
    }

    /** True when this 'deleted' event is a soft delete (recoverable), not a force delete. */
    private function isSoftDelete(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true)
            && method_exists($model, 'isForceDeleting')
            && ! $model->isForceDeleting()
            && $model->getAttribute('deleted_at') !== null;
    }

    /**
     * Snapshot the row's attributes, replacing each FK's local id with the
     * referenced row's ULID so the peer can translate it back to its own id.
     */
    private function buildPayload(Model $model, array $tableConfig, string $connection): array
    {
        $attributes = $model->getAttributes();

        foreach (($tableConfig['fks'] ?? []) as $column => $referencedTable) {
            if (! array_key_exists($column, $attributes)) {
                continue;
            }

            $value = $attributes[$column];
            if ($value === null) {
                continue; // a genuinely-null FK stays null
            }

            $referencedUlid = DB::connection($connection)->table($referencedTable)
                ->where('id', $value)->value('ulid');

            if ($referencedUlid === null) {
                // The parent has no ULID yet (backfill incomplete). Omit the column
                // rather than null it: apply leaves the peer's existing FK intact
                // instead of clobbering it. Surface it so the operator can backfill.
                Log::warning("sync: cannot resolve ULID for {$referencedTable}.id={$value} "
                    ."(FK {$column} on {$model->getTable()}); column omitted. Run sync:backfill-ulids.");
                unset($attributes[$column]);
                continue;
            }

            $attributes[$column] = $referencedUlid;
        }

        return $attributes;
    }

    private function outboxExists(string $connection): bool
    {
        return $this->outboxExists[$connection] ??= Schema::connection($connection)->hasTable('sync_changes');
    }
}
