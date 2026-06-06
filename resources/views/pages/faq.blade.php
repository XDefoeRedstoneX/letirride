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
            <div style="max-width:800px;width:100%;margin:0 auto;display:flex;flex-direction:column;gap:24px;">
                <template x-for="(category, catIndex) in [...new Set(filteredFaqs.map(f => f.category))]" :key="catIndex">
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <h2 style="font-family:var(--px);font-size:8px;letter-spacing:0.12em;color:var(--gold);margin-left:4px;" x-text="category"></h2>
                        <template x-for="(faq, index) in filteredFaqs.filter(f => f.category === category)" :key="faq.id">
                            <div class="px-accordion" x-data="{ open: false }">
                                <button @click="open = !open" class="px-accordion-btn">
                                    <div style="display:flex;align-items:flex-start;gap:10px;">
                                        <span style="margin-top:2px;padding:2px 6px;background:rgba(245,158,11,0.15);color:var(--gold);font-family:var(--px);font-size:7px;border-radius:4px;flex-shrink:0;">Q</span>
                                        <span x-text="faq.question"></span>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render" style="color:var(--text-dim);flex-shrink:0;transition:transform 0.2s;" :style="open ? 'transform:rotate(180deg)' : ''"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div x-show="open" x-collapse x-transition>
                                    <div class="px-accordion-body" style="display:flex;align-items:flex-start;gap:10px;">
                                        <span style="margin-top:2px;padding:2px 6px;background:rgba(90,122,170,0.15);color:var(--text-dim);font-family:var(--px);font-size:7px;border-radius:4px;flex-shrink:0;">A</span>
                                        <span x-text="faq.answer"></span>
                                    </div>
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
            <div class="px-card-static flex flex-col md:flex-row items-center md:justify-between gap-4 text-center md:text-left" style="padding:18px;max-width:800px;margin:0 auto;width:100%;border-color:rgba(245,158,11,0.2);">
                <div>
                    <h3 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--foreground);">Still have questions?</h3>
                    <p style="font-family:var(--px);font-size:6px;color:var(--text-dim);letter-spacing:0.1em;margin-top:6px;line-height:1.6;">OUR SUPPORT TEAM IS READY TO HELP YOU 24/7</p>
                </div>
                <a href="{{ route('tickets') }}" class="px-btn-gold w-full md:w-auto" style="padding:14px 24px;font-size:7px;text-decoration:none;white-space:nowrap;text-align:center;">CONTACT SUPPORT</a>
            </div>
        </div>
    </div>

    <script>
    function faqPage() {
        return {
            search: '',
            faqs: @json($faqs),
            get filteredFaqs() {
                const q = this.search.toLowerCase();
                if (!q) return this.faqs;
                return this.faqs.filter(f =>
                    f.question.toLowerCase().includes(q) ||
                    f.answer.toLowerCase().includes(q) ||
                    f.category.toLowerCase().includes(q)
                );
            }
        };
    }
    </script>
</x-app-layout>
