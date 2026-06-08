<?php

namespace App\Models\Concerns;

use App\Services\Sync\Sync;
use App\Services\Sync\SyncRecorder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eloquent builder for Syncable models that records bulk writes into the sync
 * outbox. Plain model events only fire for single-instance saves, so bulk
 * query-builder writes — Model::where(...)->update()/->delete()/->increment() —
 * would otherwise be invisible to the sync engine (see docs/database-sync-plan.md
 * §6). Routing them through here makes EVERY write recorded exactly once:
 * inserts via the 'created' event (Syncable), all mutations/deletes via this class.
 *
 * Recording is skipped while the engine applies pulled changes (Sync guard; the
 * engine uses the base query builder anyway, never Eloquent) and for tables not
 * listed in config/sync.php.
 */
class SyncableBuilder extends Builder
{
    /** @param  array<string, mixed>  $values */
    public function update(array $values)
    {
        if (! $this->shouldRecord()) {
            return parent::update($values);
        }

        $ids = $this->affectedKeys();
        $affected = parent::update($values);
        $this->recordKeys($ids, 'update'); // re-read fresh values after the write

        return $affected;
    }

    public function delete()
    {
        if (! $this->shouldRecord()) {
            return parent::delete();
        }

        $ids = $this->affectedKeys();

        // Soft-delete models: the row survives (deleted_at is set), so re-read it
        // AFTER and let SyncRecorder turn the tombstone into an 'update'. Hard
        // deletes: capture the full rows BEFORE they vanish.
        if ($this->modelUsesSoftDeletes()) {
            $result = parent::delete();
            $this->recordKeys($ids, 'delete');

            return $result;
        }

        $models = $this->rowsByKey($ids);
        $result = parent::delete();
        $this->recordModels($models, 'delete');

        return $result;
    }

    /** @param  array<string, mixed>  $extra */
    public function increment($column, $amount = 1, array $extra = [])
    {
        if (! $this->shouldRecord()) {
            return parent::increment($column, $amount, $extra);
        }

        $ids = $this->affectedKeys();
        $result = parent::increment($column, $amount, $extra);
        $this->recordKeys($ids, 'update');

        return $result;
    }

    /** @param  array<string, mixed>  $extra */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        if (! $this->shouldRecord()) {
            return parent::decrement($column, $amount, $extra);
        }

        $ids = $this->affectedKeys();
        $result = parent::decrement($column, $amount, $extra);
        $this->recordKeys($ids, 'update');

        return $result;
    }

    private function shouldRecord(): bool
    {
        return ! Sync::applying()
            && array_key_exists($this->getModel()->getTable(), config('sync.tables', []));
    }

    private function modelUsesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->getModel()), true);
    }

    /**
     * Primary keys matching this query, captured BEFORE the mutation (the write
     * may change the columns the WHERE clause filters on). Cloned so the pluck's
     * column selection never leaks into the query we are about to execute.
     *
     * @return array<int, mixed>
     */
    private function affectedKeys(): array
    {
        return (clone $this->toBase())->pluck($this->getModel()->getKeyName())->all();
    }

    /**
     * Re-read rows by key WITHOUT global scopes (so trashed rows are included),
     * then record each.
     *
     * @param  array<int, mixed>  $ids
     */
    private function recordKeys(array $ids, string $op): void
    {
        if (empty($ids)) {
            return;
        }

        $this->recordModels($this->rowsByKey($ids), $op);
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    private function rowsByKey(array $ids)
    {
        return $this->getModel()->newQueryWithoutScopes()->whereKey($ids)->get();
    }

    /** @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>  $models */
    private function recordModels($models, string $op): void
    {
        $recorder = app(SyncRecorder::class);

        foreach ($models as $model) {
            $recorder->record($model, $op);
        }
    }
}
