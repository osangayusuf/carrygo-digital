<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import SupportLayout from '@/layouts/SupportLayout.vue';
import { ref } from 'vue';
import { index as ticketsIndex, store as ticketsStore, show as ticketsShow } from '@/routes/support/tickets';
import { 
    Ticket as TicketIcon, 
    Plus, 
    Clock, 
    AlertCircle, 
    CheckCircle, 
    ArrowRight,
    Inbox,
    User as UserIcon,
    Search
} from 'lucide-vue-next';

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
        }
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
    <Head title="Tickets Queue - CarryGo Support" />

    <SupportLayout :breadcrumbs="[{ title: 'Tickets' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <TicketIcon class="w-6 h-6 text-primary" />
                        <span>Support Tickets</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Manage and resolve customer support inquiries.</p>
                </div>
                <button 
                    @click="showCreateModal = true"
                    class="flex items-center justify-center gap-2 px-4 py-2.5 bg-navy text-lemon hover:bg-forest font-extrabold uppercase rounded-lg transition-all cursor-pointer self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    <span>Create Ticket</span>
                </button>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Panel: Ticket list -->
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <!-- Tab filters -->
                    <div class="flex border-b border-outline-variant/50">
                        <button 
                            @click="switchTab('unassigned')"
                            :class="[
                                'px-5 py-3 font-bold uppercase tracking-wider transition-all border-b-2 text-[10px]',
                                activeTab === 'unassigned' 
                                    ? 'border-primary text-primary' 
                                    : 'border-transparent text-on-surface-variant hover:text-on-surface'
                            ]"
                        >
                            Unassigned ({{ counts?.unassigned ?? '...' }})
                        </button>
                        <button 
                            @click="switchTab('mine')"
                            :class="[
                                'px-5 py-3 font-bold uppercase tracking-wider transition-all border-b-2 text-[10px]',
                                activeTab === 'mine' 
                                    ? 'border-primary text-primary' 
                                    : 'border-transparent text-on-surface-variant hover:text-on-surface'
                            ]"
                        >
                            Assigned to Me ({{ counts?.mine ?? '...' }})
                        </button>
                        <button 
                            @click="switchTab('closed')"
                            :class="[
                                'px-5 py-3 font-bold uppercase tracking-wider transition-all border-b-2 text-[10px]',
                                activeTab === 'closed' 
                                    ? 'border-primary text-primary' 
                                    : 'border-transparent text-on-surface-variant hover:text-on-surface'
                            ]"
                        >
                            Closed ({{ counts?.closed ?? '...' }})
                        </button>
                    </div>

                    <!-- Tickets List -->
                    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm flex flex-col overflow-hidden">
                        <div class="divide-y divide-outline-variant/40">
                            <div 
                                v-for="ticket in tickets.data" 
                                :key="ticket.id" 
                                class="p-5 hover:bg-surface-container-low/20 transition-all flex flex-col md:flex-row justify-between md:items-center gap-4 group"
                            >
                                <div class="space-y-1.5 flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
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
                                    <h2 class="text-sm font-bold text-on-surface truncate group-hover:text-primary transition-colors">
                                        {{ ticket.subject }}
                                    </h2>
                                    <div class="flex items-center gap-3 text-[10px] text-on-surface-variant flex-wrap font-medium">
                                        <span class="flex items-center gap-1">
                                            <UserIcon class="w-3.5 h-3.5" />
                                            <span>{{ ticket.customer.name }}</span>
                                        </span>
                                        <span>•</span>
                                        <span>Opened {{ new Date(ticket.created_at).toLocaleString() }}</span>
                                        <template v-if="ticket.agent">
                                            <span>•</span>
                                            <span class="text-secondary font-bold bg-secondary/10 px-2 py-0.5 rounded uppercase tracking-wider text-[9px]">
                                                Agent: {{ ticket.agent.name }}
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <div class="flex items-center self-end md:self-auto">
                                    <Link 
                                        :href="ticketsShow.url(ticket.id)"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-low hover:bg-secondary-container text-on-surface hover:text-primary border border-outline-variant rounded-lg font-bold transition-all"
                                    >
                                        <span>Open</span>
                                        <ArrowRight class="w-3.5 h-3.5" />
                                    </Link>
                                </div>
                            </div>

                            <div v-if="tickets.data.length === 0" class="py-16 text-center flex flex-col items-center justify-center gap-3 text-on-surface-variant">
                                <Inbox class="w-12 h-12 text-outline-variant/60" />
                                <span class="uppercase tracking-widest font-black text-[10px]">No Tickets Found</span>
                                <span class="text-xs max-w-xs text-on-surface-variant/75">There are no tickets in this queue category.</span>
                            </div>
                        </div>

                        <!-- Pagination Footer -->
                        <div v-if="tickets.last_page > 1" class="px-6 py-4 bg-surface-container-low border-t border-outline-variant/40 flex justify-between items-center">
                            <span class="font-bold text-[10px] text-on-surface-variant uppercase tracking-widest">
                                Page {{ tickets.current_page }} of {{ tickets.last_page }}
                            </span>
                            <div class="flex gap-1">
                                <Link 
                                    v-for="link in tickets.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-1.5 border text-[10px] font-bold rounded-lg transition-all',
                                        link.active 
                                            ? 'bg-primary text-on-primary border-primary' 
                                            : 'text-on-surface-variant border-outline-variant bg-surface-container-lowest hover:bg-surface-container-low',
                                        !link.url && 'opacity-40 cursor-not-allowed'
                                    ]"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Guide or Sidebar Info -->
                <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm space-y-4 h-fit">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-outline-variant/30 pb-3">Workstation Overview</h2>
                    <div class="space-y-3.5 text-xs text-on-surface-variant leading-relaxed">
                        <p>Welcome to the carrygo helpdesk. To claim a ticket or reply to a customer request, open any ticket card in the queue list.</p>
                        <div class="border border-outline-variant/50 p-4 rounded-lg bg-surface-container-low space-y-2">
                            <p class="font-bold text-primary uppercase text-[9px] tracking-wider flex items-center gap-1.5">
                                <Clock class="w-3.5 h-3.5 text-primary" />
                                <span>SLA Priorities</span>
                            </p>
                            <ul class="list-disc pl-4 space-y-1 text-[11px]">
                                <li><strong class="text-error">Urgent</strong>: Resolve within 2 hours</li>
                                <li><strong>High</strong>: Resolve within 8 hours</li>
                                <li><strong>Normal/Low</strong>: Resolve within 24 hours</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Ticket Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-on-background/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-lg p-6 flex flex-col gap-4 rounded-xl shadow-xl">
                <div class="flex items-center justify-between border-b border-outline-variant/30 pb-4">
                    <h2 class="text-sm font-black uppercase tracking-widest text-primary flex items-center gap-1.5">
                        <Plus class="w-4 h-4 text-primary" />
                        <span>Create Manual Ticket</span>
                    </h2>
                    <button @click="showCreateModal = false" class="text-on-surface-variant hover:text-primary cursor-pointer font-bold">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4 text-xs">
                    <!-- Customer Select -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px] font-bold">Select Customer</label>
                        <select 
                            v-model="createForm.customer_id"
                            required
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary uppercase font-bold"
                        >
                            <option value="">-- SELECT CUSTOMER --</option>
                            <option value="" disabled v-if="!customers">LOADING CUSTOMERS...</option>
                            <option v-for="c in customers || []" :key="c.id" :value="c.id">{{ c.name }} ({{ c.email }})</option>
                        </select>
                        <p v-if="createForm.errors.customer_id" class="text-error mt-1">{{ createForm.errors.customer_id }}</p>
                    </div>

                    <!-- Subject -->
                    <div class="flex flex-col gap-1.5">
                        <label for="subject" class="text-on-surface-variant uppercase tracking-wider text-[10px] font-bold">Subject</label>
                        <input 
                            id="subject"
                            v-model="createForm.subject"
                            type="text"
                            required
                            placeholder="E.G. BILLING DISPUTE ON INVOICE #102"
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold"
                        />
                        <p v-if="createForm.errors.subject" class="text-error mt-1">{{ createForm.errors.subject }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Category -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px] font-bold">Category</label>
                            <select 
                                v-model="createForm.category"
                                required
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold uppercase"
                            >
                                <option value="">-- SELECT CATEGORY --</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                            <p v-if="createForm.errors.category" class="text-error mt-1">{{ createForm.errors.category }}</p>
                        </div>

                        <!-- Priority -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px] font-bold">Priority</label>
                            <select 
                                v-model="createForm.priority"
                                required
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold uppercase"
                            >
                                <option v-for="prio in priorities" :key="prio" :value="prio">{{ prio }}</option>
                            </select>
                            <p v-if="createForm.errors.priority" class="text-error mt-1">{{ createForm.errors.priority }}</p>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="flex flex-col gap-1.5">
                        <label for="body" class="text-on-surface-variant uppercase tracking-wider text-[10px] font-bold">Message Details</label>
                        <textarea 
                            id="body"
                            v-model="createForm.body"
                            rows="5"
                            required
                            placeholder="PROVIDE DETAILS ON THE INQUIRY..."
                            class="bg-surface-container-low border border-outline-variant text-on-surface p-4 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-medium"
                        ></textarea>
                        <p v-if="createForm.errors.body" class="text-error mt-1">{{ createForm.errors.body }}</p>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex gap-3 justify-end pt-4 border-t border-outline-variant/30">
                        <button 
                            type="button" 
                            @click="showCreateModal = false"
                            class="px-4 py-2.5 border border-outline-variant rounded-lg text-on-surface-variant hover:text-primary font-bold uppercase transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="createForm.processing"
                            class="px-5 py-2.5 bg-navy text-lemon font-black uppercase rounded-lg disabled:opacity-50 transition-all cursor-pointer"
                        >
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SupportLayout>
</template>
