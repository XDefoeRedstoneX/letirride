<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'noinv',
        'user_id',
        'user_discount_id',
        'subtotal',
        'discount_amount',
        'total_price_after_discount',
        'payment_gateway_ref',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_price_after_discount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Returns the invoice number without the Midtrans ::timestamp suffix.
     * The raw noinv column keeps the full ID for Midtrans API status queries.
     */
    public function getDisplayNoinvAttribute(): string
    {
        return Str::contains($this->noinv, '::')
            ? Str::beforeLast($this->noinv, '::')
            : $this->noinv;
    }

    /**
     * Calculates the total points awarded for this order based on dynamic point multipliers.
     */
    public function calculateTotalPointsAwarded(): int
    {
        $totalPointsAwarded = 0.0;
        $orderSubtotal = (float) $this->subtotal;
        $orderTotal = (float) $this->total_price_after_discount;
        $discountRatio = $orderSubtotal > 0 ? ($orderTotal / $orderSubtotal) : 1.0;

        foreach ($this->orderDetails as $detail) {
            $product = $detail->product;
            if (! $product) {
                continue;
            }

            $itemOriginalPrice = (float) $detail->total_price_in_cart;
            $itemFinalPrice = $itemOriginalPrice * $discountRatio;

            $totalPointsAwarded += $product->calculatePoints($itemFinalPrice);
        }

        return (int) floor($totalPointsAwarded);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userDiscount(): BelongsTo
    {
        return $this->belongsTo(UserDiscount::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function productKeys(): HasMany
    {
        return $this->hasMany(ProductKey::class);
    }
}
