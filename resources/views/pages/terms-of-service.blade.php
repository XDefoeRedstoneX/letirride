<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-12 py-12" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
        <!-- Header -->
        <div class="text-center space-y-6" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000" x-transition:enter-start="md:opacity-0 md:translate-y-12" x-transition:enter-end="md:opacity-100 md:translate-y-0">
            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mx-auto mb-6 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h1 class="text-4xl font-black tracking-tighter uppercase leading-none">Terms Of <span class="text-primary">Service</span></h1>
            <p class="text-muted-foreground text-lg max-w-2xl mx-auto font-medium">Please read these terms carefully before using our services.</p>
        </div>

        <!-- Content -->
        <div class="glass-card p-10 rounded-[3rem] space-y-8" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-1000 md:delay-200">
            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">1. Introduction</h2>
                <p class="text-muted-foreground leading-relaxed">Welcome to Ridly. By accessing or using our website, you agree to be bound by these Terms of Service and all applicable laws and regulations.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">2. Use License</h2>
                <p class="text-muted-foreground leading-relaxed">Permission is granted to temporarily download one copy of the materials on Ridly's website for personal, non-commercial transitory viewing only.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">3. Disclaimer</h2>
                <p class="text-muted-foreground leading-relaxed">The materials on Ridly's website are provided on an 'as is' basis. Ridly makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">4. Limitations</h2>
                <p class="text-muted-foreground leading-relaxed">In no event shall Ridly or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on Ridly's website.</p>
            </div>
        </div>
    </div>
</x-app-layout>
