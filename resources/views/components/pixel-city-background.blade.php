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
        class="absolute inset-0 transition-colors duration-700"
        :style="{
            background: isDark
                ? 'linear-gradient(180deg, #050a1a 0%, #0a0e27 30%, #0f1537 60%, #141a3d 100%)'
                : 'linear-gradient(180deg, #87CEEB 0%, #00d4ff 30%, #87CEEB 60%, #b8e6ff 100%)'
        }"
    ></div>

    <!-- Stars (dark mode only) -->
    <div x-show="isDark" class="absolute inset-0">
        @for ($i = 0; $i < 50; $i++)
            <div
                class="absolute rounded-full bg-white animate-twinkle"
                style="width: {{ rand(1, 4) }}px; height: {{ rand(1, 4) }}px; left: {{ rand(0, 100) }}%; top: {{ rand(0, 40) }}%; animation-duration: {{ rand(2, 6) }}s; animation-delay: {{ rand(0, 3) }}s;"
            ></div>
        @endfor
    </div>

    <!-- Clouds (light mode only) -->
    <div x-show="!isDark" class="absolute inset-0 overflow-hidden pointer-events-none">
        @for ($i = 0; $i < 6; $i++)
            <div
                class="absolute animate-drift-horizontal opacity-40"
                style="top: {{ 10 + $i * 12 }}%; animation-duration: {{ rand(60, 100) }}s; animation-delay: -{{ rand(0, 60) }}s;"
            >
                <svg width="{{ 120 + $i * 20 }}" height="40" viewBox="0 0 120 40" class="pixel-render">
                    <rect x="20" y="10" width="80" height="20" fill="white" />
                    <rect x="30" y="5" width="60" height="10" fill="white" />
                    <rect x="40" y="0" width="40" height="5" fill="white" />
                    <rect x="10" y="15" width="100" height="15" fill="white" />
                    <rect x="0" y="20" width="120" height="10" fill="white" />
                </svg>
            </div>
        @endfor
    </div>

    <!-- Sun/Moon -->
    <div class="absolute top-8 right-16 md:right-24">
        <div x-show="!isDark" class="w-16 h-16 md:w-20 md:h-20 pixel-render" style="background: #FFD700; box-shadow: 0 0 40px #FFD700, 0 0 80px #FFD70060;">
            <svg viewBox="0 0 80 80" class="w-full h-full animate-pulse">
                <rect x="30" y="0" width="20" height="12" fill="#FFD700" />
                <rect x="30" y="68" width="20" height="12" fill="#FFD700" />
                <rect x="0" y="30" width="12" height="20" fill="#FFD700" />
                <rect x="68" y="30" width="12" height="20" fill="#FFD700" />
                <rect x="8" y="8" width="14" height="14" fill="#FFD700" />
                <rect x="58" y="8" width="14" height="14" fill="#FFD700" />
                <rect x="8" y="58" width="14" height="14" fill="#FFD700" />
                <rect x="58" y="58" width="14" height="14" fill="#FFD700" />
                <rect x="20" y="20" width="40" height="40" rx="4" fill="#FFA500" />
                <rect x="24" y="24" width="32" height="32" rx="2" fill="#FFD700" />
            </svg>
        </div>
        <svg x-show="isDark" viewBox="0 0 60 60" class="w-12 h-12 md:w-16 md:h-16 pixel-render">
            <rect x="10" y="5" width="40" height="50" rx="8" fill="#e8e8d0" />
            <rect x="8" y="8" width="44" height="44" rx="8" fill="#f0f0d8" />
            <rect x="15" y="15" width="8" height="8" rx="2" fill="#d4d4b8" opacity="0.5" />
            <rect x="32" y="22" width="6" height="6" rx="1" fill="#d4d4b8" opacity="0.4" />
            <rect x="22" y="32" width="10" height="6" rx="2" fill="#d4d4b8" opacity="0.3" />
        </svg>
    </div>

    <!-- Buildings -->
    <div class="absolute bottom-0 left-0 right-0" :style="`transform: translateY(${scrollY * 0.05}px)`">
        <svg viewBox="0 0 1440 320" class="w-full pixel-render" preserveAspectRatio="none">
            <g x-show="isDark">
                <rect x="0" y="160" width="60" height="160" fill="#0d1230" />
                <rect x="70" y="120" width="50" height="200" fill="#0f1537" />
                <rect x="130" y="140" width="70" height="180" fill="#0d1230" />
                <rect x="210" y="100" width="40" height="220" fill="#101840" />
                <!-- Windows would go here, but for brevity we'll keep it simple -->
            </g>
            <g x-show="!isDark">
                <rect x="0" y="160" width="60" height="160" fill="#87CEEB" opacity="0.5" />
                <rect x="70" y="120" width="50" height="200" fill="#87CEEB" opacity="0.7" />
                <rect x="130" y="140" width="70" height="180" fill="#87CEEB" opacity="0.5" />
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
  from { transform: translateX(-100%); }
  to { transform: translateX(100vw); }
}
.animate-drift-horizontal {
  animation: drift var(--drift-duration, 60s) linear infinite;
}
</style>
