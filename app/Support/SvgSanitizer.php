<?php

namespace App\Support;

/**
 * Best-effort sanitiser for SVG uploads. The admin gacha-icon panel accepts
 * SVGs that are later served from the public disk, so a malicious upload
 * could carry script tags / event handlers that execute in the app origin
 * when an admin opens the file directly. We strip the obvious vectors.
 *
 * Not a full XML parser — accepts that a determined attacker with admin
 * upload access could craft a regex-aware payload. The risk surface is
 * admin-only, so the trade-off is intentional.
 */
class SvgSanitizer
{
    public static function sanitize(string $xml): string
    {
        // <script>, <iframe>, <foreignObject>, <use> (xlink remote), <embed>,
        // <object>, <handler>, <set> — drop the whole element including content.
        $blockedElements = ['script', 'iframe', 'foreignObject', 'embed', 'object', 'handler', 'set'];
        foreach ($blockedElements as $el) {
            $xml = preg_replace('#<\s*'.$el.'\b[^>]*>.*?<\s*/\s*'.$el.'\s*>#is', '', $xml) ?? $xml;
            // Also catch the self-closing / orphan form.
            $xml = preg_replace('#<\s*'.$el.'\b[^>]*/?>#is', '', $xml) ?? $xml;
        }

        // Strip any event-handler attribute (onload, onclick, onmouseover…).
        $xml = preg_replace('#\s+on[a-z]+\s*=\s*"[^"]*"#is', '', $xml) ?? $xml;
        $xml = preg_replace("#\s+on[a-z]+\s*=\s*'[^']*'#is", '', $xml) ?? $xml;

        // Strip javascript: / data: hrefs (incl. xlink:href).
        $xml = preg_replace('#\s+(?:xlink:)?href\s*=\s*"\s*(?:javascript|data)\s*:[^"]*"#is', '', $xml) ?? $xml;
        $xml = preg_replace("#\s+(?:xlink:)?href\s*=\s*'\s*(?:javascript|data)\s*:[^']*'#is", '', $xml) ?? $xml;

        return $xml;
    }
}
