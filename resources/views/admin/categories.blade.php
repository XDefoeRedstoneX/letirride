@extends('admin.layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="space-y-8" x-data="{
    showAddCat: false,
    showEditCat: false,
    showDeleteCat: false,
    showAddSub: false,
    showEditSub: false,
    showDeleteSub: false,
    cat: {},
    sub: {},
    openAddCat() { this.cat = {}; this.showAddCat = true; },
    openEditCat(c) { this.cat = JSON.parse(JSON.stringify(c)); this.showEditCat = true; },
    openDeleteCat(c) { this.cat = c; this.showDeleteCat = true; },
    openAddSub(categoryId) { this.sub = { category_id: categoryId || '' }; this.showAddSub = true; },
    openEditSub(s) { this.sub = JSON.parse(JSON.stringify(s)); this.showEditSub = true; },
    openDeleteSub(s) { this.sub = s; this.showDeleteSub = true; }
}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Category <span class="text-primary">Management</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage product categories &amp; subcategories (brands)</p>
        </div>
        <div class="flex gap-2">
            <button @click="openAddSub('')" class="px-5 py-2.5 bg-foreground/5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/10 hover:text-primary transition-colors">
                + SUBCATEGORY
            </button>
            <button @click="openAddCat()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
                + CATEGORY
            </button>
        </div>
    </div>

    {{-- Category list --}}
    <div class="space-y-5">
        @forelse($categories as $category)
        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            {{-- Category header row --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-border bg-foreground/5">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-black uppercase tracking-widest text-foreground">{{ $category->name }}</span>
                    <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[8px] font-black uppercase tracking-widest">{{ $category->products_count }} {{ Str::plural('Product', $category->products_count) }}</span>
                    <span class="px-2 py-0.5 rounded-lg bg-foreground/10 text-muted-foreground text-[8px] font-black uppercase tracking-widest">{{ $category->subcategories_count }} {{ Str::plural('Sub', $category->subcategories_count) }}</span>
                </div>
                <div class="flex gap-1">
                    <button @click="openAddSub('{{ $category->id }}')"
                            class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">
                        + Sub
                    </button>
                    <button @click="openEditCat({{ json_encode(['id' => $category->id, 'name' => $category->name]) }})"
                            class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">
                        Edit
                    </button>
                    @if ($category->products_count === 0 && $category->subcategories_count === 0)
                        <button @click="openDeleteCat({{ json_encode(['id' => $category->id, 'name' => $category->name]) }})"
                                class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">
                            Delete
                        </button>
                    @else
                        <button disabled title="Remove its products and subcategories first"
                                class="px-2.5 py-1.5 bg-foreground/5 text-muted-foreground/40 rounded-lg text-[9px] font-black uppercase tracking-widest cursor-not-allowed">
                            Delete
                        </button>
                    @endif
                </div>
            </div>

            {{-- Subcategories --}}
            @if ($category->subcategories->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                            <th class="px-5 py-2.5 w-16">ID</th>
                            <th class="px-5 py-2.5">Subcategory</th>
                            <th class="px-5 py-2.5">Slug</th>
                            <th class="px-5 py-2.5">Products</th>
                            <th class="px-5 py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($category->subcategories as $sub)
                        <tr class="hover:bg-foreground/5">
                            <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $sub->id }}</td>
                            <td class="px-5 py-3 text-xs font-bold">{{ $sub->name }}</td>
                            <td class="px-5 py-3 text-[10px] font-mono text-muted-foreground">{{ $sub->slug }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[8px] font-black uppercase tracking-widest">{{ $sub->products_count }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex gap-1 justify-end">
                                    <button @click="openEditSub({{ json_encode(['id' => $sub->id, 'name' => $sub->name, 'category_id' => $sub->category_id]) }})"
                                            class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">
                                        Edit
                                    </button>
                                    @if ($sub->products_count === 0)
                                        <button @click="openDeleteSub({{ json_encode(['id' => $sub->id, 'name' => $sub->name]) }})"
                                                class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">
                                            Delete
                                        </button>
                                    @else
                                        <button disabled title="Remove its products first"
                                                class="px-2.5 py-1.5 bg-foreground/5 text-muted-foreground/40 rounded-lg text-[9px] font-black uppercase tracking-widest cursor-not-allowed">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-5 py-4 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">No subcategories yet.</div>
            @endif
        </div>
        @empty
        <div class="text-center py-16 text-muted-foreground text-xs font-bold uppercase tracking-widest">
            No categories yet. Add one to get started.
        </div>
        @endforelse
    </div>

    {{-- ── Add Category Modal ── --}}
    <div x-show="showAddCat" @click.self="showAddCat = false"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-lg">
            <div class="p-6 sm:p-8 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW CATEGORY</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">TOP-LEVEL PRODUCT GROUP</p>
                    </div>
                    <button @click="showAddCat = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('admin.categories.store') }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Name *</label>
                        <input type="text" name="name" required autofocus
                               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
                               placeholder="e.g. Gift Cards">
                        <p class="text-[9px] font-bold text-muted-foreground/70 mt-1.5 uppercase tracking-widest">A URL slug is generated automatically.</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-border/50">
                        <button type="button" @click="showAddCat = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE CATEGORY</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Edit Category Modal ── --}}
    <div x-show="showEditCat" @click.self="showEditCat = false"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-lg">
            <div class="p-6 sm:p-8 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT CATEGORY</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + cat.id"></p>
                    </div>
                    <button @click="showEditCat = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form :action="'{{ url('admin/categories') }}/' + cat.id" method="POST" class="flex flex-col gap-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Name *</label>
                        <input type="text" name="name" :value="cat.name" required
                               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-border/50">
                        <button type="button" @click="showEditCat = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE CATEGORY</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Delete Category Modal ── --}}
    <div x-show="showDeleteCat" @click.self="showDeleteCat = false"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-lg">
            <div class="p-6 sm:p-8 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteCat = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <p class="text-sm text-muted-foreground">
                    Are you sure you want to delete the category <strong class="text-foreground" x-text="cat.name"></strong>? This cannot be undone.
                </p>
                <form :action="'{{ url('admin/categories') }}/' + cat.id" method="POST" class="flex justify-end gap-3 pt-4 border-t border-border/50">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteCat = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Add Subcategory Modal ── --}}
    <div x-show="showAddSub" @click.self="showAddSub = false"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-lg">
            <div class="p-6 sm:p-8 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW SUBCATEGORY</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">BRAND / GROUP INSIDE A CATEGORY</p>
                    </div>
                    <button @click="showAddSub = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('admin.subcategories.store') }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Category *</label>
                        <select name="category_id" x-model="sub.category_id" required
                                class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                            <option value="" disabled>Select a category…</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Name *</label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
                               placeholder="e.g. Steam">
                        <p class="text-[9px] font-bold text-muted-foreground/70 mt-1.5 uppercase tracking-widest">A URL slug is generated automatically.</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-border/50">
                        <button type="button" @click="showAddSub = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE SUBCATEGORY</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Edit Subcategory Modal ── --}}
    <div x-show="showEditSub" @click.self="showEditSub = false"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-lg">
            <div class="p-6 sm:p-8 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT SUBCATEGORY</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + sub.id"></p>
                    </div>
                    <button @click="showEditSub = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form :action="'{{ url('admin/subcategories') }}/' + sub.id" method="POST" class="flex flex-col gap-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Category *</label>
                        <select name="category_id" x-model="sub.category_id" required
                                class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Name *</label>
                        <input type="text" name="name" :value="sub.name" required
                               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-border/50">
                        <button type="button" @click="showEditSub = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE SUBCATEGORY</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Delete Subcategory Modal ── --}}
    <div x-show="showDeleteSub" @click.self="showDeleteSub = false"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-lg">
            <div class="p-6 sm:p-8 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteSub = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <p class="text-sm text-muted-foreground">
                    Are you sure you want to delete the subcategory <strong class="text-foreground" x-text="sub.name"></strong>? This cannot be undone.
                </p>
                <form :action="'{{ url('admin/subcategories') }}/' + sub.id" method="POST" class="flex justify-end gap-3 pt-4 border-t border-border/50">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteSub = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
