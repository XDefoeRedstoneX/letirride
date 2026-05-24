<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGachaState extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'pity_counter',
        'mini_pity_counter',
        'total_spins',
        'free_spins',
        'last_legendary_at',
    ];

    protected function casts(): array
    {
        return [
            'pity_counter' => 'integer',
            'mini_pity_counter' => 'integer',
            'total_spins' => 'integer',
            'free_spins' => 'integer',
            'last_legendary_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
