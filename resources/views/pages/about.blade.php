<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-16 py-12" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
        <!-- Hero Section -->
        <div class="text-center space-y-6" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000" x-transition:enter-start="md:opacity-0 md:translate-y-12" x-transition:enter-end="md:opacity-100 md:translate-y-0">
            <div class="w-20 h-20 bg-primary/10 rounded-[2rem] flex items-center justify-center text-primary mx-auto mb-8 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
            </div>
            <h1 class="text-6xl font-black tracking-tighter uppercase leading-none">About <span class="text-primary">Ridly</span></h1>
            <p class="text-muted-foreground text-xl max-w-2xl mx-auto font-medium leading-relaxed">The ultimate hub for premium digital assets, licenses, and gaming rewards. Redefining the digital marketplace experience.</p>
        </div>

        <!-- Vision & Mission -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-200">
            <div class="glass-card p-10 rounded-[3rem] space-y-6 group hover:border-primary/30 transition-all duration-500">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <h2 class="text-3xl font-black uppercase tracking-tight">Our Vision</h2>
                <p class="text-muted-foreground leading-relaxed font-medium">To become the world's most trusted and innovative digital marketplace, empowering users to access premium services with ease and excitement.</p>
            </div>
            <div class="glass-card p-10 rounded-[3rem] space-y-6 group hover:border-primary/30 transition-all duration-500">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <h2 class="text-3xl font-black uppercase tracking-tight">Our Mission</h2>
                <ul class="space-y-3 text-muted-foreground font-medium">
                    <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 rounded-full bg-primary"></div>Providing high-quality digital licenses at competitive prices.</li>
                    <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 rounded-full bg-primary"></div>Creating an engaging ecosystem through gamified rewards.</li>
                    <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 rounded-full bg-primary"></div>Ensuring top-tier security and instant delivery for every transaction.</li>
                </ul>
            </div>
        </div>

        <!-- Achievements -->
        <div class="space-y-10" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-400">
            <h2 class="text-center text-2xl font-black uppercase tracking-widest">Our Achievements</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @php
                    $stats = [
                        ['label' => 'Happy Users', 'value' => '50K+', 'icon' => 'users'],
                        ['label' => 'Products Sold', 'value' => '120K+', 'icon' => 'shopping-bag'],
                        ['label' => 'Transactions', 'value' => 'Rp 5B+', 'icon' => 'credit-card'],
                        ['label' => 'Support Rating', 'value' => '4.9/5', 'icon' => 'star']
                    ];
                @endphp
                @foreach($stats as $s)
                <div class="glass-card p-8 rounded-[2rem] text-center space-y-2 group hover:-translate-y-2 transition-all duration-500">
                    <p class="text-4xl font-black text-primary tracking-tighter">{{ $s['value'] }}</p>
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $s['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>