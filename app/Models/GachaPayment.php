<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GachaPayment extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'snap_token',
        'midtrans_order_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function history(): HasOne
    {
        return $this->hasOne(GachaHistory::class);
    }
}
