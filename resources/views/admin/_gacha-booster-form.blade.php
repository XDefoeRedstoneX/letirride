<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">KEY <span class="req">*</span></label>
        <input type="text" name="key" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-mono font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="lucky_charm">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">NAME <span class="req">*</span></label>
        <input type="text" name="name" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="Lucky Charm">
    </div>
    <div class="sm:col-span-2">
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">DESCRIPTION</label>
        <input type="text" name="description" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="+5% Rare+ chance for 10 rolls.">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">POINT COST <span class="req">*</span></label>
        <input type="number" name="point_cost" required min="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="500">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">RARITY FLOOR <span class="req">*</span></label>
        <select name="rarity_floor" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
            <option value="uncommon">Uncommon+</option>
            <option value="rare">Rare+</option>
            <option value="epic">Epic+</option>
            <option value="legendary">Legendary+</option>
            <option value="grand_prize">Grand Prize</option>
        </select>
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">BONUS % <span class="req">*</span></label>
        <input type="number" name="bonus_percent" required min="0" max="100" step="0.01" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="5">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">ROLLS GRANTED <span class="req">*</span></label>
        <input type="number" name="rolls_granted" required min="1" max="200" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="10">
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="add_is_active" value="1" checked class="w-4 h-4">
        <label for="add_is_active" class="text-xs font-bold text-foreground">Active</label>
    </div>
</div>
