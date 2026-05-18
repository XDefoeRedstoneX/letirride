<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8" style="max-width:680px;"
             x-data="settingsPage(@js(Auth::check() ? Auth::user()->name : ''))">
            <div>
                <h1 class="px-heading">Account <span class="gold">Settings</span></h1>
                <p class="px-subheading">MANAGE YOUR PERSONAL INFORMATION AND PREFERENCES</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Profile Info --}}
            <div class="px-card-static" style="padding:28px;">
                <div class="px-section-header"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div><span class="section-title">PERSONAL INFORMATION</span></div>
                <form style="display:flex;flex-direction:column;gap:16px;" x-ref="profileForm" @submit.prevent="submitProfile" method="POST" action="{{ route('updateProfile') }}">
                    @csrf
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">DISPLAY NAME</label>
                        <input type="text" name="name" value="{{ Auth::user()->name }}" x-model="profileName" class="px-input" style="padding:12px 16px;font-size:13px;">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">EMAIL ADDRESS</label>
                        <input type="email" value="{{ Auth::user()->email }}" disabled class="px-input" style="padding:12px 16px;font-size:13px;opacity:0.5;cursor:not-allowed;">
                        <p style="font-family:var(--px);font-size:5px;color:var(--text-dim);letter-spacing:0.1em;">EMAIL CANNOT BE CHANGED ONCE VERIFIED</p>
                    </div>
                    <button type="submit" :disabled="profileLoading" class="px-btn-gold" style="padding:14px;font-size:7px;">SAVE CHANGES</button>
                    <p x-show="profileError" x-text="profileError" style="font-family:var(--font-sans);font-size:13px;color:#ef4444;"></p>
                    <p x-show="profileSuccess" x-text="profileSuccess" style="font-family:var(--font-sans);font-size:13px;color:#22c55e;"></p>
                </form>
            </div>

            {{-- Security --}}
            <div class="px-card-static" style="padding:28px;">
                <div class="px-section-header"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div><span class="section-title">SECURITY</span></div>
                <form style="display:flex;flex-direction:column;gap:16px;" x-ref="passwordForm" @submit.prevent="submitPassword" method="POST" action="{{ route('changePassword') }}">
                    @csrf
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">CURRENT PASSWORD</label>
                        <input type="password" name="current_password" x-model="currentPassword" placeholder="••••••••" class="px-input" style="padding:12px 16px;font-size:13px;">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">NEW PASSWORD</label>
                        <input type="password" name="new_password" x-model="newPassword" placeholder="Min. 8 characters" class="px-input" style="padding:12px 16px;font-size:13px;">
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" :disabled="passwordLoading" class="px-btn-gold" style="padding:14px 24px;font-size:7px;">UPDATE PASSWORD</button>
                        <a href="{{ route('forgot-password') }}" class="px-btn-ghost" style="padding:14px 24px;font-size:7px;text-decoration:none;">FORGOT PASSWORD</a>
                    </div>
                    <p x-show="passwordError" x-text="passwordError" style="font-family:var(--font-sans);font-size:13px;color:#ef4444;"></p>
                    <p x-show="passwordSuccess" x-text="passwordSuccess" style="font-family:var(--font-sans);font-size:13px;color:#22c55e;"></p>
                </form>
            </div>

            {{-- Danger Zone --}}
            <div class="px-card-static" style="padding:24px;border-color:rgba(239,68,68,0.3);">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:40px;height:40px;background:rgba(239,68,68,0.1);border:2px solid rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;color:#ef4444;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:#ef4444;">Account Management</h3>
                </div>
                <p style="font-family:var(--font-sans);font-size:12px;color:var(--text-dim);line-height:1.6;margin-bottom:16px;">To ensure security, account deletion must be processed by our support team. Contact Customer Services to permanently delete your account.</p>
                <a href="{{ route('tickets') }}" class="px-btn-gold" style="padding:12px 20px;font-size:7px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;background:#ef4444;box-shadow:3px 3px 0 #991b1b;">
                    CONTACT SUPPORT
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    <script>
    function settingsPage(initialName) {
        return {
            profileName: initialName,
            profileError: '',
            profileSuccess: '',
            profileLoading: false,
            passwordError: '',
            passwordSuccess: '',
            passwordLoading: false,
            currentPassword: '',
            newPassword: '',

            async submitProfile() {
                this.profileError = ''; this.profileSuccess = ''; this.profileLoading = true;
                const form = this.$refs.profileForm;
                const r = await fetch(form.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(form) });
                const data = await r.json().catch(() => ({}));
                this.profileLoading = false;
                if (r.ok) { this.profileName = data.name || this.profileName; this.profileSuccess = data.message || 'Profile updated.'; return; }
                this.profileError = data.errors?.name?.[0] || data.message || 'Unable to update.';
            },

            async submitPassword() {
                this.passwordError = ''; this.passwordSuccess = ''; this.passwordLoading = true;
                const form = this.$refs.passwordForm;
                const r = await fetch(form.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(form) });
                const data = await r.json().catch(() => ({}));
                this.passwordLoading = false;
                if (r.ok) { this.currentPassword = ''; this.newPassword = ''; this.passwordSuccess = data.message || 'Password updated.'; return; }
                this.passwordError = data.errors?.current_password?.[0] || data.errors?.new_password?.[0] || data.message || 'Unable to update.';
            }
        };
    }
    </script>
</x-app-layout>
