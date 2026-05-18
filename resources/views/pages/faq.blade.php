<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8" x-data="faqPage()">
            {{-- Header --}}
            <div style="display:flex;flex-direction:column;gap:16px;" class="md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="px-heading">Frequently Asked <span class="gold">Questions</span></h1>
                    <p class="px-subheading">FIND ANSWERS TO COMMON QUESTIONS ABOUT RIDLY</p>
                </div>
                <div style="position:relative;width:100%;max-width:360px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-dim);" class="pixel-render"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" x-model="search" placeholder="Search for answers..." class="px-input" style="width:100%;padding:12px 12px 12px 36px;font-size:12px;">
                </div>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- FAQ List --}}
            <div style="max-width:800px;display:flex;flex-direction:column;gap:24px;">
                <template x-for="(category, catIndex) in [...new Set(filteredFaqs.map(f => f.category))]" :key="catIndex">
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <h2 style="font-family:var(--px);font-size:8px;letter-spacing:0.12em;color:var(--gold);margin-left:4px;" x-text="category"></h2>
                        <template x-for="(faq, index) in filteredFaqs.filter(f => f.category === category)" :key="index">
                            <div class="px-card" style="padding:0;overflow:hidden;" x-data="{ open: false }">
                                <button @click="open = !open" style="width:100%;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;text-align:left;cursor:pointer;background:none;border:none;">
                                    <span style="font-family:var(--font-sans);font-size:13px;font-weight:800;color:#e8f0ff;" x-text="faq.question"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render" style="color:var(--text-dim);flex-shrink:0;transition:transform 0.2s;" :style="open ? 'transform:rotate(180deg)' : ''"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div x-show="open" x-collapse x-transition>
                                    <div style="padding:0 20px 18px;font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.7;" x-text="faq.answer"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Empty State --}}
            <div x-show="filteredFaqs.length === 0" class="px-empty-state">
                <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg></div>
                <p class="empty-text">NO RESULTS FOUND</p>
            </div>

            {{-- Contact Support --}}
            <div class="px-card-static" style="padding:24px;max-width:800px;display:flex;align-items:center;justify-content:space-between;gap:16px;border-color:rgba(245,158,11,0.2);" class="flex-col md:flex-row">
                <div>
                    <h3 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:#e8f0ff;">Still have questions?</h3>
                    <p style="font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.1em;margin-top:4px;">OUR SUPPORT TEAM IS READY TO HELP YOU 24/7</p>
                </div>
                <a href="{{ route('tickets') }}" class="px-btn-gold" style="padding:14px 24px;font-size:7px;text-decoration:none;white-space:nowrap;">CONTACT SUPPORT</a>
            </div>
        </div>
    </div>

    <script>
    function faqPage() {
        return {
            search: '',
            faqs: [
                { category: 'General', question: 'What does your website sell?', answer: 'We sell digital products such as vouchers for games (e.g. Steam), subscriptions (e.g. Netflix), and similar items.' },
                { category: 'General', question: 'How do I receive my purchase?', answer: 'After payment, your product and purchase receipt will be delivered to your email.' },
                { category: 'Gacha System', question: 'What is the gacha feature?', answer: 'It\u2019s a randomized system where you can obtain discounts for products available in our store.' },
                { category: 'Gacha System', question: 'Are the gacha results guaranteed?', answer: 'No. All results are random, and there is no guarantee of receiving high discounts.' },
                { category: 'Gacha System', question: 'Can I exchange my discount for cash?', answer: 'No. Discounts are non-transferable and cannot be converted to money.' },
                { category: 'Gacha System', question: 'Do discounts expire?', answer: 'Yes, some discounts may have expiration dates or limited usage conditions.' },
                { category: 'Gacha System', question: 'Can I use multiple discounts at once?', answer: 'This depends on the promotion rules, but typically only one discount can be applied per purchase.' },
                { category: 'Payments & Orders', question: 'What payment methods do you accept?', answer: 'We support QRIS and bank transfers.' },
                { category: 'Payments & Orders', question: 'My payment went through but I didn\u2019t receive my code. What should I do?', answer: 'Contact support with your order details. We\u2019ll resolve it as quickly as possible.' },
                { category: 'Points System', question: 'What are points?', answer: 'Points are a reward currency earned through purchases that can be redeemed for discounts and rewards in the Points Shop.' },
                { category: 'Points System', question: 'How do I earn points?', answer: 'You earn points automatically when making eligible purchases.' },
                { category: 'Points System', question: 'How do I use my points?', answer: 'Redeem your points in the Points Shop for discounts or special offers.' },
                { category: 'Points System', question: 'Do points expire?', answer: 'No, points are retained until spent or account termination.' },
                { category: 'Points System', question: 'Can I convert points into cash?', answer: 'No. Points have no monetary value and cannot be withdrawn.' },
                { category: 'Points System', question: 'Can I transfer points to another account?', answer: 'No. Points are tied to your account.' },
                { category: 'Points System', question: 'What happens to my points if I get a refund?', answer: 'Points may be returned depending on the situation, handled case-by-case.' },
                { category: 'Refunds', question: 'Can I get a refund?', answer: 'Generally no—digital goods cannot be returned. Exceptions for invalid or undelivered products.' },
                { category: 'Account & Security', question: 'Do I need an account to buy?', answer: 'Some features require an account, especially for tracking purchases and rewards.' },
                { category: 'Account & Security', question: 'What happens if I lose access to my account?', answer: 'Contact support immediately for recovery assistance.' },
                { category: 'Account & Security', question: 'I forgot my password. How do I change it?', answer: 'Click "Forgot Password" on the Sign In page. We\'ll send a reset link to your email. If logged in, go to Profile > Settings > Change Password.' },
                { category: 'Technical Issues', question: 'The voucher code doesn\u2019t work. What should I do?', answer: 'Contact support with proof, and we will verify and replace it if necessary.' },
                { category: 'Fair Use', question: 'Can I create multiple accounts to get more gacha rewards?', answer: 'No. This is considered abuse and may result in account suspension.' },
                { category: 'Fair Use', question: 'Can I exploit promotions or farm points?', answer: 'No. This may result in suspension or termination of your account.' },
            ],
            get filteredFaqs() {
                return this.faqs.filter(f =>
                    f.question.toLowerCase().includes(this.search.toLowerCase()) ||
                    f.answer.toLowerCase().includes(this.search.toLowerCase()) ||
                    f.category.toLowerCase().includes(this.search.toLowerCase())
                );
            }
        };
    }
    </script>
</x-app-layout>
