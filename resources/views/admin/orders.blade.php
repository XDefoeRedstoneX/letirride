<x-admin::layouts.admin>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-black tracking-tighter uppercase">Orders</h1>
            <div class="flex gap-2">
                @foreach(['all' => 'All', 'paid' => 'Paid', 'pending' => 'Pending', 'failed' => 'Failed'] as $key => $label)
                    <a href="{{ route('admin.orders', $key === 'all' ? [] : ['status' => $key]) }}"
                       class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all
                              {{ (request('status') ?? 'all') === ($key === 'all' && !request('status') ? 'all' : $key) ? 'bg-primary text-primary-foreground' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-6 py-4">Invoice</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Items</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($orders as $order)
                        <tr class="hover:bg-foreground/5 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono font-bold">{{ $order->noinv }}</td>
                            <td class="px-6 py-4 text-xs font-bold">{{ $order->user?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-xs font-bold text-muted-foreground">{{ $order->orderDetails->count() }} item(s)</td>
                            <td class="px-6 py-4 text-xs font-bold">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest
                                    @if($order->status === 'paid') bg-green-500/10 text-green-500
                                    @elseif($order->status === 'pending') bg-amber-500/10 text-amber-500
                                    @else bg-red-500/10 text-red-500
                                    @endif">{{ $order->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-[10px] text-muted-foreground">{{ $order->created_at?->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex gap-1">
                                    @csrf @method('PATCH')
                                    <select name="status" class="bg-foreground/5 border border-border rounded-lg px-2 py-1 text-[10px] font-bold outline-none">
                                        @foreach(['pending', 'paid', 'failed'] as $s)
                                            <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1 bg-primary text-primary-foreground rounded-lg text-[10px] font-black">Save</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center">
            {{ $orders->links() }}
        </div>
    </div>
</x-admin::layouts.admin>
