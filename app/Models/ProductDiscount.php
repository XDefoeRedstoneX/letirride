<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDiscount extends Model
{
    use \App\Models\Concerns\HasUlid;
    use \App\Models\Concerns\Syncable;
    protected $fillable = [
        'product_id',
        'type',
        'value',
        'label',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'     => 'decimal:2',
            'ends_at'   => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active && ($this->ends_at === null || $this->ends_at->isFuture());
    }
}
