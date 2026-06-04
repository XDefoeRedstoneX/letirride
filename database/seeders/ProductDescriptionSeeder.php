<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Storefront descriptions shown in the product buy modal.
 *
 * This is the single source of truth for product copy: DatabaseSeeder::seedProducts()
 * overlays these onto the seeded rows, and run() below applies them to existing
 * rows without touching any other column — so it is safe to run on a live database
 * (php artisan db:seed --class=ProductDescriptionSeeder) without re-seeding anything else.
 */
class ProductDescriptionSeeder extends Seeder
{
    /**
     * Map of product id => storefront description.
     *
     * @var array<int, string>
     */
    public const DESCRIPTIONS = [
        // ===== Steam Wallet (voucher — instant code) =====
        29 => 'Top up Rp40.000 of Steam Wallet credit. Redeem the code on Steam to buy games, DLC, and in-game items. Code delivered instantly after payment.',
        30 => 'Top up Rp60.000 of Steam Wallet credit. Spend it on games, DLC, and in-game purchases across the Steam store. Code delivered instantly after payment.',
        1  => 'Top up Rp150.000 of Steam Wallet credit. Use it for games, DLC, and in-game items on Steam. Code delivered instantly after payment.',
        31 => 'Top up Rp400.000 of Steam Wallet credit — great for full-price releases and bundles. Code delivered instantly after payment.',
        2  => 'Top up Rp750.000 of Steam Wallet credit. Plenty for new releases, DLC, and your wishlist. Code delivered instantly after payment.',

        // ===== PlayStation Network (voucher — instant code) =====
        32 => 'Rp100.000 of PlayStation Store credit. Redeem on your PSN account for games, add-ons, and PS Plus. Code delivered instantly.',
        33 => 'Rp200.000 of PlayStation Store credit for games, DLC, and subscriptions on PSN. Code delivered instantly.',
        5  => 'Rp400.000 of PlayStation Store credit. Buy full games, season passes, and PS Plus on your PSN account. Code delivered instantly.',
        34 => 'Rp750.000 of PlayStation Store credit — ideal for new releases and bundles. Code delivered instantly.',

        // ===== Nintendo eShop (voucher — instant code) =====
        35 => '$5 Nintendo eShop gift card. Redeem on your Nintendo Account for Switch games, DLC, and indies. Code delivered instantly.',
        11 => '$10 Nintendo eShop gift card. Add funds to your Nintendo Account for games and DLC on Switch. Code delivered instantly.',
        12 => '$20 Nintendo eShop gift card for Switch games, add-ons, and eShop purchases. Code delivered instantly.',
        36 => '$50 Nintendo eShop gift card — stock up on full-price Switch titles and DLC. Code delivered instantly.',

        // ===== Netflix (voucher — instant code) =====
        37 => 'One month of Netflix on the Mobile plan. Watch on one phone or tablet at a time in 480p. Code delivered instantly.',
        3  => 'One month of Netflix on the Standard plan. Stream in Full HD (1080p) on up to two screens at once. Code delivered instantly.',
        38 => 'One month of Netflix Premium. Stream in 4K Ultra HD on up to four screens at once. Code delivered instantly.',
        39 => 'Three months of Netflix on the Standard plan — Full HD (1080p) on up to two screens. Code delivered instantly.',

        // ===== Spotify (voucher — instant code) =====
        40 => 'One month of Spotify Premium Individual. Ad-free music, offline downloads, and unlimited skips. Code delivered instantly.',
        4  => 'Three months of Spotify Premium. Enjoy ad-free, offline listening with unlimited skips. Code delivered instantly.',
        41 => 'Six months of Spotify Premium Individual — ad-free music with offline downloads. Code delivered instantly.',
        42 => 'Twelve months of Spotify Premium Individual. A full year of ad-free, offline listening. Code delivered instantly.',

        // ===== Discord Nitro (voucher — instant code) =====
        43 => 'One month of Discord Nitro Basic. Bigger uploads, custom emoji, and a profile badge. Code delivered instantly.',
        44 => 'One month of full Discord Nitro — HD streaming, 500MB uploads, custom emoji, and server boosts. Code delivered instantly.',
        9  => 'A full year of Discord Nitro. HD streaming, large uploads, custom emoji, and two server boosts every month. Code delivered instantly.',

        // ===== Xbox Game Pass (voucher — instant code) =====
        10 => 'One month of Xbox Game Pass Ultimate. Hundreds of games on console, PC, and cloud, plus online multiplayer. Code delivered instantly.',
        45 => 'Three months of Xbox Game Pass Ultimate — a huge library across console, PC, and cloud, plus online play. Code delivered instantly.',
        46 => 'Twelve months of Xbox Game Pass Ultimate. A full year of games on console, PC, and cloud, plus online multiplayer. Code delivered instantly.',

        // ===== YouTube Premium (voucher — instant code) =====
        20 => 'One month of YouTube Premium for one user. Ad-free videos, background play, and YouTube Music. Code delivered instantly.',
        47 => 'Three months of YouTube Premium for one user — ad-free, background play, and YouTube Music. Code delivered instantly.',
        21 => 'One month of YouTube Premium Family — ad-free YouTube and YouTube Music for up to 5 household members. Code delivered instantly.',

        // ===== Canva (voucher — instant code) =====
        22 => 'One month of Canva Pro for one user. Premium templates, brand kits, background remover, and 1TB of storage. Code delivered instantly.',
        48 => 'Twelve months of Canva Pro for one user — premium templates, brand kits, and the full Pro toolset. Code delivered instantly.',
        49 => 'One month of Canva Teams for up to 5 users. Pro features plus shared brand kits and team collaboration. Code delivered instantly.',

        // ===== ChatGPT / OpenAI (voucher — instant code) =====
        23 => 'One month of ChatGPT Go. Higher usage limits and faster responses than the free tier. Code delivered instantly.',
        24 => 'One month of ChatGPT Plus. Priority access to the latest models, advanced tools, and faster responses. Code delivered instantly.',
        25 => 'One month of ChatGPT Pro. The highest usage limits and full access to OpenAI’s most capable models. Code delivered instantly.',
        50 => 'One month of ChatGPT Team (per seat). Higher limits, a shared workspace, and collaboration features. Code delivered instantly.',

        // ===== Adobe (voucher — instant code) =====
        26 => 'One month of Adobe Creative Cloud Pro — access to the full suite including Photoshop, Illustrator, and Premiere Pro. Code delivered instantly.',
        51 => 'One month of the Adobe Photography plan: Lightroom and Photoshop with cloud storage. Code delivered instantly.',
        52 => 'One month of a single Adobe Creative Cloud app of your choice (e.g. Photoshop or Illustrator). Code delivered instantly.',

        // ===== Roblox (voucher — instant code) =====
        13 => 'Rp50.000 Roblox gift card. Redeem for Robux or a Premium subscription on your Roblox account. Code delivered instantly.',
        14 => 'Rp100.000 Roblox gift card for Robux or Premium. Redeem on your Roblox account. Code delivered instantly.',
        53 => 'Rp200.000 Roblox gift card for Robux or Premium. Redeem on your Roblox account. Code delivered instantly.',
        15 => 'Rp500.000 Roblox gift card — stock up on Robux or go Premium. Code delivered instantly.',

        // ===== Fortnite V-Bucks (voucher — instant code) =====
        16 => '1,000 Fortnite V-Bucks. Spend them on the Battle Pass, outfits, emotes, and more. Code delivered instantly.',
        17 => '2,500 Fortnite V-Bucks for the Battle Pass, skins, and cosmetics. Code delivered instantly.',
        18 => '5,000 Fortnite V-Bucks — plenty for the Battle Pass and a bundle of cosmetics. Code delivered instantly.',
        19 => '12,500 Fortnite V-Bucks. The best value for Battle Passes, skins, and emotes. Code delivered instantly.',

        // ===== PC Games (voucher — instant Steam key) =====
        27 => 'Steam key for Pragmata, Capcom’s sci-fi adventure. Redeem on Steam to add it permanently to your library. Code delivered instantly.',
        28 => 'Steam key for Resident Evil Requiem. Redeem on Steam to own the latest entry in the survival-horror series. Code delivered instantly.',
        65 => 'Steam key for Monster Hunter Wilds. Redeem on Steam to hunt across a living, open world. Code delivered instantly.',
        66 => 'Steam key for Street Fighter 6. Redeem on Steam for the latest in the legendary fighting series. Code delivered instantly.',

        // ===== Valorant Points (direct top-up — enter Riot ID) =====
        54 => '475 Valorant Points credited directly to your account. Use them for skins, the Battle Pass, and agents. Just enter your Riot ID — no code to redeem.',
        6  => '1,000 Valorant Points credited directly to your account for skins, the Battle Pass, and agents. Just enter your Riot ID — no code to redeem.',
        55 => '2,050 Valorant Points credited directly to your account for skins and the Battle Pass. Just enter your Riot ID — no code to redeem.',
        56 => '3,650 Valorant Points credited directly to your account — great value for premium skin bundles. Just enter your Riot ID — no code to redeem.',

        // ===== Mobile Legends Diamonds (direct top-up — enter Player & Zone ID) =====
        57 => '86 Mobile Legends Diamonds credited directly to your account for heroes, skins, and the Starlight pass. Enter your Player ID and Zone ID — no code to redeem.',
        7  => '500 Mobile Legends Diamonds credited directly to your account for heroes and skins. Enter your Player ID and Zone ID — no code to redeem.',
        58 => '172 Mobile Legends Diamonds credited directly to your account for heroes and skins. Enter your Player ID and Zone ID — no code to redeem.',
        59 => '257 Mobile Legends Diamonds credited directly to your account for skins and events. Enter your Player ID and Zone ID — no code to redeem.',
        60 => '706 Mobile Legends Diamonds credited directly to your account — ideal for skin bundles and the Starlight pass. Enter your Player ID and Zone ID — no code to redeem.',

        // ===== Genshin Impact (direct top-up — enter UID) =====
        8  => 'Blessing of the Welkin Moon for Genshin Impact: 300 Genesis Crystals now plus 90 Primogems daily for 30 days. Credited directly — just enter your UID.',
        61 => '60 Genesis Crystals credited directly to your Genshin Impact account, convertible to Primogems for wishes. Just enter your UID — no code to redeem.',
        62 => '330 Genesis Crystals (300 + 30 bonus) credited directly to your Genshin Impact account. Just enter your UID — no code to redeem.',
        63 => '980 Genesis Crystals (+110 bonus) credited directly to your Genshin Impact account for wishes and the Battle Pass. Just enter your UID — no code to redeem.',
        64 => '1,980 Genesis Crystals (+260 bonus) credited directly to your Genshin Impact account — great value for wishes. Just enter your UID — no code to redeem.',
    ];

    public function run(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        foreach (self::DESCRIPTIONS as $id => $description) {
            DB::table('products')->where('id', $id)->update(['description' => $description]);
        }
    }
}
