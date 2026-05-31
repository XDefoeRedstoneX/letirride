@extends('admin.layouts.admin')

@section('title', 'Gacha Icons — Admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    icon: {},
    openAdd() { this.showAddModal = true; },
    openEdit(i) {
        this.icon = JSON.parse(JSON.stringify(i));
        this.showEditModal = true;
    },
    openDelete(i) {
        this.icon = i;
        this.showDeleteModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Gacha <span class="text-primary">Icons</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Coin-form icons prizes pick from — the rarity frame is layered on at render time</p>
        </div>
        <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
            + ADD ICON
        </button>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/40 text-green-400 px-4 py-3 rounded-xl text-xs font-bold">{{ session('success') }}</div>
    @endif

    @error('key')
        <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-xs font-bold">{{ $message }}</div>
    @enderror

    @foreach($categories as $catKey => $catLabel)
        @php $catIcons = $iconsByCategory[$catKey] ?? collect(); @endphp
        @if($catIcons->count())
            <div class="bg-card border border-border rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                    <h3 class="text-sm font-black uppercase tracking-widest">{{ $catLabel }}</h3>
                    <span class="text-[10px] text-muted-foreground font-bold">{{ $catIcons->count() }} coins</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 p-5">
                    @foreach($catIcons as $icon)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-border bg-foreground/5 {{ $icon->is_active ? '' : 'opacity-50' }}">
                            <img src="{{ $icon->image_path }}" alt="{{ $icon->label }}" class="w-11 h-11 object-contain pixel-render flex-shrink-0" onerror="this.onerror=null;this.src='{{ \App\Support\GachaIconCatalog::fallbackPath() }}';">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-foreground truncate">{{ $icon->label }}</p>
                                <p class="text-[10px] font-mono text-muted-foreground truncate">{{ $icon->key }}</p>
                                <div class="flex items-center gap-1 mt-1.5">
                                    <button @click="openEdit({{ json_encode($icon) }})" class="px-2 py-1 bg-foreground/10 rounded-md text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                    <button @click="openDelete({{ json_encode($icon) }})" class="px-2 py-1 bg-red-500/10 text-red-500 rounded-md text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Del</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    <!-- Add Modal -->
    <div x-show="showAddModal" @click.self="showAddModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8" style="display:flex;flex-direction:column;gap:12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW ICON</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">ADD A COIN</p>
                    </div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('admin.gacha-icons.store') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
                    @csrf
                    @include('admin._gacha-icon-form')
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE ICON</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" @click.self="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8" style="display:flex;flex-direction:column;gap:12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT ICON</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + icon.id"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-xl border border-border bg-foreground/5">
                    <img :src="icon.image_path" alt="" class="w-12 h-12 object-contain pixel-render" />
                    <p class="text-xs text-muted-foreground">Current coin preview</p>
                </div>
                <form method="POST" :action="'{{ url('/admin/gacha-icons') }}/' + icon.id" enctype="multipart/form-data" class="flex flex-col gap-4">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">KEY <span class="req">*</span></label>
                            <input type="text" name="key" x-model="icon.key" required pattern="[a-z0-9\-]+" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-mono font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">LABEL <span class="req">*</span></label>
                            <input type="text" name="label" x-model="icon.label" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">CATEGORY <span class="req">*</span></label>
                            <select name="category" x-model="icon.category" required class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">SORT ORDER</label>
                            <input type="number" name="sort_order" x-model="icon.sort_order" min="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">UPLOAD NEW IMAGE</label>
                            <input type="file" name="image_file" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="w-full px-4 py-2.5 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs text-foreground outline-none focus:border-primary transition-all">
                            <p class="text-[10px] text-muted-foreground mt-1">Leave blank to keep the current image.</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">IMAGE URL</label>
                            <input type="text" name="image_url" x-model="icon.image_path" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-mono text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="edit_icon_is_active" value="1" x-bind:checked="icon.is_active" class="w-4 h-4">
                            <label for="edit_icon_is_active" class="text-xs font-bold text-foreground">Active</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="showDeleteModal" @click.self="showDeleteModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8" style="display:flex;flex-direction:column;gap:12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <p class="text-sm font-sans text-foreground/70 dark:text-muted-foreground">Delete coin <strong class="text-foreground" x-text="icon.label"></strong>? Prizes using it will fall back to their reward-type coin.</p>
                <form method="POST" :action="'{{ url('/admin/gacha-icons') }}/' + icon.id" class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
