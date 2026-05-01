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
        User::create([
            'name' => 'City Mayor',
            'email' => 'admin@pxshop.com',
            'password' => Hash::make('admin123'),
            'google_id' => null,
            'role' => 'admin',
            'points_balance' => 999999,
        ]);

        User::create([
            'name' => 'Pixel Walker',
            'email' => 'demo@pxshop.com',
            'password' => Hash::make('demo123'),
            'google_id' => null,
            'role' => 'customer',
            'points_balance' => 5000,
        ]);
    }
}
