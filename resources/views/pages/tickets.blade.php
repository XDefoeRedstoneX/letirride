<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Support Tickets</h1>
                <p class="text-muted-foreground">Need help? Create a ticket and our team will get back to you.</p>
            </div>
            <button class="px-6 py-2 bg-primary text-primary-foreground font-bold rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 transition-opacity flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Create Ticket
            </button>
        </div>

        <!-- Ticket List -->
        <div class="space-y-4">
            <div class="bg-card border border-border rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-primary/30 transition-all cursor-pointer group">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-muted-foreground">#TCK-001</span>
                        <span class="px-2 py-0.5 rounded-full bg-yellow-500/10 text-yellow-500 text-[10px] font-bold border border-yellow-500/20 uppercase">Pending</span>
                    </div>
                    <h3 class="font-bold group-hover:text-primary transition-colors">Voucher code not working</h3>
                    <p class="text-xs text-muted-foreground">Last updated: 2 hours ago</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden md:block">
                        <p class="text-xs font-medium">3 Replies</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-hover:translate-x-1 transition-transform"><path d="m9 18 6-6-6-6"/></svg>
                </div>
            </div>

            <div class="bg-card border border-border rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-primary/30 transition-all cursor-pointer group">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-muted-foreground">#TCK-002</span>
                        <span class="px-2 py-0.5 rounded-full bg-green-500/10 text-green-500 text-[10px] font-bold border border-green-500/20 uppercase">Resolved</span>
                    </div>
                    <h3 class="font-bold group-hover:text-primary transition-colors">How to get points?</h3>
                    <p class="text-xs text-muted-foreground">Last updated: 1 day ago</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden md:block">
                        <p class="text-xs font-medium">1 Reply</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-hover:translate-x-1 transition-transform"><path d="m9 18 6-6-6-6"/></svg>
                </div>
            </div>
        </div>

        <!-- FAQ Prompt -->
        <div class="py-12 text-center space-y-4">
            <h3 class="text-xl font-bold">Still have questions?</h3>
            <p class="text-muted-foreground max-w-md mx-auto">Check out our frequently asked questions or contact us directly on social media.</p>
            <div class="flex justify-center gap-4">
                <a href="#" class="text-sm font-bold text-primary hover:underline">Read FAQ</a>
                <span class="text-muted-foreground">•</span>
                <a href="#" class="text-sm font-bold text-primary hover:underline">Contact Support</a>
            </div>
        </div>
    </div>
</x-app-layout>
