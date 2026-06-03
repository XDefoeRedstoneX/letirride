<x-app-layout>
    <div class="px-page">
        <div class="px-page-inner space-y-8">

            {{-- Header --}}
            <div style="text-align:center;">
                <div style="width:64px;height:64px;background:rgba(245,158,11,0.15);border:3px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold);margin:0 auto 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" class="pixel-render"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h1 class="px-heading">Customer <span class="gold">Support</span></h1>
                <p style="font-family:var(--font-sans);font-size:14px;color:var(--text-dim);max-width:480px;margin:12px auto 0;line-height:1.7;">Have a question or facing an issue? Send us a message and we'll reply by email.</p>
            </div>
            <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

            {{-- Form / Success --}}
            <div class="px-card-static" style="padding:32px;max-width:640px;margin:0 auto;">
                @if (session('error'))
                    <div style="display:flex;align-items:flex-start;gap:14px;background:rgba(239,68,68,0.08);border:2px solid #ef4444;padding:16px 20px;margin-bottom:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="square" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p style="font-family:var(--font-sans);font-size:13px;color:#ef4444;font-weight:600;line-height:1.5;">{{ session('error') }}</p>
                    </div>
                @endif

                @if (session('ticket_submitted'))
                    {{-- Success state (server-driven after redirect) --}}
                    <div style="text-align:center;padding:24px 0;display:flex;flex-direction:column;align-items:center;gap:16px;">
                        <div style="width:64px;height:64px;background:rgba(34,197,94,0.15);border:3px solid #22c55e;display:flex;align-items:center;justify-content:center;color:#22c55e;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <h2 style="font-family:var(--font-sans);font-size:20px;font-weight:800;color:#e8f0ff;">Message Sent Successfully!</h2>
                        <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);max-width:360px;line-height:1.6;">Thank you for reaching out. We've received your inquiry and our team is on it.</p>
                        <div style="background:var(--dark-card2);border:2px solid var(--dark-line);padding:14px;width:100%;max-width:340px;">
                            <p style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--gold);margin-bottom:6px;">NEXT STEP</p>
                            <p style="font-family:var(--font-sans);font-size:12px;color:var(--text-dim);line-height:1.6;">Keep an eye on your inbox — we reply by email, usually within 24 hours.</p>
                        </div>
                        <a href="{{ route('tickets') }}" style="font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.1em;text-decoration:none;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-dim)'">SEND ANOTHER MESSAGE</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('tickets.store') }}"
                          style="display:flex;flex-direction:column;gap:20px;">
                        @csrf

                        {{-- Honeypot: hidden from humans, tempting to bots. --}}
                        <div style="position:absolute;left:-9999px;" aria-hidden="true">
                            <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>

                        @guest
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">YOUR EMAIL ADDRESS</label>
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com" class="px-input" style="padding:14px 18px;font-size:13px;">
                                @error('email')<span style="color:#f43f5e;font-size:11px;">{{ $message }}</span>@enderror
                            </div>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">YOUR NAME <span style="opacity:0.6;">(OPTIONAL)</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" maxlength="120" placeholder="Your name" class="px-input" style="padding:14px 18px;font-size:13px;">
                            </div>
                        @else
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">REPLYING TO</label>
                                <input type="email" value="{{ auth()->user()->email }}" readonly class="px-input" style="padding:14px 18px;font-size:13px;opacity:0.7;cursor:not-allowed;">
                            </div>
                        @endguest

                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">SUBJECT</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" required minlength="3" maxlength="120" placeholder="Briefly, what's it about?" class="px-input" style="padding:14px 18px;font-size:13px;">
                            @error('subject')<span style="color:#f43f5e;font-size:11px;">{{ $message }}</span>@enderror
                        </div>

                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="font-family:var(--px);font-size:6px;letter-spacing:0.12em;color:var(--text-dim);">HOW CAN WE HELP?</label>
                            <textarea name="message" required rows="5" minlength="10" maxlength="4000" placeholder="Describe your issue..." class="px-input" style="padding:14px 18px;font-size:13px;resize:none;">{{ old('message') }}</textarea>
                            @error('message')<span style="color:#f43f5e;font-size:11px;">{{ $message }}</span>@enderror
                        </div>

                        <button type="submit" class="px-btn-gold" style="width:100%;padding:18px;font-size:8px;display:flex;align-items:center;justify-content:center;gap:8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                            SEND MESSAGE
                        </button>
                    </form>
                @endif
            </div>

            {{-- FAQ Link --}}
            <p style="text-align:center;font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.1em;">
                QUICK ANSWER? CHECK OUR <a href="{{ route('faq') }}" style="color:var(--gold);text-decoration:underline;">FAQ</a>
            </p>
        </div>
    </div>
</x-app-layout>
