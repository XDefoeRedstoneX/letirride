<div x-data="{
    toasts: [],
    add(message, type = 'success', duration = 4000) {
        const id = Date.now();
        this.toasts.push({ id, message, type, show: false });

        this.$nextTick(() => {
            const toast = this.toasts.find(t => t.id === id);
            if (toast) toast.show = true;
        });

        setTimeout(() => this.remove(id), duration);
    },
    remove(id) {
        const toast = this.toasts.find(t => t.id === id);
        if (toast) toast.show = false;
        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }, 300);
    },
    init() {
        window.addEventListener('toast', (e) => {
            this.add(e.detail.message, e.detail.type || 'success', e.detail.duration || 4000);
        });

        window.addEventListener('show-toast', (e) => {
            this.add(e.detail.message, e.detail.type || 'success', e.detail.duration || 4000);
        });

        // Show flash messages from Laravel session
        @if(session('success'))
            this.add('{{ session('success') }}', 'success');
        @endif
        @if(session('error'))
            this.add('{{ session('error') }}', 'error');
        @endif
        @if(session('info'))
            this.add('{{ session('info') }}', 'info');
        @endif
    }
}" class="fixed top-4 right-4 z-[200] flex flex-col gap-3 max-w-sm w-full pointer-events-none">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-8"
             class="pointer-events-auto flex items-center gap-3 px-5 py-4"
             :style="'background: var(--dark-card, #08152a); border: 3px solid ' +
                 (toast.type === 'success' ? '#22c55e' :
                  toast.type === 'error'   ? '#ef4444' :
                  toast.type === 'warning' ? '#f59e0b' : '#3b82f6') +
                 '; box-shadow: 4px 4px 0 ' +
                 (toast.type === 'success' ? 'rgba(34,197,94,0.3)' :
                  toast.type === 'error'   ? 'rgba(239,68,68,0.3)' :
                  toast.type === 'warning' ? 'rgba(245,158,11,0.3)' : 'rgba(59,130,246,0.3)') + ';'"
             :class="{
                 'text-green-400': toast.type === 'success',
                 'text-red-400': toast.type === 'error',
                 'text-blue-400': toast.type === 'info',
                 'text-yellow-400': toast.type === 'warning',
             }">
            {{-- Type Label --}}
            <span style="font-family: var(--px, 'Press Start 2P', monospace); font-size: 6px; letter-spacing: 0.12em; white-space: nowrap;"
                  x-text="toast.type === 'success' ? '✓ OK' : toast.type === 'error' ? '✕ ERR' : toast.type === 'warning' ? '⚠ WARN' : 'ℹ INFO'"></span>

            {{-- Separator --}}
            <div style="width: 2px; height: 24px; background: currentColor; opacity: 0.3;"></div>

            {{-- Message --}}
            <p style="font-family: var(--font-sans, 'Geist', sans-serif); font-size: 12px; font-weight: 600; color: #e8f0ff; flex: 1;" x-html="toast.message"></p>

            {{-- Close --}}
            <button @click="remove(toast.id)" class="shrink-0" style="color: var(--text-dim, #5a7aaa); cursor: pointer; background: none; border: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
            </button>
        </div>
    </template>
</div>
