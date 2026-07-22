<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Ticket as TicketIcon,
    Plus,
    Clock,
    AlertCircle,
    CheckCircle,
    ArrowRight,
    Inbox,
    User as UserIcon,
    Search,
} from 'lucide-vue-next';
import { ref } from 'vue';
import SupportLayout from '@/layouts/SupportLayout.vue';
import {
    index as ticketsIndex,
    store as ticketsStore,
    show as ticketsShow,
} from '@/routes/support/tickets';

type User = {
    id: number;
    name: string;
    email: string;
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
};

const props = defineProps<{
    tickets: {
        data: Ticket[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        tab: string;
    };
    counts?: {
        unassigned: number;
        mine: number;
        closed: number;
    };
    customers?: User[];
    categories: string[];
    priorities: string[];
}>();

const activeTab = ref(props.filters.tab || 'unassigned');
const showCreateModal = ref(false);

const createForm = useForm({
    customer_id: '',
    subject: '',
    category: '',
    priority: 'normal',
    body: '',
});

const submitCreate = () => {
    createForm.post(ticketsStore.url(), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const switchTab = (tab: string) => {
    activeTab.value = tab;
    router.get(ticketsIndex.url(), { tab }, { preserveState: true });
};

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
    <Head title="Tickets Queue - Bidora Support" />

    <SupportLayout :breadcrumbs="[{ title: 'Tickets' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <TicketIcon class="h-6 w-6 text-primary" />
                        <span>Support Tickets</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Manage and resolve customer support inquiries.
                    </p>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="flex cursor-pointer items-center justify-center gap-2 self-start rounded-lg bg-navy px-4 py-2.5 font-extrabold text-lemon uppercase transition-all hover:bg-forest sm:self-auto"
                >
                    <Plus class="h-4 w-4" />
                    <span>Create Ticket</span>
                </button>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Panel: Ticket list -->
                <div class="flex flex-col gap-4 lg:col-span-2">
                    <!-- Tab filters -->
                    <div class="flex border-b border-outline-variant/50">
                        <button
                            @click="switchTab('unassigned')"
                            :class="[
                                'border-b-2 px-5 py-3 text-[10px] font-bold tracking-wider uppercase transition-all',
                                activeTab === 'unassigned'
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-on-surface-variant hover:text-on-surface',
                            ]"
                        >
                            Unassigned ({{ counts?.unassigned ?? '...' }})
                        </button>
                        <button
                            @click="switchTab('mine')"
                            :class="[
                                'border-b-2 px-5 py-3 text-[10px] font-bold tracking-wider uppercase transition-all',
                                activeTab === 'mine'
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-on-surface-variant hover:text-on-surface',
                            ]"
                        >
                            Assigned to Me ({{ counts?.mine ?? '...' }})
                        </button>
                        <button
                            @click="switchTab('closed')"
                            :class="[
                                'border-b-2 px-5 py-3 text-[10px] font-bold tracking-wider uppercase transition-all',
                                activeTab === 'closed'
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-on-surface-variant hover:text-on-surface',
                            ]"
                        >
                            Closed ({{ counts?.closed ?? '...' }})
                        </button>
                    </div>

                    <!-- Tickets List -->
                    <div
                        class="flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
                    >
                        <div class="divide-y divide-outline-variant/40">
                            <div
                                v-for="ticket in tickets.data"
                                :key="ticket.id"
                                class="group flex flex-col justify-between gap-4 p-5 transition-all hover:bg-surface-container-low/20 md:flex-row md:items-center"
                            >
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <span
                                            class="rounded border border-primary/20 bg-primary/80 px-2 py-0.5 text-[10px] font-bold text-primary-container uppercase"
                                        >
                                            {{ ticket.category }}
                                        </span>
                                        <span
                                            :class="[
                                                'rounded border px-2 py-0.5 text-[9px] font-black uppercase',
                                                getPriorityClass(
                                                    ticket.priority,
                                                ),
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
                                    <h2
                                        class="truncate text-sm font-bold text-on-surface transition-colors group-hover:text-primary"
                                    >
                                        {{ ticket.subject }}
                                    </h2>
                                    <div
                                        class="flex flex-wrap items-center gap-3 text-[10px] font-medium text-on-surface-variant"
                                    >
                                        <span class="flex items-center gap-1">
                                            <UserIcon class="h-3.5 w-3.5" />
                                            <span>{{
                                                ticket.customer.name
                                            }}</span>
                                        </span>
                                        <span>•</span>
                                        <span
                                            >Opened
                                            {{
                                                new Date(
                                                    ticket.created_at,
                                                ).toLocaleString()
                                            }}</span
                                        >
                                        <template v-if="ticket.agent">
                                            <span>•</span>
                                            <span
                                                class="rounded bg-secondary/10 px-2 py-0.5 text-[9px] font-bold tracking-wider text-secondary uppercase"
                                            >
                                                Agent: {{ ticket.agent.name }}
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center self-end md:self-auto"
                                >
                                    <Link
                                        :href="ticketsShow.url(ticket.id)"
                                        class="flex items-center gap-1.5 rounded-lg border border-outline-variant bg-surface-container-low px-3 py-1.5 font-bold text-on-surface transition-all hover:bg-secondary-container hover:text-primary"
                                    >
                                        <span>Open</span>
                                        <ArrowRight class="h-3.5 w-3.5" />
                                    </Link>
                                </div>
                            </div>

                            <div
                                v-if="tickets.data.length === 0"
                                class="flex flex-col items-center justify-center gap-3 py-16 text-center text-on-surface-variant"
                            >
                                <Inbox
                                    class="h-12 w-12 text-outline-variant/60"
                                />
                                <span
                                    class="text-[10px] font-black tracking-widest uppercase"
                                    >No Tickets Found</span
                                >
                                <span
                                    class="max-w-xs text-xs text-on-surface-variant/75"
                                    >There are no tickets in this queue
                                    category.</span
                                >
                            </div>
                        </div>

                        <!-- Pagination Footer -->
                        <div
                            v-if="tickets.last_page > 1"
                            class="flex items-center justify-between border-t border-outline-variant/40 bg-surface-container-low px-6 py-4"
                        >
                            <span
                                class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                            >
                                Page {{ tickets.current_page }} of
                                {{ tickets.last_page }}
                            </span>
                            <div class="flex gap-1">
                                <Link
                                    v-for="link in tickets.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    :class="[
                                        'rounded-lg border px-3 py-1.5 text-[10px] font-bold transition-all',
                                        link.active
                                            ? 'border-primary bg-primary text-on-primary'
                                            : 'border-outline-variant bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-low',
                                        !link.url &&
                                            'cursor-not-allowed opacity-40',
                                    ]"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Guide or Sidebar Info -->
                <div
                    class="h-fit space-y-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm"
                >
                    <h2
                        class="border-b border-outline-variant/30 pb-3 text-sm font-bold tracking-wider text-primary uppercase"
                    >
                        Workstation Overview
                    </h2>
                    <div
                        class="space-y-3.5 text-xs leading-relaxed text-on-surface-variant"
                    >
                        <p>
                            Welcome to the bidora helpdesk. To claim a ticket or
                            reply to a customer request, open any ticket card in
                            the queue list.
                        </p>
                        <div
                            class="space-y-2 rounded-lg border border-outline-variant/50 bg-surface-container-low p-4"
                        >
                            <p
                                class="flex items-center gap-1.5 text-[9px] font-bold tracking-wider text-primary uppercase"
                            >
                                <Clock class="h-3.5 w-3.5 text-primary" />
                                <span>SLA Priorities</span>
                            </p>
                            <ul class="list-disc space-y-1 pl-4 text-[11px]">
                                <li>
                                    <strong class="text-error">Urgent</strong>:
                                    Resolve within 2 hours
                                </li>
                                <li>
                                    <strong>High</strong>: Resolve within 8
                                    hours
                                </li>
                                <li>
                                    <strong>Normal/Low</strong>: Resolve within
                                    24 hours
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Ticket Modal -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-on-background/40 p-4 backdrop-blur-sm"
        >
            <div
                class="flex w-full max-w-lg flex-col gap-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-xl"
            >
                <div
                    class="flex items-center justify-between border-b border-outline-variant/30 pb-4"
                >
                    <h2
                        class="flex items-center gap-1.5 text-sm font-black tracking-widest text-primary uppercase"
                    >
                        <Plus class="h-4 w-4 text-primary" />
                        <span>Create Manual Ticket</span>
                    </h2>
                    <button
                        @click="showCreateModal = false"
                        class="cursor-pointer font-bold text-on-surface-variant hover:text-primary"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4 text-xs">
                    <!-- Customer Select -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            class="text-[10px] font-bold tracking-wider text-on-surface-variant uppercase"
                            >Select Customer</label
                        >
                        <select
                            v-model="createForm.customer_id"
                            required
                            class="rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 font-bold text-on-surface uppercase focus:ring-1 focus:ring-secondary focus:outline-none"
                        >
                            <option value="">-- SELECT CUSTOMER --</option>
                            <option value="" disabled v-if="!customers">
                                LOADING CUSTOMERS...
                            </option>
                            <option
                                v-for="c in customers || []"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }} ({{ c.email }})
                            </option>
                        </select>
                        <p
                            v-if="createForm.errors.customer_id"
                            class="mt-1 text-error"
                        >
                            {{ createForm.errors.customer_id }}
                        </p>
                    </div>

                    <!-- Subject -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="subject"
                            class="text-[10px] font-bold tracking-wider text-on-surface-variant uppercase"
                            >Subject</label
                        >
                        <input
                            id="subject"
                            v-model="createForm.subject"
                            type="text"
                            required
                            placeholder="E.G. BILLING DISPUTE ON INVOICE #102"
                            class="rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 font-bold text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                        />
                        <p
                            v-if="createForm.errors.subject"
                            class="mt-1 text-error"
                        >
                            {{ createForm.errors.subject }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Category -->
                        <div class="flex flex-col gap-1.5">
                            <label
                                class="text-[10px] font-bold tracking-wider text-on-surface-variant uppercase"
                                >Category</label
                            >
                            <select
                                v-model="createForm.category"
                                required
                                class="rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 font-bold text-on-surface uppercase focus:ring-1 focus:ring-secondary focus:outline-none"
                            >
                                <option value="">-- SELECT CATEGORY --</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat"
                                    :value="cat"
                                >
                                    {{ cat }}
                                </option>
                            </select>
                            <p
                                v-if="createForm.errors.category"
                                class="mt-1 text-error"
                            >
                                {{ createForm.errors.category }}
                            </p>
                        </div>

                        <!-- Priority -->
                        <div class="flex flex-col gap-1.5">
                            <label
                                class="text-[10px] font-bold tracking-wider text-on-surface-variant uppercase"
                                >Priority</label
                            >
                            <select
                                v-model="createForm.priority"
                                required
                                class="rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 font-bold text-on-surface uppercase focus:ring-1 focus:ring-secondary focus:outline-none"
                            >
                                <option
                                    v-for="prio in priorities"
                                    :key="prio"
                                    :value="prio"
                                >
                                    {{ prio }}
                                </option>
                            </select>
                            <p
                                v-if="createForm.errors.priority"
                                class="mt-1 text-error"
                            >
                                {{ createForm.errors.priority }}
                            </p>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="body"
                            class="text-[10px] font-bold tracking-wider text-on-surface-variant uppercase"
                            >Message Details</label
                        >
                        <textarea
                            id="body"
                            v-model="createForm.body"
                            rows="5"
                            required
                            placeholder="PROVIDE DETAILS ON THE INQUIRY..."
                            class="rounded-lg border border-outline-variant bg-surface-container-low p-4 font-medium text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                        ></textarea>
                        <p
                            v-if="createForm.errors.body"
                            class="mt-1 text-error"
                        >
                            {{ createForm.errors.body }}
                        </p>
                    </div>

                    <!-- Modal Actions -->
                    <div
                        class="flex justify-end gap-3 border-t border-outline-variant/30 pt-4"
                    >
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="cursor-pointer rounded-lg border border-outline-variant px-4 py-2.5 font-bold text-on-surface-variant uppercase transition-colors hover:text-primary"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="cursor-pointer rounded-lg bg-navy px-5 py-2.5 font-black text-lemon uppercase transition-all disabled:opacity-50"
                        >
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SupportLayout>
</template>
