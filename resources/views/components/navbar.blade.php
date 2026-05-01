<nav x-data="{ 
    mobileMenuOpen: false, 
    userMenuOpen: false,
    isAuthenticated: {{ Auth::check() ? 'true' : 'false' }},
    theme: 'light',
    init() {
        // sync theme from html class or localStorage
        const html = document.documentElement;
        const stored = localStorage.theme;
        const isDark = stored === 'dark' || (!stored && html.classList.contains('dark'));
        this.theme = isDark ? 'dark' : 'light';
    },
    toggleTheme() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        if (this.theme === 'dark') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }
}" class="fixed top-0 left-0 right-0 z-50 bg-background/60 backdrop-blur-lg border-b border-border/40">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8">
        <div class="flex items-center justify-between h-14 md:h-16">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2 cursor-pointer">
                <div class="w-8 h-8 md:w-9 md:h-9 bg-primary rounded-lg flex items-center justify-center pixel-render">
                    <span class="text-primary-foreground font-bold text-xs md:text-sm">RI</span>
                </div>
                <span class="font-bold text-lg md:text-xl tracking-tight">
                    <span class="text-foreground">Ridly</span>
                </span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 px-3 py-2 text-sm font-bold rounded-md {{ request()->routeIs('home') ? 'bg-primary text-primary-foreground' : 'text-foreground hover:bg-accent' }}">
                    Beranda
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </a>
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 px-3 py-2 text-sm font-bold rounded-md text-foreground hover:bg-accent">
                    Produk
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </a>
                <a href="{{ route('point-shop') }}" class="flex items-center gap-1.5 px-3 py-2 text-sm font-bold rounded-md {{ request()->routeIs('point-shop') ? 'bg-primary text-primary-foreground' : 'text-foreground hover:bg-accent' }}">
                    Toko Poin
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                </a>
                <a href="{{ route('gacha') }}" class="flex items-center gap-1.5 px-3 py-2 text-sm font-bold rounded-md {{ request()->routeIs('gacha') ? 'bg-primary text-primary-foreground' : 'text-foreground hover:bg-accent' }}">
                    Gacha Arcade
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01"/><path d="M18 12h.01"/></svg>
                </a>
                <a href="#" class="flex items-center gap-1.5 px-3 py-2 text-sm font-bold rounded-md text-foreground hover:bg-accent">
                    About Us
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </a>
                <a href="#" class="flex items-center gap-1.5 px-3 py-2 text-sm font-bold rounded-md text-foreground hover:bg-accent">
                    Customer Service
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M12 7v5"/><path d="M12 16h.01"/></svg>
                </a>
            </div>

            <!-- Right Side Actions -->
            <div class="flex items-center gap-1 md:gap-2">
                <!-- Theme Toggle -->
                <button @click="toggleTheme()" class="p-2 rounded-md hover:bg-accent transition-colors">
                    <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-400 pixel-render"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-700 pixel-render"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </button>

                @auth
                    <!-- Points & Balance -->
                    <div class="hidden sm:flex items-center gap-2 text-sm">
                        <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-yellow-500/10 text-yellow-600 dark:text-yellow-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                            <span class="font-medium">{{ number_format(Auth::user()->points) }}</span>
                        </div>
                        <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-green-500/10 text-green-600 dark:text-green-400">
                            <span class="font-medium">Rp {{ number_format(Auth::user()->balance) }}</span>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 p-1 rounded-md hover:bg-accent transition-colors">
                            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-foreground font-bold text-xs">
                                {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                            </div>
                            <span class="hidden lg:inline text-sm font-medium text-foreground">{{ Auth::user()->username }}</span>
                        </button>
                        
                        <div x-show="userMenuOpen" x-transition class="absolute right-0 mt-2 w-56 rounded-md border border-border bg-popover text-popover-foreground shadow-lg py-1 z-50">
                            <div class="px-3 py-2 border-b border-border/50">
                                <p class="text-sm font-medium">{{ Auth::user()->username }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile') }}" class="block px-3 py-2 text-sm hover:bg-accent">My Profile</a>
                            <a href="{{ route('inventory') }}" class="block px-3 py-2 text-sm hover:bg-accent">My Inventory</a>
                            <a href="{{ route('transactions') }}" class="block px-3 py-2 text-sm hover:bg-accent">Transactions</a>
                            <a href="{{ route('settings') }}" class="block px-3 py-2 text-sm hover:bg-accent">Settings</a>
                            <div class="border-t border-border/50 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-sm text-destructive hover:bg-destructive/10">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-1">
                        <button @click="$dispatch('open-auth-modal', { tab: 'login' })" class="px-3 py-1.5 text-sm font-medium hover:bg-accent rounded-md">
                            Login
                        </button>
                        <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" class="px-3 py-1.5 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:opacity-90">
                            Sign Up
                        </button>
                    </div>
                @endauth

                <!-- Mobile Menu Toggle -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-md hover:bg-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" class="md:hidden border-t border-border/50 bg-background/95 backdrop-blur-md">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('home') ? 'bg-primary text-primary-foreground' : 'hover:bg-accent' }}">Home</a>
            <a href="{{ route('point-shop') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('point-shop') ? 'bg-primary text-primary-foreground' : 'hover:bg-accent' }}">Point Shop</a>
            <a href="{{ route('gacha') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('gacha') ? 'bg-primary text-primary-foreground' : 'hover:bg-accent' }}">Gacha</a>
        </div>
    </div>
</nav>
