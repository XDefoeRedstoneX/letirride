<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Apply saved or system theme BEFORE any CSS paints, to avoid a flash -->
    <script>
      (function() {
        var stored = localStorage.theme;
        var isDark = stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);
        if (isDark) document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
      })();
    </script>
    <meta name="description" content="Ridly - Your premier digital voucher marketplace. Buy game credits, streaming subscriptions, and more with instant delivery.">
    <meta property="og:title" content="Ridly - Digital Voucher Marketplace">
    <meta property="og:description" content="Buy game credits, streaming subscriptions, and digital vouchers with instant delivery.">
    <meta property="og:type" content="website">
    <title>Ridly - Digital Marketplace & Gacha Arcade</title>
    <link rel="icon" type="image/png" href="/logo.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist-sans:400,500,600|geist-mono:400,500" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-background text-foreground min-h-screen flex flex-col transition-colors duration-200">
    <x-pixel-city-background />
    <x-navbar />
    <x-toast-notification />

    <main class="flex-1 relative z-10 pt-14 md:pt-16 pb-4 page-enter">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8 py-4 md:py-6 page-enter-content">
            {{ $slot }}
        </div>
    </main>

    <footer class="relative z-10 mt-auto" style="border-top: 3px solid var(--gold, #f59e0b); background: var(--footer-bg);">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p style="font-family: var(--px, 'Press Start 2P', monospace); font-size: 7px; letter-spacing: 0.1em; color: var(--footer-text);">
                    © 2026 RIDLY COMMERCE
                </p>
                <div class="flex items-center gap-6" style="font-family: var(--px, 'Press Start 2P', monospace); font-size: 7px; letter-spacing: 0.1em;">
                    <a href="{{ route('terms-of-service') }}" style="color: var(--footer-text); transition: color 0.15s;" onmouseover="this.style.color='var(--gold, #f59e0b)'" onmouseout="this.style.color='var(--footer-text)'">TERMS OF SERVICE</a>
                    <a href="{{ route('privacy-policy') }}" style="color: var(--footer-text); transition: color 0.15s;" onmouseover="this.style.color='var(--gold, #f59e0b)'" onmouseout="this.style.color='var(--footer-text)'">PRIVACY POLICY</a>
                </div>
            </div>
        </div>
    </footer>

    <x-auth-modal />
    <x-confirm-modal />

<style>
    .page-enter {
        animation: pageFadeIn 0.6s ease-out both;
    }
    .page-enter-content > * {
        animation: pageFadeIn 0.6s ease-out both;
    }
    .page-enter-content > *:nth-child(1) { animation-delay: 0s; }
    .page-enter-content > *:nth-child(2) { animation-delay: 0.08s; }
    .page-enter-content > *:nth-child(3) { animation-delay: 0.16s; }
    .page-enter-content > *:nth-child(4) { animation-delay: 0.24s; }
    .page-enter-content > *:nth-child(5) { animation-delay: 0.32s; }
    .page-enter-content > *:nth-child(6) { animation-delay: 0.40s; }
    .page-enter-content > *:nth-child(7) { animation-delay: 0.48s; }
    .page-enter-content > *:nth-child(8) { animation-delay: 0.56s; }
    .page-enter-content > *:nth-child(9) { animation-delay: 0.64s; }
    .page-enter-content > *:nth-child(10) { animation-delay: 0.72s; }
    /* NOTE: Using opacity-only animation to avoid breaking position:fixed
       on child modals. CSS transform creates a new containing block that
       makes fixed-position elements behave like absolute. */
    @keyframes pageFadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
</style>
</body>
</html>
