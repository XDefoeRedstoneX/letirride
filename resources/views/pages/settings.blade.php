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

                    <button type="submit" class="px-6 py-2 bg-secondary text-secondary-foreground border border-border font-bold rounded-xl hover:bg-accent transition-colors">Update Password</button>
                </form>
            </div>

            <!-- Preferences -->
            <div class="bg-card border border-border rounded-2xl p-6 space-y-4">
                <h3 class="text-lg font-bold">Preferences</h3>
                
                <div class="flex items-center justify-between p-2">
                    <div>
                        <p class="text-sm font-bold">Email Notifications</p>
                        <p class="text-xs text-muted-foreground">Receive updates about your orders and rewards.</p>
                    </div>
                    <button class="w-12 h-6 bg-primary rounded-full relative p-1 transition-colors">
                        <div class="w-4 h-4 bg-white rounded-full absolute right-1"></div>
                    </button>
                </div>
                
                <div class="flex items-center justify-between p-2">
                    <div>
                        <p class="text-sm font-bold">Public Profile</p>
                        <p class="text-xs text-muted-foreground">Allow others to see your won gacha items.</p>
                    </div>
                    <button class="w-12 h-6 bg-muted rounded-full relative p-1 transition-colors">
                        <div class="w-4 h-4 bg-white rounded-full absolute left-1"></div>
                    </button>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-destructive/5 border border-destructive/20 rounded-2xl p-6 space-y-4">
                <h3 class="text-lg font-bold text-destructive">Danger Zone</h3>
                <p class="text-sm text-muted-foreground">Permanently delete your account and all associated data. This action cannot be undone.</p>
                <button class="px-6 py-2 bg-destructive text-destructive-foreground font-bold rounded-xl hover:opacity-90 transition-opacity">Delete Account</button>
            </div>
        </div>
    </div>
</x-app-layout>
