<x-app-layout>
    <div class="space-y-8" x-data="{ 
        search: '',
        faqs: [
            { question: 'How do I top up my balance?', answer: 'You can top up your balance by going to the Top Up page and choosing your preferred payment method.' },
            { question: 'What is the Gacha system?', answer: 'The Gacha system allows you to win premium digital products and points by spinning the arcade machine using your points or balance.' },
            { question: 'How can I contact support?', answer: 'You can file a ticket in the Support section, and our admin will assist you as soon as possible via private chat.' },
            { question: 'Are the digital licenses official?', answer: 'Yes, all digital licenses and products sold on Ridly are 100% official and legal.' },
            { question: 'Can I refund my purchase?', answer: 'Refunds are subject to our terms and conditions. Generally, digital products are non-refundable once the code has been revealed.' },
            { question: 'How do I use my points?', answer: 'Points can be used in the Point Shop to buy exclusive items or used for spinning in the Gacha Arcade.' }
        ],
        get filteredFaqs() {
            return this.faqs.filter(f => 
                f.question.toLowerCase().includes(this.search.toLowerCase()) || 
                f.answer.toLowerCase().includes(this.search.toLowerCase())
            );
        }
    }">
        <!-- Header -->
        <div class="flex flex-col space-y-6">
            <div class="space-y-2">
                <h1 class="text-3xl font-black tracking-tighter uppercase leading-none">Frequently Asked <span class="text-primary">Questions</span></h1>
                <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest mt-2">Find answers to common questions about Ridly.</p>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full max-w-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground pixel-render"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" x-model="search" placeholder="Search for answers..." class="w-full pl-11 pr-4 py-4 bg-card border border-border rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all text-xs font-bold uppercase tracking-widest shadow-sm">
            </div>
        </div>

        <!-- FAQ List -->
        <div class="max-w-3xl space-y-4">
            <template x-for="(faq, index) in filteredFaqs" :key="index">
                <div class="glass-card rounded-[2rem] border border-border/50 overflow-hidden transition-all hover:border-primary/30" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full px-8 py-6 flex items-center justify-between text-left group">
                        <span class="font-black text-sm uppercase tracking-tight group-hover:text-primary transition-colors" x-text="faq.question"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render transition-transform duration-300" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="open" x-collapse x-transition>
                        <div class="px-8 pb-8 text-xs font-medium text-muted-foreground leading-relaxed uppercase tracking-wider" x-text="faq.answer">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredFaqs.length === 0" class="py-20 text-center space-y-4" x-transition>
            <div class="w-20 h-20 bg-card border border-border rounded-[2rem] flex items-center justify-center mx-auto text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <p class="text-muted-foreground font-black uppercase tracking-widest text-xs">No results found for your search.</p>
        </div>

        <!-- Contact Support Prompt -->
        <div class="max-w-3xl bg-primary/5 border border-primary/20 rounded-[2.5rem] p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 text-center md:text-left">
                <h3 class="font-black uppercase tracking-tight text-lg">Still have questions?</h3>
                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Our support team is ready to help you 24/7.</p>
            </div>
            <a href="{{ route('tickets') }}" class="px-8 py-4 bg-primary text-primary-foreground rounded-2xl font-black text-xs uppercase tracking-widest hover:scale-105 transition-all shadow-lg shadow-primary/20">Contact Support</a>
        </div>
    </div>
</x-app-layout>