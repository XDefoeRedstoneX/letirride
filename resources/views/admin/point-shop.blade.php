@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Point <span class="text-primary">Shop</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage redeemable rewards</p>
        </div>
        <button onclick="document.getElementById('addItemForm').classList.toggle('hidden')" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            Add Item
        </button>
    </div>

    <!-- Add Item Form -->
    <div id="addItemForm" class="hidden bg-card border border-border rounded-2xl p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest">New Point Shop Item</h3>
            <button onclick="document.getElementById('addItemForm').classList.add('hidden')" class="p-1 hover:bg-foreground/5 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
            </button>
        </div>
        <form class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Item Name <span class="text-red-500">*</span></label>
                <input type="text" placeholder="e.g. 10% Discount Voucher" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none">
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Point Cost <span class="text-red-500">*</span></label>
                <input type="number" placeholder="e.g. 500" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none">
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Reward Type</label>
                <select class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none">
                    <option>Discount Voucher</option>
                    <option>Free Shipping</option>
                    <option>Cashback</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Image</label>
                <input type="text" placeholder="e.g. voucher.svg" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none">
            </div>
            <div class="md:col-span-2 lg:col-span-4 space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Description</label>
                <textarea rows="2" placeholder="Describe this reward..." class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none"></textarea>
            </div>
            <div class="md:col-span-2 lg:col-span-4 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addItemForm').classList.add('hidden')" class="px-8 py-2.5 bg-foreground/5 rounded-xl text-[10px] font-black">Cancel</button>
                <button type="submit" class="px-8 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black">Add Item</button>
            </div>
        </form>
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
                    @foreach($shopItems as $item)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $item['id'] }}</td>
                        <td class="px-5 py-3">
                            <div>
                                <span class="text-xs font-bold">{{ $item['name'] }}</span>
                                <p class="text-[9px] text-muted-foreground">{{ $item['desc'] }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground">{{ $item['type'] }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs font-bold text-yellow-500">{{ number_format($item['cost']) }} pts</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $item['redeemed'] }}x</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $item['active'] ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">{{ $item['active'] ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex gap-1">
                                <button onclick="document.getElementById('editItem{{ $item['id'] }}').classList.toggle('hidden')" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <button class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                            </div>

                            <!-- Edit Inline Form -->
                            <div id="editItem{{ $item['id'] }}" class="hidden mt-3 p-4 bg-foreground/5 rounded-xl border border-border">
                                <form class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Name</label>
                                        <input type="text" value="{{ $item['name'] }}" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Point Cost</label>
                                        <input type="number" value="{{ $item['cost'] }}" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Type</label>
                                        <select class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                            <option {{ $item['type'] === 'Discount' ? 'selected' : '' }}>Discount</option>
                                            <option {{ $item['type'] === 'Shipping' ? 'selected' : '' }}>Shipping</option>
                                            <option {{ $item['type'] === 'Cashback' ? 'selected' : '' }}>Cashback</option>
                                            <option {{ $item['type'] === 'Badge' ? 'selected' : '' }}>Badge</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Status</label>
                                        <select class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                            <option value="1" {{ $item['active'] ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$item['active'] ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2 lg:col-span-4 space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Description</label>
                                        <textarea rows="2" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">{{ $item['desc'] }}</textarea>
                                    </div>
                                    <div class="md:col-span-2 lg:col-span-4 flex gap-2">
                                        <button type="submit" class="px-5 py-2 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Save Changes</button>
                                        <button type="button" onclick="document.getElementById('editItem{{ $item['id'] }}').classList.add('hidden')" class="px-5 py-2 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
