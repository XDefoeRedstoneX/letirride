<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        DB::table('products')->upsert([
            ['id' => 1, 'category_id' => 1, 'name' => 'Steam Wallet Rp 100.000', 'description' => 'Top up Steam wallet credit instantly.', 'price' => 110000.00, 'point_reward' => 1100, 'is_active' => true, 'image' => 'steam-wallet.svg'],
            ['id' => 2, 'category_id' => 3, 'name' => 'Spotify Premium 1 Month', 'description' => 'One month Spotify Premium subscription.', 'price' => 54900.00, 'point_reward' => 549, 'is_active' => true, 'image' => 'spotify.svg'],
            ['id' => 3, 'category_id' => 2, 'name' => 'Netflix Standard', 'description' => 'Netflix Standard plan for one month.', 'price' => 186000.00, 'point_reward' => 1860, 'is_active' => true, 'image' => 'netflix.svg'],
            ['id' => 4, 'category_id' => 2, 'name' => 'YouTube Premium', 'description' => 'Ad-free YouTube Premium access.', 'price' => 59000.00, 'point_reward' => 590, 'is_active' => true, 'image' => 'youtube.svg'],
            ['id' => 5, 'category_id' => 1, 'name' => 'Canva Pro 1 Year', 'description' => 'Full year access to Canva Pro.', 'price' => 120000.00, 'point_reward' => 1200, 'is_active' => true, 'image' => 'canva.svg'],
            ['id' => 6, 'category_id' => 5, 'name' => 'Xbox Game Pass', 'description' => 'Game Pass subscription for Xbox players.', 'price' => 149000.00, 'point_reward' => 1490, 'is_active' => true, 'image' => 'xbox.svg'],
            ['id' => 7, 'category_id' => 1, 'name' => 'Google Play Gift Card', 'description' => 'Google Play balance for apps and content.', 'price' => 50000.00, 'point_reward' => 500, 'is_active' => true, 'image' => 'google-play.svg'],
            ['id' => 8, 'category_id' => 10, 'name' => 'Notion Personal Pro', 'description' => 'Upgrade your Notion workspace.', 'price' => 75000.00, 'point_reward' => 750, 'is_active' => true, 'image' => 'notion.svg'],
            ['id' => 9, 'category_id' => 3, 'name' => 'SoundCloud Go+', 'description' => 'Premium SoundCloud listening experience.', 'price' => 69000.00, 'point_reward' => 690, 'is_active' => true, 'image' => 'soundcloud.svg'],
            ['id' => 10, 'category_id' => 2, 'name' => 'Viu Premium', 'description' => 'Stream Viu without ads.', 'price' => 39000.00, 'point_reward' => 390, 'is_active' => true, 'image' => 'viu.svg'],
        ], ['id'], ['category_id', 'name', 'description', 'price', 'point_reward', 'is_active', 'image']);
    }
}
