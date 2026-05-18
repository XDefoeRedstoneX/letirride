<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointShopItem extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'point_cost',
        'reward_type',
        'discount_type_id',
        'img',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'point_cost' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function discountType(): BelongsTo
    {
        return $this->belongsTo(DiscountType::class);
    }
}
