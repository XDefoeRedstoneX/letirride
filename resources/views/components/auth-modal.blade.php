<div x-data="{ 
    open: false, 
    tab: 'login',
    email: '',
    password: '',
    username: '',
    showPassword: false,
    showSignupPassword: false,
    acceptTos: false,
    acceptPrivacy: false,
    loginError: '',
    loginLoading: false,
    signupError: '',
    signupLoading: false,
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
    }
}" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" x-cloak>
    <div @click.away="open = false" 
         class="relative w-full max-w-md glass-card text-foreground shadow-2xl rounded-2xl overflow-hidden"
         x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        <div class="flex border-b border-border">
            <button @click="tab = 'login'" :class="tab === 'login' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground'" class="flex-1 py-4 text-xs font-black uppercase tracking-widest transition-colors">
                Login
            </button>
            <button @click="tab = 'signup'" :class="tab === 'signup' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground'" class="flex-1 py-4 text-xs font-black uppercase tracking-widest transition-colors">
                Sign Up
            </button>
        </div>

        <div class="p-6">
            <!-- Login Form -->
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
                
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="login-tos" x-model="acceptTos" required class="w-4 h-4 mt-0.5 rounded border-border text-primary focus:ring-primary/50 bg-background">
                        <label for="login-tos" class="text-xs font-bold text-muted-foreground uppercase tracking-widest leading-tight">
                            I agree to the <a href="{{ route('terms-of-service') }}" class="text-primary hover:underline">Terms of Service</a>
                        </label>
                    </div>
                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="login-pp" x-model="acceptPrivacy" required class="w-4 h-4 mt-0.5 rounded border-border text-primary focus:ring-primary/50 bg-background">
                        <label for="login-pp" class="text-xs font-bold text-muted-foreground uppercase tracking-widest leading-tight">
                            I accept the <a href="{{ route('privacy-policy') }}" class="text-primary hover:underline">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <button type="submit" :disabled="loginLoading" class="w-full py-3 bg-primary text-primary-foreground font-black rounded-xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 uppercase tracking-widest text-xs disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-text="loginLoading ? 'Logging In...' : 'Login Now'"></span>
                </button>

                <div x-show="loginError" x-text="loginError" class="text-center text-sm text-red-500 font-bold"></div>
            </form>

            <!-- Signup Form -->
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
                        <input :type="showSignupPassword ? 'text' : 'password'" name="password" x-model="password" required class="w-full px-4 py-3 pr-10 bg-background border-2 border-input rounded-xl focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm" placeholder="Min. 8 characters">
                        <button type="button" @click="showSignupPassword = !showSignupPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                            <svg x-show="!showSignupPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showSignupPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="signup-tos" x-model="acceptTos" required class="w-4 h-4 mt-0.5 rounded border-border text-primary focus:ring-primary/50 bg-background">
                        <label for="signup-tos" class="text-xs font-bold text-muted-foreground uppercase tracking-widest leading-tight">
                            I agree to the <a href="{{ route('terms-of-service') }}" class="text-primary hover:underline">Terms of Service</a>
                        </label>
                    </div>
                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="signup-pp" x-model="acceptPrivacy" required class="w-4 h-4 mt-0.5 rounded border-border text-primary focus:ring-primary/50 bg-background">
                        <label for="signup-pp" class="text-xs font-bold text-muted-foreground uppercase tracking-widest leading-tight">
                            I accept the <a href="{{ route('privacy-policy') }}" class="text-primary hover:underline">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <button type="submit" :disabled="signupLoading" class="w-full py-3 bg-primary text-primary-foreground font-black rounded-xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 uppercase tracking-widest text-xs disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-text="signupLoading ? 'Creating Account...' : 'Create Account'"></span>
                </button>

                <div x-show="signupError" x-text="signupError" class="text-center text-sm text-red-500 font-bold"></div>
            </form>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
