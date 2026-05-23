@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    showEditModal: false,
    showHistoryModal: false,
    user: {},
    openEdit(u) {
        this.user = JSON.parse(JSON.stringify(u));
        this.showEditModal = true;
    },
    openHistory(u) {
        this.user = u;
        this.showHistoryModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Customers</h1>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Points</th>
                        <th class="px-5 py-3">Orders</th>
                        <th class="px-5 py-3">Referral</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($users as $u)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $u->id }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-foreground/10 flex items-center justify-center text-[10px] font-black uppercase shrink-0">
                                    {{ substr($u->name, 0, 2) }}
                                </div>
                                <span class="text-xs font-bold">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-xs text-muted-foreground">{{ $u->email }}</td>
                        <td class="px-5 py-3">
                            @if($u->role === 'banned')
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-red-500/10 text-red-500">Banned</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-green-500/10 text-green-500">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($u->role === 'admin')
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-primary/10 text-primary">Admin</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-foreground/5 text-muted-foreground">Buyer</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs font-bold text-yellow-500">{{ number_format($u->points_balance) }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $u->orders_count }}</td>
                        <td class="px-5 py-3 text-[10px] text-muted-foreground">
                            @if($u->referral_code)
                                <span class="font-mono font-bold text-primary">{{ $u->referral_code }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <button @click="openEdit({{ json_encode($u) }})" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <button @click="openHistory({{ json_encode($u) }})" class="px-2.5 py-1.5 bg-blue-500/10 text-blue-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-500/20 transition-colors">History</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-border">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="showEditModal" @click.self="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-card border border-border rounded-3xl shadow-2xl w-full max-w-lg overflow-y-auto max-h-[95vh]">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT USER</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + user.id + ' — ' + user.name"></p>
                    </div>
                    <button type="button" @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ route('admin.users') }}/' + user.id" class="flex flex-col gap-4 mt-2">
                    @csrf @method('PATCH')
                    
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINTS BALANCE <span class="text-red-500">*</span></label>
                        <input type="number" name="points_balance" x-model="user.points_balance" required min="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                    </div>
                    
                    <!-- Hidden role input to pass backend validation -->
                    <input type="hidden" name="role" :value="user.role">
                    
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE USER</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order History Modal -->
    <div x-show="showHistoryModal" @click.self="showHistoryModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-card border border-border rounded-3xl shadow-2xl w-full max-w-3xl overflow-y-auto max-h-[95vh]">
            <div class="p-6 sm:p-8 flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">ORDER HISTORY</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="user.name + '\'s Past Purchases'"></p>
                    </div>
                    <button type="button" @click="showHistoryModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                
                <div class="mt-4 border-2 border-border/50 rounded-2xl overflow-hidden bg-foreground/5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground border-b-2 border-border/50">
                                    <th class="px-5 py-3">Invoice</th>
                                    <th class="px-5 py-3">Date</th>
                                    <th class="px-5 py-3">Total</th>
                                    <th class="px-5 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-border/50">
                                <!-- UI Empty/Placeholder State -->
                                <tr class="hover:bg-foreground/5">
                                    <td colspan="4" class="px-5 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-muted-foreground">
                
                                            
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    <button type="button" @click="showHistoryModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CLOSE</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection