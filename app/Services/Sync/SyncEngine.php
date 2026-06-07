<?php

namespace App\Services\Sync;

use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The bidirectional logical-sync engine. Runs ONLY on the local node (it can
 * reach CPanel; CPanel cannot reach it). One cycle = pull peer changes, then
 * push local changes. Rows are matched across nodes by ULID; foreign keys are
 * translated ULID<->local-id on the fly. See docs/database-sync-plan.md §7.
 *
 * Applies use the query builder (not Eloquent), so model observers never fire
 * during apply, and every apply is additionally wrapped in Sync::withoutRecording()
 * — there is no echo back into the outbox by construction or by guard.
 */
class SyncEngine
{
    /**
     * How many cycles a change may stay 'deferred' (missing FK dependency) before
     * it is dead-lettered to sync_conflicts and skipped. Parents always precede
     * children in id order, so a real dependency resolves within a cycle; a
     * deferral that survives this many cycles means the parent will never arrive,
     * and must not be allowed to wedge the whole inbound stream.
     */
    private const MAX_DEFERRALS = 30;

    private string $localNode;
    private string $remoteConnection;
    private string $remoteNode;
    private string $adminNode;
    private array $tables;

    public function __construct()
    {
        $this->localNode = (string) config('sync.node_id');
        $this->remoteConnection = (string) config('sync.remote');
        $this->remoteNode = (string) config('sync.remote_node_id');
        $this->adminNode = (string) config('sync.admin_node');
        $this->tables = (array) config('sync.tables', []);
    }

    /** Run a full cycle and return per-direction stats. */
    public function runCycle(): array
    {
        return [
            'pull' => $this->pull(),
            'push' => $this->push(),
        ];
    }

    private function local(): ConnectionInterface
    {
        return DB::connection(config('database.default'));
    }

    private function remote(): ConnectionInterface
    {
        return DB::connection($this->remoteConnection);
    }

    // ---------------------------------------------------------------- PULL ----

    /** Apply peer-originated changes into the local DB, in id order. */
    public function pull(): array
    {
        $stats = ['applied' => 0, 'conflicts' => 0, 'deferred' => 0, 'dropped' => 0, 'skipped' => 0];

        $state = $this->pullState();
        $watermark = (int) ($state->last_change_id ?? 0);
        $deferredId = $state->deferred_change_id ?? null;
        $deferredAttempts = (int) ($state->deferred_attempts ?? 0);

        $batch = $this->remote()->table('sync_changes')
            ->where('node_id', '!=', $this->localNode)
            ->where('id', '>', $watermark)
            ->orderBy('id')
            ->limit(1000)
            ->get();

        foreach ($batch as $change) {
            $status = Sync::withoutRecording(fn () => $this->applyOne($change, $this->local()));

            if ($status === 'deferred') {
                // Missing FK parent. Count attempts against THIS id; reset if we
                // are now stuck on a different one.
                $deferredAttempts = ((int) $deferredId === (int) $change->id) ? $deferredAttempts + 1 : 1;
                $deferredId = $change->id;

                if ($deferredAttempts < self::MAX_DEFERRALS) {
                    // Hold the line to preserve ordering; retry this id next cycle.
                    $stats['deferred']++;
                    break;
                }

                // The dependency will never arrive: dead-letter it and move on so
                // one poison change cannot wedge the entire inbound stream.
                $this->logConflict($this->local(), $change, 'deferred_dropped', $this->localNode);
                $stats['dropped']++;
                $deferredId = null;
                $deferredAttempts = 0;
                $watermark = (int) $change->id;
                continue;
            }

            $deferredId = null;
            $deferredAttempts = 0;
            $watermark = (int) $change->id;
            $stats[$status === 'older' ? 'conflicts' : ($status === 'applied' ? 'applied' : 'skipped')]++;
        }

        $this->setPullState($watermark, $deferredId, $deferredAttempts);

        return $stats;
    }

    // ---------------------------------------------------------------- PUSH ----

    /** Push local-originated changes to the peer, where policy permits. */
    public function push(): array
    {
        $stats = ['applied' => 0, 'conflicts' => 0, 'deferred' => 0, 'dropped' => 0, 'skipped' => 0];

        $batch = $this->local()->table('sync_changes')
            ->where('node_id', $this->localNode)
            ->where('applied', false)
            ->orderBy('id')
            ->limit(1000)
            ->get();

        foreach ($batch as $change) {
            $policy = $this->tables[$change->table_name]['policy'] ?? null;

            if ($policy === null || ! $this->mayPush($policy)) {
                // Never pushed (cpanel_authority / admin replica / unknown): mark done.
                $this->markPushed($change->id);
                $stats['skipped']++;
                continue;
            }

            $status = Sync::withoutRecording(fn () => $this->applyOne($change, $this->remote()));

            if ($status === 'deferred') {
                $stats['deferred']++;
                continue; // leave applied=false; retry next cycle
            }

            $this->markPushed($change->id);
            $stats[$status === 'older' ? 'conflicts' : ($status === 'applied' ? 'applied' : 'skipped')]++;
        }

        return $stats;
    }

    /** On the local node, only bidirectional (and admin tables we own) flow outward. */
    private function mayPush(string $policy): bool
    {
        return match ($policy) {
            'bidirectional' => true,
            'admin_authority' => $this->adminNode === $this->localNode,
            default => false, // cpanel_authority
        };
    }

    // --------------------------------------------------------------- APPLY ----

    /**
     * Apply a single change row onto $target. Returns one of:
     * 'applied' | 'older' (lost LWW) | 'deferred' (missing FK dep) | 'skipped'.
     */
    private function applyOne(object $change, ConnectionInterface $target): string
    {
        $config = $this->tables[$change->table_name] ?? null;
        if ($config === null) {
            return 'skipped';
        }

        $table = $change->table_name;
        $ulid = $change->row_ulid;
        $policy = $config['policy'] ?? null;

        if ($change->op === 'delete') {
            // A delete on a bidirectional row must still lose to a more-recent
            // update on the receiving side (LWW); otherwise a stale delete would
            // silently drop a newer change. (Soft deletes are recorded as updates,
            // so a real 'delete' here is an intentional hard delete.)
            if ($policy === 'bidirectional') {
                $existing = $target->table($table)->where('ulid', $ulid)->first();

                if ($existing) {
                    $incoming = $this->time($change->occurred_at);
                    $current = isset($existing->updated_at) ? $this->time($existing->updated_at) : null;

                    if ($current !== null && $incoming !== null && $incoming->lt($current)) {
                        $this->logConflict($target, $change, 'lww_older', $this->winnerFor($target));

                        return 'older';
                    }
                }
            }

            $target->table($table)->where('ulid', $ulid)->delete();

            return 'applied';
        }

        $payload = json_decode($change->payload ?? '', true) ?: [];

        // Translate FK ULIDs back to this node's local ids.
        foreach (($config['fks'] ?? []) as $column => $referencedTable) {
            if (! array_key_exists($column, $payload)) {
                continue;
            }
            if ($payload[$column] === null) {
                continue;
            }
            $localId = $target->table($referencedTable)->where('ulid', $payload[$column])->value('id');
            if ($localId === null) {
                return 'deferred'; // parent not present yet; retry later
            }
            $payload[$column] = $localId;
        }

        // Column-level authority: only the owning node may set the column.
        foreach (($config['column_overrides'] ?? []) as $column => $authorityNode) {
            if ($change->node_id !== $authorityNode) {
                unset($payload[$column]);
            }
        }

        $existing = $target->table($table)->where('ulid', $ulid)->first();

        if ($existing) {
            // Conflict resolution (LWW) applies to bidirectional rows only; for
            // authority/admin tables the source IS the authority, so it wins.
            if ($policy === 'bidirectional') {
                $incoming = $this->time($change->occurred_at);
                $current = isset($existing->updated_at) ? $this->time($existing->updated_at) : null;

                if ($current !== null && $incoming !== null && $incoming->lt($current)) {
                    $this->logConflict($target, $change, 'lww_older', $this->winnerFor($target));

                    return 'older';
                }
            }

            unset($payload['id']); // never change the PK on an existing row
            if (empty($payload)) {
                return 'skipped';
            }
            $target->table($table)->where('ulid', $ulid)->update($payload);

            return 'applied';
        }

        // New row: preserve the source's id. The id-space is partitioned per node
        // (config/sync.php), so ids are collision-free and stay identical across
        // nodes — keeping relationships and auth ids consistent under the
        // authority connection.
        if (empty($payload)) {
            return 'skipped';
        }
        $target->table($table)->insert($payload);

        return 'applied';
    }

    private function time($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    // ---------------------------------------------------------- bookkeeping ----

    /** The pull watermark + deferral-tracking row (one global row, table_name='*'). */
    private function pullState(): ?object
    {
        return $this->local()->table('sync_state')
            ->where('direction', 'pull')->where('table_name', '*')->first();
    }

    private function setPullState(int $id, $deferredId, int $deferredAttempts): void
    {
        $this->local()->table('sync_state')->updateOrInsert(
            ['direction' => 'pull', 'table_name' => '*'],
            [
                'last_change_id' => $id,
                'deferred_change_id' => $deferredId,
                'deferred_attempts' => $deferredAttempts,
                'last_synced_at' => now(),
                'last_status' => 'ok',
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    private function markPushed(int $id): void
    {
        $this->local()->table('sync_changes')->where('id', $id)->update(['applied' => true]);
    }

    /** The node whose surviving copy won a conflict = the node owning $target's DB. */
    private function winnerFor(ConnectionInterface $target): string
    {
        return $target->getName() === $this->remoteConnection ? $this->remoteNode : $this->localNode;
    }

    private function logConflict(ConnectionInterface $target, object $change, string $reason, string $winnerNode): void
    {
        try {
            $target->table('sync_conflicts')->insert([
                'table_name' => $change->table_name,
                'row_ulid' => $change->row_ulid,
                'winner_node' => $winnerNode,
                'loser_node' => $change->node_id,
                'winning_change_id' => null,
                'losing_payload' => $change->payload,
                'reason' => $reason,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('sync: failed to log conflict: '.$e->getMessage());
        }
    }
}
