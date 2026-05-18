@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Customers</h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage customer accounts & balances</p>
        </div>
    </div>

    @php
        $customers = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'active' => true, 'points' => 1250, 'orders' => 5, 'referral' => 'JOHN2024', 'joined' => 'Jan 15, 2024'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'active' => true, 'points' => 3400, 'orders' => 12, 'referral' => null, 'joined' => 'Mar 3, 2024'],
            ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'active' => false, 'points' => 500, 'orders' => 2, 'referral' => 'BOBCODE', 'joined' => 'Jun 20, 2024'],
            ['id' => 4, 'name' => 'Alice Williams', 'email' => 'alice@example.com', 'active' => true, 'points' => 8900, 'orders' => 25, 'referral' => null, 'joined' => 'Feb 8, 2024'],
            ['id' => 5, 'name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'active' => true, 'points' => 150, 'orders' => 1, 'referral' => null, 'joined' => 'Sep 12, 2024'],
            ['id' => 6, 'name' => 'Diana Prince', 'email' => 'diana@example.com', 'active' => true, 'points' => 6700, 'orders' => 18, 'referral' => 'DIANA24', 'joined' => 'Apr 5, 2024'],
            ['id' => 7, 'name' => 'Eve Adams', 'email' => 'eve@example.com', 'active' => false, 'points' => 0, 'orders' => 0, 'referral' => null, 'joined' => 'Nov 30, 2024'],
        ];
    @endphp

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Points</th>
                        <th class="px-5 py-3">Orders</th>
                        <th class="px-5 py-3">Referral</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($customers as $user)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $user['id'] }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-foreground/10 flex items-center justify-center text-[10px] font-black uppercase shrink-0">
                                    {{ substr($user['name'], 0, 2) }}
                                </div>
                                <span class="text-xs font-bold">{{ $user['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-xs text-muted-foreground">{{ $user['email'] }}</td>
                        <td class="px-5 py-3">
                            @if(!$user['active'])
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-red-500/10 text-red-500">Banned</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-green-500/10 text-green-500">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs font-bold text-yellow-500">{{ number_format($user['points']) }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $user['orders'] }}</td>
                        <td class="px-5 py-3 text-[10px] text-muted-foreground">
                            @if($user['referral'])
                                <span class="font-mono font-bold text-primary">{{ $user['referral'] }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <button onclick="document.getElementById('editUser{{ $user['id'] }}').classList.toggle('hidden')" class="px-3 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                            <button onclick="document.getElementById('deleteUser{{ $user['id'] }}').classList.toggle('hidden')" class="px-3 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors ml-1">Delete</button>

                            <!-- Delete Warning with Countdown -->
                            <div id="deleteUser{{ $user['id'] }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                                <div class="bg-card border-2 border-red-500/30 rounded-2xl p-8 max-w-md w-full mx-4 space-y-6 shadow-2xl">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-red-500"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-black uppercase tracking-tighter">Delete Account</h3>
                                            <p class="text-xs text-muted-foreground font-bold mt-1">This action <span class="text-red-500">cannot</span> be undone.</p>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-red-500/5 border border-red-500/10 rounded-xl">
                                        <p class="text-xs font-bold leading-relaxed">
                                            You are about to permanently delete <span class="text-red-500">{{ $user['name'] }}</span>'s account.
                                            All data including orders, points, and history will be lost forever.
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                                        <span>Confirm in</span>
                                        <span id="countdown{{ $user['id'] }}" class="text-2xl font-black text-red-500 tabular-nums">5</span>
                                        <span>seconds</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <button type="button" onclick="closeDeleteModal({{ $user['id'] }})" class="flex-1 py-3 bg-foreground/5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-foreground/10 transition-colors">Cancel</button>
                                        <button id="confirmDelete{{ $user['id'] }}" disabled
                                                class="flex-1 py-3 bg-red-500/20 text-red-500 rounded-xl text-[10px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed transition-all">
                                            <span class="flex items-center justify-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                Delete Forever
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Form -->
                            <div id="editUser{{ $user['id'] }}" class="hidden mt-3 p-4 bg-foreground/5 rounded-xl border border-border">
                                <form class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                    @csrf
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">New Email</label>
                                        <input type="email" name="email" placeholder="new@email.com" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none placeholder:text-muted-foreground/50">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">New Password</label>
                                        <input type="password" name="password" placeholder="Enter new password" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none placeholder:text-muted-foreground/50">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Status</label>
                                        <select name="status" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold text-foreground outline-none">
                                            <option value="1" {{ $user['active'] ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$user['active'] ? 'selected' : '' }}>Banned</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Points Balance</label>
                                        <input type="number" name="points_balance" value="{{ $user['points'] }}" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                    </div>
                                    <div class="md:col-span-2 lg:col-span-4 flex gap-2">
                                        <button type="submit" class="px-5 py-2 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Save Changes</button>
                                        <button type="button" onclick="document.getElementById('editUser{{ $user['id'] }}').classList.add('hidden')" class="px-5 py-2 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest">Cancel</button>
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

    <script>
        var deleteTimers = {};
        function closeDeleteModal(id) {
            document.getElementById('deleteUser' + id).classList.add('hidden');
            if (deleteTimers[id]) { clearInterval(deleteTimers[id]); delete deleteTimers[id]; }
            var btn = document.getElementById('confirmDelete' + id);
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            document.getElementById('countdown' + id).textContent = '5';
        }
        document.addEventListener('click', function(e) {
            document.querySelectorAll('[id^="deleteUser"]').forEach(function(el) {
                var id = el.id.replace('deleteUser', '');
                if (!id || isNaN(id)) return;
                if (e.target === el && !el.classList.contains('hidden')) {
                    closeDeleteModal(parseInt(id));
                }
            });
        });
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                var el = mutation.target;
                var id = el.id.replace('deleteUser', '');
                if (!id || isNaN(id)) return;
                if (!el.classList.contains('hidden') && !deleteTimers[id]) {
                    var count = 5;
                    document.getElementById('countdown' + id).textContent = count;
                    deleteTimers[id] = setInterval(function() {
                        count--;
                        document.getElementById('countdown' + id).textContent = count;
                        if (count <= 0) {
                            clearInterval(deleteTimers[id]); delete deleteTimers[id];
                            var btn = document.getElementById('confirmDelete' + id);
                            btn.disabled = false;
                            btn.classList.remove('opacity-50', 'cursor-not-allowed');
                            btn.classList.add('hover:bg-red-500/30');
                        }
                    }, 1000);
                }
            });
        });
        document.querySelectorAll('[id^="deleteUser"]').forEach(function(el) {
            observer.observe(el, { attributes: true, attributeFilter: ['class'] });
        });
    </script>
</div>
@endsection
