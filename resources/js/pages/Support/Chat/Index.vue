<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import SupportLayout from '@/layouts/SupportLayout.vue';
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue';
import { status as chatStatus, claim as chatClaim, close as chatClose, message as chatMessage, convert as chatConvert, show as chatShow } from '@/routes/support/chat';
import { show as ticketsShow } from '@/routes/support/tickets';
import {
    MessageSquare,
    User as UserIcon,
    Clock,
    Inbox,
    Send,
    CheckCircle,
    UserCheck,
    FileText,
    AlertCircle,
    Lock
} from 'lucide-vue-next';

type User = {
    id: number;
    name: string;
    email: string;
};

type ChatMessage = {
    id: number;
    chat_session_id: number;
    sender_id: number | null;
    sender_type: string;
    body: string;
    created_at: string;
    sender: User | null;
};

type ChatSession = {
    id: number;
    uuid: string;
    customer_id: number | null;
    customer_name: string;
    customer_email: string | null;
    agent_id: number | null;
    ticket_id: number | null;
    status: string;
    started_at: string | null;
    closed_at: string | null;
    messages: ChatMessage[];
    agent: User | null;
};

type AgentStatus = {
    status: string;
};

const props = defineProps<{
    waitingSessions: ChatSession[];
    activeSessions: ChatSession[];
    closedSessions: ChatSession[];
    agentStatus: AgentStatus;
    selectedSession: ChatSession | null;
    categories: string[];
    priorities: string[];
}>();

const currentTab = ref('active'); // 'waiting' | 'active' | 'closed'
const messageForm = useForm({
    body: '',
});

const convertForm = useForm({
    subject: '',
    category: '',
    priority: 'normal',
});

const showConvertModal = ref(false);
const messageContainer = ref<HTMLDivElement | null>(null);

const waitingList = ref<ChatSession[]>([...props.waitingSessions]);
const activeList = ref<ChatSession[]>([...props.activeSessions]);
const closedList = ref<ChatSession[]>([...props.closedSessions]);

const messages = ref<ChatMessage[]>(props.selectedSession ? [...props.selectedSession.messages] : []);
const sessionStatus = ref<string>(props.selectedSession ? props.selectedSession.status : '');
const assignedAgent = ref<User | null>(props.selectedSession ? props.selectedSession.agent : null);

const currentStatus = ref(props.agentStatus?.status || 'offline');

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

// Handle Echo events
onMounted(() => {
    scrollToBottom();

    // Register presence channel to get new incoming chats in real-time
    window.Echo?.join('support.agents')
        .listen('.NewChatSessionCreated', (event: any) => {
            const exists = waitingList.value.some(s => s.uuid === event.uuid);
            
            if (!exists) {
                waitingList.value.unshift({
                    id: event.id,
                    uuid: event.uuid,
                    customer_id: event.customer_id,
                    customer_name: event.customer_name,
                    customer_email: event.customer_email,
                    status: event.status,
                    started_at: null,
                    closed_at: null,
                    messages: [],
                    agent: null,
                    ticket_id: null
                });
            }
        });

    // Register active session channel if selected
    if (props.selectedSession) {
        joinSessionChannel(props.selectedSession.uuid);
    }
});

const joinSessionChannel = (uuid: string) => {
    window.Echo?.channel(`chat.${uuid}`)
        .listen('.ChatMessageSent', (event: any) => {
            const exists = messages.value.some(m => m.id === event.id);
            if (!exists) {
                messages.value.push({
                    id: event.id,
                    chat_session_id: event.chat_session_id,
                    sender_id: event.sender_id,
                    sender_type: event.sender_type,
                    body: event.body,
                    created_at: event.created_at,
                    sender: event.sender
                });
                scrollToBottom();
            }
        })
        .listen('.AgentClaimedSession', (event: any) => {
            sessionStatus.value = event.status;
            assignedAgent.value = event.agent_name ? { id: event.agent_id, name: event.agent_name, email: '' } : null;
        })
        .listen('.ChatSessionClosed', (event: any) => {
            sessionStatus.value = event.status;
        });
};

onUnmounted(() => {
    window.Echo?.leave('support.agents');
    if (props.selectedSession) {
        window.Echo?.leave(`chat.${props.selectedSession.uuid}`);
    }
});

watch(() => props.selectedSession, (newSession, oldSession) => {
    // Only leave and rejoin the Echo channel if the session has actually changed
    if (oldSession && oldSession.uuid !== newSession?.uuid) {
        window.Echo?.leave(`chat.${oldSession.uuid}`);
    }

    messages.value = newSession ? [...newSession.messages] : [];
    sessionStatus.value = newSession ? newSession.status : '';
    assignedAgent.value = newSession ? newSession.agent : null;

    if (newSession && (!oldSession || oldSession.uuid !== newSession.uuid)) {
        joinSessionChannel(newSession.uuid);
        scrollToBottom();
    }
}, { deep: true });

watch(() => props.waitingSessions, (newVal) => { waitingList.value = [...newVal]; });
watch(() => props.activeSessions, (newVal) => { activeList.value = [...newVal]; });
watch(() => props.closedSessions, (newVal) => { closedList.value = [...newVal]; });
watch(() => props.agentStatus, (newVal) => { if (newVal) currentStatus.value = newVal.status; });

const changePresenceStatus = () => {
    router.post(chatStatus.url(), {
        status: currentStatus.value
    });
};

const claimChat = () => {
    if (!props.selectedSession) return;

    router.post(chatClaim.url(props.selectedSession.uuid), {}, {
        onSuccess: () => {
            currentTab.value = 'active';
        }
    });
};

const closeChat = () => {
    if (!props.selectedSession) return;

    if (confirm('Are you sure you want to close this chat session?')) {
        router.post(chatClose.url(props.selectedSession.uuid));
    }
};

const submitMessage = () => {
    if (!props.selectedSession || !messageForm.body.trim()) return;

    const bodyText = messageForm.body;
    messageForm.body = ''; // Clear locally for instant response feel

    const socketId = window.Echo?.socketId();
    const headers: Record<string, string> = {};
    if (socketId) {
        headers['X-Socket-ID'] = socketId;
    }

    router.post(chatMessage.url(props.selectedSession.uuid), {
        body: bodyText,
    }, {
        preserveScroll: true,
        preserveState: true,
        headers,
    });
};

const submitConvert = () => {
    if (!props.selectedSession) return;

    convertForm.post(chatConvert.url(props.selectedSession.uuid), {
        onSuccess: () => {
            showConvertModal.value = false;
            convertForm.reset();
        }
    });
};

const getStatusDotClass = (status: string) => {
    switch (status) {
        case 'online':
            return 'bg-surface-tint';
        case 'away':
            return 'bg-amber';
        default:
            return 'bg-error';
    }
};
</script>

<template>
    <Head title="Live Chat Support - CarryGo Support" />

    <SupportLayout :breadcrumbs="[{ title: 'Live Chat' }]">
        <div class="h-[calc(100vh-12rem)] flex border border-outline-variant/60 rounded-xl overflow-hidden shadow-sm bg-surface-container-lowest font-sans text-xs">

            <!-- Middle Panel: Chat Sessions List (300px wide) -->
            <div class="w-[320px] border-r border-outline-variant flex flex-col bg-surface">
                <!-- Status Toggle -->
                <div class="flex items-center justify-between p-4 bg-surface border-b border-outline-variant">
                    <div class="flex items-center gap-2">
                        <span :class="['w-2.5 h-2.5 rounded-full', getStatusDotClass(currentStatus)]"></span>
                        <span class="font-bold text-[10px] uppercase tracking-wider text-on-surface-variant">Agent: {{ currentStatus }}</span>
                    </div>
                    <select
                        v-model="currentStatus"
                        @change="changePresenceStatus"
                        class="bg-surface-container-lowest border border-outline-variant text-[10px] font-bold uppercase rounded px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-secondary cursor-pointer"
                    >
                        <option value="online">Online</option>
                        <option value="away">Away</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>

                <!-- Tabs header -->
                <div class="flex border-b border-outline-variant/40">
                    <button
                        @click="currentTab = 'active'"
                        :class="[
                            'flex-1 py-3 text-[10px] font-bold uppercase tracking-wider transition-all border-b-2 text-center',
                            currentTab === 'active'
                                ? 'border-primary text-primary bg-surface-container-lowest'
                                : 'border-transparent text-on-surface-variant hover:text-on-surface'
                        ]"
                    >
                        Active ({{ activeList.length }})
                    </button>
                    <button
                        @click="currentTab = 'waiting'"
                        :class="[
                            'flex-1 py-3 text-[10px] font-bold uppercase tracking-wider transition-all border-b-2 text-center',
                            currentTab === 'waiting'
                                ? 'border-primary text-primary bg-surface-container-lowest'
                                : 'border-transparent text-on-surface-variant hover:text-on-surface'
                        ]"
                    >
                        Waiting ({{ waitingList.length }})
                    </button>
                    <button
                        @click="currentTab = 'closed'"
                        :class="[
                            'flex-1 py-3 text-[10px] font-bold uppercase tracking-wider transition-all border-b-2 text-center',
                            currentTab === 'closed'
                                ? 'border-primary text-primary bg-surface-container-lowest'
                                : 'border-transparent text-on-surface-variant hover:text-on-surface'
                        ]"
                    >
                        Closed
                    </button>
                </div>

                <!-- Sessions List -->
                <div class="flex-1 overflow-y-auto divide-y divide-outline-variant/30">
                    <!-- Active List -->
                    <template v-if="currentTab === 'active'">
                        <Link
                            v-for="session in activeList"
                            :key="session.uuid"
                            :href="chatShow.url(session.uuid)"
                            :class="[
                                'block p-4 transition-all hover:bg-surface-container-low/30',
                                selectedSession?.uuid === session.uuid ? 'bg-surface-container border-l-4 border-primary' : ''
                            ]"
                        >
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-bold text-on-surface truncate pr-2">{{ session.customer_name }}</span>
                                <span class="text-[9px] text-on-surface-variant/80 font-medium">
                                    {{ session.started_at ? new Date(session.started_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Active' }}
                                </span>
                            </div>
                            <p class="text-[10px] text-on-surface-variant truncate font-medium">
                                {{ session.customer_email || 'No email provided' }}
                            </p>
                            <div class="flex items-center justify-between mt-2 text-[9px] text-on-surface-variant/75 font-semibold">
                                <span v-if="session.agent" class="bg-secondary/15 text-primary border border-secondary/20 px-2 py-0.5 rounded-sm uppercase tracking-wide">
                                    Agent: {{ session.agent.name }}
                                </span>
                                <span v-if="session.ticket_id" class="text-primary font-bold">✓ Converted</span>
                            </div>
                        </Link>
                        <div v-if="activeList.length === 0" class="py-12 text-center text-on-surface-variant/60">
                            <Inbox class="w-8 h-8 mx-auto mb-2 text-outline-variant/60" />
                            <p class="uppercase font-bold tracking-widest text-[9px]">No Active Chats</p>
                        </div>
                    </template>

                    <!-- Waiting List -->
                    <template v-if="currentTab === 'waiting'">
                        <Link
                            v-for="session in waitingList"
                            :key="session.uuid"
                            :href="chatShow.url(session.uuid)"
                            :class="[
                                'block p-4 transition-all hover:bg-surface-container-low/30',
                                selectedSession?.uuid === session.uuid ? 'bg-surface-container border-l-4 border-primary' : ''
                            ]"
                        >
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-bold text-on-surface truncate pr-2">{{ session.customer_name }}</span>
                                <span class="text-[9px] text-error font-bold flex items-center gap-1">
                                    <Clock class="w-3 h-3 text-error" />
                                    <span>Waiting</span>
                                </span>
                            </div>
                            <p class="text-[10px] text-on-surface-variant truncate font-medium">
                                {{ session.customer_email || 'No email provided' }}
                            </p>
                            <span class="inline-block mt-2 text-[9px] font-bold text-on-surface-variant/80">
                                Requested: {{ new Date(session.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                            </span>
                        </Link>
                        <div v-if="waitingList.length === 0" class="py-12 text-center text-on-surface-variant/60">
                            <Inbox class="w-8 h-8 mx-auto mb-2 text-outline-variant/60" />
                            <p class="uppercase font-bold tracking-widest text-[9px]">Queue is Empty</p>
                        </div>
                    </template>

                    <!-- Closed List -->
                    <template v-if="currentTab === 'closed'">
                        <Link
                            v-for="session in closedList"
                            :key="session.uuid"
                            :href="chatShow.url(session.uuid)"
                            :class="[
                                'block p-4 transition-all hover:bg-surface-container-low/30',
                                selectedSession?.uuid === session.uuid ? 'bg-surface-container border-l-4 border-primary' : ''
                            ]"
                        >
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-bold text-on-surface truncate pr-2">{{ session.customer_name }}</span>
                                <span class="text-[9px] text-on-surface-variant/80 font-medium">
                                    {{ session.closed_at ? new Date(session.closed_at).toLocaleDateString() : 'Closed' }}
                                </span>
                            </div>
                            <p class="text-[10px] text-on-surface-variant truncate font-medium">
                                {{ session.customer_email || 'No email provided' }}
                            </p>
                            <div class="flex items-center justify-between mt-2 text-[9px] text-on-surface-variant/70 font-semibold">
                                <span>Closed support</span>
                                <span v-if="session.ticket_id" class="text-primary font-bold">✓ Converted</span>
                            </div>
                        </Link>
                        <div v-if="closedList.length === 0" class="py-12 text-center text-on-surface-variant/60">
                            <Inbox class="w-8 h-8 mx-auto mb-2 text-outline-variant/60" />
                            <p class="uppercase font-bold tracking-widest text-[9px]">No Closed Chats</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right Panel: Conversation Thread -->
            <div class="flex-1 flex flex-col bg-surface-container-lowest">
                <!-- If a chat is selected -->
                <template v-if="selectedSession">
                    <!-- Session Header Bar -->
                    <div class="px-6 py-4 bg-surface border-b border-outline-variant/60 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-primary">{{ selectedSession.customer_name }}</h2>
                            <p class="text-[10px] text-on-surface-variant mt-0.5">
                                {{ selectedSession.customer_email || 'Guest customer' }} • Session #{{ selectedSession.id }}
                            </p>
                        </div>

                        <!-- Header Action buttons -->
                        <div class="flex items-center gap-2">
                            <!-- If status is Waiting -->
                            <button
                                v-if="sessionStatus === 'waiting'"
                                @click="claimChat"
                                class="flex items-center gap-1.5 px-4 py-2 border border-secondary/25 bg-secondary-container text-on-secondary-container hover:bg-secondary hover:text-primary rounded-lg font-bold uppercase transition-all cursor-pointer"
                            >
                                <UserCheck class="w-3.5 h-3.5" />
                                <span>Claim Chat Session</span>
                            </button>

                            <!-- If status is Active -->
                            <template v-else-if="sessionStatus === 'active'">
                                <button
                                    v-if="!selectedSession.ticket_id"
                                    @click="showConvertModal = true"
                                    class="flex items-center gap-1.5 px-3 py-2 border border-outline-variant bg-surface-container-low hover:bg-surface-container text-on-surface rounded-lg font-bold uppercase transition-all cursor-pointer"
                                >
                                    <FileText class="w-3.5 h-3.5" />
                                    <span>Convert to Ticket</span>
                                </button>
                                <button
                                    @click="closeChat"
                                    class="flex items-center gap-1.5 px-3 py-2 border border-error/20 bg-error-container/10 text-error hover:bg-error hover:text-white rounded-lg font-bold uppercase transition-all cursor-pointer"
                                >
                                    <CheckCircle class="w-3.5 h-3.5" />
                                    <span>Close Session</span>
                                </button>
                            </template>

                            <!-- If already converted to a ticket -->
                            <Link
                                v-if="selectedSession.ticket_id"
                                :href="ticketsShow.url(selectedSession.ticket_id)"
                                class="flex items-center gap-1.5 px-3 py-2 border border-primary/20 bg-primary/5 text-primary hover:bg-primary hover:text-white rounded-lg font-bold uppercase transition-all"
                            >
                                <FileText class="w-3.5 h-3.5" />
                                <span>Open Ticket #{{ selectedSession.ticket_id }}</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Conversation Message Stream -->
                    <div
                        ref="messageContainer"
                        class="flex-1 p-6 overflow-y-auto flex flex-col gap-4 bg-[#fcfdfd]"
                    >
                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            :class="[
                                'flex flex-col max-w-[70%]',
                                msg.sender_type === 'system'
                                    ? 'mx-auto w-full max-w-none text-center my-2'
                                    : msg.sender_type === 'agent'
                                        ? 'self-end items-end'
                                        : 'self-start items-start'
                            ]"
                        >
                            <!-- System messages style -->
                            <template v-if="msg.sender_type === 'system'">
                                <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-outline-variant bg-surface-container-low text-[9px] uppercase tracking-wider font-extrabold text-on-surface-variant mx-auto">
                                    <AlertCircle class="w-3 h-3 text-primary" />
                                    <span>{{ msg.body }}</span>
                                </div>
                            </template>

                            <!-- Chat bubbles style -->
                            <template v-else>
                                <div class="flex items-center gap-1 mb-1 text-[9px] text-on-surface-variant font-bold uppercase">
                                    <span>{{ msg.sender_type === 'agent' ? (msg.sender?.name || 'Agent') : (selectedSession.customer_name) }}</span>
                                </div>
                                <div
                                    :class="[
                                        'p-3.5 rounded-2xl shadow-sm text-[11px] leading-relaxed whitespace-pre-wrap',
                                        msg.sender_type === 'agent'
                                            ? 'bg-navy text-lemon rounded-tr-none border border-navy/10'
                                            : 'bg-surface-container-low text-on-surface rounded-tl-none border border-outline-variant/35'
                                    ]"
                                >
                                    {{ msg.body }}
                                </div>
                                <span class="text-[8px] text-on-surface-variant/70 font-semibold mt-1">
                                    {{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Input Reply Box -->
                    <div
                        v-if="sessionStatus === 'active'"
                        class="p-4 bg-surface border-t border-outline-variant/60"
                    >
                        <form @submit.prevent="submitMessage" class="flex gap-3">
                            <textarea
                                v-model="messageForm.body"
                                rows="1"
                                placeholder="TYPE A MESSAGE DIRECTLY TO THE CUSTOMER (PRESS ENTER TO SEND)..."
                                @keydown.enter.exact.prevent="submitMessage"
                                class="flex-1 bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-medium resize-none text-[11px]"
                            ></textarea>
                            <button
                                type="submit"
                                :disabled="messageForm.processing || !messageForm.body.trim()"
                                class="px-5 bg-navy text-lemon hover:bg-forest rounded-lg flex items-center justify-center transition-all cursor-pointer disabled:opacity-50"
                            >
                                <Send class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                </template>

                <!-- If no chat is selected -->
                <div v-else class="flex-1 flex flex-col items-center justify-center text-on-surface-variant/60 gap-3">
                    <MessageSquare class="w-16 h-16 text-outline-variant/60 animate-bounce-slow" />
                    <p class="uppercase font-black tracking-widest text-[10px]">No Session Selected</p>
                    <p class="text-xs max-w-xs text-center text-on-surface-variant/75">
                        Select an active or waiting chat session from the queue left sidebar to start communicating with customers in real-time.
                    </p>
                </div>
            </div>
        </div>

        <!-- Convert to Ticket Modal -->
        <div v-if="showConvertModal" class="fixed inset-0 z-50 bg-on-background/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-md p-6 flex flex-col gap-4 rounded-xl shadow-xl">
                <div class="flex items-center justify-between border-b border-outline-variant/30 pb-4">
                    <h2 class="text-sm font-black uppercase tracking-widest text-primary flex items-center gap-1.5">
                        <FileText class="w-4 h-4 text-primary" />
                        <span>Convert Chat to Ticket</span>
                    </h2>
                    <button @click="showConvertModal = false" class="text-on-surface-variant hover:text-primary cursor-pointer font-bold">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitConvert" class="space-y-4 text-xs">
                    <!-- Subject -->
                    <div class="flex flex-col gap-1.5">
                        <label for="subject" class="text-on-surface-variant uppercase tracking-wider text-[10px] font-bold">Ticket Subject</label>
                        <input
                            id="subject"
                            v-model="convertForm.subject"
                            type="text"
                            required
                            placeholder="E.G. BILLING DISPUTE DISCOVERED DURING CHAT"
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold uppercase"
                        />
                        <p v-if="convertForm.errors.subject" class="text-error mt-1">{{ convertForm.errors.subject }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Category -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px] font-bold">Category</label>
                            <select
                                v-model="convertForm.category"
                                required
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold uppercase"
                            >
                                <option value="">-- CHOOSE --</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                            <p v-if="convertForm.errors.category" class="text-error mt-1">{{ convertForm.errors.category }}</p>
                        </div>

                        <!-- Priority -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px] font-bold">Priority</label>
                            <select
                                v-model="convertForm.priority"
                                required
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold uppercase"
                            >
                                <option v-for="prio in priorities" :key="prio" :value="prio">{{ prio }}</option>
                            </select>
                            <p v-if="convertForm.errors.priority" class="text-error mt-1">{{ convertForm.errors.priority }}</p>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex gap-3 justify-end pt-4 border-t border-outline-variant/30">
                        <button
                            type="button"
                            @click="showConvertModal = false"
                            class="px-4 py-2.5 border border-outline-variant rounded-lg text-on-surface-variant hover:text-primary font-bold uppercase transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="convertForm.processing"
                            class="px-5 py-2.5 bg-navy text-lemon font-black uppercase rounded-lg disabled:opacity-50 transition-all cursor-pointer"
                        >
                            Convert & Open
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SupportLayout>
</template>
