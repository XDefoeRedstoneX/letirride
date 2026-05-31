<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">KEY <span class="req">*</span></label>
        <input type="text" name="key" required pattern="[a-z0-9\-]+" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-mono font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="steam">
        <p class="text-[10px] text-muted-foreground mt-1">Lowercase, digits & dashes only.</p>
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">LABEL <span class="req">*</span></label>
        <input type="text" name="label" required class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="Steam">
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">CATEGORY <span class="req">*</span></label>
        <select name="category" required class="w-full px-4 py-3 bg-white dark:bg-[#0f172a] border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
            @foreach($categories as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">SORT ORDER</label>
        <input type="number" name="sort_order" min="0" value="0" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-bold text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all">
    </div>
    <div class="sm:col-span-2">
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">UPLOAD IMAGE</label>
        <input type="file" name="image_file" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="w-full px-4 py-2.5 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs text-foreground outline-none focus:border-primary transition-all">
        <p class="text-[10px] text-muted-foreground mt-1">PNG/SVG/WebP, max 512&nbsp;KB. Leave blank to use the URL below or the generated coin for this key.</p>
    </div>
    <div class="sm:col-span-2">
        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 dark:text-muted-foreground mb-2 block">IMAGE URL (fallback)</label>
        <input type="text" name="image_url" class="w-full px-4 py-3 bg-foreground/5 border-2 border-border/50 rounded-xl text-xs font-mono text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all" placeholder="/gacha-icons/steam.svg">
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="add_icon_is_active" value="1" checked class="w-4 h-4">
        <label for="add_icon_is_active" class="text-xs font-bold text-foreground">Active</label>
    </div>
</div>
