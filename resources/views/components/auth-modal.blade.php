<div x-data="{
    open: false,
    tab: 'login',
    email: '',
    password: '',
    username: '',
    showLoginPassword: false,
    showSignupPassword: false,
    loginError: '',
    loginLoading: false,
    signupError: '',
    signupLoading: false,
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
            window.location.href = data.redirect || '{{ route('home') }}';
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
            // Auto-login: redirect to home
            window.location.href = data.redirect || '{{ route('home') }}';
            return;
        }

        // Show first validation error or general message
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
        const queryTab = '{{ request()->query('auth', '') }}';
        if (queryTab === 'login' || queryTab === 'signup') {
            this.tab = queryTab;
            this.open = true;
        }

        window.addEventListener('open-auth-modal', (e) => {
            this.tab = e.detail.tab || 'login';
            this.open = true;
        });
    }
}" x-show="open" class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" x-cloak>
    <div @click.away="open = false"
         class="w-full max-w-md retro-card"
         x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-90 translate-y-4">
        <div class="flex border-b border-border">
            <button @click="tab = 'login'" :class="tab === 'login' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground'" class="flex-1 py-4 text-sm font-semibold transition-colors">
                Login
            </button>
            <button @click="tab = 'signup'" :class="tab === 'signup' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground'" class="flex-1 py-4 text-sm font-semibold transition-colors">
                Sign Up
            </button>
        </div>

        <div class="p-6">
            <!-- Login Form -->
            <form x-show="tab === 'login'" x-ref="loginForm" @submit.prevent="submitLogin" method="POST" action="{{ route('logAuth') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium">Email</label>
                    <input type="email" name="email" x-model="email" required class="w-full px-3 py-2 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="name@email.com">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Password</label>
                    <div class="relative">
                        <input :type="showLoginPassword ? 'text' : 'password'" name="password" x-model="password" required class="w-full px-3 py-2 pr-10 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="••••••••">
                        <button type="button" @click="showLoginPassword = !showLoginPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors" tabindex="-1">
                            <svg x-show="!showLoginPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showLoginPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" :disabled="loginLoading" class="w-full py-2 bg-primary text-primary-foreground font-bold rounded-md hover:opacity-90 transition-opacity tracking-widest disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-text="loginLoading ? 'Logging In...' : 'Login Now'"></span>
                </button>

                <p x-show="loginError" x-text="loginError" class="text-sm text-red-500 text-center"></p>
            </form>

            <!-- Signup Form -->
            <form x-show="tab === 'signup'" x-ref="signupForm" @submit.prevent="submitSignup" method="POST" action="{{ route('regAuth') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium">Username</label>
                    <input type="text" name="name" x-model="username" required class="w-full px-3 py-2 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="PixelWalker">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Email</label>
                    <input type="email" name="email" x-model="email" required class="w-full px-3 py-2 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="name@email.com">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Password</label>
                    <div class="relative">
                        <input :type="showSignupPassword ? 'text' : 'password'" name="password" x-model="password" @input="checkPasswordStrength($event.target.value)" required minlength="8" class="w-full px-3 py-2 pr-10 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="Min. 8 characters">
                        <button type="button" @click="showSignupPassword = !showSignupPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors" tabindex="-1">
                            <svg x-show="!showSignupPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showSignupPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                    <!-- Password Strength Indicator -->
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

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="signup-tos" required class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="signup-tos" class="text-xs text-muted-foreground">I agree to the <a href="{{ route('terms-of-service') }}" class="text-primary hover:underline" target="_blank">Terms of Service</a></label>
                </div>

                <button type="submit" :disabled="signupLoading" class="w-full py-2 bg-primary text-primary-foreground font-bold rounded-md hover:opacity-90 transition-opacity tracking-widest disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-text="signupLoading ? 'Creating Account...' : 'Create Account'"></span>
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
</div>

<style>
[x-cloak] { display: none !important; }
</style>
