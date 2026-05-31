<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::exists()) {
            return;
        }

        $faqs = [
            ['category' => 'General', 'question' => 'What does your website sell?', 'answer' => 'We sell digital products such as vouchers for games (e.g. Steam), subscriptions (e.g. Netflix), and similar items.'],
            ['category' => 'General', 'question' => 'How do I receive my purchase?', 'answer' => 'After payment, your product and purchase receipt will be delivered to your email.'],
            ['category' => 'Gacha System', 'question' => 'What is the gacha feature?', 'answer' => 'It\'s a randomized system where you can obtain discounts for products available in our store.'],
            ['category' => 'Gacha System', 'question' => 'Are the gacha results guaranteed?', 'answer' => 'No. All results are random, and there is no guarantee of receiving high discounts.'],
            ['category' => 'Gacha System', 'question' => 'Can I exchange my discount for cash?', 'answer' => 'No. Discounts are non-transferable and cannot be converted to money.'],
            ['category' => 'Gacha System', 'question' => 'Do discounts expire?', 'answer' => 'Yes, some discounts may have expiration dates or limited usage conditions.'],
            ['category' => 'Gacha System', 'question' => 'Can I use multiple discounts at once?', 'answer' => 'This depends on the promotion rules, but typically only one discount can be applied per purchase.'],
            ['category' => 'Payments & Orders', 'question' => 'What payment methods do you accept?', 'answer' => 'We support QRIS and bank transfers.'],
            ['category' => 'Payments & Orders', 'question' => 'My payment went through but I didn\'t receive my code. What should I do?', 'answer' => 'Contact support with your order details. We\'ll resolve it as quickly as possible.'],
            ['category' => 'Points System', 'question' => 'What are points?', 'answer' => 'Points are a reward currency earned through purchases that can be redeemed for discounts and rewards in the Points Shop.'],
            ['category' => 'Points System', 'question' => 'How do I earn points?', 'answer' => 'You earn points automatically when making eligible purchases.'],
            ['category' => 'Points System', 'question' => 'How do I use my points?', 'answer' => 'Redeem your points in the Points Shop for discounts or special offers.'],
            ['category' => 'Points System', 'question' => 'Do points expire?', 'answer' => 'No, points are retained until spent or account termination.'],
            ['category' => 'Points System', 'question' => 'Can I convert points into cash?', 'answer' => 'No. Points have no monetary value and cannot be withdrawn.'],
            ['category' => 'Points System', 'question' => 'Can I transfer points to another account?', 'answer' => 'No. Points are tied to your account.'],
            ['category' => 'Points System', 'question' => 'What happens to my points if I get a refund?', 'answer' => 'Points may be returned depending on the situation, handled case-by-case.'],
            ['category' => 'Refunds', 'question' => 'Can I get a refund?', 'answer' => 'Generally no — digital goods cannot be returned. Exceptions for invalid or undelivered products.'],
            ['category' => 'Account & Security', 'question' => 'Do I need an account to buy?', 'answer' => 'Some features require an account, especially for tracking purchases and rewards.'],
            ['category' => 'Account & Security', 'question' => 'What happens if I lose access to my account?', 'answer' => 'Contact support immediately for recovery assistance.'],
            ['category' => 'Account & Security', 'question' => 'I forgot my password. How do I change it?', 'answer' => 'Click "Forgot Password" on the Sign In page. We\'ll send a reset link to your email. If logged in, go to Profile > Settings > Change Password.'],
            ['category' => 'Technical Issues', 'question' => 'The voucher code doesn\'t work. What should I do?', 'answer' => 'Contact support with proof, and we will verify and replace it if necessary.'],
            ['category' => 'Fair Use', 'question' => 'Can I create multiple accounts to get more gacha rewards?', 'answer' => 'No. This is considered abuse and may result in account suspension.'],
            ['category' => 'Fair Use', 'question' => 'Can I exploit promotions or farm points?', 'answer' => 'No. This may result in suspension or termination of your account.'],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::create(array_merge($faq, ['sort_order' => $i, 'is_active' => true]));
        }
    }
}
