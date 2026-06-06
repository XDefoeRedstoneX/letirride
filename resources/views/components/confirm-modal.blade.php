{{-- Pixelated confirm dialog. Open via:
       const ok = await window.openConfirmModal({ title, message, confirmLabel, cancelLabel });
     Resolves to true if the user picks the destructive confirm action, false otherwise. --}}
<div x-data="{
    open: false,
    title: 'Are you sure?',
    message: '',
    confirmLabel: 'YES',
    cancelLabel: 'NO',
    _resolve: null,
    accept() { this.open = false; if (this._resolve) { const r = this._resolve; this._resolve = null; r(true); } },
    decline() { this.open = false; if (this._resolve) { const r = this._resolve; this._resolve = null; r(false); } },
    init() {
        const self = this;
        window.openConfirmModal = function(opts) {
            opts = opts || {};
            self.title = opts.title || 'Are you sure?';
            self.message = opts.message || '';
            self.confirmLabel = opts.confirmLabel || 'YES';
            self.cancelLabel = opts.cancelLabel || 'NO';
            self.open = true;
            return new Promise(function(resolve) { self._resolve = resolve; });
        };
    }
}"
     x-show="open"
     x-cloak
     class="px-modal-overlay"
     @keydown.escape.window="decline()"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="decline()">
    <div class="px-modal-box"
         style="max-width:380px;"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         @click.stop>

        <div style="display:flex;flex-direction:column;gap:18px;text-align:center;">
            <div style="width:56px;height:56px;background:rgba(239,68,68,0.12);border:3px solid #ef4444;color:#ef4444;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square" class="pixel-render"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
            </div>

            <h3 style="font-family:var(--font-sans);font-size:16px;font-weight:800;color:var(--foreground);letter-spacing:0.02em;" x-text="title"></h3>
            <p style="font-family:var(--font-sans);font-size:13px;color:var(--text-dim);line-height:1.5;" x-text="message"></p>

            <div style="display:flex;flex-direction:column;gap:10px;margin-top:6px;">
                {{-- NO button — primary orange, pixelated (per spec). --}}
                <button type="button"
                        @click="decline()"
                        class="px-btn-gold"
                        style="width:100%;padding:14px;font-size:7px;"
                        x-text="cancelLabel"></button>

                {{-- YES (destructive) button — ghost with red accent. --}}
                <button type="button"
                        @click="accept()"
                        class="px-btn-ghost"
                        style="width:100%;padding:12px;font-size:7px;color:#ef4444;border-color:rgba(239,68,68,0.45);"
                        x-text="confirmLabel"></button>
            </div>
        </div>
    </div>
</div>
