<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-12" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
        <!-- Profile Card -->
        <div class="relative overflow-hidden glass-card rounded-[3rem] shadow-2xl" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000" x-transition:enter-start="md:opacity-0 md:scale-95 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:scale-100 md:translate-y-0">
            <!-- Cover / Header BG (Elegant City Sky) -->
            <div class="h-48 bg-gradient-to-r from-primary/20 via-primary/5 to-transparent"></div>
            
            <div class="px-10 pb-10 -mt-20 relative z-10 flex flex-col md:flex-row items-center md:items-end gap-8">
                <!-- Avatar -->
                <div class="relative group">
                    <div class="w-40 h-40 rounded-[2.5rem] bg-background border-8 border-background p-1 shadow-2xl overflow-hidden">
                        <div class="w-full h-full bg-primary/10 rounded-[2rem] flex items-center justify-center text-primary font-black text-5xl">
                            {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                        </div>
                    </div>
                    <button class="absolute bottom-2 right-2 w-10 h-10 bg-primary text-primary-foreground rounded-2xl shadow-xl flex items-center justify-center hover:scale-110 transition-all border-4 border-background">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                    </button>
                </div>

                <!-- Info -->
                <div class="flex-1 text-center md:text-left space-y-3">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6">
                        <h1 class="text-4xl font-black tracking-tighter uppercase">{{ Auth::user()->username }}</h1>
                        @if(Auth::user()->role !== 'Customer')
                            <span class="px-4 py-1.5 rounded-full bg-primary text-primary-foreground text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary/20 self-center">
                                {{ Auth::user()->role }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-center md:justify-start gap-2 text-muted-foreground font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render text-primary"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <span>{{ Auth::user()->email }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-6 md:pt-0">
                    <a href="{{ route('settings') }}" class="px-8 py-3 glass-card font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-white/10 transition-all">Settings</a>
                </div>
            </div>
        </div>

        <!-- User Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-200" x-transition:enter-start="md:opacity-0 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:translate-y-0">
            <div class="glass-card rounded-[2.5rem] p-8 space-y-3 group hover:border-primary/30 transition-all duration-500">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Luck Rate</p>
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-foreground tracking-tighter">85.4<span class="text-xs text-muted-foreground ml-1">%</span></p>
            </div>
            <div class="glass-card rounded-[2.5rem] p-8 space-y-3 group hover:border-primary/30 transition-all duration-500">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Available Points</p>
                    <div class="w-8 h-8 rounded-lg bg-yellow-500/10 flex items-center justify-center text-yellow-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-foreground tracking-tighter">{{ number_format(Auth::user()->points) }}</p>
            </div>
            <div class="glass-card rounded-[2.5rem] p-8 space-y-3 group hover:border-primary/30 transition-all duration-500">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Inventory Size</p>
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-foreground tracking-tighter">12 <span class="text-xs text-muted-foreground uppercase tracking-widest ml-1">Items</span></p>
            </div>
        </div>

        <!-- Recent Activity with Professional List -->
        <div class="glass-card rounded-[2.5rem] overflow-hidden" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-400" x-transition:enter-start="md:opacity-0 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:translate-y-0">
            <div class="p-8 border-b border-white/5 flex items-center justify-between">
                <h3 class="font-black uppercase tracking-widest text-sm">Recent Activity</h3>
                <button class="text-[10px] font-black text-primary uppercase tracking-widest hover:underline">View All</button>
            </div>
            <div class="divide-y divide-white/5">
                @php
                    $activities = [
                        ['title' => 'Security Update', 'desc' => 'Password successfully changed', 'time' => '2 hours ago', 'color' => 'green'],
                        ['title' => 'Purchase Success', 'desc' => 'Steam Wallet Rp 100.000', 'time' => '1 day ago', 'color' => 'primary'],
                        ['title' => 'Gacha Spin', 'desc' => 'Won 500 Points Reward', 'time' => '2 days ago', 'color' => 'yellow']
                    ];
                @endphp
                @foreach($activities as $act)
                <div class="p-6 flex items-center justify-between hover:bg-white/5 transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-10 h-10 rounded-xl bg-{{ $act['color'] }}-500/10 flex items-center justify-center text-{{ $act['color'] }}-500 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-black group-hover:text-primary transition-colors">{{ $act['title'] }}</p>
                            <p class="text-xs text-muted-foreground font-medium">{{ $act['desc'] }}</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-muted-foreground">{{ $act['time'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
