@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    faq: {},
    openAdd() {
        this.showAddModal = true;
    },
    openEdit(f) {
        this.faq = JSON.parse(JSON.stringify(f));
        this.showEditModal = true;
    },
    openDelete(f) {
        this.faq = f;
        this.showDeleteModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">FAQ <span class="text-primary">Management</span></h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage frequently asked questions</p>
        </div>
        <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
            + ADD FAQ
        </button>
    </div>

    @php
        $faqs = [
            'General' => [
                ['id' => 1, 'question' => 'What is Ridly?', 'answer' => 'Ridly is a digital marketplace where you can purchase various digital products including vouchers, gift cards, and game top-ups.', 'active' => true],
                ['id' => 2, 'question' => 'How do I create an account?', 'answer' => 'Click the Sign Up button and fill in your username, email, and password.', 'active' => true],
            ],
            'Payments & Orders' => [
                ['id' => 3, 'question' => 'What payment methods are accepted?', 'answer' => 'We accept various payment methods through Midtrans including bank transfer, credit card, and e-wallets.', 'active' => true],
                ['id' => 4, 'question' => 'How long does delivery take?', 'answer' => 'Digital vouchers are delivered instantly. Direct top-ups are processed within 24 hours.', 'active' => true],
            ],
            'Gacha System' => [
                ['id' => 5, 'question' => 'How does the Gacha system work?', 'answer' => 'You can spin the arcade carousel using points or real money to win discount vouchers and prizes.', 'active' => true],
            ]
        ];
    @endphp

    <div class="space-y-6">
        @foreach($faqs as $category => $items)
        <div class="space-y-4">
            <h3 class="text-sm font-black uppercase tracking-widest">{{ $category }} <span class="text-[10px] text-muted-foreground">({{ count($items) }})</span></h3>
            
            <div class="bg-card border border-border rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                                <th class="px-5 py-3 w-16">ID</th>
                                <th class="px-5 py-3 w-1/4">Question</th>
                                <th class="px-5 py-3 w-1/2">Answer</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($items as $i)
                            <tr class="hover:bg-foreground/5">
                                <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $i['id'] }}</td>
                                <td class="px-5 py-3 text-xs font-bold">{{ $i['question'] }}</td>
                                <td class="px-5 py-3 text-[10px] text-muted-foreground line-clamp-2">{{ Str::limit($i['answer'], 80) }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $i['active'] ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">{{ $i['active'] ? 'Active' : 'Hidden' }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-1">
                                        @php $i['category'] = $category; @endphp
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
        </div>
        @endforeach
    </div>

    <!-- Add FAQ Modal -->
    <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div @click.away="showAddModal = false = false = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW FAQ</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">ADD KNOWLEDGE BASE</p>
                    </div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">CATEGORY <span class="req">*</span></label>
                            <select class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option>General</option>
                                <option>Payments & Orders</option>
                                <option>Gacha System</option>
                                <option>New Category...</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">QUESTION <span class="req">*</span></label>
                            <input type="text" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="e.g. How to refund?">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ANSWER <span class="req">*</span></label>
                        <textarea rows="3" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="Detailed explanation..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="button" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE FAQ</button>
                    </div>
                </form>
            </div>
            
            
        </div>
    </div>

    <!-- Edit FAQ Modal -->
    <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div @click.away="showEditModal = false = false = false" class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT FAQ</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + faq.id"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">CATEGORY <span class="req">*</span></label>
                            <select x-model="faq.category" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option>General</option>
                                <option>Payments & Orders</option>
                                <option>Gacha System</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">QUESTION <span class="req">*</span></label>
                            <input type="text" x-model="faq.question" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">STATUS</label>
                            <select x-model="faq.active" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option :value="true">Active</option>
                                <option :value="false">Hidden</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ANSWER <span class="req">*</span></label>
                        <textarea rows="3" x-model="faq.answer" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="button" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE FAQ</button>
                    </div>
                </form>
            </div>
            
            
        </div>
    </div>

    <!-- Delete FAQ Modal -->
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
                <p class="text-sm font-sans text-foreground/70 dark:text-muted-foreground">Are you sure you want to delete <strong class="text-foreground" x-text="faq.question"></strong>? This action cannot be undone.</p>
                <form class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="button" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
            
            
        </div>
    </div>
</div>
@endsection