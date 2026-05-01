<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountType extends Model
{
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'value',
        'target_category_id',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    public function targetCategory(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'target_category_id');
    }

    public function userDiscounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public function gachaPools(): HasMany
    {
        return $this->hasMany(GachaPool::class);
    }
}
