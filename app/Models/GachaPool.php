<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['prize_name', 'base_win_chance', 'is_grand_prize'])]
class GachaPool extends Model
{
    protected function casts(): array
    {
        return [
            'base_win_chance' => 'decimal:2',
            'is_grand_prize' => 'boolean',
        ];
    }
}
