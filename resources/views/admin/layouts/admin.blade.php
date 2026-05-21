<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Ridly Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .admin-sidebar { background: hsl(var(--card)); border-right: 1px solid hsl(var(--border)); }
        .admin-nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.9rem; border-radius: 0.75rem; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: hsl(var(--muted-foreground)); }
        .admin-nav-link:hover, .admin-nav-link.active { background: hsl(var(--primary) / 0.1); color: hsl(var(--primary)); }
        .admin-section-title { font-size: 0.6rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; color: hsl(var(--muted-foreground) / 0.5); padding: 0 0.9rem; margin-top: 1rem; margin-bottom: 0.25rem; }
    </style>
</head>
<body class="bg-background text-foreground min-h-screen flex">
    <aside class="admin-sidebar w-64 min-h-screen p-5 flex flex-col gap-1 shrink-0 sticky top-0 h-screen overflow-y-auto">
        <div class="space-y-1 px-1 mb-2">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-black tracking-tighter uppercase">Ridly <span class="text-primary">Admin</span></a>
            <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Management Panel</p>
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
            <a href="{{ route('admin.products') }}" class="admin-nav-link {{ request()->routeIs('admin.products') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                Products
            </a>
            <a href="{{ route('admin.orders') }}" class="admin-nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                Orders
            </a>
        </nav>

        <div class="admin-section-title">Gamification</div>
        <nav class="flex flex-col gap-0.5">
            <a href="{{ route('admin.gacha') }}" class="admin-nav-link {{ request()->routeIs('admin.gacha') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11V7a5 5 0 0 1 10 0v4"/><rect width="18" height="12" x="3" y="11" rx="2"/><circle cx="12" cy="17" r="1"/></svg>
                Gacha Pool
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

    <main class="flex-1 p-8 min-h-screen">
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
