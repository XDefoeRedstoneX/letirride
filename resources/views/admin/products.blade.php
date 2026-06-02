@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showKeysModal: false,
    product: {},
    discountEnabled: false,
    discountType: 'percentage',
    discountValue: '',
    discountLabel: 'SALE',
    discountEnds: '',
    openAdd() {
        this.showAddModal = true;
    },
    openEdit(p) {
        this.product = JSON.parse(JSON.stringify(p));
        const d = p.discount;
        this.discountEnabled = !!(d && d.is_active);
        this.discountType  = d?.type  || 'percentage';
        this.discountValue = d?.value || '';
        this.discountLabel = d?.label || 'SALE';
        this.discountEnds  = d?.ends_at ? d.ends_at.substring(0, 16) : '';
        this.showEditModal = true;
    },
    get discountPreview() {
        const base = parseFloat(this.product.price) || 0;
        const val  = parseFloat(this.discountValue)  || 0;
        if (!this.discountEnabled || val <= 0) return null;
        const sale = this.discountType === 'percentage'
            ? Math.round(base * (1 - val / 100) / 100) * 100
            : Math.max(0, base - val);
        return { sale, saves: base - sale };
    },
    openDelete(p) {
        this.product = p;
        this.showDeleteModal = true;
    },
    openKeys(p) {
        this.product = p;
        this.showKeysModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tighter uppercase">Products</h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage your product catalog</p>
        </div>
        <button @click="openAdd()" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary/90 transition-colors">
            + ADD PRODUCT
        </button>
    </div>

    <!-- Products Table -->
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-foreground/5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Subcategory</th>
                        <th class="px-5 py-3">Price</th>
                        <th class="px-5 py-3">Discount</th>
                        <th class="px-5 py-3">Pt. Mult.</th>
                        <th class="px-5 py-3">Keys</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($products as $p)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $p->id }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-foreground/5 rounded-lg flex items-center justify-center p-1 shrink-0">
                                    <img src="/products/{{ $p->image ?? 'soundcloud.svg' }}" class="w-full h-full object-contain" />
                                </div>
                                <div>
                                    <span class="text-xs font-bold">{{ $p->name }}</span>
                                    @if($p->description)
                                        <p class="text-[9px] text-muted-foreground font-bold truncate max-w-[200px]">{{ $p->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @if(($p->type ?? 'voucher') === 'direct_topup')
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-500">Top-Up</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-500">Voucher</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-muted-foreground font-bold">{{ $p->category?->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-xs text-muted-foreground font-bold">{{ $p->subcategory?->name ?? '-' }}</td>
                        @php $pd = $p->discount && $p->discount->isCurrentlyActive() ? $p->discount : null; @endphp
                        {{-- Price column: always shows base price --}}
                        <td class="px-5 py-3 text-xs font-bold">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        {{-- Discount column: empty dash when no active discount --}}
                        <td class="px-5 py-3">
                            @if($pd)
                                <div class="flex flex-col gap-0.5">
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-500 self-start">
                                        {{ $pd->label }}
                                        @if($pd->type === 'percentage')–{{ number_format($pd->value, 0) }}%@else–Rp {{ number_format($pd->value, 0, ',', '.') }}@endif
                                    </span>
                                    <span class="text-xs font-black text-rose-500">→ Rp {{ number_format($p->discountedPrice(), 0, ',', '.') }}</span>
                                    @if($pd->ends_at)
                                        <span class="text-[9px] text-muted-foreground font-bold">until {{ $pd->ends_at->format('d M Y') }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted-foreground text-xs font-bold">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs font-bold text-yellow-500">{{ number_format((float) $p->point_multiplier, 2) }}×</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-bold">{{ $p->product_keys_count }}</span>
                            <button @click="openKeys({{ json_encode($p) }})" class="ml-1 text-[9px] text-primary font-black uppercase tracking-widest hover:underline">+ Add</button>
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.products.update', $p) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="name" value="{{ $p->name }}">
                                <input type="hidden" name="category_id" value="{{ $p->category_id }}">
                                <input type="hidden" name="subcategory_id" value="{{ $p->subcategory_id }}">
                                <input type="hidden" name="price" value="{{ $p->price }}">
                                <input type="hidden" name="description" value="{{ $p->description }}">
                                <input type="hidden" name="point_multiplier" value="{{ $p->point_multiplier }}">
                                <input type="hidden" name="image" value="{{ $p->image }}">
                                <input type="hidden" name="type" value="{{ $p->type ?? 'voucher' }}">
                                <button type="submit" name="is_active" value="{{ $p->is_active ? '0' : '1' }}"
                                    class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-colors cursor-pointer
                                    {{ $p->is_active ? 'bg-green-500/10 text-green-500 hover:bg-red-500/10 hover:text-red-500' : 'bg-red-500/10 text-red-500 hover:bg-green-500/10 hover:text-green-500' }}">
                                    {{ $p->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <button @click="openEdit({{ json_encode($p) }})" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <button @click="openDelete({{ json_encode($p) }})" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div x-show="showAddModal" @click.self="showAddModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">NEW PRODUCT</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">ADD TO CATALOG</p>
                    </div>
                    <button @click="showAddModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('admin.products.store') }}" class="flex flex-col gap-4"
                      x-data="{ addCategory: '', addSub: '' }">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">NAME <span class="req">*</span></label>
                            <input type="text" name="name" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="Product name">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">CATEGORY <span class="req">*</span></label>
                            <select name="category_id" x-model="addCategory" required class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="">Select category</option>
                                @foreach($categories ?? \App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">SUBCATEGORY</label>
                            <select name="subcategory_id" x-model="addSub" class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="">— None —</option>
                                @foreach($subcategories ?? \App\Models\Subcategory::all() as $sub)
                                    <option value="{{ $sub->id }}" data-category="{{ $sub->category_id }}"
                                            x-show="addCategory == '' || addCategory == '{{ $sub->category_id }}'">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">TYPE</label>
                            <select name="type" class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="voucher">Voucher / Code</option>
                                <option value="direct_topup">Direct Top-Up</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">PRICE (RP) <span class="req">*</span></label>
                            <input type="number" name="price" required min="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="0">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINT MULTIPLIER</label>
                            <input type="number" step="0.01" name="point_multiplier" min="0" value="1.00" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">IMAGE FILENAME</label>
                            <input type="text" name="image" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="e.g. spotify.png">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DESCRIPTION</label>
                        <textarea name="description" rows="2" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE PRODUCT</button>
                    </div>
                </form>
            </div>
            
            
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div x-show="showEditModal" @click.self="showEditModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">EDIT PRODUCT</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="'ID: #' + product.id"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ route('admin.products') }}/' + product.id" class="flex flex-col gap-4">
                    @csrf @method('PATCH')
                    <input type="hidden" name="is_active" :value="product.is_active ? '1' : '0'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">NAME <span class="req">*</span></label>
                            <input type="text" name="name" x-model="product.name" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">CATEGORY <span class="req">*</span></label>
                            <select name="category_id" x-model="product.category_id" required class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                @foreach($categories ?? \App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">SUBCATEGORY</label>
                            <select name="subcategory_id" x-model="product.subcategory_id" class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="">— None —</option>
                                @foreach($subcategories ?? \App\Models\Subcategory::all() as $sub)
                                    <option value="{{ $sub->id }}"
                                            x-show="!product.category_id || product.category_id == '{{ $sub->category_id }}'">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">TYPE</label>
                            <select name="type" x-model="product.type" class="w-full px-4 py-3  bg-white dark:bg-[#0f172a]  border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                                <option value="voucher">Voucher / Code</option>
                                <option value="direct_topup">Direct Top-Up</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">PRICE (RP) <span class="req">*</span></label>
                            <input type="number" name="price" x-model="product.price" required min="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINT MULTIPLIER</label>
                            <input type="number" step="0.01" name="point_multiplier" x-model="product.point_multiplier" min="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">IMAGE FILENAME</label>
                            <input type="text" name="image" x-model="product.image" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DESCRIPTION</label>
                        <textarea name="description" x-model="product.description" rows="2" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all"></textarea>
                    </div>

                    {{-- ── Discount Section ─────────────────────────────── --}}
                    <div class="border border-border/50 rounded-2xl overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 bg-foreground/5 cursor-pointer"
                             @click="discountEnabled = !discountEnabled">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground">DISCOUNT / SALE PRICE</span>
                                <span x-show="discountEnabled" class="px-1.5 py-0.5 bg-rose-500/15 text-rose-500 rounded text-[8px] font-black uppercase tracking-widest">ON</span>
                            </div>
                            <div class="relative w-9 h-5 rounded-full transition-colors"
                                 :class="discountEnabled ? 'bg-rose-500' : 'bg-foreground/20'">
                                <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"
                                     :class="discountEnabled ? 'translate-x-4' : 'translate-x-0.5'"></div>
                            </div>
                        </div>

                        <input type="hidden" name="discount_enabled" :value="discountEnabled ? '1' : '0'">
                        <div x-show="discountEnabled" class="px-4 py-4 flex flex-col gap-4">

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">TYPE</label>
                                    <select name="discount_type" x-model="discountType"
                                            class="w-full px-3 py-2.5 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20 transition-all">
                                        <option value="percentage">% Percentage</option>
                                        <option value="fixed">Rp Fixed Amount</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block"
                                           x-text="discountType === 'percentage' ? 'VALUE (%)' : 'VALUE (RP)'"></label>
                                    <input type="number" name="discount_value" x-model="discountValue"
                                           :placeholder="discountType === 'percentage' ? 'e.g. 20' : 'e.g. 30000'"
                                           :max="discountType === 'percentage' ? 95 : ''"
                                           min="0.01" step="0.01"
                                           class="w-full px-3 py-2.5 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20 placeholder:text-muted-foreground/50 transition-all">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">BADGE LABEL</label>
                                    <input type="text" name="discount_label" x-model="discountLabel"
                                           placeholder="SALE" maxlength="40"
                                           class="w-full px-3 py-2.5 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20 placeholder:text-muted-foreground/50 transition-all">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">EXPIRES (OPTIONAL)</label>
                                    <input type="datetime-local" name="discount_ends" x-model="discountEnds"
                                           class="w-full px-3 py-2.5 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20 transition-all">
                                </div>
                            </div>

                            {{-- Live preview --}}
                            <template x-if="discountPreview">
                                <div class="flex items-center gap-3 px-3 py-2.5 bg-rose-500/5 border border-rose-500/20 rounded-xl">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-rose-500">PREVIEW</span>
                                    <span class="text-xs text-muted-foreground line-through"
                                          x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                    <span class="text-xs font-black text-rose-500"
                                          x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(discountPreview.sale)"></span>
                                    <span class="text-[9px] text-muted-foreground"
                                          x-text="'saves Rp ' + new Intl.NumberFormat('id-ID').format(discountPreview.saves)"></span>
                                </div>
                            </template>
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">UPDATE PRODUCT</button>
                    </div>
                </form>
            </div>
            
            
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div x-show="showDeleteModal" @click.self="showDeleteModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-red-500">CONFIRM DELETE</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">DANGER ZONE</p>
                    </div>
                    <button @click="showDeleteModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <p class="text-sm font-sans text-foreground/70 dark:text-muted-foreground">Are you sure you want to delete <strong class="text-foreground" x-text="product.name"></strong>? This action cannot be undone.</p>
                <form method="POST" :action="'{{ route('admin.products') }}/' + product.id" class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                    <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors" class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-colors">DELETE</button>
                </form>
            </div>
            
            
        </div>
    </div>

    <!-- Add Keys Modal -->
    <div x-show="showKeysModal" @click.self="showKeysModal = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;">
        <div class="bg-white dark:bg-[#0f172a] border border-border rounded-3xl shadow-2xl w-full max-w-2xl">
            
            
            <div class="p-6 sm:p-8" style=" display: flex; flex-direction: column; gap: 12px;">
                <div class="modal-header">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter text-foreground">ADD VOUCHER KEYS</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1" x-text="product.name"></p>
                    </div>
                    <button @click="showKeysModal = false" class="p-2 hover:bg-foreground/5 rounded-xl text-muted-foreground hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                </div>
                <form method="POST" :action="'{{ route('admin.products') }}/' + product.id + '/keys'" class="flex flex-col gap-4">
                    @csrf
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">KEYS (ONE PER LINE) <span class="req">*</span></label>
                        <textarea name="keys" required rows="5" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 placeholder:text-muted-foreground/50 transition-all" placeholder="XXXX-YYYY-ZZZZ&#10;AAAA-BBBB-CCCC"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border/50">
                        <button type="button" @click="showKeysModal = false" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">CANCEL</button>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-colors">SAVE KEYS</button>
                    </div>
                </form>
            </div>
            
            
        </div>
    </div>
</div>
@endsection
