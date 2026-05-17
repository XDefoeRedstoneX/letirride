<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8">
            {{-- Header --}}
            <div style="text-align:center;">
                <div style="width:56px;height:56px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);margin:0 auto 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <h1 class="px-heading">Terms of <span class="gold">Service</span></h1>
                <p class="px-subheading">LAST UPDATED: 28-APR-2026</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Content --}}
            <div class="px-card-static" style="padding:32px;display:flex;flex-direction:column;gap:28px;">
                @php
                $sections = [
                    ['1. Introduction', ['Welcome to Ridly. By accessing or using our website, you agree to be bound by these Terms of Service. If you do not agree to these terms, you are not authorised to use our services.', 'Our platform offers digital products such as game vouchers and subscriptions as well as a promotional "gacha" system and a point system that can be used in a dedicated point shop that allows users to obtain discounts for our products.']],
                    ['2. Eligibility', ['You must be at least 13 years old to use our services. If you are under 18, you must have permission from a parent or guardian.']],
                    ['3. Account Registration', null, ['You may need to create an account to access certain features.', 'You are responsible for maintaining the confidentiality of your account.', 'Any activity under your account is your responsibility.']],
                    ['4. Products & Purchases', null, ['All products sold are digital goods (e.g., voucher codes).', 'Once delivered, digital products are generally non-refundable, unless required by law.', 'Prices may change at any time without prior notice.']],
                    ['5. Gacha Discount System', ['The gacha feature provides randomized discount rewards. Rewards are generated randomly, and no specific outcome is guaranteed.'], ['Discounts may have expiration dates', 'Discounts may only apply to selected products', 'Discounts cannot be exchanged for cash']],
                    ['6A. Points & Rewards System', ['Users may earn points from eligible purchases. The amount earned per purchase is determined by us and may change.'], ['Points have no cash value and cannot be exchanged for money', 'Points are non-transferable between accounts', 'Points may expire after a specified period']],
                    ['6B. Points Redemption', ['Points may be redeemed for rewards in the Points Shop. Redemption options and availability are subject to change. Once redeemed, the transaction is final.'], ['Rewards may have usage restrictions or expiration dates', 'Rewards cannot be converted into cash or re-exchanged into points']],
                    ['7. Delivery', ['Digital products are delivered automatically or within a reasonable time after purchase. Delays may occur due to system or payment verification issues.']],
                    ['8. Refunds & Returns', ['Due to the nature of digital goods, all sales are final once delivered. Refunds may be granted if the product was not delivered or is invalid and cannot be replaced.', 'Points used will not be refunded for completed orders. If a refund is issued for failed transactions, points may be restored at our discretion.']],
                    ['9. Prohibited Activities', null, ['Exploit bugs or manipulate the gacha system', 'Use bots, scripts, or automation for unfair advantage', 'Resell or redistribute products without permission', 'Engage in fraudulent or illegal activities', 'Exploit or manipulate the points system']],
                    ['10. Account Suspension', ['We reserve the right to suspend or terminate accounts that violate these terms without prior notice.']],
                    ['11. Limitation of Liability', null, ['Losses resulting from misuse of voucher codes', 'Third-party service issues (e.g., Steam or payment providers)', 'Random outcomes from the gacha system']],
                    ['12. Changes to Terms', ['We may update these Terms at any time. Continued use of our platform means you accept any updates.']],
                    ['13. Contact', ['For support or questions: Email support@ridly.com']],
                ];
                @endphp
                @foreach($sections as $section)
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <h2 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--gold);">{{ $section[0] }}</h2>
                    @if(isset($section[1]))
                        @foreach($section[1] as $p)
                        <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.7;">{{ $p }}</p>
                        @endforeach
                    @endif
                    @if(isset($section[2]))
                        <ul style="display:flex;flex-direction:column;gap:6px;padding-left:8px;">
                            @foreach($section[2] as $li)
                            <li style="display:flex;align-items:center;gap:8px;font-family:var(--font-sans);font-size:13px;color:var(--text-dim);"><div style="width:4px;height:4px;background:var(--gold);flex-shrink:0;"></div>{{ $li }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
