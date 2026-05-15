<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Search, Menu, X } from 'lucide-vue-next';
import NotificationBell from '@/components/NotificationBell.vue';
import {
    home,
    login,
    logout,
    register,
    trending,
    openBids,
    eventItems,
    winners,
    leaderboard,
    howToPlay,
    wallet,
    profile,
} from '@/routes/index';

const page = usePage();
const mobileMenuOpen = ref(false);

// Two-row mobile state
const showAllLinks = ref(false);

const navLinks = [
    { name: 'Trending', href: trending.url() },
    { name: 'Open Bids', href: openBids.url() },
    { name: 'Event Items', href: eventItems.url() },
    { name: 'Winners', href: winners.url() },
    { name: 'Wallet', href: wallet.url() },
    { name: 'Leaderboard', href: leaderboard.url() },
    { name: 'How to Play', href: howToPlay.url() },
];

const currentUrl = computed(() => page.url);

function isActive(href: string): boolean {
    return currentUrl.value === href || currentUrl.value.startsWith(href + '?');
}
</script>

<template>
    <header class="bg-forest text-white shadow-md relative z-50">
        <!-- Main Top Bar -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <Link :href="home.url()" class="text-2xl font-bold text-lemon tracking-tight">
                        Carrygo
                    </Link>
                </div>

                <!-- Desktop Search (Center) -->
                <div class="hidden md:flex flex-1 justify-center px-8">
                    <div class="relative w-full max-w-md text-gray-900">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search class="h-5 w-5 text-gray-400" />
                        </div>
                        <input type="text" placeholder="Search auctions..."
                            class="block w-full pl-10 pr-3 py-2 border border-transparent rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-lemon focus:border-white sm:text-sm transition-colors">
                    </div>
                </div>

                <!-- Desktop Right Nav -->
                <div class="hidden md:flex items-center space-x-6">
                    <template v-if="$page.props.auth.user">
                        <!-- Notification Bell -->
                        <NotificationBell />

                        <!-- User Menu -->
                        <div class="flex items-center space-x-3 border-l border-green-800 pl-6">
                            <Link :href="wallet.url()"
                                class="text-sm font-semibold bg-green-900 px-3 py-1.5 rounded-full hover:bg-green-800 transition">
                                {{ $page.props.auth.user.points_balance?.toLocaleString() || '0' }} pts
                            </Link>
                            <Link :href="profile.url()" class="flex items-center hover:opacity-80 transition">
                                <div
                                    class="h-8 w-8 bg-white text-forest rounded-full flex items-center justify-center font-bold text-sm">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                            </Link>
                        </div>
                    </template>
                    <template v-else>
                        <Link :href="login.url()" class="text-sm font-medium text-white hover:text-lemon transition">
                            Log In
                        </Link>
                        <Link :href="register.url()"
                            class="text-sm font-medium bg-lemon text-forest px-4 py-2 rounded-md hover:bg-yellow-400 transition">
                            Sign Up
                        </Link>
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden items-center space-x-4">
                    <button class="text-white hover:text-lemon">
                        <Search class="h-6 w-6" />
                    </button>
                    <template v-if="$page.props.auth.user">
                        <NotificationBell />
                    </template>
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="text-white hover:text-lemon focus:outline-none">
                        <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Desktop Nav Links -->
        <nav class="hidden md:block bg-green-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <ul class="flex space-x-8 h-12 items-center text-sm font-medium">
                    <li>
                        <Link :href="home.url()" class="text-white hover:text-lemon transition"
                            :class="{ 'text-lemon': isActive(home.url()) }">Home</Link>
                    </li>
                    <li v-for="link in navLinks" :key="link.name">
                        <Link :href="link.href" class="text-white hover:text-lemon transition"
                            :class="{ 'text-lemon': isActive(link.href) }">
                            {{ link.name }}
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Mobile Menu Panel -->
        <div v-show="mobileMenuOpen" class="md:hidden bg-forest border-t border-green-800">
            <!-- Mobile Two-Row Layout -->
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <button @click="showAllLinks = !showAllLinks"
                    class="w-full text-left text-white px-3 py-2 rounded-md font-medium text-sm flex justify-between items-center bg-green-900">
                    <span>Menu Navigation</span>
                    <span class="text-xs opacity-70">{{ showAllLinks ? 'Hide' : 'View All' }}</span>
                </button>

                <div class="grid grid-cols-2 gap-2 mt-2">
                    <Link :href="home.url()"
                        class="text-white hover:bg-green-800 hover:text-lemon px-3 py-2 rounded-md text-sm font-medium">
                        Home
                    </Link>
                    <Link v-for="link in navLinks.slice(0, 3)" :key="link.name" :href="link.href"
                        class="text-white hover:bg-green-800 hover:text-lemon px-3 py-2 rounded-md text-sm font-medium">
                        {{ link.name }}
                    </Link>
                </div>

                <div v-show="showAllLinks" class="grid grid-cols-2 gap-2 mt-2 border-t border-green-800 pt-2">
                    <Link v-for="link in navLinks.slice(3)" :key="link.name" :href="link.href"
                        class="text-white hover:bg-green-800 hover:text-lemon px-3 py-2 rounded-md text-sm font-medium">
                        {{ link.name }}
                    </Link>
                </div>
            </div>

            <div class="pt-4 pb-3 border-t border-green-800" v-if="$page.props.auth.user">
                <div class="flex items-center px-5">
                    <div class="shrink-0">
                        <div
                            class="h-10 w-10 bg-white text-forest rounded-full flex items-center justify-center font-bold">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-white">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-lemon">{{
                            $page.props.auth.user.points_balance?.toLocaleString() || '0' }} pts</div>
                    </div>
                </div>
                <div class="mt-3 px-2 space-y-1">
                    <Link :href="profile.url()"
                        class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-lemon hover:bg-green-800">
                        Profile</Link>
                    <Link :href="logout.url()" method="post" as="button"
                        class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-white hover:text-lemon hover:bg-green-800">
                        Log Out</Link>
                </div>
            </div>
            <div class="pt-4 pb-3 border-t border-green-800" v-else>
                <div class="px-5 space-y-2">
                    <Link :href="login.url()"
                        class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-green-900 hover:bg-green-800">
                        Log In</Link>
                    <Link :href="register.url()"
                        class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-forest bg-lemon hover:bg-yellow-400">
                        Sign Up</Link>
                </div>
            </div>
        </div>
    </header>
</template>
