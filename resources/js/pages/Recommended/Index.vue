<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BidCard from '@/components/cards/BidCard.vue';
import AppPaginator from '@/components/layout/AppPaginator.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { Bid, LengthAwarePaginator } from '@/types/auction';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    bids: LengthAwarePaginator<Bid>;
    categories: string[];
    userPoints: number | null;
    search?: string | null;
}>();

const showFilters = ref(false);

const activeFilters = computed(() => {
    const chips: { key: string; label: string }[] = [];

    if (props.search?.trim()) {
        chips.push({ key: 'search', label: `Search: ${props.search.trim()}` });
    }

    return chips;
});

function visit(extra: Record<string, string | number> = {}): void {
    const query: Record<string, string | number> = {
        ...(props.search?.trim() ? { search: props.search.trim() } : {}),
        ...extra,
    };

    router.get(
        '/recommended',
        query,
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function removeFilter(key: string): void {
    if (key === 'search') {
        router.get(
            '/recommended',
            {
                page: 1,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }
}

function goToPage(page: number): void {
    visit({ page });
}
</script>

<template>
    <Head title="Recommended for You" />

    <section class="mx-auto max-w-[1300px] px-4 pt-8 pb-20">
        <header class="mb-10">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-widest text-muted-green">Personalized Picks</span>
                    <h1 class="font-condensed text-4xl font-extrabold tracking-tight text-ink md:text-5xl">
                        Recommended for You
                    </h1>
                </div>

                <div v-if="activeFilters.length > 0 || bids.total > 0" class="flex items-center gap-3">
                    <span class="text-xs font-bold text-muted-green">
                        {{ bids.total }} recommended item{{ bids.total !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <div v-if="activeFilters.length > 0" class="mt-5 flex flex-wrap items-center gap-3">
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
            </div>
        </header>

        <div v-if="bids.data.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined mb-4 text-5xl text-muted-green">recommend</span>
            <p class="font-condensed text-xl font-extrabold text-ink">No recommendations found</p>
            <p class="mt-2 text-sm text-muted-green">Please check back later or browse other active auctions.</p>
        </div>

        <div v-else class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4 xl:grid-cols-5">
            <BidCard
                v-for="bid in bids.data"
                :key="bid.id"
                :bid="bid"
                :user-points="props.userPoints"
            />
        </div>

        <AppPaginator
            :current-page="bids.current_page"
            :last-page="bids.last_page"
            @page-change="goToPage"
        />
    </section>
</template>
