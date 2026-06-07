<?php

namespace App\Services\Sync;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

        $currentVersion = (int) DB::connection($connection)->table('sync_changes')
            ->where('table_name', $table)
            ->where('row_ulid', $ulid)
            ->max('row_version');

        DB::connection($connection)->table('sync_changes')->insert([
            'node_id' => $nodeId,
            'table_name' => $table,
            'row_ulid' => $ulid,
            'op' => $op,
            'payload' => $payload !== null ? json_encode($payload) : null,
            'row_version' => $currentVersion + 1,
            'occurred_at' => $occurredAt,
            'applied' => false,
            'created_at' => now(),
        ]);
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
            $attributes[$column] = $value === null
                ? null
                : DB::connection($connection)->table($referencedTable)->where('id', $value)->value('ulid');
        }

        return $attributes;
    }

    private function outboxExists(string $connection): bool
    {
        return $this->outboxExists[$connection] ??= Schema::connection($connection)->hasTable('sync_changes');
    }
}
