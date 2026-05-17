<x-app-layout>
    <div class="space-y-8" x-data="{ 
        search: '',
        faqs: [
            { category: 'General', question: 'What does your website sell?', answer: 'We sell digital products such as vouchers for games (e.g. Steam), subscriptions (e.g. Netflix), and similar items.' },
            { category: 'General', question: 'How do I receive my purchase?', answer: 'After payment, your product and purchase receipt will be delivered to your email.' },
            
            { category: 'Gacha System', question: 'What is the gacha feature?', answer: 'It\u2019s a randomized system where you can obtain discounts for products available in our store.' },
            { category: 'Gacha System', question: 'Are the gacha results guaranteed?', answer: 'No. All results are random, and there is no guarantee of receiving high discounts.' },
            { category: 'Gacha System', question: 'Can I exchange my discount for cash?', answer: 'No. Discounts are non-transferable and cannot be converted to money.' },
            { category: 'Gacha System', question: 'Do discounts expire?', answer: 'Yes, some discounts may have expiration dates or limited usage conditions.' },
            { category: 'Gacha System', question: 'Can I use multiple discounts at once?', answer: 'This depends on the promotion rules, but typically only one discount can be applied per purchase.' },
            
            { category: 'Payments & Orders', question: 'What payment methods do you accept?', answer: 'We support QRIS and bank transfers' },
            { category: 'Payments & Orders', question: 'My payment went through but I didn\u2019t receive my code. What should I do?', answer: 'Contact support with your order details. We\u2019ll resolve it as quickly as possible.' },
            
            { category: 'Points System', question: 'What are points?', answer: 'Points are a reward currency earned through purchases in the platform that can be redeemed for discounts and other rewards in the Points Shop.' },
            { category: 'Points System', question: 'How do I earn points?', answer: 'You earn points automatically when making eligible purchases. The amount may vary depending on promotions or product types.' },
            { category: 'Points System', question: 'How do I use my points?', answer: 'You can redeem your points in the Points Shop for discounts or special offers, which can then be applied to your purchases.' },
            { category: 'Points System', question: 'Do points expire?', answer: 'No, Points are retained in your account until spent or account termination' },
            { category: 'Points System', question: 'Can I convert points into cash?', answer: 'No. Points have no monetary value and cannot be withdrawn or exchanged for money.' },
            { category: 'Points System', question: 'Can I transfer points to another account?', answer: 'No. Points are tied to your account and cannot be shared or transferred.' },
            { category: 'Points System', question: 'What happens to my points if I get a refund?', answer: 'If a refund is granted, points may be returned depending on the situation. However, this is handled on a case-by-case basis.' },
            
            { category: 'Refunds', question: 'Can I get a refund?', answer: 'Generally, no\u2014because digital goods cannot be returned. However, exceptions can be made if the product is invalid or not delivered.' },
            
            { category: 'Account & Security', question: 'Do I need an account to buy?', answer: 'Some features require an account, especially for tracking purchases and using gacha rewards.' },
            { category: 'Account & Security', question: 'What happens if I lose access to my account?', answer: 'Contact support immediately for recovery assistance.' },
            { category: 'Account & Security', question: 'I forgot my account password. How do I change it?', answer: 'If you cannot log in, click the \'Forgot Password\' link on the Sign In page. Enter your registered email, and we will send you an automated link to securely reset your password. If you are already logged in and just want to update it, you can do so by navigating to your profile and selecting Settings > Change Password.' },
            
            { category: 'Technical Issues', question: 'The voucher code doesn\u2019t work. What should I do?', answer: 'Contact support with proof, and we will verify and replace it if necessary.' },
            
            { category: 'Fair Use', question: 'Can I create multiple accounts to get more gacha rewards?', answer: 'No. This is considered abuse and may result in account suspension.' },
            { category: 'Fair Use', question: 'Can I exploit promotions or create multiple accounts to farm points?', answer: 'No. This is considered abuse and may result in suspension or termination of your account.' }
        ],
        get filteredFaqs() {
            return this.faqs.filter(f => 
                f.question.toLowerCase().includes(this.search.toLowerCase()) || 
                f.answer.toLowerCase().includes(this.search.toLowerCase()) ||
                f.category.toLowerCase().includes(this.search.toLowerCase())
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
        <div class="max-w-3xl space-y-8">
            <template x-for="(category, catIndex) in [...new Set(filteredFaqs.map(f => f.category))]" :key="catIndex">
                <div class="space-y-4">
                    <h2 class="text-lg font-black uppercase tracking-widest text-primary/80 ml-2" x-text="category"></h2>
                    <div class="space-y-3">
                        <template x-for="(faq, index) in filteredFaqs.filter(f => f.category === category)" :key="index">
                            <div class="glass-card rounded-[2rem] border border-border/50 overflow-hidden transition-all hover:border-primary/30" x-data="{ open: false }">
                                <button @click="open = !open" class="w-full px-8 py-5 flex items-center justify-between text-left group">
                                    <span class="font-black text-[13px] uppercase tracking-tight group-hover:text-primary transition-colors" x-text="faq.question"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pixel-render transition-transform duration-300" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div x-show="open" x-collapse x-transition>
                                    <div class="px-8 pb-6 text-xs font-medium text-muted-foreground leading-relaxed uppercase tracking-wider" x-text="faq.answer">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredFaqs.length === 0" class="py-20 text-center space-y-4">
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
