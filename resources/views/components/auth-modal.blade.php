<div x-data="{ 
    open: false, 
    tab: 'login',
    email: '',
    password: '',
    username: '',
    showPassword: false,
    showSignupPassword: false,
    acceptTos: false,
    loginError: '',
    loginLoading: false,
    signupError: '',
    signupLoading: false,
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
         style="max-width: 440px; width: 100%;"
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
            <div class="p-6 pb-0">
                <div class="flex p-1 bg-foreground/5 rounded-2xl w-full">
                    <button @click="tab = 'login'"
                            :class="tab === 'login' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                            class="flex-1 py-3 text-sm font-bold rounded-xl transition-all">
                        Login
                    </button>
                    <button @click="tab = 'signup'"
                            :class="tab === 'signup' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                            class="flex-1 py-3 text-sm font-bold rounded-xl transition-all">
                        Sign Up
                    </button>
                </div>
            </div>

            <div class="p-6">
                {{-- Login Form --}}
                <form x-show="tab === 'login'" x-ref="loginForm" @submit.prevent="submitLogin" method="POST" action="{{ route('logAuth') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Email</label>
                        <input type="email" name="email" x-model="email" required class="w-full px-4 py-3 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="name@email.com">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Password</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" x-model="password" required class="w-full px-4 py-3 pr-10 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="••••••••">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Pixelated Login Button --}}
                    <button type="submit" :disabled="loginLoading" class="modal-btn-primary" style="padding: 16px; width: 100%; font-size: 8px;">
                        <span x-text="loginLoading ? 'LOGGING IN...' : 'LOGIN NOW'"></span>
                    </button>

                    <div x-show="loginError" x-text="loginError" class="text-center text-sm text-red-500 font-bold"></div>
                </form>

                {{-- Signup Form --}}
                <form x-show="tab === 'signup'" x-ref="signupForm" @submit.prevent="submitSignup" method="POST" action="{{ route('regAuth') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Username</label>
                        <input type="text" name="name" x-model="username" required class="w-full px-4 py-3 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="PixelWalker">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Email</label>
                        <input type="email" name="email" x-model="email" required class="w-full px-4 py-3 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="name@email.com">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Password</label>
                        <div class="relative">
                            <input :type="showSignupPassword ? 'text' : 'password'" name="password" x-model="password" @input="checkPasswordStrength(password)" required class="w-full px-4 py-3 pr-10 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="Min. 8 characters">
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

                    {{-- Optional referral code --}}
                    <div class="space-y-2">
                        <label class="text-xs font-black text-muted-foreground uppercase tracking-widest">Referral Code <span class="text-muted-foreground/60">(optional)</span></label>
                        <input type="text" name="referral_code" x-model="referralCode" maxlength="16"
                               @input="referralCode = referralCode.toUpperCase()"
                               class="referral-signup-input"
                               placeholder="GOTACODE">
                        <p class="referral-signup-hint">Friend gave you a code? Pop it in to earn bonus points.</p>
                    </div>

                    {{-- TOS & Privacy (only on signup) --}}
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="signup-tos" x-model="acceptTos" required class="w-4 h-4 mt-0.5 rounded border-border text-primary focus:ring-primary/50 bg-background">
                            <label for="signup-tos" class="text-xs font-bold text-muted-foreground uppercase tracking-widest leading-tight">
                                I agree to the <a href="{{ route('terms-of-service') }}" class="text-primary hover:underline" target="_blank">Terms of Service</a>
                            </label>
                        </div>
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="signup-pp" required class="w-4 h-4 mt-0.5 rounded border-border text-primary focus:ring-primary/50 bg-background">
                            <label for="signup-pp" class="text-xs font-bold text-muted-foreground uppercase tracking-widest leading-tight">
                                I accept the <a href="{{ route('privacy-policy') }}" class="text-primary hover:underline" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                    </div>

                    {{-- Pixelated Create Account Button --}}
                    <button type="submit" :disabled="signupLoading || !acceptTos" class="modal-btn-primary" style="padding: 16px; width: 100%; font-size: 8px;">
                        <span x-text="signupLoading ? 'CREATING...' : 'CREATE ACCOUNT'"></span>
                    </button>

                    <p x-show="signupError" x-text="signupError" class="text-sm text-red-500 text-center"></p>
                </form>

                <div class="mt-6 text-center">
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
