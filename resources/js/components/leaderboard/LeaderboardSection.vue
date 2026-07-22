<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import LeaderboardBidCard from '@/components/cards/LeaderboardBidCard.vue';
import AppPaginator from '@/components/layout/AppPaginator.vue';
import { leaderboard } from '@/routes/index';
import type { LeaderboardAuction, LengthAwarePaginator } from '@/types/auction';

const props = defineProps<{
    auctions: LengthAwarePaginator<LeaderboardAuction>;
    search?: string | null;
}>();

const activeFilters = computed(() => {
    if (!props.search?.trim()) {
        return [];
    }

    return [{ key: 'search', label: `Search: ${props.search.trim()}` }];
});

function visit(extra: Record<string, string | number> = {}): void {
    const query: Record<string, string | number> = {
        ...(props.search?.trim() ? { search: props.search.trim() } : {}),
        ...extra,
    };

    router.get(
        leaderboard.url({ query }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function removeFilter(key: string): void {
    if (key === 'search') {
        router.get(
            leaderboard.url({ query: { page: 1 } }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }
}

function goToPage(page: number): void {
    visit({ page });
}
</script>

<template>
    <section class="mx-auto max-w-[1300px] px-4 pt-8 pb-20">
        <header class="mb-10">
            <div class="space-y-2">
                <span
                    class="text-xs font-bold tracking-widest text-muted-green uppercase"
                    >Top Bidders</span
                >
                <h1
                    class="font-condensed text-4xl font-extrabold tracking-tight text-ink md:text-5xl"
                >
                    Leaderboard
                </h1>
                <p class="text-sm text-muted-green">
                    Top bidders per live auction, ranked by total points bid on
                    each item.
                </p>
            </div>

            <div
                v-if="activeFilters.length > 0 || auctions.total > 0"
                class="mt-5 flex flex-wrap items-center gap-3"
            >
                <div
                    v-for="filter in activeFilters"
                    :key="filter.key"
                    class="flex items-center gap-2 rounded-full border-2 border-sage-border bg-sage-bg px-4 py-2 text-xs font-bold text-ink"
                >
                    <span>{{ filter.label }}</span>
                    <button
                        type="button"
                        class="material-symbols-outlined cursor-pointer text-sm text-muted-green hover:text-navy"
                        @click="removeFilter(filter.key)"
                    >
                        close
                    </button>
                </div>

                <span class="ml-auto text-xs font-bold text-muted-green">
                    {{ auctions.total }} item{{
                        auctions.total !== 1 ? 's' : ''
                    }}
                </span>
            </div>
        </header>

        <div
            v-if="auctions.data.length === 0"
            class="flex flex-col items-center justify-center py-24 text-center"
        >
            <span
                class="material-symbols-outlined mb-4 text-5xl text-muted-green"
                >leaderboard</span
            >
            <p class="font-condensed text-xl font-extrabold text-ink">
                No live auctions
            </p>
            <p class="mt-2 text-sm text-muted-green">
                Leaderboards appear when auctions are open for bidding.
            </p>
        </div>

        <div
            v-else
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <LeaderboardBidCard
                v-for="auction in auctions.data"
                :key="auction.id"
                :bid="auction"
            />
        </div>

        <AppPaginator
            :current-page="auctions.current_page"
            :last-page="auctions.last_page"
            @page-change="goToPage"
        />
    </section>
</template>
