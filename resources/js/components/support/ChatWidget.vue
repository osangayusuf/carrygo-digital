<script setup lang="ts">
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
    Loader2,
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, nextTick, computed, watch } from 'vue';
import {
    status as chatStatus,
    initiate as chatInitiate,
    messages as chatMessages,
    send as chatSend,
    offlineTicket as chatOfflineTicket,
} from '@/routes/support/chat/api';

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
            messageContainer.value.scrollTop =
                messageContainer.value.scrollHeight;
        }
    });
};

const checkStatus = async () => {
    try {
        const res = await fetch(chatStatus.url());
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
            const exists = messages.value.some((m) => m.id === event.id);

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
        const res = await fetch(chatMessages.url(uuid));

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
            sessionStorage.removeItem('bidora_chat_session_uuid');
            sessionUuid.value = null;
            isChatActive.value = false;
        }
    } catch (e) {
        console.error('Failed to load chat history', e);
        sessionStorage.removeItem('bidora_chat_session_uuid');
    } finally {
        isLoading.value = false;
    }
};

const startChat = async () => {
    isSubmitting.value = true;

    try {
        const res = await fetch(chatInitiate.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content || '',
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
            sessionStorage.setItem('bidora_chat_session_uuid', data.uuid);
            connectEcho(data.uuid);

            // Add a local welcome message
            messages.value = [
                {
                    id: 0,
                    sender_type: 'system',
                    body: 'Connecting to Bidora support. Please wait for an agent to claim the session...',
                    created_at: new Date().toISOString(),
                },
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
    if (!sessionUuid.value || !messageText.value.trim() || isSubmitting.value) {
        return;
    }

    const body = messageText.value;
    messageText.value = ''; // Instant UI response

    try {
        const socketId = window.Echo?.socketId();
        const headers: Record<string, string> = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN':
                (
                    document.querySelector(
                        'meta[name="csrf-token"]',
                    ) as HTMLMetaElement
                )?.content || '',
        };

        if (socketId) {
            headers['X-Socket-ID'] = socketId;
        }

        const res = await fetch(chatSend.url(sessionUuid.value), {
            method: 'POST',
            headers,
            body: JSON.stringify({ body }),
        });

        if (res.ok) {
            const data = await res.json();
            const exists = messages.value.some((m) => m.id === data.id);

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
        const res = await fetch(chatOfflineTicket.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content || '',
            },
            body: JSON.stringify({
                name: offlineForm.value.name,
                email: offlineForm.value.email,
                body: offlineForm.value.body,
            }),
        });

        if (res.ok) {
            alert(
                'Your request has been filed successfully. A support ticket has been created and our team will get back to you via email.',
            );
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
    sessionStorage.removeItem('bidora_chat_session_uuid');

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

const handleOpenSupportChat = (): void => {
    isOpen.value = true;
};

onMounted(() => {
    window.addEventListener('open-support-chat', handleOpenSupportChat);

    // Check if logged-in user to prefill
    if (currentUser.value) {
        prechatForm.value.name = currentUser.value.name;
        prechatForm.value.email = currentUser.value.email;
        offlineForm.value.name = currentUser.value.name;
        offlineForm.value.email = currentUser.value.email;
    }

    // Check if there is an active session UUID stored in sessionStorage
    const storedUuid = sessionStorage.getItem('bidora_chat_session_uuid');

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

onUnmounted(() => {
    window.removeEventListener('open-support-chat', handleOpenSupportChat);
});
</script>

<template>
    <div
        class="fixed bottom-20 z-50 font-sans text-xs md:bottom-6"
        :class="
            isOpen
                ? 'left-1/2 -translate-x-1/2 md:right-6 md:left-auto md:translate-x-0'
                : 'right-6'
        "
    >
        <!-- Minimized Floating Button -->
        <button
            v-if="!isOpen"
            @click="isOpen = true"
            class="flex h-14 w-14 cursor-pointer items-center justify-center rounded-full bg-navy text-lemon shadow-2xl transition-all hover:scale-105 hover:bg-forest"
        >
            <MessageSquare class="h-6 w-6" />
        </button>

        <!-- Chat Box -->
        <div
            v-else
            class="animate-fade-in flex h-[500px] w-[320px] flex-col overflow-hidden rounded-2xl border border-outline-variant bg-surface-container-lowest shadow-2xl md:w-[360px]"
        >
            <!-- Header -->
            <div
                class="flex items-center justify-between bg-navy px-5 py-4 text-white shadow-sm"
            >
                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'h-2 w-2 rounded-full',
                            isOnline ? 'bg-surface-tint' : 'bg-gray-400',
                        ]"
                    ></span>
                    <span
                        class="text-[10px] font-extrabold tracking-wide uppercase"
                    >
                        {{
                            isChatActive
                                ? agentName
                                    ? `Chat with ${agentName}`
                                    : 'Support queue'
                                : 'Bidora Support'
                        }}
                    </span>
                </div>
                <button
                    @click="isOpen = false"
                    class="cursor-pointer p-0.5 text-white/80 hover:text-white"
                >
                    <ChevronDown class="h-5 w-5" />
                </button>
            </div>

            <!-- Body -->
            <div class="flex min-h-0 flex-1 flex-col bg-background">
                <!-- 1. Loading State -->
                <div
                    v-if="isLoading"
                    class="flex flex-1 items-center justify-center"
                >
                    <Loader2 class="h-8 w-8 animate-spin text-primary" />
                </div>

                <!-- 2. Active Chat Stream -->
                <template v-else-if="isChatActive">
                    <div
                        ref="messageContainer"
                        class="flex flex-1 flex-col gap-3 overflow-y-auto bg-[#fcfdfd] p-5"
                    >
                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            :class="[
                                'flex max-w-[75%] flex-col',
                                msg.sender_type === 'system'
                                    ? 'mx-auto my-1.5 w-full max-w-none text-center'
                                    : msg.sender_type === 'customer'
                                      ? 'items-end self-end'
                                      : 'items-start self-start',
                            ]"
                        >
                            <!-- System notification -->
                            <template v-if="msg.sender_type === 'system'">
                                <div
                                    class="inline-block rounded border border-outline-variant/30 bg-surface-container px-3 py-1 text-[8.5px] font-extrabold tracking-wider text-on-surface-variant/80 uppercase"
                                >
                                    {{ msg.body }}
                                </div>
                            </template>

                            <!-- User message bubbles -->
                            <template v-else>
                                <div
                                    :class="[
                                        'rounded-2xl p-3 text-[10.5px] leading-relaxed whitespace-pre-wrap shadow-sm',
                                        msg.sender_type === 'customer'
                                            ? 'rounded-tr-none border border-navy/10 bg-navy text-lemon'
                                            : 'rounded-tl-none border border-outline-variant/35 bg-surface-container-low text-on-surface',
                                    ]"
                                >
                                    {{ msg.body }}
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Input / Ending Banner -->
                    <div
                        class="border-t border-outline-variant/50 bg-surface p-3"
                    >
                        <div
                            v-if="sessionStatus === 'closed'"
                            class="space-y-2 py-2 text-center"
                        >
                            <p
                                class="text-[10px] font-bold text-on-surface-variant uppercase"
                            >
                                This chat has been closed.
                            </p>
                            <button
                                @click="startNewChat"
                                class="cursor-pointer rounded-lg bg-navy px-4 py-2 text-[9px] font-black tracking-widest text-lemon uppercase"
                            >
                                Start New Chat
                            </button>
                        </div>
                        <form
                            v-else
                            @submit.prevent="sendChatMessage"
                            class="flex gap-2"
                        >
                            <input
                                v-model="messageText"
                                placeholder="Type a message..."
                                class="flex-1 rounded-lg border border-outline-variant bg-surface-container-low px-4 py-2.5 text-[11px] font-medium text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                            />
                            <button
                                type="submit"
                                :disabled="!messageText.trim()"
                                class="flex cursor-pointer items-center justify-center rounded-lg bg-navy px-4 text-lemon transition-colors hover:bg-forest disabled:opacity-50"
                            >
                                <Send class="h-3.5 w-3.5" />
                            </button>
                        </form>
                    </div>
                </template>

                <!-- 3. Pre-Chat Form (Online) -->
                <div
                    v-else-if="isOnline"
                    class="flex flex-1 flex-col justify-center overflow-y-auto p-6"
                >
                    <div class="mb-6 text-center">
                        <MessageSquare
                            class="mx-auto mb-2 h-12 w-12 text-primary"
                        />
                        <h3
                            class="text-xs font-black tracking-wider text-primary uppercase"
                        >
                            Chat with Bidora Support
                        </h3>
                        <p class="mt-1 text-[10px] text-on-surface-variant">
                            Our customer care agents are online and ready to
                            assist you.
                        </p>
                    </div>

                    <form @submit.prevent="startChat" class="space-y-4">
                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[9px] font-bold tracking-wider text-on-surface-variant uppercase"
                                >Your Name</label
                            >
                            <input
                                v-model="prechatForm.name"
                                required
                                type="text"
                                placeholder="Enter name"
                                class="rounded-lg border border-outline-variant bg-surface-container-low px-4 py-2.5 font-bold text-on-surface capitalize focus:ring-1 focus:ring-secondary focus:outline-none"
                            />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[9px] font-bold tracking-wider text-on-surface-variant uppercase"
                                >Email Address</label
                            >
                            <input
                                v-model="prechatForm.email"
                                required
                                type="email"
                                placeholder="Enter email"
                                class="rounded-lg border border-outline-variant bg-surface-container-low px-4 py-2.5 font-bold text-on-surface lowercase focus:ring-1 focus:ring-secondary focus:outline-none"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-lg bg-navy py-3 font-black tracking-wider text-lemon uppercase transition-colors disabled:opacity-50"
                        >
                            <Loader2
                                v-if="isSubmitting"
                                class="h-4 w-4 animate-spin"
                            />
                            <span>Start Live Chat</span>
                        </button>
                    </form>
                </div>

                <!-- 4. Offline Contact Form (Offline) -->
                <div
                    v-else
                    class="flex flex-1 flex-col justify-center overflow-y-auto p-6"
                >
                    <div class="mb-6 text-center">
                        <Clock
                            class="mx-auto mb-2 h-12 w-12 text-on-surface-variant/80"
                        />
                        <h3
                            class="text-xs font-black tracking-wider text-primary uppercase"
                        >
                            Support is Offline
                        </h3>
                        <p class="mt-1 text-[10px] text-on-surface-variant">
                            Our team is currently offline. Drop us a message
                            below and we will automatically create a support
                            ticket.
                        </p>
                    </div>

                    <form @submit.prevent="submitOfflineForm" class="space-y-4">
                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[9px] font-bold tracking-wider text-on-surface-variant uppercase"
                                >Your Name</label
                            >
                            <input
                                v-model="offlineForm.name"
                                required
                                type="text"
                                placeholder="Enter name"
                                class="rounded-lg border border-outline-variant bg-surface-container-low px-4 py-2.5 font-bold text-on-surface uppercase focus:ring-1 focus:ring-secondary focus:outline-none"
                            />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[9px] font-bold tracking-wider text-on-surface-variant uppercase"
                                >Email Address</label
                            >
                            <input
                                v-model="offlineForm.email"
                                required
                                type="email"
                                placeholder="Enter email"
                                class="rounded-lg border border-outline-variant bg-surface-container-low px-4 py-2.5 font-bold text-on-surface uppercase focus:ring-1 focus:ring-secondary focus:outline-none"
                            />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[9px] font-bold tracking-wider text-on-surface-variant uppercase"
                                >Inquiry Details</label
                            >
                            <textarea
                                v-model="offlineForm.body"
                                required
                                rows="3"
                                placeholder="Provide description..."
                                class="rounded-lg border border-outline-variant bg-surface-container-low p-4 text-[11px] font-medium text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-lg bg-navy py-3 font-black tracking-wider text-lemon uppercase transition-colors disabled:opacity-50"
                        >
                            <Loader2
                                v-if="isSubmitting"
                                class="h-4 w-4 animate-spin"
                            />
                            <span>Submit Request</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
