@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    item: {},
    openAdd() {
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
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage redeemable rewards</p>
        </div>
        <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
            + ADD ITEM
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-card border border-border rounded-2xl p-5 space-y-2">
            <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Total Items</p>
            <p class="text-2xl font-black text-primary">6</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-5 space-y-2">
            <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Active Items</p>
            <p class="text-2xl font-black text-green-500">5</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-5 space-y-2">
            <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Total Redeemed</p>
            <p class="text-2xl font-black text-amber-500">142</p>
        </div>
    </div>

    <!-- Items Table -->
    @php
        $shopItems = [
            ['id' => 1, 'name' => '10% Discount Voucher', 'desc' => 'Get 10% off your next purchase', 'cost' => 500, 'type' => 'Discount', 'active' => true, 'redeemed' => 48],
            ['id' => 2, 'name' => '25% Discount Voucher', 'desc' => 'Get 25% off your next purchase', 'cost' => 1000, 'type' => 'Discount', 'active' => true, 'redeemed' => 32],
            ['id' => 3, 'name' => '50% Discount Voucher', 'desc' => 'Get 50% off your next purchase', 'cost' => 2500, 'type' => 'Discount', 'active' => true, 'redeemed' => 18],
            ['id' => 4, 'name' => 'Free Shipping Voucher', 'desc' => 'Free shipping on any order', 'cost' => 300, 'type' => 'Shipping', 'active' => true, 'redeemed' => 27],
            ['id' => 5, 'name' => 'Rp 10.000 Cashback', 'desc' => 'Get Rp 10.000 cashback', 'cost' => 1500, 'type' => 'Cashback', 'active' => true, 'redeemed' => 15],
            ['id' => 6, 'name' => 'Exclusive Badge', 'desc' => 'Limited edition profile badge', 'cost' => 5000, 'type' => 'Badge', 'active' => false, 'redeemed' => 2],
        ];
    @endphp

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Item</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Point Cost</th>
                        <th class="px-5 py-3">Redeemed</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($shopItems as $i)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $i['id'] }}</td>
                        <td class="px-5 py-3">
                            <div>
                                <span class="text-xs font-bold">{{ $i['name'] }}</span>
                                <p class="text-[9px] text-muted-foreground">{{ $i['desc'] }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground">{{ $i['type'] }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs font-bold text-yellow-500">{{ number_format($i['cost']) }} pts</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $i['redeemed'] }}x</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $i['active'] ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">{{ $i['active'] ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex gap-1">
                                <button @click="openEdit({{ json_encode($i) }})" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <button @click="openDelete({{ json_encode($i) }})" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div @click.away="showAddModal = false = false = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW ITEM</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">ADD TO POINT SHOP</p>
                    </div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ITEM NAME <span class="req">*</span></label>
                            <input type="text" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="e.g. 10% Discount">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINT COST <span class="req">*</span></label>
                            <input type="number" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="500">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">REWARD TYPE</label>
                            <select class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option>Discount Voucher</option>
                                <option>Free Shipping</option>
                                <option>Cashback</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">IMAGE FILENAME</label>
                            <input type="text" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="e.g. voucher.svg">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DESCRIPTION</label>
                        <textarea rows="2" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="Describe this reward..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="button" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE ITEM</button>
                    </div>
                </form>
            </div>
            
            
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div @click.away="showEditModal = false = false = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT ITEM</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + item.id"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ITEM NAME <span class="req">*</span></label>
                            <input type="text" x-model="item.name" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINT COST <span class="req">*</span></label>
                            <input type="number" x-model="item.cost" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">REWARD TYPE</label>
                            <select x-model="item.type" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option>Discount</option>
                                <option>Shipping</option>
                                <option>Cashback</option>
                                <option>Badge</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">STATUS</label>
                            <select x-model="item.active" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DESCRIPTION</label>
                        <textarea rows="2" x-model="item.desc" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="button" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE ITEM</button>
                    </div>
                </form>
            </div>
            
            
        </div>
    </div>

    <!-- Delete Item Modal -->
    <div x-show="showDeleteModal" @click="showDeleteModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div @click.away="showDeleteModal = false = false = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <p class="text-sm font-sans text-foreground/70 dark:text-muted-foreground">Are you sure you want to delete <strong class="text-foreground" x-text="item.name"></strong>? This action cannot be undone.</p>
                <form class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="button" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
            
            
        </div>
    </div>
</div>
@endsection
