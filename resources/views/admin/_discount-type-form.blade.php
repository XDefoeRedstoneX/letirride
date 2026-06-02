{{-- Shared discount-type form fields. Expects $model = the Alpine object name
     ('form' for add, 'discount' for edit), plus $categories + $subcategories. --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">NAME <span class="req">*</span></label>
        <input type="text" name="name" x-model="{{ $model }}.name" required maxlength="255"
               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
               placeholder="e.g. 10% Off Steam">
    </div>

    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">TYPE <span class="req">*</span></label>
        <select name="type" x-model="{{ $model }}.type" required class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary transition-all">
            <option value="percent">Percent (%)</option>
            <option value="fixed">Fixed (Rp)</option>
        </select>
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">
            VALUE <span class="req">*</span>
            <span class="text-muted-foreground" x-text="{{ $model }}.type === 'percent' ? '(0–100)' : '(Rp)'"></span>
        </label>
        <input type="number" name="value" x-model="{{ $model }}.value" required min="0" step="0.01"
               :max="{{ $model }}.type === 'percent' ? 100 : null"
               class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all"
               placeholder="10">
    </div>

    <div class="sm:col-span-2">
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">APPLIES TO <span class="req">*</span></label>
        <select name="target_scope" x-model="{{ $model }}.target_scope" required class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary transition-all">
            <option value="storewide">Storewide (everything)</option>
            <option value="category">A whole category</option>
            <option value="subcategory">A single brand / subcategory</option>
        </select>
    </div>

    <div class="sm:col-span-2" x-show="{{ $model }}.target_scope === 'category'" x-cloak>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">CATEGORY <span class="req">*</span></label>
        <select name="target_category_id" x-model="{{ $model }}.target_category_id" class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary transition-all">
            <option value="">— Select category —</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="sm:col-span-2" x-show="{{ $model }}.target_scope === 'subcategory'" x-cloak>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">BRAND / SUBCATEGORY <span class="req">*</span></label>
        <select name="target_subcategory_id" x-model="{{ $model }}.target_subcategory_id" class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary transition-all">
            <option value="">— Select brand —</option>
            @foreach($subcategories as $s)
                <option value="{{ $s->id }}">{{ $s->category?->name }} › {{ $s->name }}</option>
            @endforeach
        </select>
    </div>
</div>
