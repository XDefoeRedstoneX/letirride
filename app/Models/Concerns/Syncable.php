<?php

namespace App\Models\Concerns;

use App\Services\Sync\SyncRecorder;

/**
 * Records this model's create/update/delete into the sync outbox. Pairs with
 * HasUlid (identity) and SoftDeletes (so deletes are tombstoned and propagate).
 * The actual recording + loop-suppression lives in SyncRecorder.
 *
 * Inserts are captured via the 'created' model event. Updates and deletes are
 * captured by SyncableBuilder instead of model events, because events only fire
 * for single-instance saves — bulk writes (Model::where(...)->update()/delete()/
 * increment()) fire none, and would silently never sync. Routing every Eloquent
 * mutation through the builder records it exactly once, instance or bulk.
 */
trait Syncable
{
    public static function bootSyncable(): void
    {
        // registerModelEvent attaches the listener without touching the model
        // instance (static::created() would route through __callStatic and
        // instantiate the model mid-boot). Updates/deletes are handled by
        // SyncableBuilder; instance saves funnel through it too, so there is no
        // double recording (the 'updated'/'deleted' events are intentionally not
        // listened to here).
        static::registerModelEvent('created', fn ($model) => app(SyncRecorder::class)->record($model, 'insert'));
    }

    /**
     * Route all Eloquent queries for this model through SyncableBuilder so bulk
     * update/delete/increment/decrement are recorded into the outbox.
     */
    public function newEloquentBuilder($query): SyncableBuilder
    {
        return new SyncableBuilder($query);
    }
}
