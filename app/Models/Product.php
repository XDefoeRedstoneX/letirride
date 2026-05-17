<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'type', 'name', 'description', 'price', 'point_reward', 'image', 'img', 'is_active'])]
class Product extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
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
}
