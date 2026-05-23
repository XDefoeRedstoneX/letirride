@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    showEditModal: false,
    showDeleteModal: false,
    user: {},
    openEdit(u) {
        this.user = JSON.parse(JSON.stringify(u));
        this.showEditModal = true;
    },
    openDelete(u) {
        this.user = u;
        this.showDeleteModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Customers</h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage customer accounts & balances</p>
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
                                <button @click="openDelete({{ json_encode($u) }})" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
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
    <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div @click.away="showEditModal = false = false = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT USER</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + user.id + ' — ' + user.name"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ route('admin.users') }}/' + user.id" class="flex flex-col gap-4">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ROLE <span class="req">*</span></label>
                            <select name="role" x-model="user.role" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="buyer">Buyer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINTS BALANCE <span class="req">*</span></label>
                            <input type="number" name="points_balance" x-model="user.points_balance" required min="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE USER</button>
                    </div>
                </form>
            </div>
            
            
        </div>
    </div>

    <!-- Delete User Modal -->
    <div x-show="showDeleteModal" @click="showDeleteModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;"> {
            if(val) {
                countdown = 5;
                timer = setInterval(() => {
                    countdown--;
                    if(countdown <= 0) clearInterval(timer);
                }, 1000);
            } else {
                clearInterval(timer);
            }
         })">
        <div @click.away="showDeleteModal = false = false = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <div class="p-4 bg-red-500/5 border border-red-500/10 rounded-xl">
                    <p class="text-xs font-bold leading-relaxed text-foreground">
                        You are about to permanently delete <strong class="text-red-500" x-text="user.name"></strong>'s account.
                        All data including orders, points, and history will be lost forever.
                    </p>
                </div>
                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-muted-foreground" x-show="countdown > 0">
                    <span>Confirm in</span>
                    <span class="text-2xl font-black text-red-500 tabular-nums" x-text="countdown"></span>
                    <span>seconds</span>
                </div>
                <form class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="button" :disabled="countdown > 0" :class="countdown > 0 ? 'modal-btn-disabled' : 'modal-btn-primary'" :style="countdown <= 0 ? 'background: var(--red); border-color: #991b1b; box-shadow: 3px 3px 0 #991b1b; color: white;' : ''">
                        <span class="flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" x-show="countdown <= 0"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            DELETE FOREVER
                        </span>
                    </button>
                </form>
            </div>
            
            
        </div>
    </div>
</div>
@endsection