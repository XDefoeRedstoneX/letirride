<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-12 py-12" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
        <!-- Header -->
        <div class="text-center space-y-6" x-show="show">
            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mx-auto mb-6 shadow-2xl shadow-primary/20 backdrop-blur-xl border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h1 class="text-4xl font-black tracking-tighter uppercase leading-none">Terms of <span class="text-primary">Service (ToS)</span></h1>
            <p class="text-muted-foreground text-xs font-bold uppercase tracking-widest mt-2">Last Updated: 28-Apr-2026</p>
        </div>

        <!-- Content -->
        <div class="glass-card p-10 rounded-[3rem] space-y-10" x-show="show">
            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">1. Introduction</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">Welcome to Ridly. By accessing or using our website, you agree to be bound by these Terms of Service. If you do not agree to these terms, you are not authorised to use our services.</p>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">Our platform offers digital products such as game vouchers and subscriptions as well as a promotional \u201cgacha\u201d system and a point system that can be used in a dedicated point shop that allows users to obtain discounts for our products.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">2. Eligibility</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">You must be at least 13 years old to use our services. If you are under 18, you must have permission from a parent or guardian.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">3. Account Registration</h2>
                <ul class="list-disc list-inside space-y-2 text-xs font-medium text-muted-foreground">
                    <li>You may need to create an account to access certain features.</li>
                    <li>You are responsible for maintaining the confidentiality of your account.</li>
                    <li>Any activity under your account is your responsibility.</li>
                </ul>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">4. Products & Purchases</h2>
                <ul class="list-disc list-inside space-y-2 text-xs font-medium text-muted-foreground">
                    <li>All products sold are digital goods (e.g., voucher codes).</li>
                    <li>Once delivered, digital products are generally non-refundable, unless required by law or stated otherwise.</li>
                    <li>Prices may change at any time without prior notice.</li>
                </ul>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">5. Gacha Discount System</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">The gacha feature provides randomized discount rewards for use on eligible products. Rewards are generated randomly, and no specific outcome is guaranteed.</p>
                <p class="text-[10px] font-bold uppercase tracking-widest text-foreground/70">Discounts:</p>
                <ul class="list-disc list-inside space-y-2 text-xs font-medium text-muted-foreground">
                    <li>May have expiration dates</li>
                    <li>May only apply to selected products</li>
                    <li>Cannot be exchanged for cash</li>
                </ul>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">We do not guarantee the availability of high-value discounts.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">6A. Points & Rewards System</h2>
                <ul class="list-disc list-inside space-y-2 text-xs font-medium text-muted-foreground">
                    <li>Payments must be made through our approved payment methods.</li>
                    <li>You agree to provide accurate billing information.</li>
                    <li>Fraudulent transactions may result in account suspension and/or legal action according to the law.</li>
                </ul>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed mt-4">Users may earn points from eligible purchases on the platform. The amount of points earned per purchase is determined by us and may change at any time.</p>
                <p class="text-[10px] font-bold uppercase tracking-widest text-foreground/70 mt-2">Points:</p>
                <ul class="list-disc list-inside space-y-2 text-xs font-medium text-muted-foreground">
                    <li>Have no cash value and cannot be exchanged for money</li>
                    <li>Are non-transferable between accounts</li>
                    <li>May expire after a specified period</li>
                </ul>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">Points can be redeemed in the Points Shop for rewards such as discount vouchers or other promotional benefits.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">6B. Points Redemption</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">Points may be redeemed for discounts or other rewards available in the Points Shop. Redemption options, required points, and availability are subject to change without notice. Once points are redeemed, the transaction is final and non-reversible.</p>
                <p class="text-[10px] font-bold uppercase tracking-widest text-foreground/70 mt-2">Rewards obtained through points:</p>
                <ul class="list-disc list-inside space-y-2 text-xs font-medium text-muted-foreground">
                    <li>May have usage restrictions or expiration dates</li>
                    <li>Cannot be converted into cash or re-exchanged into points</li>
                </ul>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">7. Delivery</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">Digital products are delivered automatically or within a reasonable time after purchase. Delays may occur due to system or payment verification issues.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">8. Refunds & Returns</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">Due to the nature of digital goods, all sales are final once a product is delivered. Refunds may only be granted if:</p>
                <ul class="list-disc list-inside space-y-2 text-xs font-medium text-muted-foreground">
                    <li>The product was not delivered or there are other errors with product delivery</li>
                    <li>The product is invalid and cannot be replaced</li>
                </ul>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed mt-2">Points used in a transaction will not be refunded if the order is completed successfully. If a refund is issued due to a failed or invalid transaction, points may be restored at our discretion.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">9. Prohibited Activities</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">You agree not to:</p>
                <ul class="list-disc list-inside space-y-2 text-xs font-medium text-muted-foreground">
                    <li>Exploit bugs or manipulate the gacha system</li>
                    <li>Use bots, scripts, or automation to gain an unfair advantage</li>
                    <li>Resell or redistribute products without permission</li>
                    <li>Engage in fraudulent or illegal activities</li>
                    <li>Exploit or manipulate the points system (e.g., farming, duplication, abuse of promotions).</li>
                </ul>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">10. Account Suspension</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">We reserve the right to suspend or terminate accounts that violate these terms without prior notice.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">11. Limitation of Liability</h2>
                <ul class="list-disc list-inside space-y-2 text-xs font-medium text-muted-foreground">
                    <li>Losses resulting from misuse of voucher codes</li>
                    <li>Third-party service issues (e.g., Steam or payment providers)</li>
                    <li>Random outcomes from the gacha system</li>
                </ul>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">12. Changes to Terms</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">We may update these Terms at any time. Continued use of our platform means you accept the Terms and any updates we make to it.</p>
            </div>

            <div class="space-y-4">
                <h2 class="text-xl font-black uppercase tracking-tight text-primary">13. Contact</h2>
                <p class="text-xs font-medium text-muted-foreground leading-relaxed">For support or questions:</p>
                <ul class="space-y-1 text-xs font-medium text-muted-foreground">
                    <li>Email: support@ridly.com</li>
                    <li>Support Page: <a href="{{ route('tickets') }}" class="text-primary hover:underline">Click Here</a></li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
