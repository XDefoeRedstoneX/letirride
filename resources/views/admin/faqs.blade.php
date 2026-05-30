@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    faq: {},
    openAdd() { this.showAddModal = true; },
    openEdit(f) { this.faq = JSON.parse(JSON.stringify(f)); this.showEditModal = true; },
    openDelete(f) { this.faq = f; this.showDeleteModal = true; }
}">

    @if(session('success'))
        <div class="px-3 py-2 bg-green-500/10 text-green-500 rounded-xl text-[10px] font-black uppercase tracking-widest">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">FAQ <span class="text-primary">Management</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage frequently asked questions</p>
        </div>
        <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
            + ADD FAQ
        </button>
    </div>

    <div class="space-y-6">
        @forelse($grouped as $category => $items)
        <div class="space-y-4">
            <h3 class="text-sm font-black uppercase tracking-widest">
                {{ $category }} <span class="text-[10px] text-muted-foreground">({{ $items->count() }})</span>
            </h3>
            <div class="bg-card border border-border rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                                <th class="px-5 py-3 w-16">ID</th>
                                <th class="px-5 py-3 w-1/4">Question</th>
                                <th class="px-5 py-3 w-1/2">Answer</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($items as $faq)
                            <tr class="hover:bg-foreground/5">
                                <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $faq->id }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-start gap-2">
                                        <span class="mt-0.5 px-1.5 py-0.5 bg-primary/10 text-primary rounded text-[7px] font-black uppercase tracking-widest shrink-0">Q</span>
                                        <span class="text-xs font-bold">{{ $faq->question }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-start gap-2">
                                        <span class="mt-0.5 px-1.5 py-0.5 bg-foreground/10 text-muted-foreground rounded text-[7px] font-black uppercase tracking-widest shrink-0">A</span>
                                        <span class="text-[10px] text-muted-foreground line-clamp-2">{{ Str::limit($faq->answer, 100) }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $faq->is_active ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                        {{ $faq->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-1">
                                        <button @click="openEdit({{ json_encode(['id' => $faq->id, 'category' => $faq->category, 'question' => $faq->question, 'answer' => $faq->answer, 'sort_order' => $faq->sort_order, 'is_active' => $faq->is_active]) }})"
                                                class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">
                                            Edit
                                        </button>
                                        <button @click="openDelete({{ json_encode(['id' => $faq->id, 'question' => $faq->question]) }})"
                                                class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16 text-muted-foreground text-xs font-bold uppercase tracking-widest">
            No FAQs yet. Add one to get started.
        </div>
        @endforelse
    </div>

    {{-- Add FAQ Modal --}}
    <div x-show="showAddModal" @click.self="showAddModal = false"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW FAQ</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">ADD KNOWLEDGE BASE</p>
                    </div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('admin.faqs.store') }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Category *</label>
                            <input type="text" name="category" list="category-list" required
                                   class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
                                   placeholder="e.g. General">
                            <datalist id="category-list">
                                @foreach($grouped->keys() as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 bg-primary/10 text-primary rounded text-[7px] font-black">Q</span> Question *
                            </label>
                            <input type="text" name="question" required
                                   class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
                                   placeholder="e.g. How to refund?">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Sort Order</label>
                            <input type="number" name="sort_order" value="0" min="0"
                                   class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Status</label>
                            <select name="is_active" class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                                <option value="1">Active</option>
                                <option value="0">Hidden</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 bg-foreground/10 text-muted-foreground rounded text-[7px] font-black">A</span> Answer *
                        </label>
                        <textarea name="answer" rows="3" required
                                  class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
                                  placeholder="Detailed explanation..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-border/50">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit FAQ Modal --}}
    <div x-show="showEditModal" @click.self="showEditModal = false"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT FAQ</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + faq.id"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <form :action="'{{ url('admin/faqs') }}/' + faq.id" method="POST" class="flex flex-col gap-4">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Category *</label>
                            <input type="text" name="category" :value="faq.category" list="edit-category-list" required
                                   class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                            <datalist id="edit-category-list">
                                @foreach($grouped->keys() as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 bg-primary/10 text-primary rounded text-[7px] font-black">Q</span> Question *
                            </label>
                            <input type="text" name="question" :value="faq.question" required
                                   class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Sort Order</label>
                            <input type="number" name="sort_order" :value="faq.sort_order" min="0"
                                   class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 block">Status</label>
                            <select name="is_active" class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                                <option value="1" :selected="faq.is_active">Active</option>
                                <option value="0" :selected="!faq.is_active">Hidden</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2 flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 bg-foreground/10 text-muted-foreground rounded text-[7px] font-black">A</span> Answer *
                        </label>
                        <textarea name="answer" rows="3" required x-text="faq.answer"
                                  class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete FAQ Modal --}}
    <div x-show="showDeleteModal" @click.self="showDeleteModal = false"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-lg">
            <div class="p-6 sm:p-8 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <p class="text-sm text-muted-foreground">
                    Are you sure you want to delete <strong class="text-foreground" x-text="faq.question"></strong>? This cannot be undone.
                </p>
                <form :action="'{{ url('admin/faqs') }}/' + faq.id" method="POST" class="flex justify-end gap-3 pt-4 border-t border-border/50">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
