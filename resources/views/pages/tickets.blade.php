<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-12" x-data="{ 
        submitted: false,
        email: '',
        message: '',
        submitForm() {
            if (this.email && this.message) {
                this.submitted = true;
            }
        }
    }">
        
        <!-- Header -->
        <div class="text-center space-y-4">
            <div class="w-20 h-20 bg-primary/10 rounded-[2rem] flex items-center justify-center text-primary mx-auto mb-6 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <h1 class="text-5xl font-black tracking-tighter uppercase leading-none">Customer <span class="text-primary">Support</span></h1>
            <p class="text-muted-foreground text-lg font-medium max-w-xl mx-auto">Have a question or facing an issue? Send us a message and we'll get back to you as soon as possible.</p>
        </div>

        <!-- Contact Form / Success Message -->
        <div class="glass-card rounded-[3rem] p-8 md:p-12 shadow-2xl relative overflow-hidden">
            <!-- Background Decorative Element -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
            
            <template x-if="!submitted">
                <form @submit.prevent="submitForm" class="space-y-8 relative z-10">
                    <div class="grid grid-cols-1 gap-8">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest ml-4">Your Email Address</label>
                            <input type="email" x-model="email" required placeholder="email@example.com" 
                                   class="w-full px-8 py-5 bg-foreground/5 border border-border rounded-[2rem] focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm">
                        </div>
                        
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest ml-4">How can we help?</label>
                            <textarea x-model="message" required rows="5" placeholder="Describe your issue or question in detail..." 
                                      class="w-full px-8 py-6 bg-foreground/5 border border-border rounded-[2.5rem] focus:ring-4 focus:ring-primary/20 outline-none transition-all font-bold text-sm resize-none"></textarea>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-6 bg-primary text-primary-foreground font-black rounded-[2rem] shadow-2xl shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all uppercase tracking-widest text-sm flex items-center justify-center gap-3 group">
                        Send Message
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render group-hover:translate-x-1 transition-transform"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                    </button>
                </form>
            </template>

            <template x-if="submitted">
                <div class="text-center py-12 space-y-8 relative z-10 animate-in fade-in zoom-in duration-500">
                    <div class="w-24 h-24 bg-green-500/10 rounded-full flex items-center justify-center text-green-500 mx-auto shadow-2xl shadow-green-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="space-y-4">
                        <h2 class="text-3xl font-black uppercase tracking-tight">Message Sent Successfully!</h2>
                        <p class="text-muted-foreground font-medium max-w-md mx-auto">
                            Thank you for reaching out. We have received your inquiry and our team is already on it.
                        </p>
                    </div>
                    <div class="bg-primary/5 border border-primary/10 rounded-2xl p-6 max-w-sm mx-auto">
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-2">Next Step</p>
                        <p class="text-xs font-bold leading-relaxed">Please check your email <span class="text-primary" x-text="email"></span> regularly. We usually respond within 24 hours.</p>
                    </div>
                    <button @click="submitted = false; message = ''" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                        Send another message
                    </button>
                </div>
            </template>
        </div>

        <!-- FAQ Link -->
        <div class="text-center">
            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">
                Quick answer? Check our <a href="{{ route('faq') }}" class="text-primary hover:underline">Frequently Asked Questions</a>
            </p>
        </div>
    </div>
</x-app-layout>
