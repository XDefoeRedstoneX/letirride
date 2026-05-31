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
        'reward_type',
        'icon_key',
        'points_amount',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'points_amount' => 'integer',
        ];
    }

    public function discountType(): BelongsTo
    {
        return $this->belongsTo(DiscountType::class);
    }

    /**
     * The catalog coin chosen for this prize (if any). Matched on the icon's
     * string `key` rather than an id, so keys can be the stable reference.
     */
    public function icon(): BelongsTo
    {
        return $this->belongsTo(GachaIcon::class, 'icon_key', 'key');
    }
}
