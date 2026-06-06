@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    item: {},
    openEdit(n) {
        this.item = JSON.parse(JSON.stringify(n));
        this.showEditModal = true;
    }
}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">News <span class="text-primary">Banner</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage hero slideshow images</p>
        </div>
        <button @click="showAddModal = true"
                class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
            + Add News
        </button>
    </div>


    {{-- Table --}}
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Preview</th>
                        <th class="px-5 py-3">Image Path</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($news as $n)
                    <tr class="hover:bg-foreground/5 transition-colors">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $n->id }}</td>

                        <td class="px-5 py-3">
                            <div class="w-20 h-12 rounded-lg overflow-hidden bg-foreground/5 shrink-0">
                                <img src="{{ asset($n->image) }}" alt="{{ $n->name }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.style.display='none'">
                            </div>
                        </td>

                        <td class="px-5 py-3 text-[10px] font-mono text-muted-foreground">{{ $n->image }}</td>

                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.news.toggle-active', $n) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-colors cursor-pointer
                                    {{ $n->is_active
                                        ? 'bg-green-500/10 text-green-500 hover:bg-red-500/10 hover:text-red-500'
                                        : 'bg-foreground/10 text-muted-foreground hover:bg-green-500/10 hover:text-green-500' }}">
                                    {{ $n->is_active ? 'Active' : 'Hidden' }}
                                </button>
                            </form>
                        </td>

                        <td class="px-5 py-3">
                            <button @click="openEdit({{ json_encode(['id' => $n->id, 'name' => $n->name, 'image' => $n->image, 'sort_order' => $n->sort_order, 'is_active' => (bool)$n->is_active]) }})"
                                    class="px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/20 transition-colors">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-xs text-muted-foreground font-bold">No news items yet. Add one above.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $news->links() }}
    </div>

    {{-- ── ADD MODAL ───────────────────────────────────────────────── --}}
    <div x-show="showAddModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition.opacity style="display:none">
        <div class="absolute inset-0 bg-black/60" @click="showAddModal = false"></div>
        <div class="relative bg-card border border-border rounded-2xl w-full max-w-md shadow-2xl p-6 space-y-5" @click.stop>
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest">Add News Item</h2>
                <button @click="showAddModal = false" class="text-muted-foreground hover:text-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Name</label>
                    <input type="text" name="name" required placeholder="e.g. Summer Sale Banner"
                           class="w-full px-3 py-2 bg-background border border-border rounded-xl text-xs font-bold outline-none focus:border-primary transition-colors">
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Image <span class="text-muted-foreground/60">(jpg/png/gif/webp · max 4 MB)</span></label>
                    <input type="file" name="image" accept="image/*" required
                           class="w-full px-3 py-2 bg-background border border-border rounded-xl text-xs font-bold outline-none focus:border-primary transition-colors file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:tracking-widest file:bg-primary/10 file:text-primary cursor-pointer">
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="w-4 h-4 rounded accent-primary">
                    <span class="text-[10px] font-black uppercase tracking-widest">Active</span>
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showAddModal = false"
                            class="px-4 py-2 bg-foreground/5 text-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-foreground/10 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">
                        Add News
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── EDIT MODAL ──────────────────────────────────────────────── --}}
    <div x-show="showEditModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition.opacity style="display:none">
        <div class="absolute inset-0 bg-black/60" @click="showEditModal = false"></div>
        <div class="relative bg-card border border-border rounded-2xl w-full max-w-md shadow-2xl p-6 space-y-5" @click.stop>
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest">Edit News Item</h2>
                <button @click="showEditModal = false" class="text-muted-foreground hover:text-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <template x-if="showEditModal">
                <form :action="'/admin/news/' + item.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Name</label>
                        <input type="text" name="name" :value="item.name" required
                               class="w-full px-3 py-2 bg-background border border-border rounded-xl text-xs font-bold outline-none focus:border-primary transition-colors">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Current Image</label>
                        <div class="w-full h-24 rounded-xl overflow-hidden bg-foreground/5">
                            <img :src="'/' + item.image" :alt="item.name" class="w-full h-full object-cover">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Replace Image <span class="text-muted-foreground/60">(leave blank to keep current)</span></label>
                        <input type="file" name="image" accept="image/*"
                               class="w-full px-3 py-2 bg-background border border-border rounded-xl text-xs font-bold outline-none focus:border-primary transition-colors file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:tracking-widest file:bg-primary/10 file:text-primary cursor-pointer">
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="item.is_active"
                               class="w-4 h-4 rounded accent-primary">
                        <span class="text-[10px] font-black uppercase tracking-widest">Active</span>
                    </label>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showEditModal = false"
                                class="px-4 py-2 bg-foreground/5 text-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-foreground/10 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
