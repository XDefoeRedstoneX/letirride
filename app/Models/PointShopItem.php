<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'points_cost', 'discount_percentage'])]
class PointShopItem extends Model
{
    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
        ];
    }
}
