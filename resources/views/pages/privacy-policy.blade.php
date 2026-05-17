<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-12 py-12" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
        <!-- Header -->
        <div class="text-center space-y-6" x-show="show">
            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mx-auto mb-6 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h1 class="text-4xl font-black tracking-tighter uppercase leading-none">Privacy <span class="text-primary">Policies</span></h1>
            <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest mt-2">Last Updated: 28-Apr-2026</p>
        </div>

        <!-- Content -->
        <div class="glass-card p-10 rounded-[3rem] space-y-8" x-show="show">
            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">Information We Collect</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">We collect information you provide directly to us when you create an account, make a purchase, or communicate with us.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">How We Use Your Information</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">We use the information we collect to provide, maintain, and improve our services, to process your transactions, and to communicate with you.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">Information Sharing</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">We do not share your personal information with third parties except as described in this policy or with your consent.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">Security</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">We take reasonable measures to help protect information about you from loss, theft, misuse, and unauthorized access.</p>
            </div>
            
            <div class="pt-8 border-t border-border/30">
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">For any privacy-related concerns, please contact our support team.</p>
            </div>
        </div>
    </div>
</x-app-layout>
