<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pxshop.com'],
            [
                'name' => 'City Mayor',
                'password' => Hash::make('admin123'),
                'google_id' => null,
                'role' => 'admin',
                'points_balance' => 999999,
            ]
        );

        User::updateOrCreate(
            ['email' => 'demo@pxshop.com'],
            [
                'name' => 'Pixel Walker',
                'password' => Hash::make('demo123'),
                'google_id' => null,
                'role' => 'customer',
                'points_balance' => 5000,
            ]
        );
    }
}
