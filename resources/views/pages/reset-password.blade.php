<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner" style="max-width:480px;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;gap:24px;"
             x-data="{
                showPassword: false,
                showConfirm: false,
                pw: '',
                pwc: '',
                get mismatch() { return this.pwc.length > 0 && this.pw !== this.pwc; },
                get canSubmit() { return this.pw.length >= 8 && this.pw === this.pwc; },
             }">

            @unless ($valid)
                {{-- Dead link: used, expired, or invalid --}}
                <div style="width:100%;display:flex;flex-direction:column;align-items:center;gap:20px;text-align:center;">
                    <div style="width:72px;height:72px;background:rgba(239,68,68,0.12);border:3px solid rgba(239,68,68,0.4);display:flex;align-items:center;justify-content:center;color:#ef4444;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                    </div>
                    <h1 class="px-heading">Link <span style="color:#ef4444;">Expired</span></h1>
                    <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.6;">This password reset link is invalid or has already been used. Reset links can only be used once. Please request a new one.</p>
                    <a href="{{ route('home') }}" class="px-btn-gold" style="padding:14px 28px;font-size:7px;text-decoration:none;margin-top:4px;">BACK TO HOME</a>
                </div>
            @else
            <div style="text-align:center;">
                <div style="width:64px;height:64px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);margin:0 auto 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h1 class="px-heading">New <span class="gold">Password</span></h1>
                <p class="px-subheading">CHOOSE A NEW PASSWORD FOR YOUR ACCOUNT</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" @submit="if (!canSubmit) $event.preventDefault()" class="px-card-static" style="width:100%;padding:28px;display:flex;flex-direction:column;gap:16px;">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">EMAIL ADDRESS</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required class="px-input" style="padding:14px 18px;font-size:13px;">
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">NEW PASSWORD</label>
                    <div style="position:relative;">
                        <input :type="showPassword ? 'text' : 'password'" name="password" x-model="pw" required minlength="8" class="px-input" style="padding:14px 44px 14px 18px;font-size:13px;width:100%;" placeholder="Min. 8 characters">
                        <button type="button" @click="showPassword = !showPassword" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-dim);cursor:pointer;">
                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">CONFIRM PASSWORD</label>
                    <div style="position:relative;">
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" x-model="pwc" required minlength="8" class="px-input" style="padding:14px 44px 14px 18px;font-size:13px;width:100%;" placeholder="Re-enter password" :style="mismatch ? 'border-color:#ef4444;' : ''">
                        <button type="button" @click="showConfirm = !showConfirm" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-dim);cursor:pointer;">
                            <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <p x-show="mismatch" x-cloak style="color:#ef4444;font-family:var(--font-sans);font-size:12px;font-weight:700;text-align:center;margin:0;">Passwords do not match.</p>

                @if ($errors->any())
                    <p style="color:#ef4444;font-family:var(--font-sans);font-size:12px;font-weight:700;text-align:center;margin:0;">{{ $errors->first() }}</p>
                @endif

                <button type="submit" :disabled="!canSubmit" :style="!canSubmit ? 'opacity:0.55;cursor:not-allowed;' : ''" class="px-btn-gold" style="width:100%;padding:18px;font-size:8px;">RESET PASSWORD</button>
            </form>
            @endunless
        </div>
    </div>
</x-app-layout>
