<div x-data="{ 
    isDark: true,
    scrollY: 0,
    init() {
        this.isDark = document.documentElement.classList.contains('dark');
        
        window.addEventListener('scroll', () => {
            window.requestAnimationFrame(() => {
                this.scrollY = window.scrollY;
            });
        }, { passive: true });
        
        const observer = new MutationObserver(() => {
            this.isDark = document.documentElement.classList.contains('dark');
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }
}" class="fixed inset-0 pointer-events-none z-0">

    <!-- ================= MOBILE FALLBACK ================= -->
    <div class="absolute inset-0 block md:hidden">
        <!-- Light Mode Mobile -->
        <div x-show="!isDark" class="absolute inset-0 bg-cover bg-bottom bg-no-repeat" style="background-image: url('{{ asset('bg/DAY/hp_day.png') }}');"></div>
        <!-- Dark Mode Mobile -->
        <div x-show="isDark" class="absolute inset-0 bg-cover bg-bottom bg-no-repeat" style="background-image: url('{{ asset('bg/NIGHT/hp_night.png') }}');"></div>
    </div>

    <!-- ================= DESKTOP PARALLAX ================= -->
    <div class="absolute inset-0 hidden md:block overflow-hidden">
        
        <!-- ─── LIGHT MODE (DAY) ─── -->
        <div x-show="!isDark" class="absolute inset-0">
            <!-- BACKGROUND (Static Sky) -->
            <img src="{{ asset('bg/DAY/bgday.png') }}" class="absolute inset-0 w-full h-full object-cover object-bottom" alt="">

            <!-- LAYER 1 (Foreground) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 9; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <img src="{{ asset('bg/DAY/D1-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D3-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>
            
            <!-- STREET (Placed far back in z-index but with Layer 1 speed) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 1; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <img src="{{ asset('bg/DAY/D14-street.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 9 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 2; transform: translate3d(0, -${scrollY * 0.1}px, 0)`">
                <img src="{{ asset('bg/DAY/D9-layer_9.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D10-layer_9.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D11-layer_9.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 8 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 2; transform: translate3d(0, -${scrollY * 0.2}px, 0)`">
                <img src="{{ asset('bg/DAY/D12-layer_8.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- STREET AND FOREGROUND BUILDINGS (All sharing street speed) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 2; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <!-- Layers 7, 6, 5, 4 combined to move with street -->
                <img src="{{ asset('bg/DAY/D8-layer_7.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D12-layer_6.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D13-layer_5.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D5-layer_4.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D7-layer_4.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <!-- Street itself -->
                <img src="{{ asset('bg/DAY/D14-street.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 3 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 7; transform: translate3d(0, -${scrollY * 0.8}px, 0)`">
                <img src="{{ asset('bg/DAY/D6-layer_3.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 2 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 8; transform: translate3d(0, -${scrollY * 0.9}px, 0)`">
                <img src="{{ asset('bg/DAY/D2-layer_2.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D4-layer_2.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 1 (Foreground) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 9; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <img src="{{ asset('bg/DAY/D1-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D3-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- UNDERGROUND (In front of Layer 1, synced with Layer 1) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 10; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <div class="absolute w-full h-[800vh]" style="top: calc(100% - 2px);">
                    <!-- Solid backing to plug transparent holes AND cover the bottom -->
                    <div class="absolute top-0 left-0 w-full h-full bg-repeat-y bg-top" style="background-image: url('{{ asset('bg/DAY/halfunderground_day.png') }}'); background-size: 100% auto;"></div>
                    
                    <!-- Main underground layers -->
                    <div class="relative w-full">
                        <img src="{{ asset('bg/under.png') }}" class="w-full h-auto block" alt="">
                        <div class="w-full h-[300vh] bg-repeat-y bg-top" style="background-image: url('{{ asset('bg/DAY/underground_day.png') }}'); background-size: 100% auto;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── DARK MODE (NIGHT) ─── -->
        <div x-show="isDark" class="absolute inset-0">
            <!-- BACKGROUND (Static Sky) -->
            <img src="{{ asset('bg/NIGHT/bgnight.png') }}" class="absolute inset-0 w-full h-full object-cover object-bottom" alt="">
            
            <!-- LAYER 8 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 2; transform: translate3d(0, -${scrollY * 0.2}px, 0)`">
                <img src="{{ asset('bg/NIGHT/N8-layer_8.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- STREET AND FOREGROUND BUILDINGS (All sharing street speed) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 2; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <!-- Layers 7, 6, 5, 4 combined to move with street -->
                <img src="{{ asset('bg/NIGHT/N11-layer_7.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/NIGHT/N9-layer_6.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/NIGHT/N10-layer_5.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/NIGHT/N5-layer_4.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/NIGHT/N7-layer4.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <!-- Street itself -->
                <img src="{{ asset('bg/NIGHT/N12-street.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 3 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 7; transform: translate3d(0, -${scrollY * 0.8}px, 0)`">
                <img src="{{ asset('bg/NIGHT/N6-layer_3.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 2 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 8; transform: translate3d(0, -${scrollY * 0.9}px, 0)`">
                <img src="{{ asset('bg/NIGHT/N2-layer_2.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/NIGHT/N4-layer_2.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 1 (Foreground) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 9; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <img src="{{ asset('bg/NIGHT/N1-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/NIGHT/N3-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- UNDERGROUND (In front of Layer 1, synced with Layer 1) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 10; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <div class="absolute w-full h-[800vh]" style="top: calc(100% - 2px);">
                    <!-- Solid backing to plug transparent holes AND cover the bottom -->
                    <div class="absolute top-0 left-0 w-full h-full bg-repeat-y bg-top" style="background-image: url('{{ asset('bg/NIGHT/halfunderground_night.png') }}'); background-size: 100% auto;"></div>
                    
                    <!-- Main underground layers -->
                    <div class="relative w-full">
                        <img src="{{ asset('bg/under.png') }}" class="w-full h-auto block" alt="">
                        <div class="w-full h-[300vh] bg-repeat-y bg-top" style="background-image: url('{{ asset('bg/NIGHT/underground_night.png') }}'); background-size: 100% auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>