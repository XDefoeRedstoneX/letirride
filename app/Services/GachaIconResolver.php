<?php

namespace App\Services;

use App\Models\GachaIcon;
use App\Models\GachaPool;
use App\Support\GachaIconCatalog;

/**
 * Resolves the coin image a prize should display, and the icon key that should
 * be snapshotted onto a history row.
 *
 * Order of precedence:
 *   1. Per-prize `image_path`            — full-custom override (hero prizes).
 *   2. Per-prize `icon_key`              — explicit catalog coin.
 *   3. Reward-type / discount-subcategory auto-pick.
 *   4. The generic gold coin             — the reusable alt/fallback.
 *
 * Inactive catalog coins are never auto-picked (admins disable them on purpose),
 * but an explicit `icon_key` still resolves to its on-disk SVG so existing
 * prizes don't break when a coin is toggled off.
 */
class GachaIconResolver
{
    /**
     * The web path to the image this prize should render.
     */
    public static function resolve(GachaPool $prize): string
    {
        // 1. Full-custom override wins outright.
        if (! empty($prize->image_path)) {
            return $prize->image_path;
        }

        // 2 + 3. Resolve a catalog key, then turn it into a path.
        $key = self::iconKeyFor($prize);

        // An active catalog row's stored path is authoritative; otherwise derive
        // the conventional path from the key (covers catalog-less environments).
        $icon = GachaIcon::byKey($key);
        if ($icon) {
            return $icon->image_path;
        }

        return GachaIconCatalog::pathFor($key);
    }

    /**
     * The catalog key this prize maps to (without the custom-image override).
     * Used both for rendering and for snapshotting onto history rows.
     */
    public static function iconKeyFor(GachaPool $prize): string
    {
        // Explicit choice always wins.
        if (! empty($prize->icon_key)) {
            return $prize->icon_key;
        }

        return self::autoKeyFor($prize) ?? GachaIconCatalog::FALLBACK_KEY;
    }

    /**
     * Best-guess coin for a prize with no explicit icon, by reward type — and,
     * for discounts, by the targeted product subcategory.
     */
    private static function autoKeyFor(GachaPool $prize): ?string
    {
        switch ($prize->reward_type) {
            case 'free_spin':
                return 'free-spin';
            case 'points':
                return 'points-coin';
            case 'nothing':
                return 'nothing';
            case 'discount':
                return self::discountKeyFor($prize) ?? 'voucher';
        }

        return null;
    }

    /**
     * Map a discount prize to its brand coin via the discount's target
     * subcategory slug, when that subcategory has a matching coin.
     */
    private static function discountKeyFor(GachaPool $prize): ?string
    {
        $discount = $prize->relationLoaded('discountType')
            ? $prize->discountType
            : $prize->discountType()->with('targetSubcategory')->first();

        $slug = $discount?->targetSubcategory?->slug;
        if (! $slug) {
            return null;
        }

        return GachaIconCatalog::subcategorySlugToKey()[$slug] ?? null;
    }
}
