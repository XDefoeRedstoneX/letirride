<div x-data="{ 
    open: false, 
    tab: 'login',
    email: '',
    password: '',
    username: '',
    showPassword: false,
    showSignupPassword: false,
    loginError: '',
    loginLoading: false,
    signupError: '',
    signupLoading: false,
    forgotMode: false,
    forgotSent: false,
    forgotLoading: false,
    forgotError: '',
    referralCode: '',
    passwordStrength: 0,
    get strengthLabel() {
        const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
        return labels[this.passwordStrength] || '';
    },
    get strengthColor() {
        const colors = ['', 'bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
        return colors[this.passwordStrength] || '';
    },
    checkPasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        this.passwordStrength = score;
    },
    async submitLogin() {
        this.loginError = '';
        this.loginLoading = true;

        const form = this.$refs.loginForm;
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });

        const data = await response.json().catch(() => ({}));
        this.loginLoading = false;

        if (response.ok) {
            window.location.href = data.redirect || window.location.href;
            return;
        }

        this.loginError = data.message || data.errors?.email?.[0] || 'Those credentials do not match our records.';
    },
    async submitForgot() {
        this.forgotError = '';
        if (!this.email) {
            this.forgotError = 'Please enter your account email first.';
            return;
        }
        this.forgotLoading = true;

        const form = this.$refs.forgotForm;
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });

        const data = await response.json().catch(() => ({}));
        this.forgotLoading = false;

        if (response.ok) {
            this.forgotSent = true;
            return;
        }

        this.forgotError = data.message || data.errors?.email?.[0] || 'Something went wrong. Please try again.';
    },
    async submitSignup() {
        this.signupError = '';
        this.signupLoading = true;

        const form = this.$refs.signupForm;
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });

        const data = await response.json().catch(() => ({}));
        this.signupLoading = false;

        if (response.ok) {
            window.location.href = data.redirect || window.location.href;
            return;
        }

        const errors = data.errors || {};
        if (errors.name?.[0]) {
            this.signupError = errors.name[0];
        } else if (errors.email?.[0]) {
            this.signupError = errors.email[0];
        } else if (errors.password?.[0]) {
            this.signupError = errors.password[0];
        } else {
            this.signupError = data.message || 'Registration failed. Please try again.';
        }
    },
    init() {
        window.addEventListener('open-auth-modal', (e) => {
            this.tab = e.detail.tab || 'login';
            this.open = true;
        });

        // ?ref=CODE in the URL pre-fills the signup field and pops the modal
        // to the signup tab so the visitor lands ready to convert.
        const params = new URLSearchParams(window.location.search);
        const ref = params.get('ref');
        if (ref) {
            this.referralCode = ref.toUpperCase().slice(0, 16);
            this.tab = 'signup';
            this.open = true;
        }
    }
}" x-show="open" class="modal-overlay" x-cloak
   x-transition:enter="transition ease-out duration-200"
   x-transition:enter-start="opacity-0"
   x-transition:enter-end="opacity-100"
   x-transition:leave="transition ease-in duration-150"
   x-transition:leave-start="opacity-100"
   x-transition:leave-end="opacity-0">

    {{-- 8-Piece Pixel Frame Modal --}}
    <div @click.away="open = false"
         class="px-frame"
         style="max-width: 440px; width: 100%; max-height: calc(100vh - 32px);"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Frame border pieces --}}
        <div class="px-frame-tl"></div>
        <div class="px-frame-t"></div>
        <div class="px-frame-tr"></div>
        <div class="px-frame-l"></div>

        {{-- Content inside the frame --}}
        <div class="px-frame-content">
            {{-- Modern Tab Header --}}
            <div class="p-4 pb-0" x-show="!forgotMode">
                <div class="flex p-1 bg-foreground/5 rounded-2xl w-full">
                    <button @click="tab = 'login'"
                            :class="tab === 'login' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                            class="flex-1 py-2 text-sm font-bold rounded-xl transition-all">
                        Login
                    </button>
                    <button @click="tab = 'signup'"
                            :class="tab === 'signup' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                            class="flex-1 py-2 text-sm font-bold rounded-xl transition-all">
                        Sign Up
                    </button>
                </div>
            </div>

            <div class="p-4">
                {{-- Login Form --}}
                <form x-show="tab === 'login' && !forgotMode" x-ref="loginForm" @submit.prevent="submitLogin" method="POST" action="{{ route('logAuth') }}" class="space-y-3">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Email</label>
                        <input type="email" name="email" x-model="email" required class="w-full px-3 py-2 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="name@email.com">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Password</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" x-model="password" required class="w-full px-3 py-2 pr-10 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="••••••••">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Pixelated Login Button --}}
                    <button type="submit" :disabled="loginLoading" class="modal-btn-primary" style="padding: 12px; width: 100%; font-size: 8px;">
                        <span x-text="loginLoading ? 'LOGGING IN...' : 'LOGIN NOW'"></span>
                    </button>

                    <div x-show="loginError" x-text="loginError" class="text-center text-sm text-red-500 font-bold"></div>

                    {{-- Forgot password entry --}}
                    <div class="text-center">
                        <button type="button" @click="forgotMode = true; forgotSent = false; forgotError = ''"
                                class="text-xs font-black uppercase tracking-widest hover:underline"
                                style="color:#ef4444;">
                            Forgot password?
                        </button>
                    </div>
                </form>

                {{-- Forgot Password panel --}}
                <div x-show="forgotMode" class="space-y-4">
                    {{-- Confirmation prompt + email --}}
                    <div x-show="!forgotSent" class="space-y-4">
                        <div class="text-center space-y-1">
                            <div class="mx-auto flex items-center justify-center" style="width:52px;height:52px;background:rgba(239,68,68,0.12);border:3px solid rgba(239,68,68,0.35);color:#ef4444;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <h3 class="font-black text-base">Reset your password</h3>
                            <p class="text-xs text-muted-foreground">We'll send a reset link to your account email. Confirm the address below.</p>
                        </div>

                        <form x-ref="forgotForm" @submit.prevent="submitForgot" method="POST" action="{{ route('password.email') }}" class="space-y-3">
                            @csrf
                            <div class="space-y-1">
                                <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Account Email</label>
                                <input type="email" name="email" x-model="email" required class="w-full px-3 py-2 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="name@email.com">
                            </div>

                            <button type="submit" :disabled="forgotLoading" class="modal-btn-primary" style="padding: 12px; width: 100%; font-size: 8px;">
                                <span x-text="forgotLoading ? 'SENDING...' : 'SEND RESET LINK'"></span>
                            </button>

                            <div x-show="forgotError" x-text="forgotError" class="text-center text-sm text-red-500 font-bold"></div>
                        </form>
                    </div>

                    {{-- Sent confirmation --}}
                    <div x-show="forgotSent" class="text-center space-y-3 py-2">
                        <div class="mx-auto flex items-center justify-center" style="width:56px;height:56px;background:rgba(34,197,94,0.12);border:3px solid #22c55e;color:#22c55e;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </div>
                        <h3 class="font-black text-base">Check your inbox</h3>
                        <p class="text-xs text-muted-foreground">If an account exists for <span class="font-bold text-foreground" x-text="email"></span>, a password reset link is on its way.</p>
                    </div>

                    {{-- Back to login --}}
                    <button type="button" @click="forgotMode = false"
                            class="w-full text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-foreground transition-colors">
                        &larr; Back to login
                    </button>
                </div>

                {{-- Signup Form --}}
                <form x-show="tab === 'signup' && !forgotMode" x-ref="signupForm" @submit.prevent="submitSignup" method="POST" action="{{ route('regAuth') }}" class="space-y-3">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Username</label>
                        <input type="text" name="name" x-model="username" required class="w-full px-3 py-2 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="PixelWalker">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Email</label>
                        <input type="email" name="email" x-model="email" required class="w-full px-3 py-2 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="name@email.com">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Password</label>
                        <div class="relative">
                            <input :type="showSignupPassword ? 'text' : 'password'" name="password" x-model="password" @input="checkPasswordStrength(password)" required class="w-full px-3 py-2 pr-10 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="Min. 8 characters">
                            <button type="button" @click="showSignupPassword = !showSignupPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                <svg x-show="!showSignupPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showSignupPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                            </button>
                        </div>
                        {{-- Password Strength Indicator --}}
                        <div x-show="password.length > 0" class="space-y-1" x-transition>
                            <div class="flex gap-1">
                                <template x-for="i in 4" :key="i">
                                    <div class="h-1 flex-1 rounded-full transition-all duration-300" :class="i <= passwordStrength ? strengthColor : 'bg-foreground/10'"></div>
                                </template>
                            </div>
                            <p class="text-[10px] font-bold uppercase tracking-widest" :class="{
                                'text-red-500': passwordStrength === 1,
                                'text-orange-500': passwordStrength === 2,
                                'text-yellow-500': passwordStrength === 3,
                                'text-green-500': passwordStrength === 4,
                            }" x-text="strengthLabel"></p>
                        </div>
                    </div>

                    {{-- Referral code carried silently from a ?ref=CODE deep link --}}
                    <input type="hidden" name="referral_code" :value="referralCode">

                    {{-- Pixelated Create Account Button --}}
                    <button type="submit" :disabled="signupLoading" class="modal-btn-primary" style="padding: 12px; width: 100%; font-size: 8px;">
                        <span x-text="signupLoading ? 'CREATING...' : 'CREATE ACCOUNT'"></span>
                    </button>

                    <p x-show="signupError" x-text="signupError" class="text-sm text-red-500 text-center"></p>
                </form>

                {{-- Divider --}}
                <div class="relative my-3" x-show="!forgotMode">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t-2 border-input"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-background px-3 text-xs font-black text-muted-foreground uppercase tracking-widest">or</span>
                    </div>
                </div>

                {{-- Google OAuth --}}
                <a x-show="!forgotMode" href="{{ route('auth.google') }}"
                   class="flex items-center justify-center gap-3 w-full px-4 py-2 bg-background border-2 border-input rounded-xl font-bold text-sm hover:border-primary/50 hover:bg-primary/5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        <path fill="none" d="M0 0h48v48H0z"/>
                    </svg>
                    <span>Continue with Google</span>
                </a>

                <div class="mt-3 text-center" x-show="!forgotMode">
                    <p class="text-xs text-muted-foreground">
                        By logging in, you agree to our Terms & Conditions.
                    </p>
                </div>
            </div>
        </div>

        <div class="px-frame-r"></div>
        <div class="px-frame-bl"></div>
        <div class="px-frame-b"></div>
        <div class="px-frame-br"></div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
