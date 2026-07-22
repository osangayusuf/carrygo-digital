<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Bell,
    MessageSquare,
    Ticket as TicketIcon,
    Reply,
    Check,
    CheckSquare,
    Clock,
    Inbox,
    ArrowRight,
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import SupportLayout from '@/layouts/SupportLayout.vue';
import {
    read as readNotification,
    readAll as readAllNotifications,
} from '@/routes/support/notifications';

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

watch(
    () => props.notificationsList,
    (newVal) => {
        list.value = [...newVal];
    },
    { deep: true },
);

const unreadCount = computed(() => list.value.filter((n) => !n.read_at).length);

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
        const response = await fetch(readNotification.url(id), {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content ?? '',
            },
        });

        if (response.ok) {
            const item = list.value.find((n) => n.id === id);

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
        const response = await fetch(readAllNotifications.url(), {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content ?? '',
            },
        });

        if (response.ok) {
            const now = new Date().toISOString();
            list.value.forEach((n) => {
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
    <Head title="Notifications - Bidora Support" />

    <SupportLayout :breadcrumbs="[{ title: 'Notifications' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <Bell class="h-6 w-6 text-primary" />
                        <span>Support Notifications</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Stay updated with live chat sessions, ticket
                        assignments, and replies.
                    </p>
                </div>

                <button
                    v-if="unreadCount > 0"
                    @click="markAllRead"
                    class="flex cursor-pointer items-center justify-center gap-2 self-start rounded-lg border border-outline-variant bg-surface-container-low px-4 py-2.5 font-bold text-on-surface uppercase transition-all hover:bg-surface-container hover:text-primary sm:self-auto"
                >
                    <CheckSquare class="h-4 w-4" />
                    <span>Mark All As Read</span>
                </button>
            </div>

            <!-- Notifications List container -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
            >
                <div class="divide-y divide-outline-variant/40">
                    <div
                        v-for="item in list"
                        :key="item.id"
                        :class="[
                            'group flex flex-col justify-between gap-4 p-5 transition-all md:flex-row md:items-center',
                            !item.read_at
                                ? 'border-l-4 border-primary bg-secondary-container/10'
                                : 'border-l-4 border-transparent',
                        ]"
                    >
                        <div class="flex min-w-0 flex-1 items-start gap-4">
                            <!-- Icon -->
                            <div
                                :class="[
                                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg shadow-sm',
                                    getIconBgClass(item.icon),
                                ]"
                            >
                                <component
                                    :is="getIconComponent(item.icon)"
                                    class="h-4 w-4"
                                />
                            </div>

                            <!-- Details -->
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2
                                        :class="[
                                            'truncate text-xs transition-colors group-hover:text-primary',
                                            !item.read_at
                                                ? 'font-extrabold text-on-surface'
                                                : 'font-bold text-on-surface-variant',
                                        ]"
                                    >
                                        {{ item.title }}
                                    </h2>
                                    <span
                                        v-if="!item.read_at"
                                        class="rounded border border-primary/25 bg-primary/10 px-1.5 py-0.5 text-[8px] font-black text-primary uppercase"
                                    >
                                        New
                                    </span>
                                </div>
                                <p
                                    :class="[
                                        'text-[11px] leading-relaxed',
                                        !item.read_at
                                            ? 'font-bold text-on-surface/90'
                                            : 'font-medium text-on-surface-variant',
                                    ]"
                                >
                                    {{ item.body }}
                                </p>
                                <div
                                    class="flex items-center gap-1.5 text-[9px] font-semibold text-on-surface-variant/70"
                                >
                                    <Clock class="h-3.5 w-3.5" />
                                    <span>{{
                                        formatTime(item.created_at)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex items-center gap-2 self-end md:self-auto"
                        >
                            <button
                                v-if="!item.read_at"
                                @click="markRead(item.id)"
                                class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-outline-variant bg-surface-container-low px-3 py-1.5 font-bold text-on-surface transition-all hover:bg-secondary-container hover:text-primary"
                                title="Mark as read"
                            >
                                <Check class="h-3.5 w-3.5" />
                                <span>Read</span>
                            </button>
                            <Link
                                v-if="item.url"
                                :href="item.url"
                                class="animate-fade-in flex items-center gap-1.5 rounded-lg bg-navy px-3 py-1.5 font-bold text-lemon transition-all hover:bg-forest"
                            >
                                <span>View</span>
                                <ArrowRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="list.length === 0"
                        class="flex flex-col items-center justify-center gap-3 py-16 text-center text-on-surface-variant"
                    >
                        <Inbox class="h-12 w-12 text-outline-variant/60" />
                        <span
                            class="text-[10px] font-black tracking-widest uppercase"
                            >No Notifications</span
                        >
                        <span
                            class="max-w-xs text-xs text-on-surface-variant/75"
                            >You have no portal notifications at this
                            time.</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </SupportLayout>
</template>
