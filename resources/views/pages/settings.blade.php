<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-8" x-data="{
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
    }">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Account Settings</h1>
            <p class="text-muted-foreground">Manage your personal information and preferences.</p>
        </div>

        <div class="space-y-6">
            <!-- Profile Info -->
            <div class="bg-card border border-border rounded-2xl p-6 space-y-6">
                <h3 class="text-lg font-bold">Personal Information</h3>

                <form class="space-y-4" x-ref="profileForm" @submit.prevent="submitProfile" method="POST" action="{{ route('updateProfile') }}">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Display Name</label>
                        <input type="text" name="name" value="{{ Auth::user()->name}}" x-model="profileName" class="w-full px-4 py-2 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/50 outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">Email Address</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" disabled class="w-full px-4 py-2 bg-muted border border-border rounded-xl cursor-not-allowed opacity-70">
                        <p class="text-[10px] text-muted-foreground">Email cannot be changed once verified.</p>
                    </div>

                    <button type="submit" :disabled="profileLoading" class="px-6 py-2 bg-primary text-primary-foreground font-bold rounded-xl hover:opacity-90 transition-opacity disabled:opacity-70 disabled:cursor-not-allowed">Save Changes</button>

                    <p x-show="profileError" x-text="profileError" class="text-sm text-red-500"></p>
                    <p x-show="profileSuccess" x-text="profileSuccess" class="text-sm text-green-600"></p>
                </form>
            </div>

            <!-- Security -->
            <div class="bg-card border border-border rounded-2xl p-6 space-y-6">
                <h3 class="text-lg font-bold">Security</h3>

                <form class="space-y-4" x-ref="passwordForm" @submit.prevent="submitPassword" method="POST" action="{{ route('changePassword') }}">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Current Password</label>
                        <input type="password" name="current_password" x-model="currentPassword" placeholder="••••••••" class="w-full px-4 py-2 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/50 outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">New Password</label>
                        <input type="password" name="new_password" x-model="newPassword" placeholder="Min. 8 characters" class="w-full px-4 py-2 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/50 outline-none transition-all">
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" :disabled="passwordLoading" class="px-6 py-3 bg-primary text-primary-foreground font-black text-[10px] uppercase tracking-widest rounded-xl hover:scale-105 transition-all shadow-lg shadow-primary/20 disabled:opacity-70 disabled:cursor-not-allowed">Update Password</button>
                        <a href="{{ route('forgot-password') }}" class="px-6 py-3 bg-card border-2 border-border text-foreground font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-foreground/5 transition-all">Forgot Password</a>
                    </div>

                    <p x-show="passwordError" x-text="passwordError" class="text-sm text-red-500"></p>
                    <p x-show="passwordSuccess" x-text="passwordSuccess" class="text-sm text-green-600"></p>
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
