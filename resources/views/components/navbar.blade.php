<nav x-data="{
    mobileMenuOpen: false,
    userMenuOpen: false,
    isAuthenticated: {{ Auth::check() ? 'true' : 'false' }},
    theme: 'light',
    cartCount: 0,
    init() {
        // sync theme from html class or localStorage
        const html = document.documentElement;
        const stored = localStorage.theme;
        const isDark = stored === 'dark' || (!stored && html.classList.contains('dark'));
        this.theme = isDark ? 'dark' : 'light';

        // Listen for cart updates
        window.addEventListener('cart-updated', (e) => {
            this.cartCount = e.detail.count;
        });
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
}" class="fixed top-0 left-0 right-0 z-50 bg-background/40 backdrop-blur-xl border-b border-white/10 shadow-sm transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 md:h-20">
            <!-- Logo -->
            <a href="/" class="flex items-center">
                <span class="font-black text-2xl tracking-tighter leading-none text-foreground">Ridly.</span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-1.5 p-1.5 bg-foreground/5 rounded-2xl backdrop-blur-md">
                <a href="{{ route('home') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-black rounded-xl transition-all {{ request()->routeIs('home') ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20' : 'text-foreground/70 hover:text-foreground hover:bg-white/10' }}">
                    <span>HOME</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </a>
                <a href="{{ route('point-shop') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-black rounded-xl transition-all {{ request()->routeIs('point-shop') ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20' : 'text-foreground/70 hover:text-foreground hover:bg-white/10' }}">
                    <span>POINT SHOP</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                </a>
                <a href="{{ route('gacha') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-black rounded-xl transition-all {{ request()->routeIs('gacha') ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20' : 'text-foreground/70 hover:text-foreground hover:bg-white/10' }}">
                    <span>CAROUSEL</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M7 11V7a5 5 0 0 1 10 0v4"/><rect width="18" height="12" x="3" y="11" rx="2"/><circle cx="12" cy="17" r="1"/></svg>
                </a>
                <a href="{{ route('favorites') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-black rounded-xl transition-all {{ request()->routeIs('favorites') ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20' : 'text-foreground/70 hover:text-foreground hover:bg-white/10' }}">
                    <span>FAVORITES</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </a>
                <a href="{{ route('about') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-black rounded-xl transition-all {{ request()->routeIs('about') ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20' : 'text-foreground/70 hover:text-foreground hover:bg-white/10' }}">
                    <span>ABOUT US</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </a>
                <a href="{{ route('faq') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-black rounded-xl transition-all {{ request()->routeIs('faq') ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20' : 'text-foreground/70 hover:text-foreground hover:bg-white/10' }}">
                    <span>FAQ</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </a>
                <a href="{{ route('tickets') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-black rounded-xl transition-all {{ request()->routeIs('tickets') ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20' : 'text-foreground/70 hover:text-foreground hover:bg-white/10' }}">
                    <span>SUPPORT</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M12 7v5"/><path d="M12 16h.01"/></svg>
                </a>
            </div>

            <!-- Right Side Actions -->
            <div class="flex items-center gap-3">
                <!-- Theme Toggle -->
                <button @click="toggleTheme()" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 transition-all">
                    <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400 pixel-render"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600 pixel-render"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </button>

                @auth
                    <!-- Cart Trolley with Badge -->
                    <a href="{{ route('viewCart') }}" class="relative w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 transition-all group">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/></svg>
                        <span x-show="cartCount > 0" x-text="cartCount" class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-600 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-background"></span>
                    </a>

                    <!-- User Dropdown (Opaque) -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 p-1.5 bg-foreground/5 rounded-2xl hover:bg-foreground/10 transition-all">
                            <div class="w-8 h-8 rounded-xl bg-primary flex items-center justify-center text-primary-foreground font-black text-xs">
                                {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                            </div>
                            <span class="text-xs font-black uppercase tracking-widest hidden lg:block">{{ Auth::user()->username }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render transition-transform" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                        </button>

                        <div x-show="open" @click.away="open = false"
                             class="absolute right-0 mt-3 w-56 menu-opaque border border-border shadow-2xl rounded-2xl overflow-hidden py-2 z-[100]"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-3 text-xs font-black uppercase tracking-widest hover:bg-foreground/5 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                My Profile
                            </a>
                            <a href="{{ route('inventory') }}" class="flex items-center gap-3 px-4 py-3 text-xs font-black uppercase tracking-widest hover:bg-foreground/5 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                                Inventory
                            </a>
                            <a href="{{ route('transactions') }}" class="flex items-center gap-3 px-4 py-3 text-xs font-black uppercase tracking-widest hover:bg-foreground/5 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/><path d="M2 12h20"/><path d="m7 7-5 5 5 5"/><path d="m17 7 5 5-5 5"/></svg>
                                Transactions
                            </a>
                            <div class="h-px bg-border my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-black uppercase tracking-widest text-destructive hover:bg-destructive/5 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                    Sign Out
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
