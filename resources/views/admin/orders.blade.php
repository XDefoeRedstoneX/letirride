@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Orders</h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage customer orders & payments</p>
        </div>
        <div class="flex gap-1.5">
            @foreach(['all' => 'All', 'paid' => 'Paid', 'pending' => 'Pending', 'failed' => 'Failed'] as $key => $label)
                <a href="{{ route('admin.orders', $key === 'all' ? [] : ['status' => $key]) }}"
                   class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all
                          {{ (request('status') ?? 'all') === $key ? 'bg-primary text-primary-foreground' : 'bg-foreground/5 text-muted-foreground hover:bg-foreground/10' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">Invoice</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Items</th>
                        <th class="px-5 py-3">Subtotal</th>
                        <th class="px-5 py-3">Discount</th>
                        <th class="px-5 py-3">Total</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($orders as $order)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold">{{ $order->noinv }}</td>
                        <td class="px-5 py-3 text-xs font-bold">{{ $order->user?->name ?? 'N/A' }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-bold">{{ $order->orderDetails->count() }} item(s)</span>
                            <button onclick="document.getElementById('orderItems{{ $order->id }}').classList.toggle('hidden')" class="ml-1 text-[9px] text-primary font-black uppercase tracking-widest hover:underline">View</button>
                            <div id="orderItems{{ $order->id }}" class="hidden mt-2 p-3 bg-foreground/5 rounded-xl space-y-1.5">
                                @foreach($order->orderDetails as $detail)
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="font-bold">{{ $detail->product?->name ?? 'Unknown' }}</span>
                                        <span class="text-muted-foreground">{{ $detail->quantity }}x Rp {{ number_format($detail->total_price_in_cart, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3 text-xs font-bold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        <td class="px-5 py-3">
                            @if($order->discount_amount > 0)
                                <span class="text-xs font-bold text-green-500">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-xs text-muted-foreground">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs font-bold">Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-[10px] text-muted-foreground font-bold">{{ $order->payment_gateway_ref ? 'Midtrans' : '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest
                                @if($order->status === 'paid') bg-green-500/10 text-green-500
                                @elseif($order->status === 'pending') bg-amber-500/10 text-amber-500
                                @else bg-red-500/10 text-red-500
                                @endif">{{ $order->status }}</span>
                        </td>
                        <td class="px-5 py-3 text-[10px] text-muted-foreground">{{ $order->created_at?->format('M d, Y') }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex gap-1">
                                @csrf @method('PATCH')
                                <select name="status" class="bg-background border border-border rounded-lg px-2 py-1.5 text-[10px] font-bold text-foreground outline-none">
                                    @foreach(['pending', 'paid', 'failed'] as $s)
                                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Update</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $orders->links() }}
    </div>
</div>
@endsection
