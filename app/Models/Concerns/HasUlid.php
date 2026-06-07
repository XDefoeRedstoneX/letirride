<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Assigns a globally-unique ULID to the `ulid` column on creation so the row
 * has a stable identity shared across the local and CPanel nodes. The bigint
 * primary key is left untouched; `ulid` is the sync key. See config/sync.php.
 */
trait HasUlid
{
    protected static function bootHasUlid(): void
    {
        static::creating(function ($model): void {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    public function initializeHasUlid(): void
    {
        if (! in_array('ulid', $this->fillable, true)) {
            $this->fillable[] = 'ulid';
        }
    }
}
