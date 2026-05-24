@extends('admin.layouts.admin')

@section('title', 'Gacha Boosters — Admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    booster: {},
    openAdd() { this.showAddModal = true; },
    openEdit(b) {
        this.booster = JSON.parse(JSON.stringify(b));
        this.showEditModal = true;
    },
    openDelete(b) {
        this.booster = b;
        this.showDeleteModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Gacha <span class="text-primary">Boosters</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Time-limited luck boosters players can buy with points</p>
        </div>
        <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
            + ADD BOOSTER
        </button>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/40 text-green-400 px-4 py-3 rounded-xl text-xs font-bold">{{ session('success') }}</div>
    @endif

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/></svg>
                Boosters
                <span class="text-[10px] text-muted-foreground font-bold">({{ $boosters->count() }} total)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Key</th>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Cost</th>
                        <th class="px-5 py-3">Floor</th>
                        <th class="px-5 py-3">Bonus %</th>
                        <th class="px-5 py-3">Duration</th>
                        <th class="px-5 py-3">Active</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($boosters as $b)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $b->id }}</td>
                        <td class="px-5 py-3 text-xs font-mono">{{ $b->key }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $b->name }}</td>
                        <td class="px-5 py-3 text-xs">{{ number_format($b->point_cost) }} pts</td>
                        <td class="px-5 py-3 text-xs uppercase">{{ str_replace('_', ' ', $b->rarity_floor) }}</td>
                        <td class="px-5 py-3 text-xs font-bold">+{{ $b->bonus_percent }}%</td>
                        <td class="px-5 py-3 text-xs">{{ $b->duration_minutes }} min</td>
                        <td class="px-5 py-3">
                            @if ($b->is_active)
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-green-500/10 text-green-400">ON</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground">OFF</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <button @click="openEdit({{ json_encode($b) }})" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <button @click="openDelete({{ json_encode($b) }})" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-5 py-8 text-xs text-center text-muted-foreground">No boosters yet. Click "Add Booster" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div @click.away="showAddModal = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8" style="display:flex;flex-direction:column;gap:12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW BOOSTER</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">CONFIGURE BUFF</p>
                    </div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('admin.gacha-boosters.store') }}" class="flex flex-col gap-4">
                    @csrf
                    @include('admin._gacha-booster-form', ['booster' => null])
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE BOOSTER</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div @click.away="showEditModal = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8" style="display:flex;flex-direction:column;gap:12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT BOOSTER</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + booster.id"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ url('/admin/gacha-boosters') }}/' + booster.id" class="flex flex-col gap-4">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">KEY <span class="req">*</span></label>
                            <input type="text" name="key" x-model="booster.key" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-mono font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">NAME <span class="req">*</span></label>
                            <input type="text" name="name" x-model="booster.name" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DESCRIPTION</label>
                            <input type="text" name="description" x-model="booster.description" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINT COST <span class="req">*</span></label>
                            <input type="number" name="point_cost" x-model="booster.point_cost" required min="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">RARITY FLOOR <span class="req">*</span></label>
                            <select name="rarity_floor" x-model="booster.rarity_floor" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                                <option value="uncommon">Uncommon+</option>
                                <option value="rare">Rare+</option>
                                <option value="epic">Epic+</option>
                                <option value="legendary">Legendary+</option>
                                <option value="grand_prize">Grand Prize</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">BONUS % <span class="req">*</span></label>
                            <input type="number" name="bonus_percent" x-model="booster.bonus_percent" required min="0" max="100" step="0.01" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DURATION (min) <span class="req">*</span></label>
                            <input type="number" name="duration_minutes" x-model="booster.duration_minutes" required min="1" max="1440" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-bind:checked="booster.is_active" class="w-4 h-4">
                            <label for="edit_is_active" class="text-xs font-bold text-foreground">Active</label>
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
    <div x-show="showDeleteModal" @click="showDeleteModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div @click.away="showDeleteModal = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            <div class="p-6 sm:p-8" style="display:flex;flex-direction:column;gap:12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <p class="text-sm font-sans text-foreground/70 dark:text-muted-foreground">Delete booster <strong class="text-foreground" x-text="booster.name"></strong>? Players will lose any active instances.</p>
                <form method="POST" :action="'{{ url('/admin/gacha-boosters') }}/' + booster.id" class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
