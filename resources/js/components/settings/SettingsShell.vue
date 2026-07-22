<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { profile, wallet } from '@/routes/index';
import { leaderboard } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

const navItems = [
    { label: 'Profile', href: profile(), icon: 'person' },
    { label: 'Wallet', href: wallet(), icon: 'account_balance_wallet' },
    { label: 'Security', href: editSecurity(), icon: 'security' },
    { label: 'Leaderboard', href: leaderboard(), icon: 'leaderboard' },
] as const;

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <div
        class="mx-auto grid w-full max-w-7xl grid-cols-1 gap-8 px-4 py-8 md:grid-cols-12 md:px-6"
    >
        <aside class="md:col-span-3">
            <div
                class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
            >
                <div
                    class="border-b border-outline-variant bg-surface-container-low p-4"
                >
                    <h2 class="text-lg font-bold text-on-surface">Settings</h2>
                </div>
                <nav class="flex flex-col gap-1 p-2">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg p-3 text-sm transition-colors"
                        :class="
                            isCurrentUrl(item.href)
                                ? 'bg-secondary-container font-bold text-on-secondary-container'
                                : 'text-on-surface-variant hover:bg-surface-container'
                        "
                    >
                        <span class="material-symbols-outlined">{{
                            item.icon
                        }}</span>
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>
            </div>
        </aside>

        <section class="md:col-span-9">
            <slot />
        </section>
    </div>
</template>
