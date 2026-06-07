<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActiveBooster extends Model
{
    use \App\Models\Concerns\HasUlid;
    use \App\Models\Concerns\Syncable;
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'gacha_booster_id',
        'rolls_remaining',
    ];

    protected function casts(): array
    {
        return [
            'rolls_remaining' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booster(): BelongsTo
    {
        return $this->belongsTo(GachaBooster::class, 'gacha_booster_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('rolls_remaining', '>', 0);
    }
}
