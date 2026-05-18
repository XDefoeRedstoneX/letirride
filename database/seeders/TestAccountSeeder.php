<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserDiscount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestAccountSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Create or Update Test User
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Master',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'points_balance' => 50000,
                'referral_code' => 'TESTMASTER99',
            ]
        );
}
}
