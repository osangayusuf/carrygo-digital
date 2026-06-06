<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import SupportLayout from '@/layouts/SupportLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { index as ticketsIndex, claim as ticketsClaim, close as ticketsClose } from '@/routes/support/tickets';
import { store as messagesStore } from '@/routes/support/tickets/messages';
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
    Unlock
} from 'lucide-vue-next';

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
        }
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
    window.Echo?.private(`support.ticket.${props.ticket.id}`)
        .listen('.TicketMessageAdded', (event: any) => {
            // Push message to the local list if it's not already there
            const messageExists = props.ticket.messages.some(m => m.id === event.id);
            if (!messageExists) {
                props.ticket.messages.push({
                    id: event.id,
                    body: event.body,
                    is_internal: event.is_internal,
                    created_at: event.created_at,
                    sender: event.sender
                });
            }
        });
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

    <SupportLayout :breadcrumbs="[
        { title: 'Tickets', href: ticketsIndex.url() },
        { title: `Ticket #${ticket.id}` }
    ]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Back & Action Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <Link 
                    :href="ticketsIndex.url()"
                    class="inline-flex items-center gap-1.5 text-on-surface-variant hover:text-primary font-bold uppercase transition-colors"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Back to tickets</span>
                </Link>

                <div class="flex items-center gap-2">
                    <button 
                        v-if="!ticket.agent"
                        @click="claimTicket"
                        class="flex items-center justify-center gap-1.5 px-3.5 py-2 border border-secondary/25 bg-secondary-container text-on-secondary-container hover:bg-secondary hover:text-primary rounded-lg font-bold uppercase transition-all cursor-pointer"
                    >
                        <UserCheck class="w-3.5 h-3.5" />
                        <span>Claim Ticket</span>
                    </button>

                    <button 
                        v-if="ticket.status !== 'closed' && (ticket.agent?.id === $page.props.auth?.user?.id || $page.props.auth?.user?.is_admin)"
                        @click="closeTicket"
                        class="flex items-center justify-center gap-1.5 px-3.5 py-2 border border-error/20 bg-error-container/15 text-error hover:bg-error-container/30 rounded-lg font-bold uppercase transition-all cursor-pointer"
                    >
                        <CheckCircle class="w-3.5 h-3.5" />
                        <span>Close Ticket</span>
                    </button>
                </div>
            </div>

            <!-- Content Area Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Conversation Stream -->
                <div class="lg:col-span-2 flex flex-col gap-6 bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                    <!-- Ticket Main Subject & Details -->
                    <div class="border-b border-outline-variant/30 pb-5 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-[10px] uppercase text-primary-container bg-primary/80 border border-primary/20 px-2 py-0.5 rounded">
                                {{ ticket.category }}
                            </span>
                            <span :class="['text-[9px] font-black uppercase px-2 py-0.5 rounded border', getPriorityClass(ticket.priority)]">
                                {{ ticket.priority }}
                            </span>
                            <span :class="['text-[9px] font-black uppercase px-2 py-0.5 rounded border', getStatusClass(ticket.status)]">
                                {{ ticket.status }}
                            </span>
                        </div>
                        <h1 class="text-lg font-bold text-primary tracking-tight">{{ ticket.subject }}</h1>
                        <div class="flex flex-wrap items-center gap-3 text-[10px] text-on-surface-variant font-medium">
                            <span class="flex items-center gap-1">
                                <UserIcon class="w-3.5 h-3.5" />
                                <span>Customer: <strong class="text-on-surface">{{ ticket.customer.name }}</strong> ({{ ticket.customer.email }})</span>
                            </span>
                            <span>•</span>
                            <span>Opened {{ new Date(ticket.created_at).toLocaleString() }}</span>
                        </div>
                    </div>

                    <!-- Conversation Thread -->
                    <div class="flex flex-col gap-4 max-h-[600px] overflow-y-auto pr-1">
                        <div 
                            v-for="msg in ticket.messages" 
                            :key="msg.id"
                            :class="[
                                'p-4 rounded-xl border flex flex-col gap-2',
                                msg.is_internal 
                                    ? 'bg-amber/10 border-amber/35 text-on-surface shadow-sm'
                                    : msg.sender.id === ticket.customer.id
                                        ? 'bg-surface-container-low border-outline-variant/50'
                                        : 'bg-secondary-container/20 border-secondary/15'
                            ]"
                        >
                            <div class="flex justify-between items-center text-[10px]">
                                <span class="font-bold flex items-center gap-1 flex-wrap">
                                    <span class="text-primary">{{ msg.sender.name }}</span>
                                    <span 
                                        v-if="msg.sender.id === ticket.customer.id"
                                        class="bg-surface-container-highest text-on-surface-variant font-black uppercase text-[8px] tracking-wider px-1.5 py-0.5 rounded"
                                    >
                                        Customer
                                    </span>
                                    <span 
                                        v-else
                                        class="bg-primary/10 text-primary font-black uppercase text-[8px] tracking-wider px-1.5 py-0.5 rounded"
                                    >
                                        Staff
                                    </span>
                                    <span 
                                        v-if="msg.is_internal"
                                        class="bg-amber/30 text-primary font-black uppercase text-[8px] tracking-widest px-2 py-0.5 rounded flex items-center gap-1"
                                    >
                                        <Lock class="w-2.5 h-2.5" />
                                        <span>Internal Note</span>
                                    </span>
                                </span>
                                <span class="text-on-surface-variant/75 font-medium">{{ new Date(msg.created_at).toLocaleString() }}</span>
                            </div>
                            <p class="text-[11px] leading-relaxed text-on-surface whitespace-pre-wrap">{{ msg.body }}</p>
                        </div>
                    </div>

                    <!-- Reply/Note Form -->
                    <div v-if="ticket.status !== 'closed'" class="border-t border-outline-variant/30 pt-5 mt-auto">
                        <form @submit.prevent="submitReply" class="space-y-4">
                            <!-- Toggle for Internal Note -->
                            <div class="flex items-center gap-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <input 
                                        type="checkbox"
                                        v-model="replyForm.is_internal"
                                        class="h-4 w-4 rounded border-outline-variant text-primary accent-primary focus:ring-primary focus:outline-none"
                                    />
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1">
                                        <Lock class="w-3 h-3" />
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
                                    :placeholder="replyForm.is_internal ? 'WRITE INTERNAL NOTES SECURELY VISIBLE ONLY TO AGENTS & ADMINS...' : 'WRITE A PUBLIC REPLY DIRECTLY TO THE CUSTOMER...'"
                                    class="bg-surface-container-low border border-outline-variant text-on-surface p-4 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-medium"
                                ></textarea>
                                <p v-if="replyForm.errors.body" class="text-error mt-1">{{ replyForm.errors.body }}</p>
                            </div>

                            <button 
                                type="submit"
                                :disabled="replyForm.processing || !replyForm.body.trim()"
                                :class="[
                                    'flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black uppercase rounded-lg disabled:opacity-50 transition-all cursor-pointer',
                                    replyForm.is_internal 
                                        ? 'bg-amber text-navy hover:bg-lemon' 
                                        : 'bg-navy text-lemon hover:bg-forest'
                                ]"
                            >
                                <Send class="w-4.5 h-4.5" />
                                <span>{{ replyForm.is_internal ? 'Add Internal Note' : 'Send Reply' }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Metadata & Assignment -->
                <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm space-y-5 h-fit">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-outline-variant/30 pb-3">Ticket Information</h2>
                    <div class="space-y-4 text-xs">
                        <div class="flex justify-between items-center py-1.5 border-b border-outline-variant/20">
                            <span class="text-on-surface-variant font-medium">Ticket ID</span>
                            <span class="font-bold text-on-surface">#{{ ticket.id }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-outline-variant/20">
                            <span class="text-on-surface-variant font-medium">Assigned Agent</span>
                            <span class="font-bold text-on-surface uppercase flex items-center gap-1.5">
                                <span v-if="ticket.agent" class="text-secondary font-black bg-secondary/15 px-2.5 py-0.5 rounded border border-secondary/20 text-[9px] tracking-wider">
                                    {{ ticket.agent.name }}
                                </span>
                                <span v-else class="text-on-surface-variant italic">Unassigned</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-outline-variant/20">
                            <span class="text-on-surface-variant font-medium">Category</span>
                            <span class="font-bold text-primary uppercase bg-primary-container/30 px-2 py-0.5 rounded">{{ ticket.category }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-outline-variant/20">
                            <span class="text-on-surface-variant font-medium">Priority</span>
                            <span :class="['font-bold uppercase px-2 py-0.5 rounded border text-[9px]', getPriorityClass(ticket.priority)]">{{ ticket.priority }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5">
                            <span class="text-on-surface-variant font-medium">Created At</span>
                            <span class="font-medium text-on-surface-variant">{{ new Date(ticket.created_at).toLocaleString() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SupportLayout>
</template>
