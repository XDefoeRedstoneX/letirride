<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Assigns a ULID to every existing row that doesn't have one, and gives rows a
 * sensible `updated_at` baseline. Run on the authority (CPanel) FIRST, before
 * seeding the local node, so both sides end up sharing identical ULIDs.
 *
 *   php artisan sync:backfill-ulids --connection=cpanel
 */
class SyncBackfillUlids extends Command
{
    protected $signature = 'sync:backfill-ulids
        {--connection= : DB connection to backfill (defaults to the default connection)}
        {--chunk=500 : Rows per batch}';

    protected $description = 'Assign ULIDs (and an updated_at baseline) to existing rows in synced tables.';

    public function handle(): int
    {
        $conn = $this->option('connection') ?: config('database.default');
        $chunk = (int) $this->option('chunk');
        $db = DB::connection($conn);

        $this->info("Backfilling ULIDs on connection [{$conn}] / database [{$db->getDatabaseName()}]");

        $tables = array_keys(config('sync.tables', []));
        $grandTotal = 0;

        foreach ($tables as $table) {
            if (! Schema::connection($conn)->hasTable($table) || ! Schema::connection($conn)->hasColumn($table, 'ulid')) {
                $this->line("  - {$table}: skipped (no table/ulid column)");
                continue;
            }

            $pk = $this->primaryKey($db, $table);
            if ($pk === null) {
                $this->warn("  - {$table}: skipped (no single-column primary key found)");
                continue;
            }

            // 1) Assign ULIDs to rows missing one.
            $count = 0;
            do {
                $rows = $db->table($table)->whereNull('ulid')->orderBy($pk)->limit($chunk)->get([$pk]);
                foreach ($rows as $row) {
                    $db->table($table)->where($pk, $row->{$pk})->update(['ulid' => (string) Str::ulid()]);
                    $count++;
                }
            } while ($rows->count() === $chunk);

            // 2) Give a baseline updated_at where it's null (LWW clock for old rows).
            if (Schema::connection($conn)->hasColumn($table, 'updated_at')) {
                $hasCreated = Schema::connection($conn)->hasColumn($table, 'created_at');
                $db->table($table)->whereNull('updated_at')->update([
                    'updated_at' => $hasCreated
                        ? DB::raw('COALESCE(created_at, NOW())')
                        : DB::raw('NOW()'),
                ]);
            }

            $grandTotal += $count;
            $this->line("  - {$table}: {$count} ULIDs assigned");
        }

        $this->info("Done. {$grandTotal} rows received a new ULID.");

        return self::SUCCESS;
    }

    /** Return the single-column primary key name, or null if composite/none. */
    private function primaryKey($db, string $table): ?string
    {
        $keys = $db->select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");

        if (count($keys) !== 1) {
            return null;
        }

        return $keys[0]->Column_name;
    }
}
