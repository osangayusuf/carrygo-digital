<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { home, trending, profile, tasks } from '@/routes/index';

const page = usePage();
const currentUser = computed(() => page.props.auth?.user ?? null);
const { isCurrentUrl } = useCurrentUrl();

function isActive(href: string): boolean {
    if (href.split('?')[0] === home.url()) {
        return isCurrentUrl(home.url());
    }

    return isCurrentUrl(href, undefined, true);
}

const navItems = computed(() => [
    { label: 'Home', href: home.url(), icon: 'home' },
    { label: 'Trending', href: trending.url(), icon: 'local_fire_department' },
    { label: 'Tasks', href: tasks.url(), icon: 'check_circle' },
    {
        label: currentUser.value ? 'Profile' : 'Sign In',
        href: currentUser.value ? profile.url() : '/login',
        icon: currentUser.value ? 'person' : 'login',
    },
]);
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-40 border-t-2 border-lemon bg-navy md:hidden"
    >
        <div class="flex items-stretch justify-around">
            <Link
                v-for="item in navItems"
                :key="item.label"
                :href="item.href"
                :class="[
                    'relative flex flex-1 flex-col items-center gap-0.5 py-2 no-underline transition-colors active:bg-lemon/10',
                    isActive(item.href) ? 'text-lemon' : 'text-white/50',
                ]"
            >
                <span
                    :class="[
                        'flex h-7 w-7 items-center justify-center rounded-lg transition-colors',
                        isActive(item.href) ? 'bg-lemon/20' : '',
                    ]"
                >
                    <span class="material-symbols-outlined text-xl">{{
                        item.icon
                    }}</span>
                </span>
                <span
                    :class="[
                        'text-[10px] tracking-wide',
                        isActive(item.href) ? 'font-black' : 'font-bold',
                    ]"
                    >{{ item.label }}</span
                >
            </Link>
        </div>
    </nav>
</template>
