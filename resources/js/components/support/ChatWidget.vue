<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    MessageSquare,
    X,
    Send,
    ChevronDown,
    Clock,
    User,
    AlertCircle,
    CheckCircle2,
    Loader2
} from 'lucide-vue-next';

type ChatMessage = {
    id: number;
    sender_type: string;
    body: string;
    created_at: string;
};

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const isOpen = ref(false);
const isOnline = ref(false);
const isLoading = ref(true);
const isSubmitting = ref(false);

const isChatActive = ref(false);
const sessionUuid = ref<string | null>(null);
const sessionStatus = ref('waiting');
const agentName = ref<string | null>(null);
const messages = ref<ChatMessage[]>([]);

const prechatForm = ref({
    name: '',
    email: '',
});

const offlineForm = ref({
    name: '',
    email: '',
    body: '',
});

const messageText = ref('');
const messageContainer = ref<HTMLDivElement | null>(null);

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

const checkStatus = async () => {
    try {
        const res = await fetch('/support/chat-api/status');
        const data = await res.json();
        isOnline.value = data.online;
    } catch (e) {
        console.error('Failed to check support status', e);
        isOnline.value = false;
    } finally {
        isLoading.value = false;
    }
};

const connectEcho = (uuid: string) => {
    window.Echo?.channel(`chat.${uuid}`)
        .listen('.ChatMessageSent', (event: any) => {
            const exists = messages.value.some(m => m.id === event.id);
            if (!exists) {
                messages.value.push({
                    id: event.id,
                    sender_type: event.sender_type,
                    body: event.body,
                    created_at: event.created_at,
                });
                scrollToBottom();
            }
        })
        .listen('.AgentClaimedSession', (event: any) => {
            sessionStatus.value = event.status;
            agentName.value = event.agent_name;
        })
        .listen('.ChatSessionClosed', (event: any) => {
            sessionStatus.value = event.status;
        });
};

const loadHistory = async (uuid: string) => {
    try {
        const res = await fetch(`/support/chat-api/${uuid}/messages`);
        if (res.ok) {
            const data = await res.json();
            messages.value = data.messages || [];
            sessionStatus.value = data.status || 'waiting';
            agentName.value = data.agent ? data.agent.name : null;
            sessionUuid.value = uuid;
            isChatActive.value = true;
            connectEcho(uuid);
            scrollToBottom();
        } else {
            sessionStorage.removeItem('carrygo_chat_session_uuid');
            sessionUuid.value = null;
            isChatActive.value = false;
        }
    } catch (e) {
        console.error('Failed to load chat history', e);
        sessionStorage.removeItem('carrygo_chat_session_uuid');
    } finally {
        isLoading.value = false;
    }
};

const startChat = async () => {
    isSubmitting.value = true;
    try {
        const res = await fetch('/support/chat-api/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                name: prechatForm.value.name,
                email: prechatForm.value.email,
            }),
        });

        if (res.ok) {
            const data = await res.json();
            sessionUuid.value = data.uuid;
            sessionStatus.value = data.status;
            isChatActive.value = true;
            sessionStorage.setItem('carrygo_chat_session_uuid', data.uuid);
            connectEcho(data.uuid);

            // Add a local welcome message
            messages.value = [
                {
                    id: 0,
                    sender_type: 'system',
                    body: 'Connecting to CarryGo support. Please wait for an agent to claim the session...',
                    created_at: new Date().toISOString()
                }
            ];
            scrollToBottom();
        }
    } catch (e) {
        console.error('Failed to initiate support chat', e);
    } finally {
        isSubmitting.value = false;
    }
};

const sendChatMessage = async () => {
    if (!sessionUuid.value || !messageText.value.trim() || isSubmitting.value) return;

    const body = messageText.value;
    messageText.value = ''; // Instant UI response

    try {
        const socketId = window.Echo?.socketId();
        const headers: Record<string, string> = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        };

        if (socketId) {
            headers['X-Socket-ID'] = socketId;
        }

        const res = await fetch(`/support/chat-api/${sessionUuid.value}/message`, {
            method: 'POST',
            headers,
            body: JSON.stringify({ body }),
        });

        if (res.ok) {
            const data = await res.json();
            const exists = messages.value.some(m => m.id === data.id);

            if (!exists) {
                messages.value.push({
                    id: data.id,
                    sender_type: data.sender_type,
                    body: data.body,
                    created_at: data.created_at,
                });
                scrollToBottom();
            }
        }
    } catch (e) {
        console.error('Failed to send message', e);
        // Put text back if failed
        messageText.value = body;
    }
};

const submitOfflineForm = async () => {
    isSubmitting.value = true;

    try {
        const res = await fetch('/support/chat-api/offline-ticket', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                name: offlineForm.value.name,
                email: offlineForm.value.email,
                body: offlineForm.value.body,
            }),
        });

        if (res.ok) {
            alert('Your request has been filed successfully. A support ticket has been created and our team will get back to you via email.');
            offlineForm.value.body = '';
            isOpen.value = false;
        }
    } catch (e) {
        console.error('Failed to submit offline ticket', e);
        alert('Something went wrong. Please try again.');
    } finally {
        isSubmitting.value = false;
    }
};

const startNewChat = () => {
    sessionStorage.removeItem('carrygo_chat_session_uuid');

    if (sessionUuid.value) {
        window.Echo?.leave(`chat.${sessionUuid.value}`);
    }

    sessionUuid.value = null;
    isChatActive.value = false;
    messages.value = [];
    sessionStatus.value = 'waiting';
    agentName.value = null;
    checkStatus();
};

onMounted(() => {
    // Check if logged-in user to prefill
    if (currentUser.value) {
        prechatForm.value.name = currentUser.value.name;
        prechatForm.value.email = currentUser.value.email;
        offlineForm.value.name = currentUser.value.name;
        offlineForm.value.email = currentUser.value.email;
    }

    // Check if there is an active session UUID stored in sessionStorage
    const storedUuid = sessionStorage.getItem('carrygo_chat_session_uuid');

    if (storedUuid) {
        loadHistory(storedUuid);
    } else {
        checkStatus();
    }
});

// Watch current user to prefill if they log in dynamically
watch(currentUser, (newUser) => {
    if (newUser) {
        prechatForm.value.name = newUser.name;
        prechatForm.value.email = newUser.email;
        offlineForm.value.name = newUser.name;
        offlineForm.value.email = newUser.email;
    }
});
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 font-sans text-xs">
        <!-- Minimized Floating Button -->
        <button
            v-if="!isOpen"
            @click="isOpen = true"
            class="w-14 h-14 bg-navy hover:bg-forest text-lemon rounded-full shadow-2xl flex items-center justify-center cursor-pointer transition-all hover:scale-105"
        >
            <MessageSquare class="w-6 h-6" />
        </button>

        <!-- Chat Box -->
        <div
            v-else
            class="w-[360px] h-[500px] bg-surface-container-lowest border border-outline-variant shadow-2xl rounded-2xl flex flex-col overflow-hidden animate-fade-in"
        >
            <!-- Header -->
            <div class="px-5 py-4 bg-navy text-white flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <span :class="['w-2 h-2 rounded-full', isOnline ? 'bg-[#10b981]' : 'bg-gray-400']"></span>
                    <span class="font-extrabold tracking-wide uppercase text-[10px]">
                        {{ isChatActive ? (agentName ? `Chat with ${agentName}` : 'Support queue') : 'CarryGo Support' }}
                    </span>
                </div>
                <button @click="isOpen = false" class="text-white/80 hover:text-white cursor-pointer p-0.5">
                    <ChevronDown class="w-5 h-5" />
                </button>
            </div>

            <!-- Body -->
            <div class="flex-1 flex flex-col min-h-0 bg-background">
                <!-- 1. Loading State -->
                <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                    <Loader2 class="w-8 h-8 text-primary animate-spin" />
                </div>

                <!-- 2. Active Chat Stream -->
                <template v-else-if="isChatActive">
                    <div
                        ref="messageContainer"
                        class="flex-1 p-5 overflow-y-auto flex flex-col gap-3 bg-[#fcfdfd]"
                    >
                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            :class="[
                                'flex flex-col max-w-[75%]',
                                msg.sender_type === 'system'
                                    ? 'mx-auto w-full max-w-none text-center my-1.5'
                                    : msg.sender_type === 'customer'
                                        ? 'self-end items-end'
                                        : 'self-start items-start'
                            ]"
                        >
                            <!-- System notification -->
                            <template v-if="msg.sender_type === 'system'">
                                <div class="inline-block px-3 py-1 rounded bg-surface-container text-[8.5px] uppercase tracking-wider font-extrabold text-on-surface-variant/80 border border-outline-variant/30">
                                    {{ msg.body }}
                                </div>
                            </template>

                            <!-- User message bubbles -->
                            <template v-else>
                                <div
                                    :class="[
                                        'p-3 rounded-2xl text-[10.5px] leading-relaxed shadow-sm whitespace-pre-wrap',
                                        msg.sender_type === 'customer'
                                            ? 'bg-navy text-lemon rounded-tr-none border border-navy/10'
                                            : 'bg-surface-container-low text-on-surface rounded-tl-none border border-outline-variant/35'
                                    ]"
                                >
                                    {{ msg.body }}
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Input / Ending Banner -->
                    <div class="p-3 bg-surface border-t border-outline-variant/50">
                        <div v-if="sessionStatus === 'closed'" class="text-center py-2 space-y-2">
                            <p class="text-[10px] font-bold text-on-surface-variant uppercase">This chat has been closed.</p>
                            <button
                                @click="startNewChat"
                                class="px-4 py-2 bg-navy text-lemon font-black uppercase text-[9px] rounded-lg tracking-widest cursor-pointer"
                            >
                                Start New Chat
                            </button>
                        </div>
                        <form v-else @submit.prevent="sendChatMessage" class="flex gap-2">
                            <input
                                v-model="messageText"
                                placeholder="Type a message..."
                                class="flex-1 bg-surface-container-low border border-outline-variant text-on-surface px-4 py-2.5 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-[11px] font-medium"
                            />
                            <button
                                type="submit"
                                :disabled="!messageText.trim()"
                                class="px-4 bg-navy text-lemon hover:bg-forest rounded-lg flex items-center justify-center transition-colors cursor-pointer disabled:opacity-50"
                            >
                                <Send class="w-3.5 h-3.5" />
                            </button>
                        </form>
                    </div>
                </template>

                <!-- 3. Pre-Chat Form (Online) -->
                <div v-else-if="isOnline" class="flex-1 p-6 flex flex-col justify-center overflow-y-auto">
                    <div class="text-center mb-6">
                        <MessageSquare class="w-12 h-12 text-primary mx-auto mb-2" />
                        <h3 class="text-xs font-black uppercase tracking-wider text-primary">Chat with CarryGo Support</h3>
                        <p class="text-[10px] text-on-surface-variant mt-1">Our customer care agents are online and ready to assist you.</p>
                    </div>

                    <form @submit.prevent="startChat" class="space-y-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-on-surface-variant font-bold text-[9px] uppercase tracking-wider">Your Name</label>
                            <input
                                v-model="prechatForm.name"
                                required
                                type="text"
                                placeholder="Enter name"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-2.5 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold capitalize"
                            />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-on-surface-variant font-bold text-[9px] uppercase tracking-wider">Email Address</label>
                            <input
                                v-model="prechatForm.email"
                                required
                                type="email"
                                placeholder="Enter email"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-2.5 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold lowercase"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full py-3 bg-navy text-lemon font-black uppercase rounded-lg tracking-wider transition-colors cursor-pointer disabled:opacity-50 flex items-center justify-center gap-1.5"
                        >
                            <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                            <span>Start Live Chat</span>
                        </button>
                    </form>
                </div>

                <!-- 4. Offline Contact Form (Offline) -->
                <div v-else class="flex-1 p-6 flex flex-col justify-center overflow-y-auto">
                    <div class="text-center mb-6">
                        <Clock class="w-12 h-12 text-on-surface-variant/80 mx-auto mb-2" />
                        <h3 class="text-xs font-black uppercase tracking-wider text-primary">Support is Offline</h3>
                        <p class="text-[10px] text-on-surface-variant mt-1">Our team is currently offline. Drop us a message below and we will automatically create a support ticket.</p>
                    </div>

                    <form @submit.prevent="submitOfflineForm" class="space-y-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-on-surface-variant font-bold text-[9px] uppercase tracking-wider">Your Name</label>
                            <input
                                v-model="offlineForm.name"
                                required
                                type="text"
                                placeholder="Enter name"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-2.5 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold uppercase"
                            />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-on-surface-variant font-bold text-[9px] uppercase tracking-wider">Email Address</label>
                            <input
                                v-model="offlineForm.email"
                                required
                                type="email"
                                placeholder="Enter email"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-2.5 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-bold uppercase"
                            />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-on-surface-variant font-bold text-[9px] uppercase tracking-wider">Inquiry Details</label>
                            <textarea
                                v-model="offlineForm.body"
                                required
                                rows="3"
                                placeholder="Provide description..."
                                class="bg-surface-container-low border border-outline-variant text-on-surface p-4 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary font-medium text-[11px]"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full py-3 bg-navy text-lemon font-black uppercase rounded-lg tracking-wider transition-colors cursor-pointer disabled:opacity-50 flex items-center justify-center gap-1.5"
                        >
                            <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                            <span>Submit Request</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
