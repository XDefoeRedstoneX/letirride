<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GachaPool extends Model
{
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'prize_name',
        'discount_type_id',
        'rarity_item',
        'base_win_chance',
    ];

    protected function casts(): array
    {
        return [
            'base_win_chance' => 'decimal:2',
        ];
    }

    public function discountType(): BelongsTo
    {
        return $this->belongsTo(DiscountType::class);
    }
}
