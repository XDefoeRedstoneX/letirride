@extends('admin.layouts.admin')

@section('title', 'Discount Types — Admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    discount: {},
    form: { type: 'percent', target_scope: 'storewide' },
    openAdd() {
        this.form = { type: 'percent', target_scope: 'storewide' };
        this.showAddModal = true;
    },
    openEdit(d) {
        this.discount = JSON.parse(JSON.stringify(d));
        this.discount.target_scope = d.target_subcategory_id ? 'subcategory' : (d.target_category_id ? 'category' : 'storewide');
        this.showEditModal = true;
    },
    openDelete(d) {
        this.discount = d;
        this.showDeleteModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Discount <span class="text-primary">Types</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">The shared voucher catalog used by gacha prizes &amp; the point shop</p>
        </div>
        <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">+ ADD DISCOUNT</button>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/40 text-green-400 px-4 py-3 rounded-xl text-xs font-bold">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-xs font-bold">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-xs font-bold">{{ $errors->first() }}</div>
    @endif

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Value</th>
                        <th class="px-5 py-3">Applies to</th>
                        <th class="px-5 py-3">Used by</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($discounts as $d)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $d->id }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $d->name }}</td>
                        <td class="px-5 py-3"><span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground">{{ $d->type }}</span></td>
                        <td class="px-5 py-3 text-xs font-bold text-primary">{{ $d->valueLabel() }}</td>
                        <td class="px-5 py-3 text-xs text-muted-foreground">{{ $d->scopeLabel() }}</td>
                        <td class="px-5 py-3 text-[10px] text-muted-foreground">{{ $d->gacha_pools_count }} gacha · {{ $d->point_shop_items_count }} shop</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <button @click="openEdit({{ json_encode($d) }})" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <button @click="openDelete({{ json_encode($d) }})" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-xs text-muted-foreground">No discount types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Modal --}}
    <div x-show="showAddModal" @click.self="showAddModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="modal-header">
                    <div><h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW DISCOUNT</h2></div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('admin.discounts.store') }}" class="flex flex-col gap-4">
                    @csrf
                    @include('admin._discount-type-form', ['model' => 'form'])
                    <div class="flex justify-end gap-3 mt-4 pt-5 border-t border-border/50">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest">SAVE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" @click.self="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="modal-header">
                    <div><h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT DISCOUNT</h2><p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + discount.id"></p></div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ url('/admin/discounts') }}/' + discount.id" class="flex flex-col gap-4">
                    @csrf @method('PATCH')
                    @include('admin._discount-type-form', ['model' => 'discount'])
                    <div class="flex justify-end gap-3 mt-4 pt-5 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest">UPDATE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div x-show="showDeleteModal" @click.self="showDeleteModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-md">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                <p class="text-sm text-foreground/70 dark:text-muted-foreground">Delete <strong class="text-foreground" x-text="discount.name"></strong>? In-use discounts can't be deleted — reassign any gacha prizes / point-shop items first.</p>
                <form method="POST" :action="'{{ url('/admin/discounts') }}/' + discount.id" class="flex justify-end gap-3 mt-4 pt-5 border-t border-border/50">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">DELETE</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
