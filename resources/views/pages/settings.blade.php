<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-8">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Account Settings</h1>
            <p class="text-muted-foreground">Manage your personal information and preferences.</p>
        </div>

        <div class="space-y-6">
            <!-- Profile Info -->
            <div class="bg-card border border-border rounded-2xl p-6 space-y-6">
                <h3 class="text-lg font-bold">Personal Information</h3>
                
                <form class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Display Name</label>
                        <input type="text" value="{{ Auth::user()->username }}" class="w-full px-4 py-2 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/50 outline-none transition-all">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Email Address</label>
                        <input type="email" value="{{ Auth::user()->email }}" disabled class="w-full px-4 py-2 bg-muted border border-border rounded-xl cursor-not-allowed opacity-70">
                        <p class="text-[10px] text-muted-foreground">Email cannot be changed once verified.</p>
                    </div>

                    <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground font-bold rounded-xl hover:opacity-90 transition-opacity">Save Changes</button>
                </form>
            </div>

            <!-- Security -->
            <div class="bg-card border border-border rounded-2xl p-6 space-y-6">
                <h3 class="text-lg font-bold">Security</h3>
                
                <form class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Current Password</label>
                        <input type="password" placeholder="••••••••" class="w-full px-4 py-2 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/50 outline-none transition-all">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium">New Password</label>
                        <input type="password" placeholder="Min. 8 characters" class="w-full px-4 py-2 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/50 outline-none transition-all">
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-6 py-3 bg-primary text-primary-foreground font-black text-[10px] uppercase tracking-widest rounded-xl hover:scale-105 transition-all shadow-lg shadow-primary/20">Update Password</button>
                        <a href="{{ route('forgot-password') }}" class="px-6 py-3 bg-card border-2 border-border text-foreground font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-foreground/5 transition-all">Forgot Password</a>
                    </div>
                </form>
            </div>

            <!-- Danger Zone Replacement -->
            <div class="bg-destructive/5 border border-destructive/20 rounded-[2.5rem] p-8 space-y-4">
                <div class="flex items-center gap-4 text-destructive">
                    <div class="w-12 h-12 bg-destructive/10 rounded-2xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <h3 class="text-lg font-black uppercase tracking-tighter">Account Management</h3>
                </div>
                <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest leading-relaxed">To ensure security, account deletion must be processed by our support team. If you wish to permanently delete your account, please contact our Customer Services.</p>
                <a href="{{ route('tickets') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-destructive text-destructive-foreground font-black rounded-2xl hover:scale-105 transition-all shadow-xl shadow-destructive/20 uppercase tracking-widest text-[10px]">
                    Contact Customer Services
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
