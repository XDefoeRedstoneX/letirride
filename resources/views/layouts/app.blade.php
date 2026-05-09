<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Ridly - Your premier digital voucher marketplace. Buy game credits, streaming subscriptions, and more with instant delivery.">
    <meta property="og:title" content="Ridly - Digital Voucher Marketplace">
    <meta property="og:description" content="Buy game credits, streaming subscriptions, and digital vouchers with instant delivery.">
    <meta property="og:type" content="website">
    <title>Ridly - Digital Marketplace & Gacha Arcade</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist-sans:400,500,600|geist-mono:400,500" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Apply saved or system theme -->
    <script>
      (function() {
        var stored = localStorage.theme;
        var isDark = stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);
        var html = document.documentElement;
        if (isDark) html.classList.add('dark');
        else html.classList.remove('dark');
      })();
    </script>
</head>
<body class="antialiased bg-background text-foreground min-h-screen flex flex-col transition-colors duration-200">
    <x-pixel-city-background />
    <x-navbar />
    <x-toast-notification />

    <main class="flex-1 relative z-10 pt-14 md:pt-16 pb-4">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8 py-4 md:py-6 animate-in fade-in slide-in-from-bottom-4 duration-300">
            {{ $slot }}
        </div>
    </main>

    <footer class="relative z-10 mt-auto border-t border-border/30 bg-background/50 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                <!-- Brand -->
                <div class="col-span-2 md:col-span-1 space-y-3">
                    <h3 class="text-lg font-black tracking-tighter uppercase">Ridly</h3>
                    <p class="text-xs text-muted-foreground leading-relaxed">Your premier digital voucher marketplace. Instant delivery, guaranteed.</p>
                </div>

                <!-- Quick Links -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Browse</h4>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('home') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Products</a>
                        @auth
                        <a href="{{ route('point-shop') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Point Shop</a>
                        <a href="{{ route('gacha') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Gacha</a>
                        @endauth
                    </div>
                </div>

                <!-- Support -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Support</h4>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('faq') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">FAQ</a>
                        <a href="{{ route('tickets') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Contact Us</a>
                        <a href="{{ route('about') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">About</a>
                    </div>
                </div>

                <!-- Legal -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Legal</h4>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('terms-of-service') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Terms & Conditions</a>
                        <a href="{{ route('privacy-policy') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Privacy Policy</a>
                    </div>
                </div>
            </div>

            <!-- Bottom bar -->
            <div class="pt-6 border-t border-border/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">
                    © 2026 Ridly. All rights reserved.
                </p>
                <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">
                    Built with 🎮 for gamers
                </p>
            </div>
        </div>
    </footer>

    <x-auth-modal />
</body>
</html>
