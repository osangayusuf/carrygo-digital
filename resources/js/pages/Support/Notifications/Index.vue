<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import SupportLayout from '@/layouts/SupportLayout.vue';
import { ref, watch, computed } from 'vue';
import { 
    Bell, 
    MessageSquare, 
    Ticket as TicketIcon, 
    Reply, 
    Check, 
    CheckSquare, 
    Clock, 
    Inbox,
    ArrowRight
} from 'lucide-vue-next';

type AppNotification = {
    id: string;
    title: string;
    body: string;
    icon: string;
    created_at: string;
    read_at: string | null;
    url: string | null;
};

const props = defineProps<{
    notificationsList: AppNotification[];
}>();

const list = ref<AppNotification[]>([...props.notificationsList]);

watch(() => props.notificationsList, (newVal) => {
    list.value = [...newVal];
}, { deep: true });

const unreadCount = computed(() => list.value.filter(n => !n.read_at).length);

const getIconComponent = (iconName: string) => {
    switch (iconName) {
        case 'message-square':
            return MessageSquare;
        case 'ticket':
            return TicketIcon;
        case 'reply':
            return Reply;
        default:
            return Bell;
    }
};

const getIconBgClass = (iconName: string) => {
    switch (iconName) {
        case 'message-square':
            return 'bg-secondary/15 text-primary border border-secondary/20';
        case 'ticket':
            return 'bg-primary-container text-on-primary-container border border-primary/20';
        case 'reply':
            return 'bg-forest/15 text-forest border border-forest/20';
        default:
            return 'bg-surface-container-high text-on-surface-variant border border-outline-variant/60';
    }
};

const markRead = async (id: string) => {
    try {
        const response = await fetch(`/support/notifications/${id}/read`, {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
            },
        });
        if (response.ok) {
            const item = list.value.find(n => n.id === id);
            if (item) {
                item.read_at = new Date().toISOString();
            }
            // Also refresh shared props
            router.reload({ only: ['notifications'] });
        }
    } catch (e) {
        console.error('Failed to mark notification as read', e);
    }
};

const markAllRead = async () => {
    try {
        const response = await fetch('/support/notifications/read-all', {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
            },
        });
        if (response.ok) {
            const now = new Date().toISOString();
            list.value.forEach(n => {
                n.read_at = now;
            });
            // Also refresh shared props
            router.reload({ only: ['notifications'] });
        }
    } catch (e) {
        console.error('Failed to mark all notifications as read', e);
    }
};

const formatTime = (timeStr: string) => {
    const date = new Date(timeStr);
    return date.toLocaleString();
};
</script>

<template>
    <Head title="Notifications - CarryGo Support" />

    <SupportLayout :breadcrumbs="[{ title: 'Notifications' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <Bell class="w-6 h-6 text-primary" />
                        <span>Support Notifications</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Stay updated with live chat sessions, ticket assignments, and replies.</p>
                </div>
                
                <button 
                    v-if="unreadCount > 0"
                    @click="markAllRead"
                    class="flex items-center justify-center gap-2 px-4 py-2.5 border border-outline-variant bg-surface-container-low hover:bg-surface-container text-on-surface hover:text-primary font-bold uppercase rounded-lg transition-all cursor-pointer self-start sm:self-auto"
                >
                    <CheckSquare class="w-4 h-4" />
                    <span>Mark All As Read</span>
                </button>
            </div>

            <!-- Notifications List container -->
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm flex flex-col overflow-hidden">
                <div class="divide-y divide-outline-variant/40">
                    <div 
                        v-for="item in list" 
                        :key="item.id" 
                        :class="[
                            'p-5 transition-all flex flex-col md:flex-row justify-between md:items-center gap-4 group',
                            !item.read_at ? 'bg-secondary-container/10 border-l-4 border-primary' : 'border-l-4 border-transparent'
                        ]"
                    >
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            <!-- Icon -->
                            <div :class="['w-8 h-8 rounded-lg flex items-center justify-center shrink-0 shadow-sm', getIconBgClass(item.icon)]">
                                <component :is="getIconComponent(item.icon)" class="w-4 h-4" />
                            </div>

                            <!-- Details -->
                            <div class="space-y-1 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 :class="['text-xs truncate transition-colors group-hover:text-primary', !item.read_at ? 'font-extrabold text-on-surface' : 'font-bold text-on-surface-variant']">
                                        {{ item.title }}
                                    </h2>
                                    <span v-if="!item.read_at" class="bg-primary/10 text-primary border border-primary/25 text-[8px] font-black uppercase px-1.5 py-0.5 rounded">
                                        New
                                    </span>
                                </div>
                                <p :class="['text-[11px] leading-relaxed', !item.read_at ? 'font-bold text-on-surface/90' : 'font-medium text-on-surface-variant']">
                                    {{ item.body }}
                                </p>
                                <div class="flex items-center gap-1.5 text-[9px] text-on-surface-variant/70 font-semibold">
                                    <Clock class="w-3.5 h-3.5" />
                                    <span>{{ formatTime(item.created_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 self-end md:self-auto">
                            <button 
                                v-if="!item.read_at"
                                @click="markRead(item.id)"
                                class="flex items-center gap-1.5 px-3 py-1.5 border border-outline-variant bg-surface-container-low hover:bg-secondary-container text-on-surface hover:text-primary rounded-lg font-bold transition-all cursor-pointer"
                                title="Mark as read"
                            >
                                <Check class="w-3.5 h-3.5" />
                                <span>Read</span>
                            </button>
                            <Link 
                                v-if="item.url"
                                :href="item.url"
                                class="flex items-center gap-1.5 px-3 py-1.5 bg-navy text-lemon hover:bg-forest rounded-lg font-bold transition-all animate-fade-in"
                            >
                                <span>View</span>
                                <ArrowRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-if="list.length === 0" class="py-16 text-center flex flex-col items-center justify-center gap-3 text-on-surface-variant">
                        <Inbox class="w-12 h-12 text-outline-variant/60" />
                        <span class="uppercase tracking-widest font-black text-[10px]">No Notifications</span>
                        <span class="text-xs max-w-xs text-on-surface-variant/75">You have no portal notifications at this time.</span>
                    </div>
                </div>
            </div>
        </div>
    </SupportLayout>
</template>
