<?php

namespace App\Models;

use Database\Factories\ReferralFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referral extends Model
{
    use \App\Models\Concerns\HasUlid;
    use \App\Models\Concerns\Syncable;
    /** @use HasFactory<ReferralFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    public const STATUS_PENDING = 'pending';

    public const STATUS_FIRST_PURCHASE_REWARDED = 'first_purchase_rewarded';

    public const STATUS_VOID = 'void';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'status',
        'first_purchase_order_id',
        'first_purchase_rewarded_at',
        'total_commission_paid',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'first_purchase_rewarded_at' => 'datetime',
            'total_commission_paid' => 'integer',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function firstPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'first_purchase_order_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }
}
