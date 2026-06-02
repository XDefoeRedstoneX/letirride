<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointShopItem extends Model
{
    public const REWARD_TYPES = ['discount', 'cashback'];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'point_cost',
        'reward_type',
        'discount_type_id',
        'points_amount',
        'img',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'point_cost' => 'integer',
            'points_amount' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function discountType(): BelongsTo
    {
        return $this->belongsTo(DiscountType::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(PointShopPurchase::class);
    }
}
