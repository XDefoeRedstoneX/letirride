<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GachaHistory extends Model
{
    use \App\Models\Concerns\HasUlid;
    use \App\Models\Concerns\Syncable;
    public const UPDATED_AT = null;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'gacha_pool_id',
        'points_spent',
        'gacha_payment_id',
        'cost_type',
        'pity_triggered',
        'reward_type',
        'image_path',
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

    public function gachaPayment(): BelongsTo
    {
        return $this->belongsTo(GachaPayment::class);
    }
}
