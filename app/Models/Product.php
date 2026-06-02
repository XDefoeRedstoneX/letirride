<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Subcategory;

#[Fillable(['category_id', 'type', 'name', 'description', 'price', 'point_multiplier', 'image', 'img', 'is_active'])]
class Product extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'point_multiplier' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Calculate points based on the final fiat price and the product's point multiplier.
     * 1 point per Rp 1,000 spent.
     */
    public function calculatePoints(float $finalPrice): float
    {
        // Calculate dynamic points and handle potential floating point inaccuracies
        return ($finalPrice / 1000.0) * (float) $this->point_multiplier;
    }

    public function isVoucher(): bool
    {
        return $this->type === 'voucher';
    }

    public function isDirectTopup(): bool
    {
        return $this->type === 'direct_topup';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function productKeys(): HasMany
    {
        return $this->hasMany(ProductKey::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function discount(): HasOne
    {
        return $this->hasOne(ProductDiscount::class);
    }

    /**
     * The effective selling price after any active product-level discount.
     * User vouchers are stacked on top of this value during checkout.
     */
    public function discountedPrice(): float
    {
        $d = $this->discount;
        if (! $d || ! $d->isCurrentlyActive()) {
            return (float) $this->price;
        }

        if ($d->type === 'percentage') {
            return round((float) $this->price * (1 - $d->value / 100), -2);
        }

        return max(0.0, (float) $this->price - (float) $d->value);
    }
}
