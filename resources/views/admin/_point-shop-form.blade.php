{{-- Shared point-shop item form. Expects $model = Alpine object name
     ('form' add / 'item' edit) and $discounts. --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">NAME <span class="req">*</span></label>
        <input type="text" name="name" x-model="{{ $model }}.name" required maxlength="255"
               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
               placeholder="e.g. Steam Master Discount">
    </div>

    <div class="sm:col-span-2">
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DESCRIPTION</label>
        <input type="text" name="description" x-model="{{ $model }}.description" maxlength="1000"
               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
               placeholder="Short blurb shown to customers">
    </div>

    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINT COST <span class="req">*</span></label>
        <input type="number" name="point_cost" x-model="{{ $model }}.point_cost" required min="1"
               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
               placeholder="500">
    </div>

    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">REWARD TYPE <span class="req">*</span></label>
        <select name="reward_type" x-model="{{ $model }}.reward_type" required class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary transition-all">
            <option value="discount">Discount voucher</option>
            <option value="cashback">Cashback (points)</option>
        </select>
    </div>

    <div class="sm:col-span-2" x-show="{{ $model }}.reward_type === 'discount'" x-cloak>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DISCOUNT <span class="req">*</span></label>
        <select name="discount_type_id" x-model="{{ $model }}.discount_type_id" class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary transition-all">
            <option value="">— Select discount —</option>
            @foreach($discounts as $d)
                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->valueLabel() }})</option>
            @endforeach
        </select>
        <p class="text-[10px] text-muted-foreground mt-1">Manage these in <a href="{{ route('admin.discounts') }}" class="text-primary underline">Discount Types</a>.</p>
    </div>

    <div class="sm:col-span-2" x-show="{{ $model }}.reward_type === 'cashback'" x-cloak>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">CASHBACK POINTS <span class="req">*</span></label>
        <input type="number" name="points_amount" x-model="{{ $model }}.points_amount" min="1"
               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
               placeholder="1000">
        <p class="text-[10px] text-muted-foreground mt-1">Points credited to the buyer on redemption. Keep below the point cost to avoid a net point gain.</p>
    </div>

    <div class="sm:col-span-2">
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">IMAGE</label>
        <input type="file" name="image_file" accept="image/png,image/jpeg,image/webp" class="w-full px-4 py-2.5 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs text-foreground outline-none focus:border-primary transition-all">
        <p class="text-[10px] text-muted-foreground mt-1">PNG/JPG/WebP, max 1&nbsp;MB. Leave blank to keep the current image / use the default voucher art.</p>
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" x-bind:checked="{{ $model }}.is_active" class="w-4 h-4">
        <label class="text-xs font-bold text-foreground">Active</label>
    </div>
</div>
