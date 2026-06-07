<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Ridly Admin</title>
    <link rel="icon" type="image/png" href="/logo.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Apply saved theme on load to prevent flickering -->
    <script>
      (function() {
        var stored = localStorage.theme;
        var isDark = stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);
        var html = document.documentElement;
        if (isDark) html.classList.add('dark');
        else html.classList.remove('dark');
      })();
    </script>
    <style>
        .admin-sidebar { border-right: 1px solid var(--border); background-color: var(--card); }
        .admin-nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.9rem; border-radius: 0.75rem; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted-foreground); transition: all 0.1s; }
        .admin-nav-link:hover, .admin-nav-link.active { background: rgba(245, 158, 11, 0.15); color: var(--primary); }
        .admin-section-title { font-size: 0.6rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; color: var(--muted-foreground); opacity: 0.6; padding: 0 0.9rem; margin-top: 1rem; margin-bottom: 0.25rem; }
    </style>
</head>
<body class="bg-background text-foreground min-h-screen flex flex-col md:flex-row" x-data="{ mobileMenuOpen: false }">

    <!-- Mobile Top Bar -->
    <div class="md:hidden flex items-center justify-between p-4 border-b border-border bg-card sticky top-0 z-40">
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 bg-foreground/5 rounded-lg text-foreground hover:bg-foreground/10 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        </button>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-black tracking-tighter uppercase">Ridly <span class="text-primary">Admin</span></a>
        </div>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="mobileMenuOpen" class="fixed inset-0 z-40 bg-black/60 md:hidden" @click="mobileMenuOpen = false" x-transition.opacity style="display: none;"></div>
    <aside class="admin-sidebar w-64 p-5 flex flex-col gap-1 shrink-0 fixed md:sticky top-0 left-0 h-screen overflow-y-auto z-50 bg-card shadow-[4px_0_24px_rgba(0,0,0,0.5)] transition-transform duration-300 md:translate-x-0"
       :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="flex items-center justify-between px-1 mb-2">
            <div class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-black tracking-tighter uppercase">Ridly <span class="text-primary">Admin</span></a>
                <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Management Panel</p>
            </div>
            <button @click="mobileMenuOpen = false" class="md:hidden p-2 text-muted-foreground hover:text-foreground hover:bg-foreground/5 rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <div class="admin-section-title">Overview</div>
        <nav class="flex flex-col gap-0.5">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                Dashboard
            </a>
        </nav>

        <div class="admin-section-title">Store</div>
        <nav class="flex flex-col gap-0.5">
            <a href="{{ route('admin.news') }}" class="admin-nav-link {{ request()->routeIs('admin.news') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                News
            </a>
            <a href="{{ route('admin.products') }}" class="admin-nav-link {{ request()->routeIs('admin.products') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                Products
            </a>
            <a href="{{ route('admin.orders') }}" class="admin-nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                Orders
            </a>
            <a href="{{ route('admin.discounts') }}" class="admin-nav-link {{ request()->routeIs('admin.discounts') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                Discount Types
            </a>
        </nav>

        <div class="admin-section-title">Gamification</div>
        <nav class="flex flex-col gap-0.5">
            <a href="{{ route('admin.gacha') }}" class="admin-nav-link {{ request()->routeIs('admin.gacha') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11V7a5 5 0 0 1 10 0v4"/><rect width="18" height="12" x="3" y="11" rx="2"/><circle cx="12" cy="17" r="1"/></svg>
                Gacha Pool
            </a>
            <a href="{{ route('admin.gacha-boosters') }}" class="admin-nav-link {{ request()->routeIs('admin.gacha-boosters') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/></svg>
                Gacha Boosters
            </a>
            <a href="{{ route('admin.gacha-rarities') }}" class="admin-nav-link {{ request()->routeIs('admin.gacha-rarities') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                Rarity Chances
            </a>
            <a href="{{ route('admin.gacha-icons') }}" class="admin-nav-link {{ request()->routeIs('admin.gacha-icons') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v12"/><path d="M9.5 9.5h3.5a1.5 1.5 0 0 1 0 3h-2a1.5 1.5 0 0 0 0 3h3.5"/></svg>
                Gacha Icons
            </a>
            <a href="{{ route('admin.point-shop') }}" class="admin-nav-link {{ request()->routeIs('admin.point-shop') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h1"/><path d="M15 12h1"/><path d="M12 8v1"/><path d="M12 15v1"/></svg>
                Point Shop
            </a>
        </nav>

        <div class="admin-section-title">People</div>
        <nav class="flex flex-col gap-0.5">
            <a href="{{ route('admin.users') }}" class="admin-nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Users
            </a>
            <a href="{{ route('admin.referrals') }}" class="admin-nav-link {{ request()->routeIs('admin.referrals') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11h-6"/><path d="M19 8v6"/></svg>
                Referrals
            </a>
            <a href="{{ route('admin.referral-tiers') }}" class="admin-nav-link {{ request()->routeIs('admin.referral-tiers') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v6"/><path d="m6 7 6-5 6 5"/><rect x="4" y="8" width="16" height="14" rx="2"/><path d="M9 14h6"/></svg>
                Tier Rewards
            </a>
        </nav>

        <div class="admin-section-title">Support</div>
        <nav class="flex flex-col gap-0.5">
            <a href="{{ route('admin.tickets') }}" class="admin-nav-link {{ request()->routeIs('admin.tickets') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                Tickets
            </a>
            <a href="{{ route('admin.faqs') }}" class="admin-nav-link {{ request()->routeIs('admin.faqs') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                FAQs
            </a>
            @if (config('sync.is_local'))
            <a href="{{ route('admin.sync') }}" class="admin-nav-link {{ request()->routeIs('admin.sync') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
                Sync
            </a>
            @endif
        </nav>

        <div class="mt-auto pt-4 space-y-1 border-t border-border/30">
            <button id="admin-theme-toggle" onclick="toggleAdminTheme()" class="admin-nav-link w-full text-left">
                <span id="theme-icon"></span>
                <span id="theme-label" class="text-[10px] font-black uppercase tracking-widest"></span>
            </button>
            <script>
                function toggleAdminTheme() {
                    document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                    updateAdminThemeBtn();
                }
                function updateAdminThemeBtn() {
                    var isDark = document.documentElement.classList.contains('dark');
                    document.getElementById('theme-icon').innerHTML = isDark
                        ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>'
                        : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>';
                    document.getElementById('theme-label').textContent = isDark ? 'Light Mode' : 'Dark Mode';
                }
                updateAdminThemeBtn();
            </script>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="admin-nav-link w-full text-left text-red-400 hover:!text-red-500 hover:!bg-red-500/10">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 p-4 sm:p-6 md:p-8 min-h-screen w-full max-w-full overflow-x-hidden">
        @if(session('success'))
            <div class="mb-6 px-5 py-3 bg-green-500/10 border border-green-500/20 rounded-xl text-green-500 text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 px-5 py-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-500 text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
