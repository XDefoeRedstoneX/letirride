@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Points Activity History</h1>
            <p class="text-sm text-muted-foreground mt-2">All users • earned and spent points timeline</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Activity</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Points Change</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($activities as $a)
                        @php
                            $val = (int) ($a['points_change'] ?? 0);
                            $isEarn = $val > 0;
                        @endphp
                        <tr class="hover:bg-foreground/5">
                            <td class="px-5 py-3 text-xs font-bold">{{ $a['user_name'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-xs text-muted-foreground">{{ optional($a['date'])->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-xs font-bold text-primary">{{ $a['description'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-xs font-bold text-muted-foreground">{{ $a['category'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-xs font-bold">
                                <span class="{{ $isEarn ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $isEarn ? '+' : '' }}{{ number_format($val) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-muted-foreground">No point activities found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-border">
            {{ $paginator ? $paginator->links() : '' }}
        </div>
    </div>
</div>
@endsection

