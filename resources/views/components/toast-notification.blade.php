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
}" class="fixed top-6 right-6 z-[100] flex flex-col gap-3 max-w-sm w-full pointer-events-none">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-8 scale-95"
             class="pointer-events-auto flex items-center gap-3 px-5 py-4 rounded-2xl border shadow-2xl backdrop-blur-xl"
             :class="{
                 'bg-green-500/10 border-green-500/20 text-green-400': toast.type === 'success',
                 'bg-red-500/10 border-red-500/20 text-red-400': toast.type === 'error',
                 'bg-blue-500/10 border-blue-500/20 text-blue-400': toast.type === 'info',
                 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400': toast.type === 'warning',
             }">
            <!-- Icon -->
            <div class="shrink-0">
                <template x-if="toast.type === 'success'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                </template>
            </div>

            <!-- Message -->
            <p class="text-xs font-bold tracking-wide flex-1" x-text="toast.message"></p>

            <!-- Close -->
            <button @click="remove(toast.id)" class="shrink-0 hover:opacity-70 transition-opacity">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
            </button>
        </div>
    </template>
</div>
