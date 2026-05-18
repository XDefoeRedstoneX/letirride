@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">FAQ <span class="text-primary">Management</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage frequently asked questions</p>
        </div>
        <button onclick="document.getElementById('addFaqForm').classList.toggle('hidden')" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            Add FAQ
        </button>
    </div>

    <!-- Add FAQ Form -->
    <div id="addFaqForm" class="hidden bg-card border border-border rounded-2xl p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest">New FAQ</h3>
            <button onclick="document.getElementById('addFaqForm').classList.add('hidden')" class="p-1 hover:bg-foreground/5 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
            </button>
        </div>
        <form class="grid grid-cols-1 gap-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Category <span class="text-red-500">*</span></label>
                    <select class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none">
                        <option>General</option>
                        <option>Payments & Orders</option>
                        <option>Gacha System</option>
                        <option>Points System</option>
                        <option>Account & Security</option>
                        <option>Refunds</option>
                    </select>
                </div>
                <div class="md:col-span-2 space-y-1.5">
                    <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Question <span class="text-red-500">*</span></label>
                    <input type="text" placeholder="Enter the question" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none">
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Answer <span class="text-red-500">*</span></label>
                <textarea rows="4" placeholder="Enter the answer..." class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addFaqForm').classList.add('hidden')" class="px-8 py-2.5 bg-foreground/5 rounded-xl text-[10px] font-black">Cancel</button>
                <button type="submit" class="px-8 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black">Add FAQ</button>
            </div>
        </form>
    </div>

    <!-- FAQ List by Category -->
    @php
        $faqs = [
            ['id' => 1, 'category' => 'General', 'question' => 'What is Ridly?', 'answer' => 'Ridly is a digital marketplace where you can purchase various digital products including vouchers, gift cards, and game top-ups.', 'active' => true],
            ['id' => 2, 'category' => 'General', 'question' => 'How do I create an account?', 'answer' => 'Click the Sign Up button and fill in your username, email, and password.', 'active' => true],
            ['id' => 3, 'category' => 'Payments & Orders', 'question' => 'What payment methods are accepted?', 'answer' => 'We accept various payment methods through Midtrans including bank transfer, credit card, and e-wallets.', 'active' => true],
            ['id' => 4, 'category' => 'Payments & Orders', 'question' => 'How long does delivery take?', 'answer' => 'Digital vouchers are delivered instantly. Direct top-ups are processed within 24 hours.', 'active' => true],
            ['id' => 5, 'category' => 'Gacha System', 'question' => 'How does the Gacha system work?', 'answer' => 'You can spin the arcade carousel using points or real money to win discount vouchers and prizes.', 'active' => true],
            ['id' => 6, 'category' => 'Gacha System', 'question' => 'What are the drop rates?', 'answer' => 'Drop rates vary by rarity. Common prizes have higher chances while legendary prizes are rare.', 'active' => true],
            ['id' => 7, 'category' => 'Points System', 'question' => 'How do I earn points?', 'answer' => 'You earn points by purchasing products on the store.', 'active' => true],
            ['id' => 8, 'category' => 'Points System', 'question' => 'Can I transfer points?', 'answer' => 'No, points are non-transferable between accounts.', 'active' => false],
        ];

        $grouped = collect($faqs)->groupBy('category');
    @endphp

    @foreach($grouped as $category => $items)
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-sm font-black uppercase tracking-widest">{{ $category }} <span class="text-[10px] text-muted-foreground">({{ count($items) }})</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Question</th>
                        <th class="px-5 py-3">Answer</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($items as $faq)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $faq['id'] }}</td>
                        <td class="px-5 py-3 text-xs font-bold max-w-xs">{{ $faq['question'] }}</td>
                        <td class="px-5 py-3 text-xs text-muted-foreground max-w-md truncate">{{ $faq['answer'] }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $faq['active'] ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">{{ $faq['active'] ? 'Active' : 'Hidden' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex gap-1">
                                <button onclick="document.getElementById('editFaq{{ $faq['id'] }}').classList.toggle('hidden')" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <button class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                            </div>

                            <!-- Edit Inline Form -->
                            <div id="editFaq{{ $faq['id'] }}" class="hidden mt-3 p-4 bg-foreground/5 rounded-xl border border-border">
                                <form class="grid grid-cols-1 gap-3">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[8px] font-black text-muted-foreground uppercase">Category</label>
                                            <select class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                                <option {{ $faq['category'] === 'General' ? 'selected' : '' }}>General</option>
                                                <option {{ $faq['category'] === 'Payments & Orders' ? 'selected' : '' }}>Payments & Orders</option>
                                                <option {{ $faq['category'] === 'Gacha System' ? 'selected' : '' }}>Gacha System</option>
                                                <option {{ $faq['category'] === 'Points System' ? 'selected' : '' }}>Points System</option>
                                                <option {{ $faq['category'] === 'Account & Security' ? 'selected' : '' }}>Account & Security</option>
                                                <option {{ $faq['category'] === 'Refunds' ? 'selected' : '' }}>Refunds</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="text-[8px] font-black text-muted-foreground uppercase">Question</label>
                                            <input type="text" value="{{ $faq['question'] }}" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Answer</label>
                                        <textarea rows="3" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">{{ $faq['answer'] }}</textarea>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase flex items-center gap-2">
                                            <input type="checkbox" {{ $faq['active'] ? 'checked' : '' }} class="rounded border-border">
                                            Active
                                        </label>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="px-5 py-2 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Save Changes</button>
                                        <button type="button" onclick="document.getElementById('editFaq{{ $faq['id'] }}').classList.add('hidden')" class="px-5 py-2 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest">Cancel</button>
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
    @endforeach
</div>
@endsection
