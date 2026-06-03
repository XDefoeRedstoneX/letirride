<?php

use App\Support\SvgSanitizer;

it('strips script tags', function () {
    $svg = '<svg><script>alert(1)</script><circle r="10"/></svg>';
    $out = SvgSanitizer::sanitize($svg);
    expect($out)->not->toContain('script')->toContain('circle');
});

it('strips event handler attributes', function () {
    $svg = '<svg onload="alert(1)"><circle onclick="x()" r="10"/></svg>';
    $out = SvgSanitizer::sanitize($svg);
    expect($out)->not->toContain('onload')->not->toContain('onclick');
});

it('strips javascript: hrefs (incl. xlink)', function () {
    $svg = '<svg><a href="javascript:alert(1)"><text>x</text></a><image xlink:href="javascript:alert(2)"/></svg>';
    $out = SvgSanitizer::sanitize($svg);
    expect($out)->not->toContain('javascript:');
});

it('strips iframe and foreignObject', function () {
    $svg = '<svg><foreignObject><iframe src="x"></iframe></foreignObject></svg>';
    $out = SvgSanitizer::sanitize($svg);
    expect($out)->not->toContain('iframe')->not->toContain('foreignObject');
});

it('leaves benign SVG untouched in shape', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><circle cx="32" cy="32" r="20" fill="#f59e0b"/></svg>';
    $out = SvgSanitizer::sanitize($svg);
    expect($out)->toContain('circle')->toContain('viewBox')->toContain('#f59e0b');
});
