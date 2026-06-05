<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('faqs')) {
            return;
        }

        $faqs = [
            // ── General ──────────────────────────────────────────────
            ['category' => 'General', 'question' => 'What does your website sell?', 'answer' => 'We sell digital products such as vouchers for games (e.g. Steam), subscriptions (e.g. Netflix), and similar items.'],
            ['category' => 'General', 'question' => 'How do I receive my purchase?', 'answer' => 'After payment, your product and purchase receipt will be delivered to your email.'],
            ['category' => 'General', 'question' => 'Is Ridly a legitimate store?', 'answer' => 'Yes. Ridly is a registered digital marketplace that sources vouchers directly from official distributors. All transactions are secured and receipted.'],
            ['category' => 'General', 'question' => 'What brands and platforms do you sell?', 'answer' => 'We carry vouchers for Steam, PlayStation, Xbox, Nintendo, Valorant, Genshin Impact, Fortnite, Netflix, Spotify, Discord Nitro, ChatGPT, and more.'],

            // ── Payments & Orders ────────────────────────────────────
            ['category' => 'Payments & Orders', 'question' => 'What payment methods do you accept?', 'answer' => 'We support QRIS and bank transfers.'],
            ['category' => 'Payments & Orders', 'question' => 'My payment went through but I didn\'t receive my code. What should I do?', 'answer' => 'Contact support with your order details. We\'ll resolve it as quickly as possible.'],
            ['category' => 'Payments & Orders', 'question' => 'How do I check my order status?', 'answer' => 'Log in to your account and go to the Orders page. You can view the status of each order and download your voucher codes from there.'],
            ['category' => 'Payments & Orders', 'question' => 'Is my payment information secure?', 'answer' => 'Absolutely. All payments are processed through secure, trusted payment gateways. We never store your bank or card details on our servers.'],
            ['category' => 'Payments & Orders', 'question' => 'Can I cancel an order after paying?', 'answer' => 'Because digital vouchers are delivered instantly, cancellations after payment are generally not possible. If you have an issue, please contact support.'],

            // ── Delivery & Redemption ────────────────────────────────
            ['category' => 'Delivery & Redemption', 'question' => 'How do I redeem a Steam Wallet code?', 'answer' => 'Open Steam, click "Games" in the top menu, then "Redeem a Steam Wallet Code". Enter the code and the balance will be added to your account.'],
            ['category' => 'Delivery & Redemption', 'question' => 'How do I redeem a PlayStation Store code?', 'answer' => 'On your PS console, go to PlayStation Store > "..." menu > "Redeem Code". Alternatively, visit store.playstation.com, sign in, and enter your code.'],
            ['category' => 'Delivery & Redemption', 'question' => 'How do I redeem a Netflix or Spotify subscription code?', 'answer' => 'For Netflix, go to netflix.com/redeem and enter your code. For Spotify, visit spotify.com/redeem. Both will apply the subscription credit to your account.'],
            ['category' => 'Delivery & Redemption', 'question' => 'How long does delivery take?', 'answer' => 'Voucher delivery is instant upon successful payment. You\'ll receive your code via email and can also view it in your Orders page.'],
            ['category' => 'Delivery & Redemption', 'question' => 'Are vouchers region locked?', 'answer' => 'Some vouchers are region-specific. Please check the product description for region information before purchasing.'],

            // ── Gacha System ─────────────────────────────────────────
            ['category' => 'Gacha System', 'question' => 'What is the gacha feature?', 'answer' => 'It\'s a randomized system where you can obtain discounts for products available in our store.'],
            ['category' => 'Gacha System', 'question' => 'Are the gacha results guaranteed?', 'answer' => 'No. All results are random, and there is no guarantee of receiving high discounts.'],
            ['category' => 'Gacha System', 'question' => 'Can I exchange my discount for cash?', 'answer' => 'No. Discounts are non-transferable and cannot be converted to money.'],
            ['category' => 'Gacha System', 'question' => 'Do discounts expire?', 'answer' => 'Yes, some discounts may have expiration dates or limited usage conditions.'],
            ['category' => 'Gacha System', 'question' => 'Can I use multiple discounts at once?', 'answer' => 'This depends on the promotion rules, but typically only one discount can be applied per purchase.'],
            ['category' => 'Gacha System', 'question' => 'How much does a gacha spin cost?', 'answer' => 'Each spin costs points earned from purchases. Check the Gacha page for the current spin cost.'],
            ['category' => 'Gacha System', 'question' => 'What are gacha boosters?', 'answer' => 'Boosters like Lucky Charm and Golden Touch temporarily increase your chances of landing rarer prizes. You can purchase them with points before spinning.'],

            // ── Points System ────────────────────────────────────────
            ['category' => 'Points System', 'question' => 'What are points?', 'answer' => 'Points are a reward currency earned through purchases that can be redeemed for discounts and rewards in the Points Shop.'],
            ['category' => 'Points System', 'question' => 'How do I earn points?', 'answer' => 'You earn points automatically when making eligible purchases.'],
            ['category' => 'Points System', 'question' => 'How do I use my points?', 'answer' => 'Redeem your points in the Points Shop for discounts or special offers.'],
            ['category' => 'Points System', 'question' => 'Do points expire?', 'answer' => 'No, points are retained until spent or account termination.'],
            ['category' => 'Points System', 'question' => 'Can I convert points into cash?', 'answer' => 'No. Points have no monetary value and cannot be withdrawn.'],
            ['category' => 'Points System', 'question' => 'Can I transfer points to another account?', 'answer' => 'No. Points are tied to your account.'],
            ['category' => 'Points System', 'question' => 'What happens to my points if I get a refund?', 'answer' => 'Points may be returned depending on the situation, handled case-by-case.'],

            // ── Point Shop ───────────────────────────────────────────
            ['category' => 'Point Shop', 'question' => 'What is the Point Shop?', 'answer' => 'The Point Shop is a rewards store where you can spend your earned points on exclusive discounts, brand-specific vouchers, and bonus point bundles.'],
            ['category' => 'Point Shop', 'question' => 'What can I buy in the Point Shop?', 'answer' => 'Items include platform-specific discounts (Steam, Netflix, Valorant, etc.), storewide discount codes, and loyalty cashback rewards.'],
            ['category' => 'Point Shop', 'question' => 'Do Point Shop items expire after I buy them?', 'answer' => 'Discount codes obtained from the Point Shop may have expiration dates. Check your My Discounts page for details on each item.'],

            // ── Referral Program ─────────────────────────────────────
            ['category' => 'Referral Program', 'question' => 'What is the referral program?', 'answer' => 'Invite friends to Ridly using your unique referral code. When they sign up and make their first purchase, both of you earn bonus points.'],
            ['category' => 'Referral Program', 'question' => 'How do I find my referral code?', 'answer' => 'Go to your Profile page. Your unique referral code and a shareable link are displayed in the Referral section.'],
            ['category' => 'Referral Program', 'question' => 'What rewards do I earn from referrals?', 'answer' => 'You earn points for each friend who makes their first purchase. As you refer more friends, you unlock milestone tiers with bigger rewards including free gacha spins and exclusive discounts.'],

            // ── Refunds ──────────────────────────────────────────────
            ['category' => 'Refunds', 'question' => 'Can I get a refund?', 'answer' => 'Generally no — digital goods cannot be returned. Exceptions are made for invalid, already-redeemed, or undelivered products.'],
            ['category' => 'Refunds', 'question' => 'How long does a refund take to process?', 'answer' => 'Once approved by our support team, refunds are typically processed within 3–5 business days depending on your payment method.'],
            ['category' => 'Refunds', 'question' => 'What if I received the wrong product?', 'answer' => 'Contact support immediately with your order number and a screenshot of the issue. We will verify and replace the product or issue a refund.'],

            // ── Account & Security ───────────────────────────────────
            ['category' => 'Account & Security', 'question' => 'Do I need an account to buy?', 'answer' => 'Some features require an account, especially for tracking purchases and rewards.'],
            ['category' => 'Account & Security', 'question' => 'What happens if I lose access to my account?', 'answer' => 'Contact support immediately for recovery assistance.'],
            ['category' => 'Account & Security', 'question' => 'I forgot my password. How do I change it?', 'answer' => 'Click "Forgot Password" on the Sign In page. We\'ll send a reset link to your email. If logged in, go to Profile > Settings > Change Password.'],
            ['category' => 'Account & Security', 'question' => 'How do I change my email address?', 'answer' => 'Go to Profile > Settings > Change Email. You\'ll need to verify the new email address before the change takes effect.'],
            ['category' => 'Account & Security', 'question' => 'Can I delete my account?', 'answer' => 'Yes. Contact our support team to request account deletion. Please note that all points, discounts, and purchase history will be permanently removed.'],

            // ── Technical Issues ─────────────────────────────────────
            ['category' => 'Technical Issues', 'question' => 'The voucher code doesn\'t work. What should I do?', 'answer' => 'Contact support with proof, and we will verify and replace it if necessary.'],
            ['category' => 'Technical Issues', 'question' => 'The website won\'t load or is very slow. What should I do?', 'answer' => 'Try clearing your browser cache, disabling extensions, or switching to a different browser. If the issue persists, contact support.'],
            ['category' => 'Technical Issues', 'question' => 'I didn\'t receive my confirmation email.', 'answer' => 'Check your spam/junk folder first. If it\'s not there, try resending from the login page or contact support for assistance.'],

            // ── Fair Use ─────────────────────────────────────────────
            ['category' => 'Fair Use', 'question' => 'Can I create multiple accounts to get more gacha rewards?', 'answer' => 'No. This is considered abuse and may result in account suspension.'],
            ['category' => 'Fair Use', 'question' => 'Can I exploit promotions or farm points?', 'answer' => 'No. This may result in suspension or termination of your account.'],
        ];

        $rows = [];
        foreach ($faqs as $i => $faq) {
            $rows[] = [
                'id' => $i + 1,
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'category' => $faq['category'],
                'sort_order' => $i,
                'is_active' => true,
            ];
        }

        DB::table('faqs')->upsert($rows, ['id'], ['question', 'answer', 'category', 'sort_order', 'is_active']);
    }
}
