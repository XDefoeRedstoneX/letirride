<?php

/**
 * Generates the default gacha "coin form" icons as flat SVG discs — one per
 * entry in App\Support\GachaIconCatalog. Run from the project root:
 *
 *     php database/icons/generate-coins.php
 *
 * Output: public/gacha-icons/<key>.svg. The catalog is the single source of
 * truth; re-run this whenever the catalog changes. SVGs are committed so the
 * app has no runtime image-generation dependency.
 */

require __DIR__.'/../../vendor/autoload.php';

use App\Support\GachaIconCatalog;

/** Shift a #rrggbb hex toward black (<1) or white (>1, via blend). */
function shade(string $hex, float $factor): string
{
    $hex = ltrim($hex, '#');
    [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];

    if ($factor <= 1) {
        $r = (int) round($r * $factor);
        $g = (int) round($g * $factor);
        $b = (int) round($b * $factor);
    } else {
        $t = $factor - 1;
        $r = (int) round($r + (255 - $r) * $t);
        $g = (int) round($g + (255 - $g) * $t);
        $b = (int) round($b + (255 - $b) * $t);
    }

    return sprintf('#%02x%02x%02x', min(255, $r), min(255, $g), min(255, $b));
}

function coinSvg(array $def): string
{
    $color = $def['color'];
    $rim = shade($color, 0.72);
    $faceTop = shade($color, 1.35);
    $faceBottom = $color;
    $glyph = htmlspecialchars($def['glyph'], ENT_XML1);
    $kind = $def['kind'];

    $len = mb_strlen($def['glyph']);
    $fontSize = $len >= 2 ? 21 : 31;
    // Symbol glyphs read better a touch larger.
    if (in_array($def['glyph'], ['★', '?', '%', '↻', '✕', 'Rp'], true)) {
        $fontSize = $def['glyph'] === 'Rp' ? 22 : 33;
    }

    $id = 'g'.substr(md5($def['key']), 0, 8);
    $voidOpacity = $kind === 'void' ? '0.55' : '1';

    // Stacked coins behind the face for "cash" piles.
    $stack = '';
    if ($kind === 'cash') {
        $stack = <<<SVG
  <circle cx="24" cy="40" r="20" fill="{$rim}"/>
  <circle cx="24" cy="40" r="16" fill="{$faceBottom}"/>
  <circle cx="40" cy="38" r="20" fill="{$rim}"/>
  <circle cx="40" cy="38" r="16" fill="url(#{$id})"/>
SVG;
    }

    // Free-spin token gets an arrow-ring motion hint via a dashed accent ring.
    $tokenRing = $kind === 'token'
        ? '  <circle cx="32" cy="32" r="20" fill="none" stroke="#ffffff" stroke-width="2" stroke-dasharray="4 5" opacity="0.6"/>'."\n"
        : '';

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64" role="img" aria-label="{$def['label']} coin">
  <defs>
    <radialGradient id="{$id}" cx="38%" cy="32%" r="72%">
      <stop offset="0%" stop-color="{$faceTop}"/>
      <stop offset="100%" stop-color="{$faceBottom}"/>
    </radialGradient>
  </defs>
  <g opacity="{$voidOpacity}">
{$stack}  <circle cx="32" cy="32" r="29" fill="{$rim}"/>
  <circle cx="32" cy="32" r="29" fill="none" stroke="{$rim}" stroke-width="2" stroke-dasharray="2.6 2.6"/>
  <circle cx="32" cy="32" r="24" fill="url(#{$id})"/>
  <circle cx="32" cy="32" r="24" fill="none" stroke="#ffffff" stroke-width="1.5" opacity="0.35"/>
{$tokenRing}  <ellipse cx="24" cy="22" rx="11" ry="6" fill="#ffffff" opacity="0.22"/>
  <text x="32" y="33" text-anchor="middle" dominant-baseline="central"
        font-family="Arial, Helvetica, sans-serif" font-weight="800"
        font-size="{$fontSize}" fill="#ffffff">{$glyph}</text>
  </g>
</svg>

SVG;
}

$outDir = __DIR__.'/../../public/gacha-icons';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$count = 0;
foreach (GachaIconCatalog::all() as $def) {
    file_put_contents($outDir.'/'.$def['key'].'.svg', coinSvg($def));
    $count++;
}

echo "Generated {$count} coin icons into public/gacha-icons/\n";
