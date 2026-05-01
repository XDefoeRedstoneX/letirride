<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-12" x-data="{ 
        show: false,
        view: 'list',
        selectedTicket: null,
        tickets: [
            { id: '#TKT-8821', subject: 'Payment Issue', status: 'OPEN', date: '2 hours ago', messages: [
                { from: 'user', text: 'Hi, I paid for Steam Wallet but haven\'t received the code yet.', time: '2 hours ago' },
                { from: 'admin', text: 'Hello! Let me check your transaction. Can you provide the TRX ID?', time: '1 hour ago' }
            ]},
            { id: '#TKT-7712', subject: 'Gacha Reward Missing', status: 'CLOSED', date: '1 day ago', messages: [
                { from: 'user', text: 'I won 500 points but my balance didn\'t increase.', time: '1 day ago' },
                { from: 'admin', text: 'Issue resolved. Points added to your account.', time: '20 hours ago' }
            ]}
        ]
    }" x-init="setTimeout(() => show = true, 50)">
        
        <!-- Header -->
        <div class="flex items-center justify-between" x-show="show" x-transition:enter="md:transition md:ease-out md:duration-700">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase">Customer <span class="text-primary">Support</span></h1>
                <p class="text-muted-foreground font-medium">Need help? File a ticket and our team will get back to you shortly.</p>
            </div>
            <button @click="view = 'create'" x-show="view === 'list'" class="px-8 py-3 bg-primary text-primary-foreground font-black text-xs uppercase tracking-widest rounded-2xl shadow-xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                New Ticket
            </button>
            <button @click="view = 'list'; selectedTicket = null" x-show="view !== 'list'" class="px-8 py-3 glass-card font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-white/10 transition-all">
                Back to List
            </button>
        </div>

        <!-- Ticket List -->
        <div x-show="view === 'list'" class="space-y-6">
            <div class="grid grid-cols-1 gap-4">
                <template x-for="ticket in tickets" :key="ticket.id">
                    <div @click="selectedTicket = ticket; view = 'chat'" class="glass-card p-6 rounded-[2rem] flex items-center justify-between group cursor-pointer hover:border-primary/30 transition-all duration-500">
                        <div class="flex items-center gap-6">
                            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest" x-text="ticket.id"></p>
                                <h3 class="font-black text-lg group-hover:text-primary transition-colors" x-text="ticket.subject"></h3>
                                <p class="text-xs text-muted-foreground font-medium" x-text="ticket.date"></p>
                            </div>
                        </div>
                        <span class="px-4 py-1.5 rounded-full text-[9px] font-black tracking-widest border"
                              :class="ticket.status === 'OPEN' ? 'bg-green-500/10 text-green-500 border-green-500/20' : 'bg-foreground/5 text-muted-foreground border-white/5'"
                              x-text="ticket.status"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Create Ticket Form -->
        <div x-show="view === 'create'" class="max-w-2xl mx-auto" x-transition>
            <div class="glass-card p-10 rounded-[3rem] space-y-8">
                <h2 class="text-2xl font-black uppercase tracking-widest text-center">Open New Ticket</h2>
                <form class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-2">Subject</label>
                        <input type="text" placeholder="e.g. Missing Item Reward" class="w-full px-6 py-4 bg-foreground/5 border border-white/5 rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all font-bold">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-2">Message</label>
                        <textarea rows="4" placeholder="Describe your problem in detail..." class="w-full px-6 py-4 bg-foreground/5 border border-white/5 rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all font-bold"></textarea>
                    </div>
                    <button type="button" @click="view = 'list'" class="w-full py-5 bg-primary text-primary-foreground font-black rounded-[1.5rem] shadow-2xl shadow-primary/30 hover:scale-[1.02] active:scale-95 transition-all text-sm tracking-widest uppercase">
                        Submit Ticket
                    </button>
                </form>
            </div>
        </div>

        <!-- Chat View -->
        <div x-show="view === 'chat' && selectedTicket" class="max-w-4xl mx-auto space-y-6" x-transition>
            <div class="glass-card rounded-[3rem] overflow-hidden flex flex-col h-[600px]">
                <!-- Chat Header -->
                <div class="p-8 border-b border-white/5 flex items-center justify-between bg-foreground/5">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-black">#</div>
                        <div>
                            <h3 class="font-black text-sm uppercase tracking-widest" x-text="selectedTicket?.subject"></h3>
                            <p class="text-[10px] text-muted-foreground font-bold" x-text="selectedTicket?.id"></p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-500 text-[9px] font-black tracking-widest border border-green-500/20" x-text="selectedTicket?.status"></span>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-8 space-y-6 scrollbar-hide">
                    <template x-for="msg in selectedTicket?.messages">
                        <div class="flex" :class="msg.from === 'user' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[80%] space-y-1">
                                <div class="px-6 py-4 rounded-[2rem]"
                                     :class="msg.from === 'user' ? 'bg-primary text-primary-foreground rounded-tr-none' : 'glass-card border-white/10 rounded-tl-none'">
                                    <p class="text-sm font-medium leading-relaxed" x-text="msg.text"></p>
                                </div>
                                <p class="text-[9px] font-bold text-muted-foreground px-2" :class="msg.from === 'user' ? 'text-right' : 'text-left'" x-text="msg.time"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Input -->
                <div class="p-6 border-t border-white/5 bg-foreground/5">
                    <div class="relative flex items-center gap-3">
                        <input type="text" placeholder="Type your message..." class="flex-1 px-6 py-4 bg-background/50 border border-white/5 rounded-2xl focus:ring-2 focus:ring-primary/50 outline-none transition-all font-bold text-sm">
                        <button class="w-14 h-14 bg-primary text-primary-foreground rounded-2xl flex items-center justify-center shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="pixel-render rotate-45"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 6 22 2"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
