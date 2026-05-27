<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GachaRarityChance extends Model
{
    protected $primaryKey = 'rarity';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'rarity',
        'base_chance',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_chance' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
