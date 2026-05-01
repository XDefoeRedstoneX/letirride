<div x-data="{ 
    open: false, 
    tab: 'login',
    email: '',
    password: '',
    username: '',
    init() {
        window.addEventListener('open-auth-modal', (e) => {
            this.tab = e.detail.tab || 'login';
            this.open = true;
        });
    }
}" x-show="open" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" x-cloak>
    <div @click.away="open = false" 
         class="w-full max-w-md bg-card text-card-foreground border border-border shadow-2xl rounded-2xl overflow-hidden"
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
            <form x-show="tab === 'login'" method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium">Email</label>
                    <input type="email" name="email" x-model="email" required class="w-full px-3 py-2 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="name@email.com">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Password</label>
                    <input type="password" name="password" x-model="password" required class="w-full px-3 py-2 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="••••••••">
                </div>
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="login-tos" required class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="login-tos" class="text-xs text-muted-foreground">I agree to the <a href="#" class="text-primary hover:underline">Terms of Service</a></label>
                </div>

                <button type="submit" class="w-full py-2 bg-primary text-primary-foreground font-bold rounded-md hover:opacity-90 transition-opacity tracking-widest">
                    Login Now
                </button>
            </form>

            <!-- Signup Form -->
            <form x-show="tab === 'signup'" method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium">Username</label>
                    <input type="text" name="username" x-model="username" required class="w-full px-3 py-2 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="PixelWalker">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Email</label>
                    <input type="email" name="email" x-model="email" required class="w-full px-3 py-2 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="name@email.com">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Password</label>
                    <input type="password" name="password" x-model="password" required class="w-full px-3 py-2 bg-background border border-input rounded-md focus:ring-2 focus:ring-primary/50 outline-none transition-all" placeholder="Min. 6 characters">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="signup-tos" required class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="signup-tos" class="text-xs text-muted-foreground">I agree to the <a href="#" class="text-primary hover:underline">Terms of Service</a></label>
                </div>

                <button type="submit" class="w-full py-2 bg-primary text-primary-foreground font-bold rounded-md hover:opacity-90 transition-opacity tracking-widest">
                    Create Account
                </button>
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
