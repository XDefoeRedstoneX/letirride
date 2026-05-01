<x-app-layout>
    <div class="min-h-[70vh] flex flex-col items-center justify-center p-6" x-data="{ 
        email: '{{ Auth::user()->email }}',
        sent: false,
        loading: false,
        sendEmail() {
            this.loading = true;
            setTimeout(() => {
                this.loading = false;
                this.sent = true;
            }, 2000);
        }
    }">
        <div class="w-full max-w-md space-y-8" x-show="!sent" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="text-center space-y-2">
                <div class="w-20 h-20 bg-primary/10 rounded-[2rem] flex items-center justify-center text-primary mx-auto mb-6 shadow-xl shadow-primary/10">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                </div>
                <h1 class="text-3xl font-black tracking-tighter uppercase">Reset <span class="text-primary">Password</span></h1>
                <p class="text-muted-foreground text-[10px] font-black uppercase tracking-widest">We will send a reset link to your email</p>
            </div>

            <div class="glass-card rounded-[2.5rem] p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Email Address</label>
                    <input type="email" x-model="email" class="w-full px-5 py-4 bg-foreground/5 border border-border rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all text-xs font-bold uppercase tracking-widest shadow-sm">
                </div>

                <button @click="sendEmail()" :disabled="loading" 
                        class="w-full py-5 bg-primary text-primary-foreground font-black rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/20 uppercase tracking-widest text-xs flex items-center justify-center gap-3">
                    <template x-if="loading">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Sending...' : 'Send Reset Link'"></span>
                </button>

                <a href="{{ route('settings') }}" class="block text-center text-[10px] font-black text-muted-foreground uppercase tracking-widest hover:text-primary transition-colors">Back to Settings</a>
            </div>
        </div>

        <!-- Success Animation -->
        <div class="w-full max-w-md text-center space-y-8" x-show="sent" x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-1000" x-transition:enter-start="opacity-0 scale-50 translate-y-12" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="relative w-32 h-32 mx-auto">
                <div class="absolute inset-0 bg-primary/20 rounded-full animate-ping"></div>
                <div class="relative w-32 h-32 bg-primary rounded-full flex items-center justify-center text-primary-foreground shadow-2xl shadow-primary/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                </div>
            </div>
            
            <div class="space-y-3">
                <h2 class="text-3xl font-black tracking-tighter uppercase">Email <span class="text-primary">Sent!</span></h2>
                <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest leading-relaxed">Check your inbox. We've sent a password reset link to <span class="text-foreground" x-text="email"></span></p>
            </div>

            <div class="pt-6">
                <a href="{{ route('settings') }}" class="inline-block px-10 py-4 bg-card border-2 border-border text-foreground font-black rounded-2xl hover:scale-105 transition-all shadow-xl uppercase tracking-widest text-[10px]">Return to Settings</a>
            </div>
        </div>
    </div>
</x-app-layout>