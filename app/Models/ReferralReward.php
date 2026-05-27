<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralReward extends Model
{
    public const UPDATED_AT = null;

    public const KIND_REFEREE_WELCOME = 'referee_welcome';

    public const KIND_FIRST_PURCHASE = 'first_purchase';

    public const KIND_COMMISSION = 'commission';

    public const KIND_MILESTONE = 'milestone';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'referral_id',
        'recipient_id',
        'order_id',
        'tier_id',
        'kind',
        'points_amount',
    ];

    protected function casts(): array
    {
        return [
            'points_amount' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(ReferralTier::class, 'tier_id');
    }
}
