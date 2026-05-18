<div x-data="{ 
    isDark: true,
    scrollY: 0,
    init() {
        this.isDark = document.documentElement.classList.contains('dark');
        window.addEventListener('scroll', () => {
            this.scrollY = window.scrollY;
        });
        
        // Listen for theme changes
        const observer = new MutationObserver(() => {
            this.isDark = document.documentElement.classList.contains('dark');
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }
}" class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
    
    <!-- Sky gradient -->
    <div
        class="absolute inset-0 transition-colors duration-1000"
        :style="{
            background: isDark
                ? 'linear-gradient(180deg, #020617 0%, #0f172a 100%)'
                : 'linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%)'
        }"
    ></div>

    <!-- Stars & Moon (dark mode only) -->
    <div x-show="isDark" x-transition:enter="transition opacity duration-1000" class="absolute inset-0">
        <!-- Crescent Moon -->
        <div class="absolute top-20 right-[15%] w-16 h-16 opacity-80"
             :style="{ transform: 'translateY(' + (scrollY * -0.05) + 'px)' }">
            <div class="w-16 h-16 bg-amber-100 rounded-full shadow-[0_0_20px_rgba(254,243,199,0.3)]"></div>
            <div class="absolute -top-2 -right-4 w-16 h-16 rounded-full transition-colors duration-1000"
                 :style="{ background: isDark ? '#020617' : '#f0f9ff' }"></div>
        </div>

        @for ($i = 0; $i < 40; $i++)
            <div
                class="absolute rounded-full bg-blue-100/40 animate-twinkle"
                style="width: {{ rand(1, 3) }}px; height: {{ rand(1, 3) }}px; left: {{ rand(0, 100) }}%; top: {{ rand(0, 45) }}%; animation-duration: {{ rand(3, 8) }}s; animation-delay: {{ rand(0, 5) }}s;"
            ></div>
        @endfor
    </div>

    <!-- Clouds (light mode only) -->
    <div x-show="!isDark" x-transition:enter="transition opacity duration-1000" class="absolute inset-0">
        @for ($k = 0; $k < 5; $k++)
            <div class="absolute opacity-60 animate-drift"
                 style="top: {{ rand(10, 40) }}%; left: -200px; animation-duration: {{ rand(60, 120) }}s; animation-delay: -{{ rand(0, 60) }}s;">
                <div class="flex">
                    <div class="w-16 h-16 bg-white rounded-full"></div>
                    <div class="w-20 h-20 bg-white rounded-full -ml-8 -mt-4"></div>
                    <div class="w-16 h-16 bg-white rounded-full -ml-8"></div>
                </div>
            </div>
        @endfor
    </div>

    <!-- Interactive Professional Cityscape -->
    <div class="absolute bottom-[-100px] left-0 right-0 h-[70vh] transition-all duration-300 ease-out"
         :style="{ transform: 'translateY(' + (scrollY * -0.15) + 'px)' }">
        <svg viewBox="0 0 1440 400" class="w-full h-full pixel-render" preserveAspectRatio="none">
            <defs>
                <linearGradient id="buildingGradDark" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#1e293b" />
                    <stop offset="100%" style="stop-color:#0f172a" />
                </linearGradient>
                <linearGradient id="buildingGradLight" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#94a3b8" />
                    <stop offset="100%" style="stop-color:#64748b" />
                </linearGradient>
            </defs>
            
            <!-- City silhouette -->
            <g :fill="isDark ? 'url(#buildingGradDark)' : 'url(#buildingGradLight)'">
                <rect x="0" y="250" width="150" height="250" />
                <rect x="120" y="150" width="100" height="350" />
                <rect x="200" y="200" width="120" height="300" />
                <rect x="300" y="80" width="80" height="420" />
                <rect x="360" y="180" width="140" height="320" />
                <rect x="480" y="120" width="110" height="380" />
                <rect x="580" y="220" width="130" height="280" />
                <rect x="680" y="50" width="70" height="450" />
                <rect x="740" y="160" width="150" height="340" />
                <rect x="880" y="100" width="110" height="400" />
                <rect x="980" y="210" width="100" height="290" />
                <rect x="1060" y="140" width="130" height="360" />
                <rect x="1180" y="70" width="90" height="430" />
                <rect x="1260" y="190" width="180" height="310" />
            </g>

            <!-- Large Twinkling Window Lights -->
            <g fill="#fbbf24">
                @for ($j = 0; $j < 25; $j++)
                    @php 
                        $wx = rand(20, 1400);
                        $wy = rand(100, 380);
                        $dur = rand(2, 5);
                        $del = rand(0, 5);
                        $isHigh = rand(0, 1) > 0.5;
                    @endphp
                    <!-- Window pair -->
                    <rect x="{{ $wx }}" y="{{ $wy }}" width="8" height="12" class="animate-pulse" style="animation-duration: {{ $dur }}s; animation-delay: {{ $del }}s; opacity: {{ $isHigh ? 0.6 : 0.2 }};" />
                    <rect x="{{ $wx + 12 }}" y="{{ $wy }}" width="8" height="12" class="animate-pulse" style="animation-duration: {{ $dur }}s; animation-delay: {{ $del + 0.5 }}s; opacity: {{ $isHigh ? 0.6 : 0.2 }};" />
                @endfor
            </g>
        </svg>
    </div>
</div>

<style>
@keyframes twinkle {
  0%, 100% { opacity: 0.3; transform: scale(0.8); }
  50% { opacity: 1; transform: scale(1.2); }
}
.animate-twinkle {
  animation: twinkle var(--twinkle-duration, 3s) infinite ease-in-out;
}
@keyframes drift {
  from { transform: translateX(-200px); }
  to { transform: translateX(calc(100vw + 200px)); }
}
.animate-drift {
  animation: drift linear infinite;
}
</style>
