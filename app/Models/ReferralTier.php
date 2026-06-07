<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralTier extends Model
{
    use \App\Models\Concerns\HasUlid;
    use \App\Models\Concerns\Syncable;
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'threshold',
        'title',
        'description',
        'points_reward',
        'discount_type_id',
        'free_spins_reward',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'integer',
            'points_reward' => 'integer',
            'free_spins_reward' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function discountType(): BelongsTo
    {
        return $this->belongsTo(DiscountType::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * True when the tier hands out at least one tangible reward.
     */
    public function hasReward(): bool
    {
        return $this->points_reward > 0
            || $this->discount_type_id !== null
            || $this->free_spins_reward > 0;
    }
}
