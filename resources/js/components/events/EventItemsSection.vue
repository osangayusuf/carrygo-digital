<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BidCard from '@/components/cards/BidCard.vue';
import AppPaginator from '@/components/layout/AppPaginator.vue';
import { eventItems } from '@/routes/index';
import type { Bid, LengthAwarePaginator } from '@/types/auction';

const props = defineProps<{
    bids: LengthAwarePaginator<Bid>;
    userPoints: number | null;
    search?: string | null;
    sort?: string | null;
    status?: string | null;
}>();

const sort = ref(props.sort ?? 'recent');
const status = ref(props.status ?? '');
const showFilters = ref(false);

const sortOptions: { value: string; label: string }[] = [
    { value: 'recent', label: 'Most Recent Activity' },
    { value: 'popular', label: 'Most Popular' },
    { value: 'price', label: 'Value: High to Low' },
];

const statusOptions: { value: string; label: string }[] = [
    { value: '', label: 'All' },
    { value: 'live', label: 'Live' },
    { value: 'upcoming', label: 'Upcoming' },
];

const sortLabel = computed(
    () => sortOptions.find((option) => option.value === sort.value)?.label ?? 'Sort By',
);

const activeFilters = computed(() => {
    const chips: { key: string; label: string }[] = [];

    if (props.search?.trim()) {
        chips.push({ key: 'search', label: `Search: ${props.search.trim()}` });
    }

    if (status.value === 'live') {
        chips.push({ key: 'status', label: 'Status: Live' });
    }

    if (status.value === 'upcoming') {
        chips.push({ key: 'status', label: 'Status: Upcoming' });
    }

    return chips;
});

function visit(extra: Record<string, string | number> = {}): void {
    const query: Record<string, string | number> = {
        ...(props.search?.trim() ? { search: props.search.trim() } : {}),
        ...(sort.value !== 'recent' ? { sort: sort.value } : {}),
        ...(status.value ? { status: status.value } : {}),
        ...extra,
    };

    router.get(eventItems.url({ query }), {}, { preserveState: true, preserveScroll: true, replace: true });
}

function onSortChange(): void {
    visit({ page: 1 });
}

function setStatus(value: string): void {
    status.value = value;
    showFilters.value = false;
    visit({ page: 1 });
}

function removeFilter(key: string): void {
    if (key === 'status') {
        status.value = '';
        visit({ page: 1 });
    }

    if (key === 'search') {
        router.get(
            eventItems.url({
                query: {
                    ...(sort.value !== 'recent' ? { sort: sort.value } : {}),
                    ...(status.value ? { status: status.value } : {}),
                    page: 1,
                },
            }),
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
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-widest text-muted-green">Curated Selection</span>
                    <h1 class="font-condensed text-4xl font-extrabold tracking-tight text-ink md:text-5xl">Event Items</h1>
                </div>

                <div class="flex w-full flex-col gap-3 sm:flex-row md:w-auto">
                    <div class="relative min-w-[220px]">
                        <div
                            class="flex cursor-pointer items-center justify-between rounded-xl border-2 border-sage-border bg-white px-5 py-3 transition-colors hover:border-lemon"
                        >
                            <span class="mr-4 text-sm font-bold text-ink">{{ sortLabel }}</span>
                            <span class="material-symbols-outlined text-sm text-muted-green">expand_more</span>
                        </div>
                        <select
                            v-model="sort"
                            class="absolute inset-0 w-full cursor-pointer appearance-none opacity-0"
                            @change="onSortChange"
                        >
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div class="relative">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center rounded-xl border-2 px-5 py-3 transition-colors sm:w-auto"
                            :class="
                                status
                                    ? 'border-navy bg-navy text-lemon'
                                    : 'border-sage-border bg-white text-ink hover:border-lemon'
                            "
                            @click="showFilters = !showFilters"
                        >
                            <span class="material-symbols-outlined mr-2 text-sm">tune</span>
                            <span class="text-sm font-bold">Filters</span>
                            <span
                                v-if="status"
                                class="ml-2 flex h-5 w-5 items-center justify-center rounded-full bg-lemon/30 text-[10px] font-black"
                            >
                                1
                            </span>
                        </button>

                        <div
                            v-if="showFilters"
                            class="absolute top-full right-0 z-20 mt-2 w-64 overflow-hidden rounded-xl border-2 border-sage-border bg-white shadow-xl"
                        >
                            <div class="p-3">
                                <p class="mb-2 px-2 text-[10px] font-black uppercase tracking-widest text-muted-green">
                                    Status
                                </p>
                                <button
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    type="button"
                                    class="mb-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition-colors"
                                    :class="
                                        status === option.value
                                            ? 'bg-navy text-lemon'
                                            : 'text-ink hover:bg-sage-bg'
                                    "
                                    @click="setStatus(option.value)"
                                >
                                    <span class="material-symbols-outlined text-base">
                                        {{
                                            status === option.value
                                                ? 'radio_button_checked'
                                                : 'radio_button_unchecked'
                                        }}
                                    </span>
                                    {{ option.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeFilters.length > 0 || bids.total > 0" class="mt-5 flex flex-wrap items-center gap-3">
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
                    {{ bids.total }} result{{ bids.total !== 1 ? 's' : '' }}
                </span>
            </div>
        </header>

        <div v-if="bids.data.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined mb-4 text-5xl text-muted-green">event_busy</span>
            <p class="font-condensed text-xl font-extrabold text-ink">No event items are currently live.</p>
            <p class="mt-2 text-sm text-muted-green">Try adjusting your search or filters.</p>
        </div>

        <div v-else class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4 xl:grid-cols-5">
            <BidCard v-for="bid in bids.data" :key="bid.id" :bid="bid" :user-points="props.userPoints" />
        </div>

        <AppPaginator :current-page="bids.current_page" :last-page="bids.last_page" @page-change="goToPage" />
    </section>
</template>
