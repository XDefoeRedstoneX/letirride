<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountType extends Model
{
    use \App\Models\Concerns\HasUlid;
    use \App\Models\Concerns\Syncable;
    public $timestamps = false;

    public const TYPES = ['percent', 'fixed'];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'value',
        'target_category_id',
        'target_subcategory_id',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    public function targetCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'target_category_id');
    }

    public function targetSubcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'target_subcategory_id');
    }

    public function userDiscounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public function gachaPools(): HasMany
    {
        return $this->hasMany(GachaPool::class);
    }

    public function pointShopItems(): HasMany
    {
        return $this->hasMany(PointShopItem::class);
    }

    /**
     * Human-readable value, e.g. "10%" or "Rp 30.000".
     */
    public function valueLabel(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.').'%'
            : 'Rp '.number_format((float) $this->value, 0, ',', '.');
    }

    /**
     * Where the voucher applies: a subcategory, a category, or storewide.
     */
    public function scopeLabel(): string
    {
        return $this->targetSubcategory?->name
            ?? $this->targetCategory?->name
            ?? 'Storewide';
    }

    /**
     * How many places reference this discount (prizes + shop items). Used to
     * block deletion of an in-use discount.
     */
    public function usageCount(): int
    {
        return $this->gachaPools()->count() + $this->pointShopItems()->count();
    }
}
