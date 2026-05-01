<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

    <main class="flex-1 relative z-10 pt-14 md:pt-16 pb-4">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8 py-4 md:py-6 animate-in fade-in slide-in-from-bottom-4 duration-300">
            {{ $slot }}
        </div>
    </main>

    <footer class="relative z-10 mt-auto border-t border-border/30 bg-background/50 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-muted-foreground">
                    © 2026 Ridly.
                </p>
                <div></div>
                <div class="flex items-center gap-4 text-xs text-muted-foreground">
                    <span class="hover:text-foreground transition-colors cursor-pointer">Terms & Conditions</span>
                    <span class="hover:text-foreground transition-colors cursor-pointer">Privacy Policy</span>
                </div>
            </div>
        </div>
    </footer>

    <x-auth-modal />
</body>
</html>
