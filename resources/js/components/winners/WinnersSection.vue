<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppPaginator from '@/components/layout/AppPaginator.vue';
import { getDaysAgo, maskedMsisdnParts } from '@/lib/utils';
import { winners as winnersRoute } from '@/routes/index';
import type { LengthAwarePaginator, WinnerListing } from '@/types/auction';

const props = defineProps<{
    winners: LengthAwarePaginator<WinnerListing>;
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

    router.get(winnersRoute.url({ query }), {}, { preserveState: true, preserveScroll: true, replace: true });
}

function removeFilter(key: string): void {
    if (key === 'search') {
        router.get(winnersRoute.url({ query: { page: 1 } }), {}, { preserveState: true, preserveScroll: true, replace: true });
    }
}

function goToPage(page: number): void {
    visit({ page });
}

function winnerMsisdn(msisdn: string): { prefix: string; suffix: string } {
    return maskedMsisdnParts(msisdn);
}
</script>

<template>
    <section class="mx-auto max-w-[1300px] px-4 pt-8 pb-20">
        <header class="mb-10">
            <div class="space-y-2">
                <span class="text-xs font-bold uppercase tracking-widest text-muted-green">Success Stories</span>
                <h1 class="font-condensed text-4xl font-extrabold tracking-tight text-ink md:text-5xl">Winners</h1>
                <p class="text-sm text-muted-green">Verified winners from completed auctions on CarryGo.</p>
            </div>

            <div v-if="activeFilters.length > 0 || winners.total > 0" class="mt-5 flex flex-wrap items-center gap-3">
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
                    {{ winners.total }} winner{{ winners.total !== 1 ? 's' : '' }}
                </span>
            </div>
        </header>

        <div v-if="winners.data.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined mb-4 text-5xl text-muted-green">emoji_events</span>
            <p class="font-condensed text-xl font-extrabold text-ink">No winners yet</p>
            <p class="mt-2 text-sm text-muted-green">Check back after the next auctions close.</p>
        </div>

        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <article
                v-for="winner in winners.data"
                :key="winner.id"
                class="flex flex-col overflow-hidden rounded-xl border-2 border-sage-border bg-white shadow-sm transition-all hover:border-lemon hover:-translate-y-0.5"
            >
                <div class="relative h-44 bg-sage-bg p-3">
                    <img
                        v-if="winner.bid?.image"
                        :src="winner.bid.image"
                        :alt="winner.bid.name"
                        class="h-full w-full rounded-lg object-cover"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center rounded-lg bg-sage-bg text-muted-green"
                    >
                        <span class="pi pi-image text-4xl"></span>
                    </div>
                    <span
                        class="absolute top-5 left-5 rounded-md bg-lemon px-2 py-1 text-[10px] font-black uppercase tracking-wider text-navy shadow"
                    >
                        Won Bid
                    </span>
                </div>

                <div class="flex flex-1 flex-col p-4">
                    <h2 class="mb-1 line-clamp-2 font-extrabold text-ink">{{ winner.bid?.name ?? 'Luxury Item' }}</h2>
                    <p class="mb-3 text-sm font-bold text-forest">{{ winner.winner_name }}</p>
                    <p v-if="winnerMsisdn(winner.msisdn).suffix" class="mb-4 text-xs font-bold text-ink">
                        {{ winnerMsisdn(winner.msisdn).prefix }}
                        <span class="text-muted-green">***</span>
                        {{ winnerMsisdn(winner.msisdn).suffix }}
                    </p>

                    <div class="mb-4 grid grid-cols-3 gap-2 rounded-lg border border-sage-border bg-sage-bg p-3 text-center">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-muted-green">Winning</p>
                            <p class="text-sm font-black text-ink">{{ winner.winning_pts.toLocaleString() }} pts</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-muted-green">Total Bid</p>
                            <p class="text-sm font-black text-ink">{{ winner.total_pts_bid.toLocaleString() }} pts</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-muted-green">Bids</p>
                            <p class="text-sm font-black text-ink">{{ winner.bid_count }}</p>
                        </div>
                    </div>

                    <div class="mt-auto flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-muted-green">{{ getDaysAgo(winner.created_at) }}</span>
                        <Link
                            v-if="winner.bid?.url"
                            :href="winner.bid.url"
                            class="rounded-md bg-navy px-3 py-1.5 text-xs font-extrabold text-lemon transition-colors hover:bg-forest"
                        >
                            View Auction
                        </Link>
                    </div>
                </div>
            </article>
        </div>

        <AppPaginator :current-page="winners.current_page" :last-page="winners.last_page" @page-change="goToPage" />
    </section>
</template>
