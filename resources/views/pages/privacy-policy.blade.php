<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8">
            {{-- Header --}}
            <div style="text-align:center;">
                <div style="width:56px;height:56px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);margin:0 auto 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h1 class="px-heading">Privacy <span class="gold">Policy</span></h1>
                <p style="font-family:var(--font-sans);font-size:14px;color:var(--text-dim);max-width:560px;margin:12px auto 0;line-height:1.7;">Your privacy is important to us. This policy explains how we handle your information.</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Content --}}
            <div class="px-card-static" style="padding:32px;display:flex;flex-direction:column;gap:28px;">
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <h2 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--gold);">1. Information We Collect</h2>
                    <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.7;">We collect information you provide directly to us when you create an account, make a purchase, or communicate with us.</p>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <h2 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--gold);">2. How We Use Your Information</h2>
                    <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.7;">We use the information we collect to provide, maintain, and improve our services, to process your transactions, and to communicate with you.</p>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <h2 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--gold);">3. Information Sharing</h2>
                    <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.7;">We do not share your personal information with third parties except as described in this policy or with your consent.</p>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <h2 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--gold);">4. Security</h2>
                    <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.7;">We take reasonable measures to help protect information about you from loss, theft, misuse, and unauthorized access.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
