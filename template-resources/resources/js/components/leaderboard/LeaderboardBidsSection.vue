<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import LeaderboardBidCard from '@/components/cards/LeaderboardBidCard.vue';
import AppPaginator from '@/components/layout/AppPaginator.vue';
import type { LeaderboardBid } from '@/pages/LeaderboardBids.vue';
import { leaderboard } from '@/routes';
import type { LengthAwarePaginator } from '@/types';

defineProps<{
    bids: LengthAwarePaginator<LeaderboardBid>;
}>();

function visit(extra: Record<string, string | number> = {}) {
    const currentSearch = new URLSearchParams(window.location.search).get('search') ?? '';
    router.get(
        leaderboard.url({
            query: {
                ...(currentSearch ? { search: currentSearch } : {}),
                ...extra,
            },
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function goToPage(page: number): void {
    visit({ page });
}


</script>

<template>
    <section class="mx-auto max-w-screen-2xl px-6 pt-12 pb-24">
        <!-- Page header -->
        <header class="mb-12">
            <div class="flex flex-col justify-between gap-8 md:flex-row md:items-end">
                <div class="space-y-2">
                    <span class="font-label text-xs font-bold tracking-widest text-secondary uppercase">
                        Active Bidding Wars
                    </span>
                    <h1 class="font-headline text-4xl font-extrabold tracking-tight text-on-surface md:text-5xl">
                        Leaderboard
                    </h1>
                </div>
            </div>

            <div v-if="bids.total > 0" class="mt-6 flex flex-wrap items-center gap-3">
                <span class="ml-auto text-xs font-bold text-outline">
                    {{ bids.total }} active item{{ bids.total !== 1 ? 's' : '' }}
                </span>
            </div>
        </header>

        <!-- Empty state -->
        <div v-if="bids.data.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined mb-4 text-5xl text-outline">equalizer</span>
            <p class="font-headline text-xl font-bold text-on-surface-variant">
                No active bids found
            </p>
            <p class="mt-2 text-sm text-outline">
                Check back soon when more upcoming and live items hit the shelves.
            </p>
        </div>

        <!-- Leaderboard grid -->
        <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4 xl:grid-cols-5">
            <LeaderboardBidCard v-for="bid in bids.data" :key="bid.id" :bid="bid" />
        </div>

        <!-- Pagination -->
        <AppPaginator :current-page="bids.current_page" :last-page="bids.last_page" @page-change="goToPage" />

    </section>
</template>
