<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SupportLayout from '@/layouts/SupportLayout.vue';
import { 
    Ticket, 
    MessageSquare, 
    Clock, 
    ShieldCheck, 
    Activity
} from 'lucide-vue-next';

// Placeholder stats for later phases
const stats = {
    openTickets: 5,
    myTickets: 2,
    activeChats: 1,
    onlineAgents: 3,
};
</script>

<template>
    <Head title="Agent Dashboard - CarryGo Support" />

    <SupportLayout :breadcrumbs="[{ title: 'Dashboard' }]">
        <div class="flex flex-col gap-6">
            <!-- Greeting & Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm">
                <div>
                    <h1 class="text-xl font-bold text-primary tracking-tight">Welcome to Agent Dashboard</h1>
                    <p class="text-xs text-on-surface-variant mt-1">Here is a quick overview of your current queue and activity metrics.</p>
                </div>
                <div class="flex items-center gap-2 bg-secondary-container border border-secondary/20 px-3.5 py-1.5 rounded-lg text-on-secondary-container">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-secondary"></span>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider">System Online</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Open Tickets -->
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center justify-between group hover:border-primary hover:shadow-md transition-all duration-200">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Unassigned Tickets</p>
                        <p class="text-2xl font-black text-primary">{{ stats.openTickets }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-primary-container text-on-primary-container group-hover:scale-110 transition-transform">
                        <Ticket class="w-5 h-5" />
                    </div>
                </div>

                <!-- My Tickets -->
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center justify-between group hover:border-secondary hover:shadow-md transition-all duration-200">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">My Active Tickets</p>
                        <p class="text-2xl font-black text-primary">{{ stats.myTickets }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-secondary-container text-on-secondary-container group-hover:scale-110 transition-transform">
                        <Clock class="w-5 h-5" />
                    </div>
                </div>

                <!-- Active Chats -->
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center justify-between group hover:border-tertiary hover:shadow-md transition-all duration-200">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Active Live Chats</p>
                        <p class="text-2xl font-black text-primary">{{ stats.activeChats }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-tertiary-container text-on-tertiary-container group-hover:scale-110 transition-transform">
                        <MessageSquare class="w-5 h-5" />
                    </div>
                </div>

                <!-- Online Agents -->
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center justify-between group hover:border-outline hover:shadow-md transition-all duration-200">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Online Support Staff</p>
                        <p class="text-2xl font-black text-primary">{{ stats.onlineAgents }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-surface-container-high text-primary group-hover:scale-110 transition-transform">
                        <Activity class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <!-- Main Content Area Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Portal Overview / Guide -->
                <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm lg:col-span-2 space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-outline-variant/30 pb-3">Getting Started Guide</h2>
                    <div class="space-y-4 text-xs leading-relaxed text-on-surface-variant">
                        <p>Welcome to your agent workstation. This portal helps you handle and resolve customer inquiries efficiently. Below are the key modules:</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-outline-variant/60 p-4 rounded-lg bg-surface-container-low">
                                <h3 class="font-bold text-primary flex items-center gap-1.5 mb-1.5">
                                    <Ticket class="w-4 h-4 text-primary" />
                                    <span>Ticket Queue</span>
                                </h3>
                                <p>Claim unassigned tickets, respond with public replies or internal notes, and close tickets once resolved.</p>
                            </div>
                            <div class="border border-outline-variant/60 p-4 rounded-lg bg-surface-container-low">
                                <h3 class="font-bold text-primary flex items-center gap-1.5 mb-1.5">
                                    <MessageSquare class="w-4 h-4 text-secondary" />
                                    <span>Live Chat</span>
                                </h3>
                                <p>Initiate or claim waiting customer chats, message in real-time, and convert conversations into tickets when needed.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Info -->
                <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-outline-variant/30 pb-3">My Session Details</h2>
                    <div class="space-y-3.5 text-xs">
                        <div class="flex justify-between items-center py-1 border-b border-outline-variant/30">
                            <span class="text-on-surface-variant">Portal Role</span>
                            <span class="font-bold text-on-surface uppercase">Support Agent</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-outline-variant/30">
                            <span class="text-on-surface-variant">Department</span>
                            <span class="font-bold text-on-surface">{{ $page.props.auth?.user?.department || 'Operations' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-outline-variant/30">
                            <span class="text-on-surface-variant">Employee ID</span>
                            <span class="font-mono font-bold text-on-surface">{{ $page.props.auth?.user?.employee_id || 'EMP-XXXX' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-on-surface-variant">Status</span>
                            <span class="inline-flex items-center gap-1 bg-secondary-container text-on-secondary-container border border-secondary/20 px-2.5 py-1 rounded-full font-bold text-[9px] uppercase tracking-wider">
                                Online
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SupportLayout>
</template>
