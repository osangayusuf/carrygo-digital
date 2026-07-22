<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Ticket as TicketIcon,
    ArrowLeft,
    Clock,
    User as UserIcon,
    AlertCircle,
    CheckCircle,
    MessageSquare,
    Lock,
    Send,
    UserCheck,
    Unlock,
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import SupportLayout from '@/layouts/SupportLayout.vue';
import {
    index as ticketsIndex,
    claim as ticketsClaim,
    close as ticketsClose,
} from '@/routes/support/tickets';
import { store as messagesStore } from '@/routes/support/tickets/messages';

type User = {
    id: number;
    name: string;
    email: string;
};

type TicketMessage = {
    id: number;
    body: string;
    is_internal: boolean;
    created_at: string;
    sender: User;
};

type Ticket = {
    id: number;
    uuid: string;
    subject: string;
    status: string;
    priority: string;
    category: string;
    created_at: string;
    customer: User;
    agent: User | null;
    messages: TicketMessage[];
};

const props = defineProps<{
    ticket: Ticket;
    categories: string[];
    priorities: string[];
}>();

const replyForm = useForm({
    body: '',
    is_internal: false,
});

const submitReply = () => {
    replyForm.post(messagesStore.url(props.ticket.id), {
        onSuccess: () => {
            replyForm.reset('body');
        },
    });
};

const claimTicket = () => {
    if (confirm('Are you sure you want to claim this ticket?')) {
        useForm({}).patch(ticketsClaim.url(props.ticket.id));
    }
};

const closeTicket = () => {
    if (confirm('Are you sure you want to close this ticket?')) {
        useForm({}).patch(ticketsClose.url(props.ticket.id));
    }
};

// Real-time echo listener
onMounted(() => {
    window.Echo?.private(`support.ticket.${props.ticket.id}`).listen(
        '.TicketMessageAdded',
        (event: any) => {
            // Push message to the local list if it's not already there
            const messageExists = props.ticket.messages.some(
                (m) => m.id === event.id,
            );

            if (!messageExists) {
                props.ticket.messages.push({
                    id: event.id,
                    body: event.body,
                    is_internal: event.is_internal,
                    created_at: event.created_at,
                    sender: event.sender,
                });
            }
        },
    );
});

onUnmounted(() => {
    window.Echo?.leave(`support.ticket.${props.ticket.id}`);
});

const getPriorityClass = (priority: string) => {
    switch (priority.toLowerCase()) {
        case 'urgent':
            return 'bg-error-container/30 text-error border-error/20';
        case 'high':
            return 'bg-amber/15 text-primary border-amber/30';
        case 'normal':
            return 'bg-secondary-container/40 text-on-secondary-container border-secondary/20';
        default:
            return 'bg-surface-container-high text-on-surface-variant border-outline-variant/60';
    }
};

const getStatusClass = (status: string) => {
    switch (status.toLowerCase()) {
        case 'open':
            return 'bg-secondary-container/50 text-on-secondary-container border border-secondary/20';
        case 'in_progress':
            return 'bg-primary-container text-on-primary-container border border-primary/20';
        case 'closed':
            return 'bg-surface-container-highest text-on-surface-variant border border-outline-variant/40';
        default:
            return 'bg-surface-container-low text-on-surface-variant border border-outline-variant';
    }
};
</script>

<template>
    <Head :title="`Ticket Details - ${ticket.subject}`" />

    <SupportLayout
        :breadcrumbs="[
            { title: 'Tickets', href: ticketsIndex.url() },
            { title: `Ticket #${ticket.id}` },
        ]"
    >
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Back & Action Header -->
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <Link
                    :href="ticketsIndex.url()"
                    class="inline-flex items-center gap-1.5 font-bold text-on-surface-variant uppercase transition-colors hover:text-primary"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Back to tickets</span>
                </Link>

                <div class="flex items-center gap-2">
                    <button
                        v-if="!ticket.agent"
                        @click="claimTicket"
                        class="flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-secondary/25 bg-secondary-container px-3.5 py-2 font-bold text-on-secondary-container uppercase transition-all hover:bg-secondary hover:text-primary"
                    >
                        <UserCheck class="h-3.5 w-3.5" />
                        <span>Claim Ticket</span>
                    </button>

                    <button
                        v-if="
                            ticket.status !== 'closed' &&
                            (ticket.agent?.id === $page.props.auth?.user?.id ||
                                $page.props.auth?.user?.is_admin)
                        "
                        @click="closeTicket"
                        class="flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-error/20 bg-error-container/15 px-3.5 py-2 font-bold text-error uppercase transition-all hover:bg-error-container/30"
                    >
                        <CheckCircle class="h-3.5 w-3.5" />
                        <span>Close Ticket</span>
                    </button>
                </div>
            </div>

            <!-- Content Area Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left: Conversation Stream -->
                <div
                    class="flex flex-col gap-6 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm lg:col-span-2"
                >
                    <!-- Ticket Main Subject & Details -->
                    <div
                        class="space-y-3 border-b border-outline-variant/30 pb-5"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded border border-primary/20 bg-primary/80 px-2 py-0.5 text-[10px] font-bold text-primary-container uppercase"
                            >
                                {{ ticket.category }}
                            </span>
                            <span
                                :class="[
                                    'rounded border px-2 py-0.5 text-[9px] font-black uppercase',
                                    getPriorityClass(ticket.priority),
                                ]"
                            >
                                {{ ticket.priority }}
                            </span>
                            <span
                                :class="[
                                    'rounded border px-2 py-0.5 text-[9px] font-black uppercase',
                                    getStatusClass(ticket.status),
                                ]"
                            >
                                {{ ticket.status }}
                            </span>
                        </div>
                        <h1
                            class="text-lg font-bold tracking-tight text-primary"
                        >
                            {{ ticket.subject }}
                        </h1>
                        <div
                            class="flex flex-wrap items-center gap-3 text-[10px] font-medium text-on-surface-variant"
                        >
                            <span class="flex items-center gap-1">
                                <UserIcon class="h-3.5 w-3.5" />
                                <span
                                    >Customer:
                                    <strong class="text-on-surface">{{
                                        ticket.customer.name
                                    }}</strong>
                                    ({{ ticket.customer.email }})</span
                                >
                            </span>
                            <span>•</span>
                            <span
                                >Opened
                                {{
                                    new Date(ticket.created_at).toLocaleString()
                                }}</span
                            >
                        </div>
                    </div>

                    <!-- Conversation Thread -->
                    <div
                        class="flex max-h-[600px] flex-col gap-4 overflow-y-auto pr-1"
                    >
                        <div
                            v-for="msg in ticket.messages"
                            :key="msg.id"
                            :class="[
                                'flex flex-col gap-2 rounded-xl border p-4',
                                msg.is_internal
                                    ? 'border-amber/35 bg-amber/10 text-on-surface shadow-sm'
                                    : msg.sender.id === ticket.customer.id
                                      ? 'border-outline-variant/50 bg-surface-container-low'
                                      : 'border-secondary/15 bg-secondary-container/20',
                            ]"
                        >
                            <div
                                class="flex items-center justify-between text-[10px]"
                            >
                                <span
                                    class="flex flex-wrap items-center gap-1 font-bold"
                                >
                                    <span class="text-primary">{{
                                        msg.sender.name
                                    }}</span>
                                    <span
                                        v-if="
                                            msg.sender.id === ticket.customer.id
                                        "
                                        class="rounded bg-surface-container-highest px-1.5 py-0.5 text-[8px] font-black tracking-wider text-on-surface-variant uppercase"
                                    >
                                        Customer
                                    </span>
                                    <span
                                        v-else
                                        class="rounded bg-primary/10 px-1.5 py-0.5 text-[8px] font-black tracking-wider text-primary uppercase"
                                    >
                                        Staff
                                    </span>
                                    <span
                                        v-if="msg.is_internal"
                                        class="flex items-center gap-1 rounded bg-amber/30 px-2 py-0.5 text-[8px] font-black tracking-widest text-primary uppercase"
                                    >
                                        <Lock class="h-2.5 w-2.5" />
                                        <span>Internal Note</span>
                                    </span>
                                </span>
                                <span
                                    class="font-medium text-on-surface-variant/75"
                                    >{{
                                        new Date(
                                            msg.created_at,
                                        ).toLocaleString()
                                    }}</span
                                >
                            </div>
                            <p
                                class="text-[11px] leading-relaxed whitespace-pre-wrap text-on-surface"
                            >
                                {{ msg.body }}
                            </p>
                        </div>
                    </div>

                    <!-- Reply/Note Form -->
                    <div
                        v-if="ticket.status !== 'closed'"
                        class="mt-auto border-t border-outline-variant/30 pt-5"
                    >
                        <form @submit.prevent="submitReply" class="space-y-4">
                            <!-- Toggle for Internal Note -->
                            <div class="flex items-center gap-2">
                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 select-none"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="replyForm.is_internal"
                                        class="h-4 w-4 rounded border-outline-variant text-primary accent-primary focus:ring-primary focus:outline-none"
                                    />
                                    <span
                                        class="flex items-center gap-1 text-[10px] font-bold tracking-wider text-on-surface-variant uppercase"
                                    >
                                        <Lock class="h-3 w-3" />
                                        <span>Post as Internal Note</span>
                                    </span>
                                </label>
                            </div>

                            <!-- Form Textarea -->
                            <div class="flex flex-col gap-1.5">
                                <textarea
                                    v-model="replyForm.body"
                                    rows="4"
                                    required
                                    :placeholder="
                                        replyForm.is_internal
                                            ? 'WRITE INTERNAL NOTES SECURELY VISIBLE ONLY TO AGENTS & ADMINS...'
                                            : 'WRITE A PUBLIC REPLY DIRECTLY TO THE CUSTOMER...'
                                    "
                                    class="rounded-lg border border-outline-variant bg-surface-container-low p-4 font-medium text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                                ></textarea>
                                <p
                                    v-if="replyForm.errors.body"
                                    class="mt-1 text-error"
                                >
                                    {{ replyForm.errors.body }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    replyForm.processing ||
                                    !replyForm.body.trim()
                                "
                                :class="[
                                    'flex cursor-pointer items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-xs font-black uppercase transition-all disabled:opacity-50',
                                    replyForm.is_internal
                                        ? 'bg-amber text-navy hover:bg-lemon'
                                        : 'bg-navy text-lemon hover:bg-forest',
                                ]"
                            >
                                <Send class="h-4.5 w-4.5" />
                                <span>{{
                                    replyForm.is_internal
                                        ? 'Add Internal Note'
                                        : 'Send Reply'
                                }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Metadata & Assignment -->
                <div
                    class="h-fit space-y-5 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm"
                >
                    <h2
                        class="border-b border-outline-variant/30 pb-3 text-sm font-bold tracking-wider text-primary uppercase"
                    >
                        Ticket Information
                    </h2>
                    <div class="space-y-4 text-xs">
                        <div
                            class="flex items-center justify-between border-b border-outline-variant/20 py-1.5"
                        >
                            <span class="font-medium text-on-surface-variant"
                                >Ticket ID</span
                            >
                            <span class="font-bold text-on-surface"
                                >#{{ ticket.id }}</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-outline-variant/20 py-1.5"
                        >
                            <span class="font-medium text-on-surface-variant"
                                >Assigned Agent</span
                            >
                            <span
                                class="flex items-center gap-1.5 font-bold text-on-surface uppercase"
                            >
                                <span
                                    v-if="ticket.agent"
                                    class="rounded border border-secondary/20 bg-secondary/15 px-2.5 py-0.5 text-[9px] font-black tracking-wider text-secondary"
                                >
                                    {{ ticket.agent.name }}
                                </span>
                                <span
                                    v-else
                                    class="text-on-surface-variant italic"
                                    >Unassigned</span
                                >
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-outline-variant/20 py-1.5"
                        >
                            <span class="font-medium text-on-surface-variant"
                                >Category</span
                            >
                            <span
                                class="rounded bg-primary-container/30 px-2 py-0.5 font-bold text-primary uppercase"
                                >{{ ticket.category }}</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-outline-variant/20 py-1.5"
                        >
                            <span class="font-medium text-on-surface-variant"
                                >Priority</span
                            >
                            <span
                                :class="[
                                    'rounded border px-2 py-0.5 text-[9px] font-bold uppercase',
                                    getPriorityClass(ticket.priority),
                                ]"
                                >{{ ticket.priority }}</span
                            >
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="font-medium text-on-surface-variant"
                                >Created At</span
                            >
                            <span class="font-medium text-on-surface-variant">{{
                                new Date(ticket.created_at).toLocaleString()
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SupportLayout>
</template>
