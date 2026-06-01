<?php

namespace App\Support;

/**
 * Canonical catalog of default gacha coin icons.
 *
 * Every entry is a "coin form" — a minted disc carrying a brand monogram or
 * symbol — so the carousel reads as a unified set of collectible coins rather
 * than a grab-bag of logos. There is one coin per product subcategory plus the
 * special reward coins (points, free spin, cash, voucher, etc.). The generic
 * `coin` entry is the reusable fallback used as the `alt` everywhere.
 *
 * This single source of truth is consumed by:
 *   - database/icons/generate-coins.php  → writes the SVG files
 *   - DatabaseSeeder::seedGachaIcons()   → inserts the catalog rows
 *   - GachaIconResolver                  → auto-fallback by subcategory/reward
 */
class GachaIconCatalog
{
    /** Public web path (under /public) where the generated coin SVGs live. */
    public const ASSET_DIR = '/gacha-icons';

    /** The reusable generic coin — used as the alt / final fallback. */
    public const FALLBACK_KEY = 'coin';

    /**
     * Admin-facing grouping labels for the icon picker.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'special' => 'Special & Currency',
        'games' => 'Games',
        'wallet' => 'Wallet Top-Ups',
        'game-currency' => 'In-Game Currency',
        'gift-cards' => 'Gift Cards',
        'subscription' => 'Subscriptions',
    ];

    /**
     * Maps a product subcategory slug (see DatabaseSeeder::seedSubcategories)
     * to its coin key, so a discount prize with no explicit icon can still
     * auto-resolve to the matching brand coin.
     *
     * @return array<string, string>
     */
    public static function subcategorySlugToKey(): array
    {
        return [
            'capcom' => 'capcom',
            'steam' => 'steam',
            'playstation' => 'playstation',
            'nintendo' => 'nintendo',
            'riot-games-valorant' => 'valorant',
            'mobile-legends' => 'mobile-legends',
            'fortnite' => 'fortnite',
            'roblox' => 'roblox',
            'genshin-impact' => 'genshin',
            'netflix' => 'netflix',
            'spotify' => 'spotify',
            'discord' => 'discord',
            'xbox' => 'xbox',
            'youtube' => 'youtube',
            'canva' => 'canva',
            'openai' => 'openai',
            'adobe' => 'adobe',
        ];
    }

    /**
     * The full coin catalog.
     *
     * Each row: key, label, category, color (brand tint for ring + glyph),
     * glyph (the minted mark), sort_order. `kind` tweaks the coin face:
     * 'coin' (default monogram), 'cash' (stacked), 'token' (free spin),
     * 'void' (no prize).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $rows = [
            // ===== Special & currency =====
            ['key' => 'coin',         'label' => 'Gold Coin',      'category' => 'special', 'color' => '#f59e0b', 'glyph' => '★',   'kind' => 'coin'],
            ['key' => 'points-coin',  'label' => 'Points Coin',    'category' => 'special', 'color' => '#f59e0b', 'glyph' => 'P',   'kind' => 'coin'],
            ['key' => 'points-stack', 'label' => 'Points Stack',   'category' => 'special', 'color' => '#f59e0b', 'glyph' => 'P',   'kind' => 'cash'],
            ['key' => 'cash',         'label' => 'Cash Bonus',     'category' => 'special', 'color' => '#22c55e', 'glyph' => 'Rp',  'kind' => 'cash'],
            ['key' => 'voucher',      'label' => 'Voucher',        'category' => 'special', 'color' => '#eab308', 'glyph' => '%',   'kind' => 'coin'],
            ['key' => 'free-spin',    'label' => 'Free Spin',      'category' => 'special', 'color' => '#06b6d4', 'glyph' => '↻',   'kind' => 'token'],
            ['key' => 'mystery',      'label' => 'Mystery',        'category' => 'special', 'color' => '#a855f7', 'glyph' => '?',   'kind' => 'coin'],
            ['key' => 'whale',        'label' => 'Whale Status',   'category' => 'special', 'color' => '#6366f1', 'glyph' => 'W',   'kind' => 'coin'],
            ['key' => 'nothing',      'label' => 'No Prize',       'category' => 'special', 'color' => '#9ca3af', 'glyph' => '✕',   'kind' => 'void'],

            // ===== Games =====
            ['key' => 'capcom',       'label' => 'Capcom',         'category' => 'games', 'color' => '#0a5bd3', 'glyph' => 'C',  'kind' => 'coin'],

            // ===== Wallet top-ups =====
            ['key' => 'steam',        'label' => 'Steam',          'category' => 'wallet', 'color' => '#3a6ea5', 'glyph' => 'S',  'kind' => 'coin'],
            ['key' => 'playstation',  'label' => 'PlayStation',    'category' => 'wallet', 'color' => '#0070d1', 'glyph' => 'PS', 'kind' => 'coin'],
            ['key' => 'nintendo',     'label' => 'Nintendo',       'category' => 'wallet', 'color' => '#e60012', 'glyph' => 'N',  'kind' => 'coin'],

            // ===== In-game currency =====
            ['key' => 'valorant',       'label' => 'Valorant',       'category' => 'game-currency', 'color' => '#fa4454', 'glyph' => 'V',  'kind' => 'coin'],
            ['key' => 'mobile-legends', 'label' => 'Mobile Legends', 'category' => 'game-currency', 'color' => '#2b6cb0', 'glyph' => 'ML', 'kind' => 'coin'],
            ['key' => 'fortnite',       'label' => 'Fortnite',       'category' => 'game-currency', 'color' => '#9d4dff', 'glyph' => 'V$', 'kind' => 'coin'],

            // ===== Gift cards =====
            ['key' => 'roblox',       'label' => 'Roblox',         'category' => 'gift-cards', 'color' => '#e2231a', 'glyph' => 'R',  'kind' => 'coin'],

            // ===== Subscriptions =====
            ['key' => 'genshin',      'label' => 'Genshin Impact', 'category' => 'subscription', 'color' => '#4a90d9', 'glyph' => 'G',  'kind' => 'coin'],
            ['key' => 'netflix',      'label' => 'Netflix',        'category' => 'subscription', 'color' => '#e50914', 'glyph' => 'N',  'kind' => 'coin'],
            ['key' => 'spotify',      'label' => 'Spotify',        'category' => 'subscription', 'color' => '#1db954', 'glyph' => 'S',  'kind' => 'coin'],
            ['key' => 'discord',      'label' => 'Discord',        'category' => 'subscription', 'color' => '#5865f2', 'glyph' => 'D',  'kind' => 'coin'],
            ['key' => 'xbox',         'label' => 'Xbox',           'category' => 'subscription', 'color' => '#107c10', 'glyph' => 'X',  'kind' => 'coin'],
            ['key' => 'youtube',      'label' => 'YouTube',        'category' => 'subscription', 'color' => '#ff0000', 'glyph' => 'YT', 'kind' => 'coin'],
            ['key' => 'canva',        'label' => 'Canva',          'category' => 'subscription', 'color' => '#00c4cc', 'glyph' => 'C',  'kind' => 'coin'],
            ['key' => 'openai',       'label' => 'ChatGPT',        'category' => 'subscription', 'color' => '#10a37f', 'glyph' => 'AI', 'kind' => 'coin'],
            ['key' => 'adobe',        'label' => 'Adobe',          'category' => 'subscription', 'color' => '#fa0f00', 'glyph' => 'A',  'kind' => 'coin'],
        ];

        $sort = 0;
        foreach ($rows as &$row) {
            $row['sort_order'] = $sort++;
            $row['image_path'] = self::ASSET_DIR.'/'.$row['key'].'.svg';
        }

        return $rows;
    }

    /**
     * The public web path to a coin's SVG, falling back to the generic coin.
     */
    public static function pathFor(?string $key): string
    {
        $key = $key ?: self::FALLBACK_KEY;

        return self::ASSET_DIR.'/'.$key.'.svg';
    }

    /** Path to the reusable generic coin used as the universal alt/fallback. */
    public static function fallbackPath(): string
    {
        return self::pathFor(self::FALLBACK_KEY);
    }
}
