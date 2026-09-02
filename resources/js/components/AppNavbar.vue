<script setup lang="ts">
import { Link, router, useHttp, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRealtimeNotifications } from '@/composables/useRealtimeNotifications';
import { useTriggeredAuctionBanner } from '@/composables/useTriggeredAuctionBanner';
import { formatDate, getRemainingTimeWithSeconds } from '@/lib/utils';
import { dashboard as adminDashboard } from '@/routes/admin/index';
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

const notifications = ref<Notification[]>([
    ...((page.props.notifications as Notification[]) ?? []),
]);
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
        const data = (await http.submit(
            notificationsRoutes.feed.get(),
        )) as Notification[];
        notifications.value = data;
    } catch {
        // Keep existing list if refresh fails.
    } finally {
        isRefreshingNotifications.value = false;
    }
}

const unreadCount = computed(
    () => notifications.value.filter((n) => isUnread(n)).length,
);

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

    if (
        panel &&
        !panel.contains(event.target as Node) &&
        button &&
        !button.contains(event.target as Node)
    ) {
        notificationsOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', closeNotifications);

    router.on('success', () => {
        if (currentUser.value) {
            console.log(currentUser.value);
            refreshNotifications();
        }
    });
});

onBeforeUnmount(() =>
    document.removeEventListener('click', closeNotifications),
);

const search = ref(
    new URLSearchParams(
        typeof window !== 'undefined' ? window.location.search : '',
    ).get('search') ?? '',
);

function visit(extra: Record<string, string | number> = {}) {
    const currentParams = new URLSearchParams(
        typeof window !== 'undefined' ? window.location.search : '',
    );
    const existing: Record<string, string> = {};
    currentParams.forEach((value, key) => {
        existing[key] = value;
    });

    router.get(
        currentPath.value,
        {
            ...existing,
            ...(search.value
                ? { search: search.value }
                : { search: undefined }),
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
    {
        label: 'Earn Free Points',
        href: tasks.url(),
        icon: 'pi pi-check-square',
    },
    {
        label: 'How to play',
        href: howToPlay.url(),
        icon: 'pi pi-question-circle',
    },
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

const {
    liveAuctions: bannerAuctions,
    activeAuction: activeTriggeredAuction,
    activeExpiresAt: bannerExpiresAt,
    activeIndex: bannerIndex,
    hasMultiple: hasMultipleBannerAuctions,
    pause: pauseBanner,
    resume: resumeBanner,
    goTo: goToBannerSlide,
} = useTriggeredAuctionBanner();

const bannerTimeLeft = computed(() =>
    bannerExpiresAt.value
        ? getRemainingTimeWithSeconds(bannerExpiresAt.value)
        : '',
);
</script>

<template>
    <nav class="glass-nav sticky top-0 z-50 shadow-sm dark:shadow-none">
        <!-- Persistent Countdown Banner -->
        <div
            v-if="activeTriggeredAuction"
            class="relative overflow-hidden bg-red-600 text-white"
            role="region"
            aria-label="Auctions closing soon"
            :aria-roledescription="
                hasMultipleBannerAuctions ? 'carousel' : undefined
            "
            @mouseenter="pauseBanner"
            @mouseleave="resumeBanner"
            @focusin="pauseBanner"
            @focusout="resumeBanner"
        >
            <Transition name="banner-slide">
                <div
                    :key="activeTriggeredAuction.id"
                    class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 px-4 py-2 text-center text-xs font-bold"
                >
                    <div class="flex items-center justify-center gap-1.5">
                        <span>
                            Closing Soon:
                            <strong class="text-lemon">{{
                                activeTriggeredAuction.name
                            }}</strong>
                            is ending soon!
                        </span>
                    </div>
                    <div class="flex items-center justify-center gap-3">
                        <span
                            class="flex items-center gap-1 rounded border border-white/15 bg-black/35 px-2 py-0.5 font-mono text-[11px] font-black tracking-wide shadow-inner"
                        >
                            <i class="pi pi-hourglass text-sm"></i>
                            <span>{{ bannerTimeLeft }}</span>
                        </span>
                        <Link
                            :href="`/auctions/${activeTriggeredAuction.id}`"
                            class="flex items-center gap-0.5 rounded px-1 font-extrabold underline transition-colors hover:text-lemon focus:ring-2 focus:ring-white focus:outline-none"
                            :aria-label="`Bid Now on ${activeTriggeredAuction.name}`"
                            :title="`Bid Now on ${activeTriggeredAuction.name}`"
                        >
                            Bid Now
                            <i class="pi pi-arrow-right text-[10px]"></i>
                        </Link>
                    </div>
                </div>
            </Transition>

            <!-- Slide indicators, only when there is more than one open auction -->
            <div
                v-if="hasMultipleBannerAuctions"
                class="flex items-center justify-center gap-1.5 pb-1.5"
            >
                <button
                    v-for="(auction, index) in bannerAuctions"
                    :key="auction.id"
                    type="button"
                    class="h-1.5 rounded-full transition-all duration-300 focus:ring-2 focus:ring-white focus:outline-none"
                    :class="
                        index === bannerIndex
                            ? 'w-4 bg-white'
                            : 'w-1.5 bg-gray-400 hover:bg-gray-300'
                    "
                    :aria-label="`Show ${auction.name}`"
                    :aria-current="index === bannerIndex"
                    @click="goToBannerSlide(index)"
                />
            </div>
        </div>

        <!-- NAVBAR -->
        <nav class="border-b-2 border-lemon bg-white shadow-md">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-3 gap-y-2 px-4 py-2 sm:py-1.5 md:gap-x-10"
            >
                <Link
                    :href="home.url()"
                    class="order-1 flex h-12 shrink-0 items-center overflow-hidden no-underline focus:ring-2 focus:ring-primary focus:outline-none"
                    aria-label="Bidora Home"
                    title="Go to Bidora Home"
                >
                    <img
                        class="block h-auto w-30 max-w-none object-cover"
                        :src="`${$page.props.asset_url}logo.png`"
                        alt="Bidora"
                    />
                </Link>
                <!-- Search: full-width second row on mobile, inline flex-1 on md+ -->
                <div
                    class="order-3 flex w-full min-w-0 md:order-2 md:w-auto md:flex-1"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search luxury items, brands, auctions..."
                        class="min-w-0 flex-1 rounded-l-xl border-2 border-r-0 border-forest px-3.5 py-1.5 font-sans text-xs outline-none focus:ring-2 focus:ring-primary focus:outline-none sm:py-2 sm:text-sm"
                        title="Search luxury items, brands, auctions"
                        aria-label="Search luxury items, brands, auctions"
                        @input="onSearch"
                    />
                    <button
                        type="button"
                        class="cursor-pointer rounded-r-xl border-none bg-forest px-4.5 py-2 text-sm font-bold whitespace-nowrap text-lemon hover:bg-forest-dark focus:ring-2 focus:ring-primary focus:outline-none"
                        aria-label="Submit search"
                        title="Submit search"
                        @click="onSearch"
                    >
                        <i class="pi pi-search"></i>
                    </button>
                </div>
                <div
                    class="relative order-2 ml-auto flex items-center gap-2 md:order-3 md:ml-0 md:gap-5"
                >
                    <button
                        id="notification-btn"
                        class="relative cursor-pointer rounded-lg border-none bg-transparent p-1.5 focus:ring-2 focus:ring-primary focus:outline-none"
                        aria-label="Notifications"
                        title="Toggle notifications panel"
                        @click="toggleNotifications"
                    >
                        <i
                            class="pi pi-bell text-base text-muted-green transition-colors hover:text-navy sm:text-xl"
                        ></i>
                        <div
                            v-if="unreadCount > 0"
                            class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-ink text-[10px] font-extrabold text-lemon"
                        >
                            {{ unreadCount > 9 ? '9+' : unreadCount }}
                        </div>
                    </button>

                    <!-- Notification Panel -->
                    <div
                        v-if="notificationsOpen"
                        id="notification-panel"
                        class="absolute top-full right-0 z-50 mt-2 flex max-h-[80vh] w-80 flex-col overflow-hidden rounded-2xl border-2 border-lemon bg-white shadow-xl sm:w-96 md:-right-2"
                    >
                        <div
                            class="flex items-center justify-between border-b-2 border-lemon bg-gray-50/50 px-4 py-3"
                        >
                            <h3
                                class="m-0 font-headline text-sm font-extrabold text-navy"
                            >
                                Notifications
                            </h3>
                            <button
                                v-if="unreadCount > 0"
                                class="cursor-pointer rounded border-none bg-transparent p-0 text-xs font-bold text-primary transition-colors hover:text-forest focus:ring-2 focus:ring-primary focus:outline-none"
                                aria-label="Mark all notifications as read"
                                title="Mark all notifications as read"
                                @click="markAllRead"
                            >
                                Mark all read
                            </button>
                        </div>
                        <div
                            class="hide-scrollbar flex-1 overflow-x-hidden overflow-y-auto bg-white"
                        >
                            <div
                                v-if="notifications.length === 0"
                                class="flex h-full flex-col items-center justify-center gap-2 p-8 text-center text-sm font-bold text-secondary"
                            >
                                <i
                                    class="pi pi-check-circle text-3xl text-gray-300"
                                ></i>
                                You're all caught up!
                            </div>
                            <div
                                v-for="notification in notifications"
                                :key="notification.id"
                                :class="[
                                    'flex gap-3.5 border-b border-gray-100 px-4 py-4 transition-colors last:border-b-0',
                                    isUnread(notification)
                                        ? 'bg-forest/5'
                                        : 'bg-white hover:bg-gray-50',
                                ]"
                            >
                                <div class="mt-0.5 shrink-0">
                                    <span
                                        class="material-symbols-outlined text-2xl"
                                        :class="
                                            isUnread(notification)
                                                ? 'text-forest'
                                                : 'text-gray-400'
                                        "
                                    >
                                        {{
                                            notification.icon || 'notifications'
                                        }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="mb-1.5 flex items-start justify-between gap-2"
                                    >
                                        <h4
                                            :class="[
                                                'm-0 font-sans text-sm leading-tight',
                                                isUnread(notification)
                                                    ? 'font-extrabold text-navy'
                                                    : 'font-bold text-secondary',
                                            ]"
                                        >
                                            {{ notification.title }}
                                        </h4>
                                        <span
                                            class="mt-0.5 shrink-0 text-[10px] font-bold whitespace-nowrap text-gray-400"
                                            >{{
                                                formatDate(
                                                    notification.created_at,
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <p
                                        :class="[
                                            'm-0 text-xs leading-relaxed',
                                            isUnread(notification)
                                                ? 'text-navy'
                                                : 'text-secondary',
                                        ]"
                                    >
                                        {{ notification.body }}
                                    </p>
                                    <div
                                        class="mt-2.5 flex flex-wrap items-center gap-3"
                                    >
                                        <Link
                                            v-if="notification.url"
                                            :href="notification.url"
                                            class="inline-flex items-center gap-1 rounded text-xs font-bold text-forest transition-colors hover:text-forest-dark focus:ring-2 focus:ring-primary focus:outline-none"
                                            title="View notification details"
                                            @click="
                                                onNotificationNavigate(
                                                    notification,
                                                )
                                            "
                                        >
                                            View details
                                            <i
                                                class="pi pi-arrow-right text-[10px]"
                                            ></i>
                                        </Link>
                                        <button
                                            v-if="isUnread(notification)"
                                            type="button"
                                            class="cursor-pointer rounded border-none bg-transparent p-0 text-xs font-bold text-secondary transition-colors hover:text-navy focus:ring-2 focus:ring-primary focus:outline-none"
                                            aria-label="Mark notification as read"
                                            title="Mark notification as read"
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
                            class="h-10 cursor-pointer rounded-xl border-none bg-lemon px-3 font-sans text-sm font-extrabold whitespace-nowrap text-navy transition-colors hover:bg-amber focus:ring-2 focus:ring-primary focus:outline-none md:h-12 md:px-4.5"
                            aria-label="Log In"
                            title="Log In to your account"
                        >
                            Log In
                        </Link>
                        <Link
                            :href="register.url()"
                            as="button"
                            class="h-10 cursor-pointer rounded-xl border-none bg-navy px-3 font-sans text-sm font-extrabold whitespace-nowrap text-lemon transition-colors hover:bg-forest focus:ring-2 focus:ring-primary focus:outline-none md:h-12 md:px-4.5"
                            aria-label="Register"
                            title="Register a new account"
                        >
                            Register
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="isAdmin"
                            :href="adminDashboard.url()"
                            as="button"
                            class="flex h-10 cursor-pointer items-center gap-1.5 rounded-xl border-none bg-amber px-3 font-sans text-xs font-extrabold whitespace-nowrap text-navy transition-colors hover:bg-lemon focus:ring-2 focus:ring-primary focus:outline-none sm:text-sm md:h-12 md:px-4"
                            aria-label="Admin Dashboard"
                            title="Go to Admin Dashboard"
                        >
                            <i class="pi pi-shield text-sm"></i>
                            <span class="hidden sm:inline">Admin</span>
                        </Link>
                        <Link
                            :href="profile.url()"
                            as="button"
                            class="flex h-10 cursor-pointer items-center gap-1.5 rounded-xl border-none bg-lemon px-3.5 font-sans text-xs font-extrabold whitespace-nowrap text-navy transition-colors hover:bg-amber focus:ring-2 focus:ring-primary focus:outline-none sm:text-sm md:h-12 md:px-4.5"
                            :aria-label="`Wallet: ${currentUser.points_balance ?? 0} points`"
                            title="View profile and wallet balance"
                        >
                            <i class="pi pi-wallet text-sm text-forest"></i>
                            <span class="hidden sm:inline"
                                >{{ currentUser.points_balance ?? 0 }} pts</span
                            >
                            <span class="sm:hidden">{{
                                currentUser.points_balance ?? 0
                            }}</span>
                        </Link>
                        <Link
                            :href="logoutRoute.url()"
                            method="post"
                            as="button"
                            class="hidden h-10 cursor-pointer rounded-xl border-2 border-gray-200 bg-transparent px-3 text-sm font-extrabold whitespace-nowrap text-gray-500 transition-colors hover:border-gray-300 hover:text-navy focus:ring-2 focus:ring-primary focus:outline-none sm:block md:h-12 md:px-4"
                            aria-label="Log Out"
                            title="Log Out of your account"
                        >
                            Log Out
                        </Link>
                        <Link
                            :href="logoutRoute.url()"
                            method="post"
                            as="button"
                            class="h-10 cursor-pointer rounded-xl border-none bg-transparent px-3 text-gray-500 hover:text-navy focus:ring-2 focus:ring-primary focus:outline-none sm:hidden md:h-12 md:px-4"
                            aria-label="Log Out"
                            title="Log Out of your account"
                        >
                            <i class="pi pi-sign-out text-sm sm:text-xl"></i>
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- SUB NAV -->
        <div class="relative bg-navy">
            <!-- Desktop View -->
            <div
                class="hide-scrollbar mx-auto hidden max-w-7xl min-w-full items-center justify-between gap-1 overflow-x-auto md:flex"
            >
                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    :class="[
                        'mx-auto block cursor-pointer rounded px-4 py-2 text-sm whitespace-nowrap text-white no-underline hover:bg-lemon/18 hover:text-white focus:ring-2 focus:ring-primary focus:outline-none',
                        navItemClass(link.href),
                    ]"
                    :aria-label="link.label"
                    :title="link.label"
                >
                    <i v-if="link.icon" :class="[link.icon, 'mr-1']"></i>
                    {{ link.label }}
                </a>
            </div>

            <!-- Mobile View -->
            <div class="flex w-full flex-col md:hidden">
                <div class="flex w-full items-center justify-between px-1">
                    <a
                        v-for="link in firstLineLinks"
                        :key="link.label"
                        :href="link.href"
                        :class="[
                            'flex-1 cursor-pointer rounded px-1 py-2 text-center text-[11px] whitespace-nowrap text-white no-underline hover:bg-lemon/18 hover:text-white focus:ring-2 focus:ring-primary focus:outline-none sm:text-xs',
                            navItemClass(link.href),
                        ]"
                        :aria-label="link.label"
                        :title="link.label"
                    >
                        {{ link.label }}
                    </a>
                </div>
                <div
                    class="flex w-full items-center justify-between border-t border-white/10 bg-navy/90 px-1"
                >
                    <a
                        v-for="link in secondLineLinks"
                        :key="link.label"
                        :href="link.href"
                        :class="[
                            'flex-1 cursor-pointer rounded px-1 py-2 text-center text-[11px] whitespace-nowrap text-white no-underline hover:bg-lemon/18 hover:text-white focus:ring-2 focus:ring-primary focus:outline-none sm:text-xs',
                            navItemClass(link.href),
                        ]"
                        :aria-label="link.label"
                        :title="link.label"
                    >
                        {{ link.label }}
                    </a>
                </div>
            </div>
        </div>
        <div
            class="absolute bottom-0 h-px w-full bg-linear-to-r from-transparent via-primary/20 to-transparent"
        />
    </nav>
</template>

<style scoped>
/* Horizontal slide for the "closing soon" strip as it rotates between auctions. */
.banner-slide-enter-active,
.banner-slide-leave-active {
    transition:
        transform 0.45s cubic-bezier(0.4, 0, 0.2, 1),
        opacity 0.35s ease;
}

.banner-slide-enter-from {
    transform: translateX(100%);
    opacity: 0;
}

.banner-slide-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}

/* Overlay the outgoing slide so the strip never collapses mid-transition. */
.banner-slide-leave-active {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
}

@media (prefers-reduced-motion: reduce) {
    .banner-slide-enter-active,
    .banner-slide-leave-active {
        transition: opacity 0.2s ease;
    }

    .banner-slide-enter-from,
    .banner-slide-leave-to {
        transform: none;
        opacity: 0;
    }
}
</style>
