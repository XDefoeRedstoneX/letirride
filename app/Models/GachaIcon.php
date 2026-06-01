<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class GachaIcon extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'label',
        'category',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Per-request cache of active icons keyed by `key`, so repeated lookups in a
     * single roll/render don't hammer the DB.
     *
     * @var Collection<string, self>|null
     */
    protected static $activeByKeyCache = null;

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Look up a single active icon by its key, using a per-request cache.
     */
    public static function byKey(?string $key): ?self
    {
        if (! $key) {
            return null;
        }

        if (static::$activeByKeyCache === null) {
            static::$activeByKeyCache = static::query()->active()->get()->keyBy('key');
        }

        return static::$activeByKeyCache->get($key);
    }

    /**
     * Drop the per-request cache (admin writes, tests).
     */
    public static function flushCache(): void
    {
        static::$activeByKeyCache = null;
    }
}
