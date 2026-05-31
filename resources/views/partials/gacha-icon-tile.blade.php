{{--
    Reusable gacha coin tile: the coin image layered under a CSS rarity wedge +
    frame. Single source of truth for how a prize renders, used by the history
    table, admin tables and the admin icon picker preview. (The spinning carousel
    renders the same structure inline via Alpine — keep the two in sync.)

    Props:
      $iconSrc  string  Web path to the coin SVG/PNG.
      $rarity   string  common|uncommon|rare|epic|legendary|grand_prize.
      $label    string  Accessible label / alt text.
      $size     int     Pixel box size (default 56).
--}}
@php
    $size = $size ?? 56;
    $rarity = $rarity ?? 'common';
    $label = $label ?? 'Prize';
    $rarityColor = [
        'grand_prize' => '#f43f5e',
        'legendary' => '#f59e0b',
        'epic' => '#a855f7',
        'rare' => '#3b82f6',
        'uncommon' => '#22c55e',
        'common' => '#9ca3af',
    ][$rarity] ?? '#9ca3af';
    $fallback = \App\Support\GachaIconCatalog::fallbackPath();
@endphp
<span class="gacha-icon-tile gacha-rarity-{{ $rarity }}"
      style="--rarity-color: {{ $rarityColor }}; width: {{ $size }}px; height: {{ $size }}px;">
    <img src="{{ $iconSrc ?: $fallback }}" alt="{{ $label }}" class="gacha-icon-tile-img pixel-render"
         loading="lazy" onerror="this.onerror=null;this.src='{{ $fallback }}';" />
    <span class="gacha-card-rarity-wedge" aria-hidden="true"></span>
</span>
