<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8"
             x-data="ticketsPage()" >

            {{-- Header --}}
            <div style="text-align:center;">
                <div style="width:64px;height:64px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);margin:0 auto 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h1 class="px-heading">Customer <span class="gold">Support</span></h1>
                <p style="font-family:var(--font-sans);font-size:14px;color:var(--text-dim);max-width:480px;margin:12px auto 0;line-height:1.7;">Have a question or facing an issue? Send us a message and we'll get back to you.</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Form / Success --}}
            <div class="px-card-static" style="padding:32px;max-width:640px;margin:0 auto;">
                <template x-if="!submitted">
                    <form @submit.prevent="submitForm" style="display:flex;flex-direction:column;gap:20px;">
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">YOUR EMAIL ADDRESS</label>
                            <input type="email" x-model="email" required placeholder="email@example.com" class="px-input" style="padding:14px 18px;font-size:13px;">
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">HOW CAN WE HELP?</label>
                            <textarea x-model="message" required rows="5" placeholder="Describe your issue..." class="px-input" style="padding:14px 18px;font-size:13px;resize:none;"></textarea>
                        </div>
                        <button type="submit" class="px-btn-gold" style="width:100%;padding:18px;font-size:8px;display:flex;align-items:center;justify-content:center;gap:8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                            SEND MESSAGE
                        </button>
                    </form>
                </template>

                <template x-if="submitted">
                    <div style="text-align:center;padding:24px 0;display:flex;flex-direction:column;align-items:center;gap:16px;">
                        <div style="width:64px;height:64px;background:rgba(34,197,94,0.15);border:3px solid #22c55e;display:flex;align-items:center;justify-content:center;color:#22c55e;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <h2 style="font-family:var(--font-sans);font-size:20px;font-weight:800;color:#e8f0ff;">Message Sent Successfully!</h2>
                        <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);max-width:360px;line-height:1.6;">Thank you for reaching out. We have received your inquiry and our team is already on it.</p>
                        <div style="background:var(--dark-card2);border:2px solid var(--dark-line);padding:14px;width:100%;max-width:320px;">
                            <p style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--gold);margin-bottom:6px;">NEXT STEP</p>
                            <p style="font-family:var(--font-sans);font-size:12px;color:var(--text-dim);line-height:1.6;">Check your email <span style="color:var(--gold);font-weight:800;" x-text="email"></span> regularly. We usually respond within 24 hours.</p>
                        </div>
                        <button @click="submitted = false; message = ''" style="font-family:var(--px);font-size:6px;color:var(--text-dim);cursor:pointer;background:none;border:none;letter-spacing:0.1em;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-dim)'">SEND ANOTHER MESSAGE</button>
                    </div>
                </template>
            </div>

            {{-- FAQ Link --}}
            <p style="text-align:center;font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.1em;">
                QUICK ANSWER? CHECK OUR <a href="{{ route('faq') }}" style="color:var(--gold);text-decoration:underline;">FAQ</a>
            </p>
        </div>
    </div>

    <script>
    function ticketsPage() {
        return {
            submitted: false,
            email: '',
            message: '',
            submitForm() {
                if (this.email && this.message) {
                    this.submitted = true;
                }
            }
        };
    }
    </script>
</x-app-layout>
