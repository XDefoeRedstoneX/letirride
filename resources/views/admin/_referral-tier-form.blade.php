<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">THRESHOLD <span class="req">*</span></label>
        <input type="number" name="threshold" required min="1" max="1000" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="3">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">TITLE <span class="req">*</span></label>
        <input type="text" name="title" required maxlength="128" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="Triple Threat">
    </div>
    <div class="sm:col-span-2">
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DESCRIPTION</label>
        <input type="text" name="description" maxlength="255" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="Three paying friends. Have a free spin on the house.">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINTS REWARD</label>
        <input type="number" name="points_reward" min="0" max="1000000" value="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DISCOUNT TYPE</label>
        <select name="discount_type_id" class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
            <option value="">— None —</option>
            @foreach ($discountTypes as $dt)
                <option value="{{ $dt->id }}">{{ $dt->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">FREE SPINS</label>
        <input type="number" name="free_spins_reward" min="0" max="1000" value="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ICON (LUCIDE NAME)</label>
        <input type="text" name="icon" maxlength="64" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="sparkles">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">SORT ORDER</label>
        <input type="number" name="sort_order" min="0" max="1000" value="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
    </div>
    <div class="flex items-center gap-2 sm:col-span-2">
        <input type="checkbox" name="is_active" id="add_tier_is_active" value="1" checked class="w-4 h-4">
        <label for="add_tier_is_active" class="text-xs font-bold text-foreground">Active (inactive tiers never grant)</label>
    </div>
</div>
