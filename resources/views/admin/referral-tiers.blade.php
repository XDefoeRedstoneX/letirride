@extends('admin.layouts.admin')

@section('title', 'Referral Tiers — Admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    tier: {},
    openAdd() {
        this.tier = { threshold: '', title: '', description: '', points_reward: 0, discount_type_id: '', free_spins_reward: 0, icon: '', sort_order: 0, is_active: true };
        this.showAddModal = true;
    },
    openEdit(t) {
        this.tier = JSON.parse(JSON.stringify(t));
        this.tier.discount_type_id = this.tier.discount_type_id ?? '';
        this.showEditModal = true;
    },
    openDelete(t) {
        this.tier = t;
        this.showDeleteModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Referral <span class="text-primary">Tiers</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Milestone rewards by paid-referral count &mdash; auto-grants on unlock</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.referral-tiers.backfill') }}">
                @csrf
                <button type="submit" class="px-5 py-2.5 bg-foreground/5 border border-border rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">
                    RUN BACKFILL
                </button>
            </form>
            <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
                + ADD TIER
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-xs font-bold">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest">Tiers ({{ $tiers->total() }})</h3>
            <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">Lower threshold = unlocks first</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Threshold</th>
                        <th class="px-5 py-3">Title</th>
                        <th class="px-5 py-3">Points</th>
                        <th class="px-5 py-3">Discount</th>
                        <th class="px-5 py-3">Free Spins</th>
                        <th class="px-5 py-3">Active</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($tiers as $tier)
                        <tr class="hover:bg-foreground/5">
                            <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $tier->id }}</td>
                            <td class="px-5 py-3 text-xs font-bold">{{ $tier->threshold }}</td>
                            <td class="px-5 py-3 text-xs font-bold">{{ $tier->title }}</td>
                            <td class="px-5 py-3 text-xs">{{ number_format($tier->points_reward) }}</td>
                            <td class="px-5 py-3 text-xs text-muted-foreground">{{ $tier->discountType?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-xs">{{ $tier->free_spins_reward }}</td>
                            <td class="px-5 py-3">
                                @if ($tier->is_active)
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-green-500/10 text-green-400">ON</span>
                                @else
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground">OFF</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-1">
                                    <button @click="openEdit({{ json_encode($tier) }})" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                    <button @click="openDelete({{ json_encode($tier) }})" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-8 text-xs text-center text-muted-foreground">No tiers yet. Click "Add Tier" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $tiers->links() }}
    </div>

    {{-- Add Modal --}}
    <div x-show="showAddModal" @click.self="showAddModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW TIER</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DEFINE A MILESTONE</p>
                    </div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('admin.referral-tiers.store') }}" class="flex flex-col gap-4">
                    @csrf
                    @include('admin._referral-tier-form', ['discountTypes' => $discountTypes])
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE TIER</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" @click.self="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT TIER</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + tier.id"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ url('/admin/referral-tiers') }}/' + tier.id" class="flex flex-col gap-4">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">THRESHOLD <span class="req">*</span></label>
                            <input type="number" name="threshold" x-model.number="tier.threshold" required min="1" max="1000" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">TITLE <span class="req">*</span></label>
                            <input type="text" name="title" x-model="tier.title" required maxlength="128" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DESCRIPTION</label>
                            <input type="text" name="description" x-model="tier.description" maxlength="255" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINTS REWARD</label>
                            <input type="number" name="points_reward" x-model.number="tier.points_reward" min="0" max="1000000" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DISCOUNT TYPE</label>
                            <select name="discount_type_id" x-model="tier.discount_type_id" class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                                <option value="">— None —</option>
                                @foreach ($discountTypes as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">FREE SPINS</label>
                            <input type="number" name="free_spins_reward" x-model.number="tier.free_spins_reward" min="0" max="1000" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ICON (LUCIDE NAME)</label>
                            <input type="text" name="icon" x-model="tier.icon" maxlength="64" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">SORT ORDER</label>
                            <input type="number" name="sort_order" x-model.number="tier.sort_order" min="0" max="1000" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div class="flex items-center gap-2 sm:col-span-2">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-bind:checked="tier.is_active" class="w-4 h-4">
                            <label for="edit_is_active" class="text-xs font-bold text-foreground">Active (inactive tiers never grant)</label>
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

    {{-- Delete Modal --}}
    <div x-show="showDeleteModal" @click.self="showDeleteModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <p class="text-sm font-sans text-foreground/70 dark:text-muted-foreground">Delete tier <strong class="text-foreground" x-text="tier.title"></strong>? Already-granted rewards stay in the audit log; only future grants stop.</p>
                <form method="POST" :action="'{{ url('/admin/referral-tiers') }}/' + tier.id" class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
