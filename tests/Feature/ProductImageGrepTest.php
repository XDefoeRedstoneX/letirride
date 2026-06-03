<?php

/**
 * Structural regression: the old 'soundcloud.png' fallback was repeatedly
 * copy-pasted across controllers and views even though the file never
 * existed under public/products/. Every site is now expected to route
 * product images through Product::imageUrl() — this test fails the build
 * if anyone reintroduces the literal in app/ or resources/views/.
 */
it('does not reference the bogus soundcloud.png fallback anywhere in app code', function () {
    $base = dirname(__DIR__, 2);
    $roots = [$base.'/app', $base.'/resources/views'];

    $offenders = [];
    foreach ($roots as $root) {
        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iter as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['php', 'blade.php'], true) && ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            if (str_contains($contents, 'soundcloud.png') || str_contains($contents, 'soundcloud.svg')) {
                $offenders[] = str_replace($base.'/', '', $file->getPathname());
            }
        }
    }

    expect($offenders)->toBe([], 'These files still reference the broken soundcloud fallback — route them through Product::imageUrl(): '.implode(', ', $offenders));
});
