<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    Ticket,
    MessageSquare,
    Clock,
    ShieldCheck,
    Activity,
} from 'lucide-vue-next';
import { computed } from 'vue';
import SupportLayout from '@/layouts/SupportLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user as any);
const currentStatus = computed(
    () => user.value?.agent_status?.status || 'offline',
);

const statusStyles: Record<string, string> = {
    online: 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20',
    away: 'bg-amber-500/10 text-amber-500 border border-amber-500/20',
    offline: 'bg-rose-500/10 text-rose-500 border border-rose-500/20',
};

const statusLabel: Record<string, string> = {
    online: 'Online',
    away: 'Away',
    offline: 'Offline',
};

const props = defineProps<{
    stats: {
        openTickets: number;
        myTickets: number;
        activeChats: number;
        onlineAgents: number;
    };
}>();
</script>

<template>
    <Head title="Agent Dashboard - Bidora Support" />

    <SupportLayout :breadcrumbs="[{ title: 'Dashboard' }]">
        <div class="flex flex-col gap-6">
            <!-- Greeting & Header -->
            <div
                class="flex flex-col items-start justify-between gap-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm md:flex-row md:items-center"
            >
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-primary">
                        Welcome to Agent Dashboard
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Here is a quick overview of your current queue and
                        activity metrics.
                    </p>
                </div>
                <div
                    class="flex items-center gap-2 rounded-lg border border-secondary/20 bg-secondary-container px-3.5 py-1.5 text-on-secondary-container"
                >
                    <span class="relative flex h-2 w-2">
                        <span
                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-secondary opacity-75"
                        ></span>
                        <span
                            class="relative inline-flex h-2 w-2 rounded-full bg-secondary"
                        ></span>
                    </span>
                    <span class="text-xs font-bold tracking-wider uppercase"
                        >System Online</span
                    >
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Open Tickets -->
                <div
                    class="group flex items-center justify-between rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all duration-200 hover:border-primary hover:shadow-md"
                >
                    <div class="space-y-1">
                        <p
                            class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                        >
                            Unassigned Tickets
                        </p>
                        <p class="text-2xl font-black text-primary">
                            {{ stats.openTickets }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-primary-container p-3 text-on-primary-container transition-transform group-hover:scale-110"
                    >
                        <Ticket class="h-5 w-5" />
                    </div>
                </div>

                <!-- My Tickets -->
                <div
                    class="group flex items-center justify-between rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all duration-200 hover:border-secondary hover:shadow-md"
                >
                    <div class="space-y-1">
                        <p
                            class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                        >
                            My Active Tickets
                        </p>
                        <p class="text-2xl font-black text-primary">
                            {{ stats.myTickets }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-secondary-container p-3 text-on-secondary-container transition-transform group-hover:scale-110"
                    >
                        <Clock class="h-5 w-5" />
                    </div>
                </div>

                <!-- Active Chats -->
                <div
                    class="group flex items-center justify-between rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all duration-200 hover:border-tertiary hover:shadow-md"
                >
                    <div class="space-y-1">
                        <p
                            class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                        >
                            Active Live Chats
                        </p>
                        <p class="text-2xl font-black text-primary">
                            {{ stats.activeChats }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-tertiary-container p-3 text-on-tertiary-container transition-transform group-hover:scale-110"
                    >
                        <MessageSquare class="h-5 w-5" />
                    </div>
                </div>

                <!-- Online Agents -->
                <div
                    class="group flex items-center justify-between rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all duration-200 hover:border-outline hover:shadow-md"
                >
                    <div class="space-y-1">
                        <p
                            class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                        >
                            Online Support Staff
                        </p>
                        <p class="text-2xl font-black text-primary">
                            {{ stats.onlineAgents }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-surface-container-high p-3 text-primary transition-transform group-hover:scale-110"
                    >
                        <Activity class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Main Content Area Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Portal Overview / Guide -->
                <div
                    class="space-y-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm lg:col-span-2"
                >
                    <h2
                        class="border-b border-outline-variant/30 pb-3 text-sm font-bold tracking-wider text-primary uppercase"
                    >
                        Getting Started Guide
                    </h2>
                    <div
                        class="space-y-4 text-xs leading-relaxed text-on-surface-variant"
                    >
                        <p>
                            Welcome to your agent workstation. This portal helps
                            you handle and resolve customer inquiries
                            efficiently. Below are the key modules:
                        </p>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div
                                class="rounded-lg border border-outline-variant/60 bg-surface-container-low p-4"
                            >
                                <h3
                                    class="mb-1.5 flex items-center gap-1.5 font-bold text-primary"
                                >
                                    <Ticket class="h-4 w-4 text-primary" />
                                    <span>Ticket Queue</span>
                                </h3>
                                <p>
                                    Claim unassigned tickets, respond with
                                    public replies or internal notes, and close
                                    tickets once resolved.
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-outline-variant/60 bg-surface-container-low p-4"
                            >
                                <h3
                                    class="mb-1.5 flex items-center gap-1.5 font-bold text-primary"
                                >
                                    <MessageSquare
                                        class="h-4 w-4 text-secondary"
                                    />
                                    <span>Live Chat</span>
                                </h3>
                                <p>
                                    Initiate or claim waiting customer chats,
                                    message in real-time, and convert
                                    conversations into tickets when needed.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Info -->
                <div
                    class="space-y-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm"
                >
                    <h2
                        class="border-b border-outline-variant/30 pb-3 text-sm font-bold tracking-wider text-primary uppercase"
                    >
                        My Session Details
                    </h2>
                    <div class="space-y-3.5 text-xs">
                        <div
                            class="flex items-center justify-between border-b border-outline-variant/30 py-1"
                        >
                            <span class="text-on-surface-variant"
                                >Portal Role</span
                            >
                            <span class="font-bold text-on-surface uppercase"
                                >Support Agent</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-outline-variant/30 py-1"
                        >
                            <span class="text-on-surface-variant"
                                >Department</span
                            >
                            <span class="font-bold text-on-surface">{{
                                $page.props.auth?.user?.department ||
                                'Operations'
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-outline-variant/30 py-1"
                        >
                            <span class="text-on-surface-variant"
                                >Employee ID</span
                            >
                            <span class="font-mono font-bold text-on-surface">{{
                                $page.props.auth?.user?.employee_id ||
                                'EMP-XXXX'
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-on-surface-variant">Status</span>
                            <span
                                :class="[
                                    'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[9px] font-bold tracking-wider uppercase',
                                    statusStyles[currentStatus],
                                ]"
                            >
                                {{ statusLabel[currentStatus] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SupportLayout>
</template>
