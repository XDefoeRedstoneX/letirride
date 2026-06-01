{{--
    Widget controls partial — included in every dashboard widget header.
    Props:
      $id           — widget identifier string (e.g. 'revenue_trend')
      $configurable — bool, default true; shows the ⚙ Edit button when true
--}}
@php $configurable = $configurable ?? true; @endphp

<div class="flex items-center gap-1 shrink-0 ml-2">

    {{-- ⚙ Edit / Configure (chart widgets only) --}}
    @if($configurable)
    <button @click="toggleConfig('{{ $id }}')"
            :class="isConfigOpen('{{ $id }}')
                ? 'bg-primary/10 text-primary border-primary/30'
                : 'bg-foreground/5 text-muted-foreground border-transparent hover:bg-foreground/10 hover:text-foreground hover:border-border'"
            class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[8px] font-black uppercase tracking-widest transition-all duration-100">
        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>
        <span x-text="isConfigOpen('{{ $id }}') ? 'Close' : 'Edit'"></span>
    </button>
    @endif

    {{-- ⋮ More options --}}
    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
        <button @click="open = !open"
                class="w-7 h-7 flex items-center justify-center rounded-lg border border-transparent bg-foreground/5 text-muted-foreground hover:bg-foreground/10 hover:text-foreground hover:border-border transition-all duration-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
        </button>

        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"   x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 top-full mt-1.5 w-44 bg-card border border-border rounded-xl shadow-xl z-40 py-1.5 overflow-hidden origin-top-right">

            {{-- Width / size --}}
            <div class="px-3.5 pt-1.5 pb-2">
                <p class="text-[8px] font-black text-muted-foreground uppercase tracking-widest mb-1.5">Width</p>
                <div class="flex gap-1">
                    <button @click="setWidgetSpan('{{ $id }}', 1)"
                            :class="getWidgetSpan('{{ $id }}') === 1 ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                            class="flex-1 px-1.5 py-1 rounded text-[9px] font-black uppercase tracking-widest border transition-all">⅓</button>
                    <button @click="setWidgetSpan('{{ $id }}', 2)"
                            :class="getWidgetSpan('{{ $id }}') === 2 ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                            class="flex-1 px-1.5 py-1 rounded text-[9px] font-black uppercase tracking-widest border transition-all">⅔</button>
                    <button @click="setWidgetSpan('{{ $id }}', 3)"
                            :class="getWidgetSpan('{{ $id }}') === 3 ? 'bg-primary/15 text-primary border-primary/30' : 'bg-foreground/5 text-muted-foreground border-border hover:bg-foreground/10 hover:text-foreground'"
                            class="flex-1 px-1.5 py-1 rounded text-[8px] font-black uppercase tracking-widest border transition-all">Full</button>
                </div>
            </div>

            <div class="h-px bg-border mx-3 my-1"></div>

            <button @click="openChangeWidget('{{ $id }}'); open = false"
                    class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-[9px] font-black uppercase tracking-widest hover:bg-foreground/5 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>
                Change Widget
            </button>

            <div class="h-px bg-border mx-3 my-1"></div>

            <button @click="removeWidget('{{ $id }}'); open = false"
                    class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-[9px] font-black uppercase tracking-widest text-red-500 hover:bg-red-500/5 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                Remove Widget
            </button>
        </div>
    </div>
</div>
