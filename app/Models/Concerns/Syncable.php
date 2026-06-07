<?php

namespace App\Models\Concerns;

use App\Services\Sync\SyncRecorder;

/**
 * Records this model's create/update/delete/restore into the sync outbox.
 * Pairs with HasUlid (identity) and SoftDeletes (so deletes are tombstoned and
 * propagate). The actual recording + loop-suppression lives in SyncRecorder.
 */
trait Syncable
{
    public static function bootSyncable(): void
    {
        // Use registerModelEvent directly: it just attaches listeners without
        // touching the model instance (static::restored() would route through
        // __callStatic and instantiate the model mid-boot). 'restored' only ever
        // fires for SoftDeletes models (e.g. News); it's a harmless no-op otherwise.
        static::registerModelEvent('created', fn ($model) => app(SyncRecorder::class)->record($model, 'insert'));
        static::registerModelEvent('updated', fn ($model) => app(SyncRecorder::class)->record($model, 'update'));
        static::registerModelEvent('deleted', fn ($model) => app(SyncRecorder::class)->record($model, 'delete'));
        static::registerModelEvent('restored', fn ($model) => app(SyncRecorder::class)->record($model, 'update'));
    }
}
