<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GachaBooster extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'description',
        'point_cost',
        'rarity_floor',
        'bonus_percent',
        'rolls_granted',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'point_cost' => 'integer',
            'bonus_percent' => 'decimal:2',
            'rolls_granted' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function activations(): HasMany
    {
        return $this->hasMany(UserActiveBooster::class);
    }
}
