<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Profile Card -->
        <div class="relative overflow-hidden bg-card border border-border rounded-[2rem] shadow-xl">
            <!-- Cover / Header BG -->
            <div class="h-32 bg-gradient-to-r from-primary via-neon-purple to-neon-pink opacity-20"></div>
            
            <div class="px-8 pb-8 -mt-12 relative z-10 flex flex-col md:flex-row items-center md:items-end gap-6">
                <!-- Avatar -->
                <div class="relative group">
                    <div class="w-32 h-32 rounded-3xl bg-card border-4 border-background p-1 shadow-2xl">
                        <div class="w-full h-full bg-primary/20 rounded-2xl flex items-center justify-center text-primary font-black text-4xl">
                            {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                        </div>
                    </div>
                    <button class="absolute bottom-2 right-2 p-2 bg-background border border-border rounded-xl shadow-lg hover:bg-accent transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                    </button>
                </div>

                <!-- Info -->
                <div class="flex-1 text-center md:text-left space-y-2">
                    <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                        <h1 class="text-3xl font-black tracking-tight">{{ Auth::user()->username }}</h1>
                        <span class="px-3 py-1 rounded-full bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest border border-primary/20 self-center">
                            {{ Auth::user()->role }}
                        </span>
                    </div>
                    <p class="text-muted-foreground">{{ Auth::user()->email }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 pt-4 md:pt-0">
                    <a href="{{ route('settings') }}" class="px-6 py-2 bg-card border border-border rounded-xl font-bold text-sm hover:bg-accent transition-colors">Edit Profile</a>
                </div>
            </div>
        </div>

        <!-- User Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-card border border-border rounded-2xl p-6 space-y-2">
                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Balance</p>
                <p class="text-2xl font-black text-green-500">Rp {{ number_format(Auth::user()->balance) }}</p>
            </div>
            <div class="bg-card border border-border rounded-2xl p-6 space-y-2">
                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Points</p>
                <p class="text-2xl font-black text-yellow-500">{{ number_format(Auth::user()->points) }}</p>
            </div>
            <div class="bg-card border border-border rounded-2xl p-6 space-y-2">
                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Gacha Spins</p>
                <p class="text-2xl font-black text-neon-pink">12</p>
            </div>
        </div>

        <!-- Recent Activity Placeholder -->
        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-border">
                <h3 class="font-bold">Recent Activity</h3>
            </div>
            <div class="divide-y divide-border">
                <div class="p-4 flex items-center justify-between hover:bg-accent/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-green-500/10 flex items-center justify-center text-green-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold">Security Update</p>
                            <p class="text-xs text-muted-foreground">You changed your password</p>
                        </div>
                    </div>
                    <span class="text-xs text-muted-foreground">2 hours ago</span>
                </div>
                <div class="p-4 flex items-center justify-between hover:bg-accent/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold">Purchase Success</p>
                            <p class="text-xs text-muted-foreground">Steam Wallet Rp 100.000</p>
                        </div>
                    </div>
                    <span class="text-xs text-muted-foreground">1 day ago</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
