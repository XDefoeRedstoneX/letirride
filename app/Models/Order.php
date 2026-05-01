<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'total_price', 'payment_gateway_ref', 'status'])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucherCodes()
    {
        return $this->hasMany(VoucherCode::class);
    }
}
