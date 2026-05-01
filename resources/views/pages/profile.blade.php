<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-12" x-data="{
        show: false,
        profileName: @js(Auth::user()->name),
        profileError: '',
        profileSuccess: '',
        profileLoading: false,
        passwordError: '',
        passwordSuccess: '',
        passwordLoading: false,
        currentPassword: '',
        newPassword: '',
        async submitProfile() {
            this.profileError = '';
            this.profileSuccess = '';
            this.profileLoading = true;

            const form = this.$refs.profileForm;
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            const data = await response.json().catch(() => ({}));
            this.profileLoading = false;

            if (response.ok) {
                this.profileName = data.name || this.profileName;
                this.profileSuccess = data.message || 'Profile updated successfully.';
                return;
            }

            const errors = data.errors || {};
            this.profileError = errors.name?.[0] || data.message || 'Unable to update profile.';
        },
        async submitPassword() {
            this.passwordError = '';
            this.passwordSuccess = '';
            this.passwordLoading = true;

            const form = this.$refs.passwordForm;
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            const data = await response.json().catch(() => ({}));
            this.passwordLoading = false;

            if (response.ok) {
                this.currentPassword = '';
                this.newPassword = '';
                this.passwordSuccess = data.message || 'Password updated successfully.';
                return;
            }

            const errors = data.errors || {};
            this.passwordError = errors.current_password?.[0] || errors.new_password?.[0] || data.message || 'Unable to update password.';
        }
    }" x-init="setTimeout(() => show = true, 50)">
        <!-- Profile Card -->
        <div class="relative overflow-hidden glass-card rounded-[3rem] shadow-2xl" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000" x-transition:enter-start="md:opacity-0 md:scale-95 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:scale-100 md:translate-y-0">
            <!-- Cover / Header BG (Elegant City Sky) -->
            <div class="h-48 bg-gradient-to-r from-primary/20 via-primary/5 to-transparent"></div>

            <div class="px-10 pb-10 -mt-20 relative z-10 flex flex-col md:flex-row items-center md:items-end gap-8">
                <!-- Avatar -->
                <div class="relative group">
                    <div class="w-40 h-40 rounded-[2.5rem] bg-background border-8 border-background p-1 shadow-2xl overflow-hidden">
                        <div class="w-full h-full bg-primary/10 rounded-[2rem] flex items-center justify-center text-primary font-black text-5xl">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                    </div>
                    <button class="absolute bottom-2 right-2 w-10 h-10 bg-primary text-primary-foreground rounded-2xl shadow-xl flex items-center justify-center hover:scale-110 transition-all border-4 border-background">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                    </button>
                </div>

                <!-- Info -->
                <div class="flex-1 text-center md:text-left space-y-3">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6">
                        <h1 class="text-4xl font-black tracking-tighter uppercase">{{ Auth::user()->name }}</h1>
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
            </div>
        </div>

        <!-- User Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-200" x-transition:enter-start="md:opacity-0 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:translate-y-0">
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

        <!-- Account Settings Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-300" x-transition:enter-start="md:opacity-0 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:translate-y-0">
            <!-- Personal Information -->
            <div class="glass-card rounded-[2.5rem] p-8 space-y-6 group hover:border-primary/30 transition-all duration-500">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <h3 class="text-lg font-black uppercase tracking-widest">Personal Info</h3>
                </div>

                <form class="space-y-4" x-ref="profileForm" @submit.prevent="submitProfile" method="POST" action="{{ route('updateProfile') }}">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Display Name</label>
                        <input type="text" name="name" :value="profileName" x-model="profileName" class="w-full px-6 py-3 glass-card rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all font-bold text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Email Address</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" disabled class="w-full px-6 py-3 glass-card rounded-2xl cursor-not-allowed opacity-50 font-bold text-sm">
                    </div>

                    <button type="submit" :disabled="profileLoading" class="w-full px-8 py-4 bg-primary text-primary-foreground font-black text-[10px] uppercase tracking-widest rounded-2xl hover:scale-[1.02] transition-all shadow-xl shadow-primary/20 disabled:opacity-70">Save Changes</button>

                    <p x-show="profileError" x-text="profileError" class="text-sm text-red-500"></p>
                    <p x-show="profileSuccess" x-text="profileSuccess" class="text-sm text-green-600"></p>
                </form>
            </div>

            <!-- Security -->
            <div class="glass-card rounded-[2.5rem] p-8 space-y-6 group hover:border-primary/30 transition-all duration-500">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3 class="text-lg font-black uppercase tracking-widest">Security</h3>
                </div>

                <form class="space-y-4" x-ref="passwordForm" @submit.prevent="submitPassword" method="POST" action="{{ route('changePassword') }}">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Current Password</label>
                        <input type="password" name="current_password" x-model="currentPassword" placeholder="••••••••" class="w-full px-6 py-3 glass-card rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all font-bold text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">New Password</label>
                        <input type="password" name="new_password" x-model="newPassword" placeholder="Min. 8 characters" class="w-full px-6 py-3 glass-card rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all font-bold text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="submit" :disabled="passwordLoading" class="px-4 py-4 bg-primary text-primary-foreground font-black text-[10px] uppercase tracking-widest rounded-2xl hover:scale-[1.02] transition-all shadow-xl shadow-primary/20 disabled:opacity-70">Update</button>
                        <a href="{{ route('forgot-password') }}" class="px-4 py-4 glass-card text-center font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-white/10 transition-all">Forgot?</a>
                    </div>

                    <p x-show="passwordError" x-text="passwordError" class="text-sm text-red-500"></p>
                    <p x-show="passwordSuccess" x-text="passwordSuccess" class="text-sm text-green-600"></p>
                </form>
            </div>
        </div>

        <!-- Account Management (Danger Zone) -->
        <div class="glass-card rounded-[2.5rem] p-8 space-y-6" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-400" x-transition:enter-start="md:opacity-0 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:translate-y-0">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="space-y-2 text-center md:text-left">
                    <h3 class="text-lg font-black uppercase tracking-widest text-destructive">Account Management</h3>
                    <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest leading-relaxed max-w-xl">To ensure security, account deletion must be processed by our support team. If you wish to permanently delete your account, please contact our Customer Services.</p>
                </div>
                <a href="{{ route('tickets') }}" class="whitespace-nowrap px-8 py-4 bg-destructive text-destructive-foreground font-black rounded-2xl hover:scale-105 transition-all shadow-xl shadow-destructive/20 uppercase tracking-widest text-[10px] flex items-center gap-2">
                    Contact Support
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Legal Links -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 pb-12" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-500" x-transition:enter-start="md:opacity-0 md:translate-y-8" x-transition:enter-end="md:opacity-100 md:translate-y-0">
            <a href="{{ route('terms-of-service') }}" class="glass-card rounded-[2.5rem] p-8 flex items-center justify-between group hover:border-primary/30 transition-all duration-500">
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Legal</p>
                    <h3 class="text-xl font-black uppercase tracking-tight group-hover:text-primary transition-colors">Terms Of Service</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
            </a>
            <a href="{{ route('privacy-policy') }}" class="glass-card rounded-[2.5rem] p-8 flex items-center justify-between group hover:border-primary/30 transition-all duration-500">
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Privacy</p>
                    <h3 class="text-xl font-black uppercase tracking-tight group-hover:text-primary transition-colors">Privacy Policies</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
