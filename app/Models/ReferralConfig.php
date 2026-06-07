<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralConfig extends Model
{
    use \App\Models\Concerns\HasUlid;
    use \App\Models\Concerns\Syncable;
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'referee_welcome_points',
        'referrer_first_purchase_pts',
        'commission_percent',
        'commission_per_order_cap',
        'commission_lifetime_cap',
        'referrer_min_account_age_h',
    ];

    protected function casts(): array
    {
        return [
            'referee_welcome_points' => 'integer',
            'referrer_first_purchase_pts' => 'integer',
            'commission_percent' => 'decimal:2',
            'commission_per_order_cap' => 'integer',
            'commission_lifetime_cap' => 'integer',
            'referrer_min_account_age_h' => 'integer',
        ];
    }

    /**
     * Return the singleton config row, creating it from defaults if missing.
     */
    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'referee_welcome_points' => 100,
            'referrer_first_purchase_pts' => 500,
            'commission_percent' => 0,
            'commission_per_order_cap' => 5000,
            'commission_lifetime_cap' => 50000,
            'referrer_min_account_age_h' => 0,
        ]);
    }
}
