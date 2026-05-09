<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GachaHistory extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'gacha_pool_id',
        'points_spent',
    ];

    protected function casts(): array
    {
        return [
            'points_spent' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gachaPool(): BelongsTo
    {
        return $this->belongsTo(GachaPool::class);
    }
}
