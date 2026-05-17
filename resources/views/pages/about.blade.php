<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-12">
            {{-- Hero --}}
            <div style="text-align:center;">
                <div style="width:64px;height:64px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);margin:0 auto 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </div>
                <h1 class="px-heading">About <span class="gold">Ridly</span></h1>
                <p style="font-family:var(--font-sans);font-size:15px;color:var(--text-dim);max-width:560px;margin:12px auto 0;line-height:1.7;">The ultimate hub for premium digital assets, licenses, and gaming rewards. Redefining the digital marketplace experience.</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Vision & Mission --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="px-card-static" style="padding:32px;">
                    <div class="px-section-header"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></div><span class="section-title">OUR VISION</span></div>
                    <p style="font-family:var(--font-sans);font-size:14px;color:var(--text-dim);line-height:1.8;">To become the world's most trusted and innovative digital marketplace, empowering users to access premium services with ease and excitement.</p>
                </div>
                <div class="px-card-static" style="padding:32px;">
                    <div class="px-section-header"><div class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/><path d="m9 12 2 2 4-4"/></svg></div><span class="section-title">OUR MISSION</span></div>
                    <ul style="display:flex;flex-direction:column;gap:12px;">
                        <li style="display:flex;align-items:center;gap:10px;font-family:var(--font-sans);font-size:14px;color:var(--text-dim);"><div style="width:6px;height:6px;background:var(--gold);flex-shrink:0;"></div>Providing high-quality digital licenses at competitive prices.</li>
                        <li style="display:flex;align-items:center;gap:10px;font-family:var(--font-sans);font-size:14px;color:var(--text-dim);"><div style="width:6px;height:6px;background:var(--gold);flex-shrink:0;"></div>Creating an engaging ecosystem through gamified rewards.</li>
                        <li style="display:flex;align-items:center;gap:10px;font-family:var(--font-sans);font-size:14px;color:var(--text-dim);"><div style="width:6px;height:6px;background:var(--gold);flex-shrink:0;"></div>Ensuring top-tier security and instant delivery for every transaction.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
