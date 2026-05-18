<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner" style="max-width:480px;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;gap:24px;"
             x-data="forgotPasswordPage('{{ Auth::user()->email }}')">

            {{-- Form State --}}
            <div x-show="!sent" style="width:100%;display:flex;flex-direction:column;gap:24px;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div style="text-align:center;">
                    <div style="width:64px;height:64px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);margin:0 auto 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                    </div>
                    <h1 class="px-heading">Reset <span class="gold">Password</span></h1>
                    <p class="px-subheading">WE WILL SEND A RESET LINK TO YOUR EMAIL</p>
                </div>

                <div class="px-card-static" style="padding:28px;display:flex;flex-direction:column;gap:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">EMAIL ADDRESS</label>
                        <input type="email" x-model="email" class="px-input" style="padding:14px 18px;font-size:13px;">
                    </div>
                    <button @click="sendEmail()" :disabled="loading" class="px-btn-gold" style="width:100%;padding:18px;font-size:8px;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <template x-if="loading"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></template>
                        <span x-text="loading ? 'SENDING...' : 'SEND RESET LINK'"></span>
                    </button>
                    <a href="{{ route('settings') }}" style="display:block;text-align:center;font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.1em;text-decoration:none;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-dim)'">BACK TO SETTINGS</a>
                </div>
            </div>

            {{-- Success State --}}
            <div x-show="sent" style="width:100%;display:flex;flex-direction:column;align-items:center;gap:20px;text-align:center;" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                <div style="width:80px;height:80px;background:rgba(34,197,94,0.15);border:3px solid #22c55e;display:flex;align-items:center;justify-content:center;color:#22c55e;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                </div>
                <h2 class="px-heading">Email <span style="color:#22c55e;">Sent!</span></h2>
                <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.6;">Check your inbox. We've sent a password reset link to <span style="color:var(--gold);font-weight:800;" x-text="email"></span></p>
                <a href="{{ route('settings') }}" class="px-btn-ghost" style="padding:14px 28px;font-size:7px;text-decoration:none;margin-top:8px;">RETURN TO SETTINGS</a>
            </div>
        </div>
    </div>

    <script>
    function forgotPasswordPage(initialEmail) {
        return {
            email: initialEmail,
            sent: false,
            loading: false,
            sendEmail() {
                this.loading = true;
                setTimeout(() => {
                    this.loading = false;
                    this.sent = true;
                }, 2000);
            }
        };
    }
    </script>
</x-app-layout>
