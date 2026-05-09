<x-admin::layouts.admin>
    <div class="space-y-8">
        <h1 class="text-3xl font-black tracking-tighter uppercase">Users</h1>

        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Points</th>
                        <th class="px-6 py-4">Orders</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($users as $user)
                        <tr class="hover:bg-foreground/5 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono font-bold text-muted-foreground">#{{ $user->id }}</td>
                            <td class="px-6 py-4 text-xs font-bold">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-xs text-muted-foreground">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $user->role === 'admin' ? 'bg-primary/10 text-primary' : 'bg-foreground/5 text-muted-foreground' }}">{{ $user->role }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-yellow-500">{{ number_format($user->points_balance) }}</td>
                            <td class="px-6 py-4 text-xs font-bold">{{ $user->orders_count }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex gap-1 items-center">
                                    @csrf @method('PATCH')
                                    <select name="role" class="bg-foreground/5 border border-border rounded-lg px-2 py-1 text-[10px] font-bold outline-none">
                                        <option value="buyer" {{ $user->role === 'buyer' ? 'selected' : '' }}>Buyer</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    <input type="number" name="points_balance" value="{{ $user->points_balance }}" class="w-20 bg-foreground/5 border border-border rounded-lg px-2 py-1 text-[10px] font-bold outline-none" />
                                    <button type="submit" class="px-3 py-1 bg-primary text-primary-foreground rounded-lg text-[10px] font-black">Save</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center">
            {{ $users->links() }}
        </div>
    </div>
</x-admin::layouts.admin>
