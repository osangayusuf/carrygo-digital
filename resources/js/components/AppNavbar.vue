<script setup lang="ts">
import { Link, router, useHttp, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRealtimeNotifications } from '@/composables/useRealtimeNotifications';
import { formatDate } from '@/lib/utils';
import {
    eventItems,
    home,
    howToPlay,
    leaderboard,
    login as loginShow,
    register,
    logout as logoutRoute,
    openBids,
    profile,
    recommended,
    tasks,
    trending,
    winners,
} from '@/routes/index';
import { dashboard as adminDashboard } from '@/routes/admin/index';
import notificationsRoutes from '@/routes/notifications';

interface Notification {
    id: string;
    title: string;
    body: string;
    icon: string;
    created_at: string;
    read_at: string | null;
    url: string | null;
}

const page = usePage();
const http = useHttp();
const notificationsOpen = ref(false);
const currentPath = computed(() => page.url.split('?')[0]);

const notifications = ref<Notification[]>([...((page.props.notifications as Notification[]) ?? [])]);
const isRefreshingNotifications = ref(false);

watch(
    () => page.props.notifications,
    (value) => {
        notifications.value = [...((value as Notification[]) ?? [])];
    },
);

async function refreshNotifications(): Promise<void> {
    if (!currentUser.value || isRefreshingNotifications.value) {
        return;
    }

    isRefreshingNotifications.value = true;

    try {
        const data = (await http.submit(notificationsRoutes.feed.get())) as Notification[];
        notifications.value = data;
    } catch {
        // Keep existing list if refresh fails.
    } finally {
        isRefreshingNotifications.value = false;
    }
}

const unreadCount = computed(() => notifications.value.filter((n) => isUnread(n)).length);

function isUnread(notification: Notification): boolean {
    return notification.read_at === null;
}

async function markRead(id: string): Promise<void> {
    const notification = notifications.value.find((n) => n.id === id);

    if (!notification || !isUnread(notification)) {
        return;
    }

    try {
        await http.submit(notificationsRoutes.read.patch(id));
        notification.read_at = new Date().toISOString();
    } catch {
        // Keep unread state if the request fails.
    }
}

async function markAllRead(): Promise<void> {
    if (unreadCount.value === 0) {
        return;
    }

    try {
        await http.submit(notificationsRoutes.readAll.patch());
        const now = new Date().toISOString();
        notifications.value.forEach((notification) => {
            if (isUnread(notification)) {
                notification.read_at = now;
            }
        });
    } catch {
        // Keep unread state if the request fails.
    }
}

function onNotificationNavigate(notification: Notification): void {
    if (isUnread(notification)) {
        markRead(notification.id);
    }

    notificationsOpen.value = false;
}

async function toggleNotifications(): Promise<void> {
    const willOpen = !notificationsOpen.value;
    notificationsOpen.value = willOpen;

    if (willOpen) {
        await refreshNotifications();
    }
}

function closeNotifications(event: MouseEvent): void {
    const panel = document.getElementById('notification-panel');
    const button = document.getElementById('notification-btn');

    if (panel && !panel.contains(event.target as Node) && button && !button.contains(event.target as Node)) {
        notificationsOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', closeNotifications);

    router.on('success', () => {
        if (currentUser.value) {
            console.log(currentUser.value)
            refreshNotifications();
        }
    });
});

onBeforeUnmount(() => document.removeEventListener('click', closeNotifications));

const search = ref(new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '').get('search') ?? '');

function visit(extra: Record<string, string | number> = {}) {
    const currentParams = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');
    const existing: Record<string, string> = {};
    currentParams.forEach((value, key) => {
        existing[key] = value;
    });

    router.get(
        currentPath.value,
        {
            ...existing,
            ...(search.value ? { search: search.value } : { search: undefined }),
            ...extra,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

const onSearch = useDebounceFn(() => visit({ page: 1 }), 400);

const navLinks = [
    { label: 'Home', href: home.url(), icon: 'pi pi-home' },
    { label: 'Trending', href: trending.url(), icon: 'pi pi-chart-line' },
    { label: 'Recommended', href: recommended.url(), icon: 'pi pi-star' },
    { label: 'Open Bids', href: openBids.url(), icon: 'pi pi-box' },
    { label: 'Event Items', href: eventItems.url(), icon: 'pi pi-calendar' },
    { label: 'Winners', href: winners.url(), icon: 'pi pi-history' },
    { label: 'Leaderboard', href: leaderboard.url(), icon: 'pi pi-chart-bar' },
    { label: 'Tasks', href: tasks.url(), icon: 'pi pi-check-square' },
    { label: 'How to play', href: howToPlay.url(), icon: 'pi pi-question-circle' },
];

const firstLineLinks = computed(() => navLinks.slice(0, 4));
const secondLineLinks = computed(() => navLinks.slice(4));

const currentUser = computed(() => page.props.auth?.user ?? null);

const currentUserId = computed(() => currentUser.value?.id ?? null);

const isAdmin = computed(() => currentUser.value?.is_admin === true);

useRealtimeNotifications(currentUserId, notifications, refreshNotifications);

function navItemClass(href: string): string {
    const path = href.split('?')[0];
    const active =
        href !== '#' &&
        (path === home.url()
            ? page.url === home.url() || page.url === ''
            : page.url.startsWith(path));
    const base = 'font-headline px-1 py-0.5 tracking-tight transition-colors';

    if (active) {
        return `${base} border-b-2 border-primary font-extrabold text-primary`;
    }

    return `${base} font-bold text-secondary hover:text-primary`;
}

const marqueeItems = computed(() => {
    const items = page.props.marquee_items;
    if (Array.isArray(items) && items.length > 0) {
        return items as { text: string; icon?: string }[];
    }
    return [
        { text: 'Bid on premium luxury items with points!', icon: 'pi pi-gift' },
        { text: 'Complete tasks in the Task Center to earn free points!', icon: 'pi pi-check-square' },
        { text: 'Check the leaderboard to see top bidders of the week!', icon: 'pi pi-chart-bar' },
        { text: 'New auction drops every Monday!', icon: 'pi pi-send' }
    ];
});
</script>

<template>
    <nav class="glass-nav sticky top-0 z-50 shadow-sm dark:shadow-none">
        <!-- TOP BAR -->
        <div class="bg-navy text-lemon text-xs py-1 px-4 flex items-center justify-between gap-2">
            <div class="flex-1 overflow-hidden whitespace-nowrap">
                <span class="inline-block animate-marquee">
                    <template v-for="(item, idx) in marqueeItems" :key="idx">
                        <i :class="[item.icon || 'pi pi-bolt', 'mr-1']"></i>
                        {{ item.text }}
                        <span v-if="idx < marqueeItems.length - 1" class="mx-3">&nbsp;|&nbsp;</span>
                    </template>
                </span>
            </div>
        </div>

        <!-- NAVBAR -->
        <nav class="bg-white border-b-2 border-lemon shadow-md">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center gap-x-3 md:gap-x-10 gap-y-2 py-2 sm:py-1.5 px-4">
                <Link :href="home.url()" class="flex items-center no-underline shrink-0 order-1">
                    <img class="h-7 sm:h-10 w-auto block" :src="`${$page.props.asset_url}logo.png`" alt="CarryGo" />
                </Link>
                <!-- Search: full-width second row on mobile, inline flex-1 on md+ -->
                <div class="flex w-full md:flex-1 md:w-auto order-3 md:order-2 min-w-0">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search luxury items, brands, auctions..."
                        class="flex-1 border-2 border-forest border-r-0 py-1.5 sm:py-2 px-3.5 text-xs sm:text-sm font-sans rounded-l-xl outline-none min-w-0"
                        @input="onSearch"
                    />
                    <button
                        type="button"
                        class="bg-forest text-lemon border-none py-2 px-4.5 text-sm font-bold cursor-pointer rounded-r-xl whitespace-nowrap hover:bg-forest-dark"
                        @click="onSearch"
                    >
                        <i class="pi pi-search"></i>
                    </button>
                </div>
                <div class="flex items-center gap-2 md:gap-5 relative ml-auto md:ml-0 order-2 md:order-3">
                    <button id="notification-btn" class="bg-transparent border-none cursor-pointer relative p-1.5" @click="toggleNotifications">
                        <i class="pi pi-bell text-base sm:text-xl text-muted-green hover:text-navy transition-colors"></i>
                        <div
                            v-if="unreadCount > 0"
                            class="absolute top-0 right-0 bg-ink text-lemon rounded-full w-4 h-4 text-[10px] font-extrabold flex items-center justify-center"
                        >
                            {{ unreadCount > 9 ? '9+' : unreadCount }}
                        </div>
                    </button>

                    <!-- Notification Panel -->
                    <div
                        v-if="notificationsOpen"
                        id="notification-panel"
                        class="absolute top-full mt-2 right-0 md:-right-2 w-80 sm:w-96 bg-white border-2 border-lemon shadow-xl rounded-2xl z-50 overflow-hidden flex flex-col max-h-[80vh]"
                    >
                        <div class="flex items-center justify-between px-4 py-3 border-b-2 border-lemon bg-gray-50/50">
                            <h3 class="font-extrabold text-navy m-0 text-sm font-headline">Notifications</h3>
                            <button
                                v-if="unreadCount > 0"
                                class="text-xs text-primary font-bold bg-transparent border-none cursor-pointer hover:text-forest transition-colors p-0"
                                @click="markAllRead"
                            >
                                Mark all read
                            </button>
                        </div>
                        <div class="overflow-y-auto overflow-x-hidden hide-scrollbar flex-1 bg-white">
                            <div
                                v-if="notifications.length === 0"
                                class="p-8 text-center text-secondary text-sm font-bold flex flex-col items-center justify-center h-full gap-2"
                            >
                                <i class="pi pi-check-circle text-3xl text-gray-300"></i>
                                You're all caught up!
                            </div>
                            <div
                                v-for="notification in notifications"
                                :key="notification.id"
                                :class="[
                                    'px-4 py-4 border-b border-gray-100 last:border-b-0 flex gap-3.5 transition-colors',
                                    isUnread(notification) ? 'bg-forest/5' : 'bg-white hover:bg-gray-50',
                                ]"
                            >
                                <div class="mt-0.5 shrink-0">
                                    <span
                                        class="material-symbols-outlined text-2xl"
                                        :class="isUnread(notification) ? 'text-forest' : 'text-gray-400'"
                                    >
                                        {{ notification.icon || 'notifications' }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1.5">
                                        <h4
                                            :class="[
                                                'm-0 text-sm leading-tight font-sans',
                                                isUnread(notification) ? 'font-extrabold text-navy' : 'font-bold text-secondary',
                                            ]"
                                        >
                                            {{ notification.title }}
                                        </h4>
                                        <span class="text-[10px] text-gray-400 whitespace-nowrap font-bold shrink-0 mt-0.5">{{
                                            formatDate(notification.created_at)
                                        }}</span>
                                    </div>
                                    <p :class="['m-0 text-xs leading-relaxed', isUnread(notification) ? 'text-navy' : 'text-secondary']">{{ notification.body }}</p>
                                    <div class="mt-2.5 flex flex-wrap items-center gap-3">
                                        <Link
                                            v-if="notification.url"
                                            :href="notification.url"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-forest hover:text-forest-dark transition-colors"
                                            @click="onNotificationNavigate(notification)"
                                        >
                                            View details
                                            <i class="pi pi-arrow-right text-[10px]"></i>
                                        </Link>
                                        <button
                                            v-if="isUnread(notification)"
                                            type="button"
                                            class="text-xs font-bold text-secondary hover:text-navy bg-transparent border-none cursor-pointer p-0 transition-colors"
                                            @click="markRead(notification.id)"
                                        >
                                            Mark read
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template v-if="!currentUser">
                        <Link
                            :href="loginShow.url()"
                            as="button"
                            class="bg-lemon text-navy border-none md:h-12 h-10 md:px-4.5 px-3 rounded-xl text-sm font-extrabold cursor-pointer whitespace-nowrap font-sans hover:bg-amber transition-colors"
                        >
                            Log In
                        </Link>
                        <Link
                            :href="register.url()"
                            as="button"
                            class="bg-navy text-lemon border-none md:h-12 h-10 md:px-4.5 px-3 rounded-xl text-sm font-extrabold cursor-pointer whitespace-nowrap font-sans hover:bg-forest transition-colors"
                        >
                            Register
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="isAdmin"
                            :href="adminDashboard.url()"
                            as="button"
                            class="bg-amber text-navy border-none md:h-12 h-10 px-3 md:px-4 rounded-xl text-xs sm:text-sm font-extrabold cursor-pointer whitespace-nowrap font-sans hover:bg-lemon flex items-center gap-1.5 transition-colors"
                        >
                            <i class="pi pi-shield text-sm"></i>
                            <span class="hidden sm:inline">Admin</span>
                        </Link>
                        <Link
                            :href="profile.url()"
                            as="button"
                            class="bg-lemon text-navy border-none md:h-12 h-10 px-3.5 md:px-4.5 rounded-xl text-xs sm:text-sm font-extrabold cursor-pointer whitespace-nowrap font-sans hover:bg-amber flex items-center gap-1.5 transition-colors"
                        >
                            <i class="pi pi-wallet text-sm text-forest"></i>
                            <span class="hidden sm:inline">{{ currentUser.points_balance ?? 0 }} pts</span>
                            <span class="sm:hidden">{{ currentUser.points_balance ?? 0 }}</span>
                        </Link>
                        <Link
                            :href="logoutRoute.url()"
                            method="post"
                            as="button"
                            class="bg-transparent border-2 border-gray-200 text-gray-500 hover:text-navy hover:border-gray-300 md:h-12 h-10 px-3 md:px-4 rounded-xl text-sm font-extrabold cursor-pointer whitespace-nowrap transition-colors hidden sm:block"
                        >
                            Log Out
                        </Link>
                        <Link
                            :href="logoutRoute.url()"
                            method="post"
                            as="button"
                            class="bg-transparent border-none text-gray-500 hover:text-navy md:h-12 h-10 px-3 md:px-4 rounded-xl cursor-pointer sm:hidden"
                        >
                            <i class="pi pi-sign-out text-sm sm:text-xl"></i>
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- SUB NAV -->
        <div class="bg-navy relative">
            <!-- Desktop View -->
            <div class="hidden md:flex max-w-7xl mx-auto items-center min-w-full gap-1 justify-between overflow-x-auto hide-scrollbar">
                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    :class="['text-white no-underline py-2 px-4 text-sm whitespace-nowrap block mx-auto hover:bg-lemon/18 hover:text-white cursor-pointer', navItemClass(link.href)]"
                >
                    <i v-if="link.icon" :class="[link.icon, 'mr-1']"></i> {{ link.label }}
                </a>
            </div>

            <!-- Mobile View -->
            <div class="md:hidden w-full flex flex-col">
                <div class="flex items-center w-full justify-between px-1">
                    <a
                        v-for="link in firstLineLinks"
                        :key="link.label"
                        :href="link.href"
                        :class="[
                            'text-white no-underline py-2 px-1 text-[11px] sm:text-xs whitespace-nowrap text-center flex-1 hover:bg-lemon/18 hover:text-white cursor-pointer',
                            navItemClass(link.href),
                        ]"
                    >
                        {{ link.label }}
                    </a>
                </div>
                <div class="flex items-center w-full justify-between px-1 bg-navy/90 border-t border-white/10">
                    <a
                        v-for="link in secondLineLinks"
                        :key="link.label"
                        :href="link.href"
                        :class="[
                            'text-white no-underline py-2 px-1 text-[11px] sm:text-xs whitespace-nowrap text-center flex-1 hover:bg-lemon/18 hover:text-white cursor-pointer',
                            navItemClass(link.href),
                        ]"
                    >
                        {{ link.label }}
                    </a>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 h-px w-full bg-linear-to-r from-transparent via-primary/20 to-transparent" />
    </nav>
</template>
