<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
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
    tasks,
    trending,
    winners,
} from '@/routes/index';

interface Notification {
    id: number;
    title: string;
    body: string;
    icon: string;
    created_at: string;
    action_route?: string;
    action_label?: string;
}

const page = usePage();
const notificationsOpen = ref(false);
const currentPath = computed(() => page.url.split('?')[0]);

const notifications = computed<Notification[]>(() => (page.props.notifications as Notification[]) ?? []);

const LS_KEY = 'carrygo_read_notification_ids';

function loadReadIds(): number[] {
    try {
        return JSON.parse(localStorage.getItem(LS_KEY) ?? '[]') as number[];
    } catch {
        return [];
    }
}

const readIds = ref<number[]>(loadReadIds());

const unreadCount = computed(() => notifications.value.filter((n) => !readIds.value.includes(n.id)).length);

function isUnread(id: number): boolean {
    return !readIds.value.includes(id);
}

function markAllRead(): void {
    readIds.value = notifications.value.map((n) => n.id);
    localStorage.setItem(LS_KEY, JSON.stringify(readIds.value));
}

function toggleNotifications(): void {
    notificationsOpen.value = !notificationsOpen.value;
}

function closeNotifications(event: MouseEvent): void {
    const panel = document.getElementById('notification-panel');
    const button = document.getElementById('notification-btn');

    if (panel && !panel.contains(event.target as Node) && button && !button.contains(event.target as Node)) {
        notificationsOpen.value = false;
    }
}

onMounted(() => document.addEventListener('click', closeNotifications));
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
    { label: 'Open Bids', href: openBids.url(), icon: 'pi pi-box' },
    { label: 'Event Items', href: eventItems.url(), icon: 'pi pi-calendar' },
    { label: 'Winners', href: winners.url(), icon: 'pi pi-history' },
    { label: 'Leaderboard', href: leaderboard.url(), icon: 'pi pi-chart-bar' },
    { label: 'Tasks', href: tasks.url(), icon: 'pi pi-check-square' },
    { label: 'How to play', href: howToPlay.url(), icon: 'pi pi-question-circle' },
];

const firstLineLinks = computed(() => navLinks.slice(0, 4));
const secondLineLinks = computed(() => navLinks.slice(4));

const currentUser = computed(() => (page.props.auth?.user as any) ?? null);

const routeMap: Record<string, any> = {
    openBids,
    eventItems,
    leaderboard,
    tasks,
};

function getNotificationLink(routeKey?: string): string | null {
    if (!routeKey || !routeMap[routeKey]) {
        return null;
    }

    return routeMap[routeKey].url();
}

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
</script>

<template>
    <nav class="glass-nav sticky top-0 z-50 shadow-sm dark:shadow-none">
        <!-- TOP BAR -->
        <div class="bg-navy text-lemon text-xs py-1 px-4 flex items-center justify-between gap-2">
            <div class="flex-1 overflow-hidden whitespace-nowrap">
                <span class="inline-block animate-marquee">
                    <i class="pi pi-bolt mr-1"></i> LIVE AUCTION: Duffel Bag @ ₦150,000 &nbsp;|&nbsp;
                    <i class="pi pi-trophy mr-1"></i> Gucci Sunglasses – 65/3500 bids &nbsp;|&nbsp;
                    <i class="pi pi-bolt mr-1"></i> Prada Suede Olive – 9% progress! &nbsp;|&nbsp;
                    <i class="pi pi-gift mr-1"></i> White Fendi Shirt – ₦50,000 &nbsp;|&nbsp;
                    <i class="pi pi-star-fill mr-1"></i> Denim Backpack – Only 40 bids so far &nbsp;|&nbsp;
                    <i class="pi pi-send mr-1"></i> New auction drops every Monday!
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
                                    isUnread(notification.id) ? 'bg-forest/5' : 'bg-white hover:bg-gray-50',
                                ]"
                            >
                                <div class="mt-0.5 shrink-0">
                                    <span
                                        class="material-symbols-outlined text-2xl"
                                        :class="isUnread(notification.id) ? 'text-forest' : 'text-gray-400'"
                                    >
                                        {{ notification.icon || 'notifications' }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1.5">
                                        <h4
                                            :class="[
                                                'm-0 text-sm leading-tight font-sans',
                                                isUnread(notification.id) ? 'font-extrabold text-navy' : 'font-bold text-secondary',
                                            ]"
                                        >
                                            {{ notification.title }}
                                        </h4>
                                        <span class="text-[10px] text-gray-400 whitespace-nowrap font-bold shrink-0 mt-0.5">{{
                                            formatDate(notification.created_at)
                                        }}</span>
                                    </div>
                                    <p :class="['m-0 text-xs leading-relaxed', isUnread(notification.id) ? 'text-navy' : 'text-secondary']">{{ notification.body }}</p>
                                    <Link
                                        v-if="notification.action_route && getNotificationLink(notification.action_route)"
                                        :href="getNotificationLink(notification.action_route)!"
                                        class="inline-flex items-center gap-1 mt-2.5 text-xs font-bold text-forest hover:text-forest-dark transition-colors"
                                        @click="notificationsOpen = false"
                                    >
                                        {{ notification.action_label || 'View details' }}
                                        <i class="pi pi-arrow-right text-[10px]"></i>
                                    </Link>
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
