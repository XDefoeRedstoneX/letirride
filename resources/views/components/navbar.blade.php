<nav x-data="{
    mobileMenuOpen: false,
    userMenuOpen: false,
    isAuthenticated: {{ Auth::check() ? 'true' : 'false' }},
    theme: 'light',
    cartCount: {{ Auth::check() ? Auth::user()->cartItems()->sum('quantity') : 0 }},
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
}" style="position: fixed; top: 0; left: 0; right: 0; z-index: 50; background: var(--nav-bg); border-bottom: 3px solid var(--gold, #f59e0b); box-shadow: 0 4px 0 var(--gold-dim, #92650a);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div style="display: flex; align-items: center; justify-content: space-between; height: 56px; gap: 8px;">
            {{-- Logo --}}
            <a href="/" style="display: flex; align-items: center; flex-shrink: 0; text-decoration: none;">
                <span style="font-family: var(--px, 'Press Start 2P', monospace); font-size: 16px; color: var(--gold, #f59e0b); letter-spacing: 0.05em;">RIDLY</span>
                <span style="font-family: var(--px, 'Press Start 2P', monospace); font-size: 16px; color: var(--nav-text);">.</span>
            </a>

            {{-- Desktop Navigation --}}
            @unless(Auth::check() && Auth::user()->isAdmin())
            <div class="hidden md:flex" style="align-items: center; gap: 4px; padding: 4px; background: var(--nav-bg); border: 2px solid var(--gold, #f59e0b);">
                @php
                    $navItems = [
                        ['route' => 'home', 'label' => 'HOME', 'icon' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
                        ['route' => 'point-shop', 'label' => 'SHOP', 'icon' => '<circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/>'],
                        ['route' => 'gacha', 'label' => 'ARCADE', 'icon' => '<path d="M7 11V7a5 5 0 0 1 10 0v4"/><rect width="18" height="12" x="3" y="11" rx="2"/><circle cx="12" cy="17" r="1"/>'],
                        ['route' => 'favorites', 'label' => 'FAVS', 'icon' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>'],
                        ['route' => 'about', 'label' => 'ABOUT', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>'],
                        ['route' => 'faq', 'label' => 'FAQ', 'icon' => '<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>'],
                    ];
                @endphp
                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       style="display: flex; align-items: center; gap: 5px; padding: 6px 10px; font-family: var(--px, 'Press Start 2P', monospace); font-size: 8px; letter-spacing: 0.08em; white-space: nowrap; text-decoration: none; transition: all 0.1s;
                              {{ request()->routeIs($item['route']) 
                                  ? 'background: var(--gold, #f59e0b); color: var(--nav-bg); box-shadow: 2px 2px 0 var(--gold-dim, #92650a);' 
                                  : 'color: var(--nav-text);' }}"
                       onmouseover="if(!this.classList.contains('nav-active')){this.style.color='var(--gold)';this.style.borderColor='var(--gold)';}"
                       onmouseout="if(!this.classList.contains('nav-active')){this.style.color='var(--nav-text)';this.style.borderColor='transparent';}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter" class="pixel-render">{!! $item['icon'] !!}</svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
            @endunless

            {{-- Right side: Actions --}}
            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                
                {{-- Theme Toggle (Available for all) --}}
                <button @click="toggleTheme()" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: var(--nav-btn-bg); border: 2px solid var(--nav-btn-border); color: var(--nav-btn-text); cursor: pointer; transition: all 0.15s;"
                        onmouseover="this.style.borderColor='var(--gold)';this.style.color='var(--gold)';"
                        onmouseout="this.style.borderColor='var(--nav-btn-border)';this.style.color='var(--nav-btn-text)';">
                    <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </button>

                @auth
                    @php
                        // Pulse the profile chip when a milestone has been granted since the user last
                        // visited /referrals. Single boolean fed to a CSS-only dot.
                        $hasNewReferralUnlocks = false;
                        if (! Auth::user()->isAdmin()) {
                            $lastSeen = Auth::user()->referrals_last_seen_at;
                            $hasNewReferralUnlocks = \App\Models\ReferralReward::where('recipient_id', Auth::id())
                                ->where('kind', \App\Models\ReferralReward::KIND_MILESTONE)
                                ->when($lastSeen, fn ($q) => $q->where('created_at', '>', $lastSeen))
                                ->exists();
                        }
                    @endphp
                    @unless(Auth::user()->isAdmin())
                    {{-- Points Balance --}}
                    <div class="hidden sm:flex" style="align-items: center; gap: 6px; padding: 5px 10px; background: rgba(245,158,11,0.1); border: 2px solid rgba(245,158,11,0.25);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" style="color: var(--gold);"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                        <span style="font-family: var(--px); font-size: 8px; color: var(--gold); letter-spacing: 0.08em;">{{ number_format(Auth::user()->points_balance) }}</span>
                    </div>

                    {{-- Cart --}}
                    <a href="{{ route('cart') }}" style="position: relative; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: var(--nav-btn-bg); border: 2px solid var(--nav-btn-border); color: var(--nav-btn-text); text-decoration: none; transition: all 0.15s;"
                       onmouseover="this.style.borderColor='var(--gold)';this.style.color='var(--gold)';"
                       onmouseout="this.style.borderColor='var(--nav-btn-border)';this.style.color='var(--nav-btn-text)';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.56-7.43H5.94"/></svg>
                        <span x-show="cartCount > 0" x-text="cartCount"
                              style="position: absolute; top: -6px; right: -6px; min-width: 18px; height: 18px; background: #ef4444; color: white; font-family: var(--px); font-size: 7px; display: flex; align-items: center; justify-content: center; border: 2px solid var(--nav-bg);"></span>
                    </a>
                    @endunless

                    {{-- User Dropdown --}}
                    <div style="position: relative;" x-data="{ open: false }">
                        <button @click="open = !open" class="navbar-user-chip {{ $hasNewReferralUnlocks ? 'has-referral-pulse' : '' }}" style="display: flex; align-items: center; gap: 8px; padding: 4px 8px; background: var(--nav-btn-bg); border: 2px solid var(--nav-btn-border); cursor: pointer; transition: all 0.15s; position: relative;"
                                onmouseover="this.style.borderColor='var(--gold)';"
                                onmouseout="this.style.borderColor='var(--nav-btn-border)';">
                            <div style="width: 28px; height: 28px; overflow: hidden;">
                                <img src="{{ Auth::user()->avatar_url }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Avatar">
                            </div>
                            <span class="hidden lg:block" style="font-family: var(--px); font-size: 8px; color: var(--nav-text); letter-spacing: 0.08em;">{{ Auth::user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" style="color: var(--nav-btn-text); transition: transform 0.2s;" :style="open ? 'transform: rotate(180deg)' : ''"><path d="m6 9 6 6 6-6"/></svg>
                            @if ($hasNewReferralUnlocks)
                                <span class="navbar-referral-pulse" aria-label="New referral rewards unlocked"></span>
                            @endif
                        </button>

                        <div x-show="open" @click.away="open = false"
                             style="position: absolute; right: 0; margin-top: 8px; width: 220px; background: var(--nav-menu-bg); border: 3px solid var(--gold, #f59e0b); box-shadow: 4px 4px 0 var(--gold-dim, #92650a); z-index: 100; padding: 6px 0;"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100">
                            @unless(Auth::user()->isAdmin())
                            @php
                                $menuItems = [
                                    ['route' => 'profile', 'label' => 'MY PROFILE', 'icon' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
                                    ['route' => 'inventory', 'label' => 'INVENTORY', 'icon' => '<path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>'],
                                    ['route' => 'transactions', 'label' => 'TRANSACTIONS', 'icon' => '<path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/><path d="M2 12h20"/><path d="m7 7-5 5 5 5"/><path d="m17 7 5 5-5 5"/>'],
                                    ['route' => 'referrals', 'label' => 'REFER FRIENDS', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11h-6"/><path d="M19 8v6"/>', 'pulse' => $hasNewReferralUnlocks],
                                ];
                            @endphp
                            @foreach($menuItems as $mi)
                                <a href="{{ route($mi['route']) }}" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; font-family: var(--px); font-size: 7px; letter-spacing: 0.08em; color: var(--nav-text); text-decoration: none; transition: all 0.1s;"
                                   onmouseover="this.style.background='var(--nav-menu-hover)';this.style.color='var(--gold)';"
                                   onmouseout="this.style.background='transparent';this.style.color='var(--nav-text)';">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">{!! $mi['icon'] !!}</svg>
                                    <span style="flex:1;">{{ $mi['label'] }}</span>
                                    @if (! empty($mi['pulse']))
                                        <span class="navbar-dropdown-pulse" aria-hidden="true"></span>
                                    @endif
                                </a>
                            @endforeach
                            @endunless
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; font-family: var(--px); font-size: 7px; letter-spacing: 0.08em; color: var(--gold); text-decoration: none; transition: all 0.1s;"
                                   onmouseover="this.style.background='var(--nav-menu-hover)';"
                                   onmouseout="this.style.background='transparent';">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                    ADMIN PANEL
                                </a>
                            @endif
                            <div style="height: 2px; background: var(--nav-btn-border); margin: 4px 0;"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; font-family: var(--px); font-size: 7px; letter-spacing: 0.08em; color: #ef4444; cursor: pointer; background: none; border: none; width: 100%; text-align: left; transition: all 0.1s;"
                                        onmouseover="this.style.background='rgba(239,68,68,0.1)';"
                                        onmouseout="this.style.background='transparent';">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                    SIGN OUT
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Guest: Login / Signup --}}
                    <button @click="$dispatch('open-auth-modal', { tab: 'login' })"
                            class="modal-btn-secondary" style="padding: 8px 14px; font-size: 7px;">
                        LOGIN
                    </button>
                    <button @click="$dispatch('open-auth-modal', { tab: 'signup' })"
                            class="buy-btn" style="padding: 8px 14px;">
                        SIGN UP
                    </button>
                @endauth

                {{-- Mobile Menu Toggle --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden" style="padding: 8px; background: var(--nav-btn-bg); border: 2px solid var(--nav-btn-border); color: var(--nav-btn-text); cursor: pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen" class="md:hidden" style="border-top: 2px solid var(--nav-btn-border); background: var(--nav-bg);">
        <div style="padding: 8px;">
            @unless(Auth::check() && Auth::user()->isAdmin())
            @php
                $mobileNav = [
                    ['route' => 'home', 'label' => 'HOME'],
                    ['route' => 'point-shop', 'label' => 'POINT SHOP'],
                    ['route' => 'gacha', 'label' => 'ARCADE'],
                    ['route' => 'favorites', 'label' => 'FAVORITES'],
                    ['route' => 'about', 'label' => 'ABOUT US'],
                    ['route' => 'faq', 'label' => 'FAQ'],
                ];
            @endphp
            @foreach($mobileNav as $mn)
                <a href="{{ route($mn['route']) }}"
                   style="display: block; padding: 10px 14px; font-family: var(--px); font-size: 7px; letter-spacing: 0.1em; text-decoration: none; transition: all 0.1s;
                          {{ request()->routeIs($mn['route'])
                              ? 'background: var(--gold); color: var(--dark-bg);'
                              : 'color: var(--text-dim);' }}">
                    {{ $mn['label'] }}
                </a>
            @endforeach
            @auth
                <div style="display: flex; align-items: center; gap: 6px; padding: 10px 14px; color: var(--gold);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                    <span style="font-family: var(--px); font-size: 7px; letter-spacing: 0.08em;">{{ number_format(Auth::user()->points_balance) }} POINTS</span>
                </div>
            @endauth
            @endunless
        </div>
    </div>
</nav>
