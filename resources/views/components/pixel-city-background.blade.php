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

            <!-- LAYER 7 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 3; transform: translate3d(0, -${scrollY * 0.3}px, 0)`">
                <img src="{{ asset('bg/DAY/D8-layer_7.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 6 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 4; transform: translate3d(0, -${scrollY * 0.4}px, 0)`">
                <img src="{{ asset('bg/DAY/D12-layer_6.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 5 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 5; transform: translate3d(0, -${scrollY * 0.5}px, 0)`">
                <img src="{{ asset('bg/DAY/D13-layer_5.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 4 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 6; transform: translate3d(0, -${scrollY * 0.65}px, 0)`">
                <img src="{{ asset('bg/DAY/D5-layer_4.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D7-layer_4.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
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

            <!-- LAYER 1 (Foreground) + STREET. The street is the foreground's
                 ground plane — same z, same speed, so back buildings can't tear
                 away from it as the user scrolls. -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 9; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <img src="{{ asset('bg/DAY/D14-street.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D1-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/DAY/D3-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            @php $hasSewer = file_exists(public_path('bg/DAY/D15-sewer-transition.png')); @endphp
            @if ($hasSewer)
                <!-- SEWER TRANSITION (bridges city → underground) -->
                <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 10; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                    <!-- top: calc(100% - 2px) butts against the bottom of the viewport with the same anti-seam trick. -->
                    <img src="{{ asset('bg/DAY/D15-sewer-transition.png') }}"
                         class="absolute w-full h-auto"
                         style="top: calc(100% - 2px);" alt="">
                </div>
            @endif

            <!-- UNDERGROUND (now sits BELOW the sewer band) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 11; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <!-- top offsets by the sewer image height when present; 16.667vw ≈ 1920/300 ≈ image aspect. -->
                <div class="absolute w-full h-[500vh] bg-repeat-y bg-top"
                     style="top: calc(100% + {{ $hasSewer ? '15.625vw' : '0px' }} - 2px); background-image: url('{{ asset('bg/DAY/underground_day.png') }}'); background-size: 100% auto;"></div>
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

            <!-- LAYER 7 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 3; transform: translate3d(0, -${scrollY * 0.3}px, 0)`">
                <img src="{{ asset('bg/NIGHT/N11-layer_7.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 6 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 4; transform: translate3d(0, -${scrollY * 0.4}px, 0)`">
                <img src="{{ asset('bg/NIGHT/N9-layer_6.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 5 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 5; transform: translate3d(0, -${scrollY * 0.5}px, 0)`">
                <img src="{{ asset('bg/NIGHT/N10-layer_5.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            <!-- LAYER 4 -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 6; transform: translate3d(0, -${scrollY * 0.65}px, 0)`">
                <img src="{{ asset('bg/NIGHT/N5-layer_4.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/NIGHT/N7-layer4.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
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

            <!-- LAYER 1 (Foreground) + STREET. Street joins the foreground at the
                 same z and speed so back layers can't tear off it. -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 9; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <img src="{{ asset('bg/NIGHT/N12-street.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/NIGHT/N1-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
                <img src="{{ asset('bg/NIGHT/N3-layer_1.png') }}" class="absolute bottom-0 w-full h-auto object-bottom" alt="">
            </div>

            @php $hasSewerNight = file_exists(public_path('bg/DAY/D15-sewer-transition.png')); @endphp
            @if ($hasSewerNight)
                <!-- SEWER TRANSITION (shared asset, dimmed for night) -->
                <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 10; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                    <img src="{{ asset('bg/DAY/D15-sewer-transition.png') }}"
                         class="absolute w-full h-auto"
                         style="top: calc(100% - 2px); filter: brightness(0.55) saturate(0.75) hue-rotate(-10deg);" alt="">
                </div>
            @endif

            <!-- UNDERGROUND (now sits BELOW the sewer band) -->
            <div class="absolute inset-0 w-full h-full will-change-transform" :style="`z-index: 11; transform: translate3d(0, -${scrollY * 1.0}px, 0)`">
                <div class="absolute w-full h-[500vh] bg-repeat-y bg-top"
                     style="top: calc(100% + {{ $hasSewerNight ? '15.625vw' : '0px' }} - 2px); background-image: url('{{ asset('bg/NIGHT/underground_night.png') }}'); background-size: 100% auto;"></div>
            </div>
        </div>
    </div>
</div>