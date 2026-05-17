@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase">Products</h1>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Manage your product catalog</p>
        </div>
        <button onclick="document.getElementById('addProductForm').classList.toggle('hidden')" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            Add Product
        </button>
    </div>

    <!-- Add Product Form -->
    <div id="addProductForm" class="hidden bg-card border border-border rounded-2xl p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-widest">New Product</h3>
            <button onclick="document.getElementById('addProductForm').classList.add('hidden')" class="p-1 hover:bg-foreground/5 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.products.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Product Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30 placeholder:text-muted-foreground/50">
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Category <span class="text-red-500">*</span></label>
                <select name="category_id" required class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="">Select category</option>
                    @foreach(\App\Models\Category::all() as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Product Type</label>
                <select name="product_type" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="voucher">Voucher / Code</option>
                    <option value="direct_topup">Direct Top-Up</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Price (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="price" required min="0" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30 placeholder:text-muted-foreground/50">
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Point Reward</label>
                <input type="number" name="point_reward" min="0" value="0" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Image Filename</label>
                <input type="text" name="image" placeholder="e.g. spotify.svg" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30 placeholder:text-muted-foreground/50">
            </div>
            <div class="md:col-span-2 lg:col-span-3 space-y-1.5">
                <label class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Description</label>
                <textarea name="description" rows="2" class="w-full px-4 py-2.5 bg-foreground/5 border border-border rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-primary/30 placeholder:text-muted-foreground/50"></textarea>
            </div>
            <div class="md:col-span-2 lg:col-span-3 flex justify-end">
                <button type="submit" class="px-8 py-2.5 bg-primary text-primary-foreground rounded-xl text-[10px] font-black uppercase tracking-widest">Create Product</button>
            </div>
        </form>
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
                        <th class="px-5 py-3">Price</th>
                        <th class="px-5 py-3">Points</th>
                        <th class="px-5 py-3">Keys</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($products as $product)
                    <tr class="hover:bg-foreground/5">
                        <td class="px-5 py-3 text-xs font-mono font-bold text-muted-foreground">#{{ $product->id }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-foreground/5 rounded-lg flex items-center justify-center p-1 shrink-0">
                                    <img src="/products/{{ $product->image ?? 'soundcloud.svg' }}" class="w-full h-full object-contain" />
                                </div>
                                <div>
                                    <span class="text-xs font-bold">{{ $product->name }}</span>
                                    @if($product->description)
                                        <p class="text-[9px] text-muted-foreground font-bold truncate max-w-[200px]">{{ $product->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @if($product->product_type === 'direct_topup')
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-500">Top-Up</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-500">Voucher</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-muted-foreground font-bold">{{ $product->category?->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-xs font-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-xs font-bold text-yellow-500">{{ $product->point_reward }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-bold">{{ $product->product_keys_count }}</span>
                            <button onclick="document.getElementById('keysForm{{ $product->id }}').classList.toggle('hidden')" class="ml-1 text-[9px] text-primary font-black uppercase tracking-widest hover:underline">+ Add</button>
                            <div id="keysForm{{ $product->id }}" class="hidden mt-2">
                                <form method="POST" action="{{ route('admin.products.keys', $product) }}" class="space-y-1.5">
                                    @csrf
                                    <textarea name="keys" rows="3" class="w-full px-3 py-2 bg-foreground/5 border border-border rounded-lg text-[10px] font-mono font-bold outline-none focus:ring-2 focus:ring-primary/30" placeholder="One key per line"></textarea>
                                    <button type="submit" class="px-3 py-1.5 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Add Keys</button>
                                </form>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.products.update', $product) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="name" value="{{ $product->name }}">
                                <input type="hidden" name="category_id" value="{{ $product->category_id }}">
                                <input type="hidden" name="price" value="{{ $product->price }}">
                                <input type="hidden" name="description" value="{{ $product->description }}">
                                <input type="hidden" name="point_reward" value="{{ $product->point_reward }}">
                                <input type="hidden" name="image" value="{{ $product->image }}">
                                <input type="hidden" name="product_type" value="{{ $product->product_type }}">
                                <button type="submit" name="is_active" value="{{ $product->is_active ? '0' : '1' }}"
                                    class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-colors cursor-pointer
                                    {{ $product->is_active ? 'bg-green-500/10 text-green-500 hover:bg-red-500/10 hover:text-red-500' : 'bg-red-500/10 text-red-500 hover:bg-green-500/10 hover:text-green-500' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <button onclick="document.getElementById('editProduct{{ $product->id }}').classList.toggle('hidden')" class="px-2.5 py-1.5 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-primary/10 hover:text-primary transition-colors">Edit</button>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Deactivate {{ $product->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-colors">Delete</button>
                                </form>
                            </div>
                            <!-- Edit Inline Form -->
                            <div id="editProduct{{ $product->id }}" class="hidden mt-3 p-4 bg-foreground/5 rounded-xl border border-border">
                                <form method="POST" action="{{ route('admin.products.update', $product) }}" class="grid grid-cols-2 gap-3">
                                    @csrf @method('PATCH')
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Name</label>
                                        <input type="text" name="name" value="{{ $product->name }}" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Category</label>
                                        <select name="category_id" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                            @foreach(\App\Models\Category::all() as $cat)
                                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Price (Rp)</label>
                                        <input type="number" name="price" value="{{ $product->price }}" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Points</label>
                                        <input type="number" name="point_reward" value="{{ $product->point_reward }}" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Image</label>
                                        <input type="text" name="image" value="{{ $product->image }}" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Type</label>
                                        <select name="product_type" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">
                                            <option value="voucher" {{ $product->product_type === 'voucher' ? 'selected' : '' }}>Voucher</option>
                                            <option value="direct_topup" {{ $product->product_type === 'direct_topup' ? 'selected' : '' }}>Top-Up</option>
                                        </select>
                                    </div>
                                    <div class="col-span-2 space-y-1">
                                        <label class="text-[8px] font-black text-muted-foreground uppercase">Description</label>
                                        <textarea name="description" rows="2" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-xs font-bold outline-none">{{ $product->description }}</textarea>
                                    </div>
                                    <div class="col-span-2">
                                        <input type="hidden" name="is_active" value="{{ $product->is_active ? '1' : '0' }}">
                                        <button type="submit" class="px-5 py-2 bg-primary text-primary-foreground rounded-lg text-[9px] font-black uppercase tracking-widest">Save Changes</button>
                                        <button type="button" onclick="document.getElementById('editProduct{{ $product->id }}').classList.add('hidden')" class="ml-2 px-5 py-2 bg-foreground/5 rounded-lg text-[9px] font-black uppercase tracking-widest">Cancel</button>
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
</div>
@endsection
