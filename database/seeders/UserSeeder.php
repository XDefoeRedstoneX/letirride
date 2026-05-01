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
            'username' => 'CityMayor',
            'email' => 'admin@pxshop.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'balance' => 0,
            'points' => 999999,
        ]);

        User::create([
            'name' => 'Pixel Walker',
            'username' => 'PixelWalker',
            'email' => 'demo@pxshop.com',
            'password' => Hash::make('demo123'),
            'role' => 'customer',
            'balance' => 1000000,
            'points' => 5000,
        ]);
    }
}
