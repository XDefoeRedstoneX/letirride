<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopupCredential extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'order_detail_id',
        'player_id',
        'zone_id',
        'server_id',
        'topup_status',
        'fulfilled_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'fulfilled_at' => 'datetime',
        ];
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class);
    }
}
