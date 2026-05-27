<x-app-layout>
    @auth
    <div class="px-page">
        <div class="px-page-inner space-y-8" style="max-width:780px;" x-data="profilePage(@js(Auth::user()->name))">

            {{-- Heading --}}
            <div>
                <h1 class="px-heading">My <span class="gold">Profile</span></h1>
                <p class="px-subheading">MANAGE YOUR ACCOUNT AND PREFERENCES</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Profile Info Card --}}
            <div class="px-card-static" style="padding:28px;">
                <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                    {{-- Pixel avatar placeholder --}}
                    <div style="width:72px;height:72px;background:rgba(245,158,11,0.1);border:3px solid rgba(245,158,11,0.25);display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            <h2 style="font-family:var(--font-sans);font-size:22px;font-weight:800;color:var(--foreground);letter-spacing:-0.02em;">{{ Auth::user()->name }}</h2>
                            @if(Auth::user()->role !== 'customer')
                                <span class="px-badge px-badge-gold">{{ strtoupper(Auth::user()->role) }}</span>
                            @endif
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render" style="color:var(--gold);flex-shrink:0;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            <span style="font-family:var(--font-sans);font-size:13px;color:var(--muted-foreground);">{{ Auth::user()->email }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="px-stat-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="stat-label">AVAILABLE POINTS</span>
                        <div style="width:32px;height:32px;background:rgba(245,158,11,0.1);border:2px solid rgba(245,158,11,0.25);display:flex;align-items:center;justify-content:center;color:var(--gold);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                        </div>
                    </div>
                    <p class="stat-value" style="color:var(--gold);">{{ number_format(Auth::user()->points_balance) }}</p>
                </div>
                <div class="px-stat-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="stat-label">INVENTORY SIZE</span>
                        <div style="width:32px;height:32px;background:rgba(59,130,246,0.1);border:2px solid rgba(59,130,246,0.25);display:flex;align-items:center;justify-content:center;color:#3b82f6;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        </div>
                    </div>
                    <p class="stat-value">
                        {{ \App\Models\ProductKey::whereHas('order', fn($q) => $q->where('user_id', Auth::id())->where('status', 'paid'))->count()
                         + Auth::user()->userDiscounts()->where('is_used', false)->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count() }}
                        <span style="font-family:var(--px);font-size:7px;letter-spacing:0.1em;color:var(--muted-foreground);margin-left:4px;">ITEMS</span>
                    </p>
                </div>
            </div>

            {{-- Personal Information --}}
            <div class="px-card-static" style="padding:28px;">
                <div class="px-section-header"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div><span class="section-title">PERSONAL INFORMATION</span></div>
                <form style="display:flex;flex-direction:column;gap:16px;" x-ref="profileForm" @submit.prevent="submitProfile" method="POST" action="{{ route('updateProfile') }}">
                    @csrf
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">DISPLAY NAME</label>
                        <input type="text" name="name" :value="profileName" x-model="profileName" class="px-input" style="padding:12px 16px;font-size:13px;">
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

            {{-- Delete Account (Danger Zone) --}}
            <div class="px-card-static" style="padding:24px;border-color:rgba(239,68,68,0.3);">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:40px;height:40px;background:rgba(239,68,68,0.1);border:2px solid rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;color:#ef4444;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:#ef4444;">Delete Account</h3>
                </div>
                <p style="font-family:var(--font-sans);font-size:12px;color:var(--text-dim);line-height:1.6;margin-bottom:16px;">Once your account is deleted, all of your data — including orders, inventory, points, and vouchers — will be permanently removed. This action cannot be undone.</p>
                <button type="button" @click="startDeleteAccount()" class="px-btn-danger" style="padding:12px 20px;font-size:7px;display:inline-flex;align-items:center;gap:6px;">
                    DELETE MY ACCOUNT
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/></svg>
                </button>
                <p x-show="deleteError" x-text="deleteError" style="font-family:var(--font-sans);font-size:13px;color:#ef4444;margin-top:10px;"></p>
            </div>

            {{-- Refer Friends --}}
            <a href="{{ route('referrals') }}" class="px-card" style="padding:22px;display:flex;align-items:center;justify-content:space-between;gap:16px;text-decoration:none;border-color:rgba(245,158,11,0.35);background:rgba(245,158,11,0.06);">
                <div style="display:flex;align-items:center;gap:16px;min-width:0;">
                    <div style="width:48px;height:48px;background:rgba(245,158,11,0.12);border:2px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11h-6"/><path d="M19 8v6"/></svg>
                    </div>
                    <div style="min-width:0;">
                        <p style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--gold);text-transform:uppercase;">EARN POINTS</p>
                        <h3 style="font-family:var(--font-sans);font-size:15px;font-weight:800;color:var(--foreground);margin-top:4px;">Refer Friends</h3>
                        <p style="font-family:var(--font-sans);font-size:11px;color:var(--text-dim);margin-top:2px;">Share your code &middot; earn when friends shop</p>
                    </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render" style="color:var(--gold);flex-shrink:0;"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>

            {{-- Legal Links --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" style="padding-bottom:40px;">
                <a href="{{ route('terms-of-service') }}" class="px-card" style="padding:20px;display:flex;align-items:center;justify-content:space-between;text-decoration:none;">
                    <div>
                        <p style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--muted-foreground);text-transform:uppercase;">LEGAL</p>
                        <h3 style="font-family:var(--font-sans);font-size:15px;font-weight:800;color:var(--foreground);margin-top:4px;">Terms Of Service</h3>
                    </div>
                    <div style="width:40px;height:40px;background:rgba(245,158,11,0.1);border:2px solid rgba(245,158,11,0.25);display:flex;align-items:center;justify-content:center;color:var(--gold);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                </a>
                <a href="{{ route('privacy-policy') }}" class="px-card" style="padding:20px;display:flex;align-items:center;justify-content:space-between;text-decoration:none;">
                    <div>
                        <p style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--muted-foreground);text-transform:uppercase;">PRIVACY</p>
                        <h3 style="font-family:var(--font-sans);font-size:15px;font-weight:800;color:var(--foreground);margin-top:4px;">Privacy Policies</h3>
                    </div>
                    <div style="width:40px;height:40px;background:rgba(245,158,11,0.1);border:2px solid rgba(245,158,11,0.25);display:flex;align-items:center;justify-content:center;color:var(--gold);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Password prompt modal for account deletion --}}
    <div x-show="showPasswordModal" x-cloak
         class="px-modal-overlay"
         @keydown.escape.window="showPasswordModal = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showPasswordModal = false">
        <div class="px-modal-box" style="max-width:400px;"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>
            <div style="display:flex;flex-direction:column;gap:18px;text-align:center;">
                <div style="width:56px;height:56px;background:rgba(239,68,68,0.12);border:3px solid #ef4444;color:#ef4444;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h3 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:#e8f0ff;letter-spacing:0.02em;">Enter Your Password</h3>
                <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.5;">To confirm account deletion, please enter your current password.</p>
                <input type="password" x-model="deletePassword" placeholder="Your password" class="px-input" style="padding:12px 16px;font-size:13px;text-align:center;" @keydown.enter="confirmDeleteWithPassword()">
                <p x-show="deleteError" x-text="deleteError" style="font-family:var(--font-sans);font-size:12px;color:#ef4444;"></p>
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:6px;">
                    <button type="button" @click="showPasswordModal = false" class="px-btn-gold" style="width:100%;padding:14px;font-size:7px;">CANCEL</button>
                    <button type="button" @click="confirmDeleteWithPassword()" :disabled="deletingAccount" class="px-btn-ghost" style="width:100%;padding:12px;font-size:7px;color:#ef4444;border-color:rgba(239,68,68,0.45);">
                        <span x-text="deletingAccount ? 'DELETING...' : 'CONFIRM DELETE'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function profilePage(initialName) {
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
            deleteError: '',
            deletePassword: '',
            deletingAccount: false,
            showPasswordModal: false,

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
            },

            async startDeleteAccount() {
                this.deleteError = '';
                // First confirmation
                const first = await window.openConfirmModal({
                    title: 'Delete Your Account?',
                    message: 'This action is permanent. All your data including orders, inventory, points, and vouchers will be deleted forever.',
                    confirmLabel: 'YES, I WANT TO DELETE',
                    cancelLabel: 'NO, KEEP MY ACCOUNT'
                });
                if (!first) return;

                // Second confirmation
                const second = await window.openConfirmModal({
                    title: 'Final Warning',
                    message: 'This is your last chance. Once deleted, your account and all associated data cannot be recovered. Are you absolutely sure?',
                    confirmLabel: 'DELETE PERMANENTLY',
                    cancelLabel: 'CANCEL'
                });
                if (!second) return;

                // Prompt for password
                this.deletePassword = '';
                this.showPasswordModal = true;
            },

            async confirmDeleteWithPassword() {
                if (!this.deletePassword || this.deletingAccount) return;
                this.deleteError = '';
                this.deletingAccount = true;

                try {
                    const r = await fetch('{{ route("deleteAccount") }}', {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ password: this.deletePassword })
                    });
                    const data = await r.json().catch(() => ({}));
                    this.deletingAccount = false;

                    if (r.ok) {
                        this.showPasswordModal = false;
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Account deleted successfully. Goodbye!', type: 'success' } }));
                        setTimeout(() => { window.location.href = data.redirect || '/'; }, 1500);
                        return;
                    }

                    this.deleteError = data.errors?.password?.[0] || data.message || 'Unable to delete account.';
                } catch (e) {
                    this.deletingAccount = false;
                    this.deleteError = 'Network error. Please try again.';
                }
            }
        };
    }
    </script>
    @endauth
</x-app-layout>
