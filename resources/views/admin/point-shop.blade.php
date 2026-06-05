@extends('admin.layouts.admin')

@section('title', 'Point Shop — Admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    item: {},
    form: { reward_type: 'discount', is_active: true },
    openAdd() {
        this.form = { reward_type: 'discount', is_active: true };
        this.showAddModal = true;
    },
    openEdit(i) {
        this.item = JSON.parse(JSON.stringify(i));
        this.showEditModal = true;
    },
    openDelete(i) {
        this.item = i;
        this.showDeleteModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Point <span class="text-primary">Shop</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Items customers redeem with points — discount vouchers or cashback points</p>
        </div>
        <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">+ ADD ITEM</button>
    </div>

    @if ($errors->any())
        <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-xs font-bold">{{ $errors->first() }}</div>
    @endif

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">Item</th>
                        <th class="px-5 py-3">Cost</th>
                        <th class="px-5 py-3">Reward</th>
                        <th class="px-5 py-3">Redeemed</th>
                        <th class="px-5 py-3">Active</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($items as $i)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $i->img ? '/point-shop-assets/'.$i->img : '/gacha-assets/voucher.svg' }}" alt="" class="w-9 h-9 object-contain rounded-lg bg-foreground/5" onerror="this.src='/gacha-assets/voucher.svg'">
                                <div>
                                    <p class="text-xs font-bold">{{ $i->name }}</p>
                                    <p class="text-[10px] text-muted-foreground line-clamp-1 max-w-xs">{{ $i->description }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-xs font-bold text-yellow-500">{{ number_format($i->point_cost) }} pts</td>
                        <td class="px-5 py-3 text-xs">
                            @if($i->reward_type === 'cashback')
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-green-500/10 text-green-500">+{{ number_format($i->points_amount) }} pts</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-primary/10 text-primary">{{ $i->discountType?->name ?? 'Discount' }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-[10px] text-muted-foreground">{{ $i->purchases_count }}×</td>
                        <td class="px-5 py-3">
                            @if($i->is_active)
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-green-500/10 text-green-400">ON</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground">OFF</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <button @click="openEdit({{ json_encode($i) }})" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <button @click="openDelete({{ json_encode($i) }})" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-xs text-muted-foreground">No point-shop items yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $items->links() }}
    </div>

    {{-- Add Modal --}}
    <div x-show="showAddModal" @click.self="showAddModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display:none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="modal-header">
                    <div><h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW ITEM</h2></div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('admin.point-shop.store') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
                    @csrf
                    @include('admin._point-shop-form', ['model' => 'form'])
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
                    <div><h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT ITEM</h2><p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + item.id"></p></div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ url('/admin/point-shop') }}/' + item.id" enctype="multipart/form-data" class="flex flex-col gap-4">
                    @csrf @method('PATCH')
                    @include('admin._point-shop-form', ['model' => 'item'])
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
                <p class="text-sm text-foreground/70 dark:text-muted-foreground">Delete <strong class="text-foreground" x-text="item.name"></strong>? Past redemptions are kept.</p>
                <form method="POST" :action="'{{ url('/admin/point-shop') }}/' + item.id" class="flex justify-end gap-3 mt-4 pt-5 border-t border-border/50">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">DELETE</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
