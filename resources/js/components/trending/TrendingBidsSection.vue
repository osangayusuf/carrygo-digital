<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BidCard from '@/components/cards/BidCard.vue';
import AppPaginator from '@/components/layout/AppPaginator.vue';
import { trending } from '@/routes/index';
import type { Bid, LengthAwarePaginator } from '@/types/auction';

const props = defineProps<{
    bids: LengthAwarePaginator<Bid>;
    categories: string[];
    userPoints: number | null;
    search?: string | null;
    category?: string | null;
    sort?: string | null;
}>();

const sort = ref(props.sort ?? 'recent');
const category = ref(props.category ?? '');
const showFilters = ref(false);

const sortOptions: { value: string; label: string }[] = [
    { value: 'recent', label: 'Most Recent Activity' },
    { value: 'closing_soon', label: 'Closing Soon' },
    { value: 'popular', label: 'Most Popular' },
    { value: 'price', label: 'Value: High to Low' },
    { value: 'new', label: 'Recently Added' },
];

const sortLabel = computed(
    () =>
        sortOptions.find((option) => option.value === sort.value)?.label ??
        'Sort By',
);

function toggleClosingSoon(): void {
    if (sort.value === 'closing_soon') {
        sort.value = 'recent';
    } else {
        sort.value = 'closing_soon';
    }

    visit({ page: 1 });
}

const activeFilters = computed(() => {
    const chips: { key: string; label: string }[] = [];

    if (props.search?.trim()) {
        chips.push({ key: 'search', label: `Search: ${props.search.trim()}` });
    }

    if (category.value) {
        chips.push({ key: 'category', label: `Category: ${category.value}` });
    }

    return chips;
});

function visit(extra: Record<string, string | number> = {}): void {
    const query: Record<string, string | number> = {
        ...(props.search?.trim() ? { search: props.search.trim() } : {}),
        ...(sort.value !== 'recent' ? { sort: sort.value } : {}),
        ...(category.value ? { category: category.value } : {}),
        ...extra,
    };

    router.get(
        trending.url({ query }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function onSortChange(): void {
    visit({ page: 1 });
}

function setCategory(value: string): void {
    category.value = value;
    showFilters.value = false;
    visit({ page: 1 });
}

function removeFilter(key: string): void {
    if (key === 'category') {
        category.value = '';
        visit({ page: 1 });
    }

    if (key === 'search') {
        router.get(
            trending.url({
                query: {
                    ...(sort.value !== 'recent' ? { sort: sort.value } : {}),
                    ...(category.value ? { category: category.value } : {}),
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
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
            >
                <div class="space-y-2">
                    <span
                        class="text-xs font-bold tracking-widest text-muted-green uppercase"
                        >Curated Selection</span
                    >
                    <h1
                        class="font-condensed text-4xl font-extrabold tracking-tight text-ink md:text-5xl"
                    >
                        Trending Bids
                    </h1>
                </div>

                <div class="flex w-full flex-col gap-3 sm:flex-row md:w-auto">
                    <div class="relative min-w-[220px]">
                        <div
                            class="flex cursor-pointer items-center justify-between rounded-xl border-2 border-sage-border bg-white px-5 py-3 transition-colors hover:border-lemon"
                        >
                            <span class="mr-4 text-sm font-bold text-ink">{{
                                sortLabel
                            }}</span>
                            <span
                                class="material-symbols-outlined text-sm text-muted-green"
                                >expand_more</span
                            >
                        </div>
                        <select
                            v-model="sort"
                            class="absolute inset-0 w-full cursor-pointer appearance-none opacity-0"
                            @change="onSortChange"
                        >
                            <option
                                v-for="option in sortOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-center rounded-xl border-2 px-5 py-3 whitespace-nowrap transition-colors sm:w-auto"
                        :class="
                            sort === 'closing_soon'
                                ? 'animate-pulse-subtle border-navy bg-navy font-extrabold text-lemon shadow-sm'
                                : 'border-sage-border bg-white font-bold text-ink hover:border-lemon'
                        "
                        @click="toggleClosingSoon"
                    >
                        <span class="text-sm">Closing Soon ⏳</span>
                    </button>

                    <div class="relative">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center rounded-xl border-2 px-5 py-3 transition-colors sm:w-auto"
                            :class="
                                activeFilters.some(
                                    (filter) => filter.key === 'category',
                                )
                                    ? 'border-navy bg-navy text-lemon'
                                    : 'border-sage-border bg-white text-ink hover:border-lemon'
                            "
                            @click="showFilters = !showFilters"
                        >
                            <span class="material-symbols-outlined mr-2 text-sm"
                                >tune</span
                            >
                            <span class="text-sm font-bold">Filters</span>
                            <span
                                v-if="category"
                                class="ml-2 flex h-5 w-5 items-center justify-center rounded-full bg-lemon/30 text-[10px] font-black text-lemon"
                            >
                                1
                            </span>
                        </button>

                        <div
                            v-if="showFilters"
                            class="absolute top-full right-0 z-20 mt-2 w-64 overflow-hidden rounded-xl border-2 border-sage-border bg-white shadow-xl"
                        >
                            <div class="p-3">
                                <p
                                    class="mb-2 px-2 text-[10px] font-black tracking-widest text-muted-green uppercase"
                                >
                                    Category
                                </p>
                                <div class="max-h-48 overflow-y-auto">
                                    <button
                                        type="button"
                                        class="mb-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition-colors"
                                        :class="
                                            category === ''
                                                ? 'bg-navy text-lemon'
                                                : 'text-ink hover:bg-sage-bg'
                                        "
                                        @click="setCategory('')"
                                    >
                                        <span
                                            class="material-symbols-outlined text-base"
                                        >
                                            {{
                                                category === ''
                                                    ? 'radio_button_checked'
                                                    : 'radio_button_unchecked'
                                            }}
                                        </span>
                                        All Categories
                                    </button>
                                    <button
                                        v-for="itemCategory in props.categories"
                                        :key="itemCategory"
                                        type="button"
                                        class="mb-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition-colors"
                                        :class="
                                            category === itemCategory
                                                ? 'bg-navy text-lemon'
                                                : 'text-ink hover:bg-sage-bg'
                                        "
                                        @click="setCategory(itemCategory)"
                                    >
                                        <span
                                            class="material-symbols-outlined text-base"
                                        >
                                            {{
                                                category === itemCategory
                                                    ? 'radio_button_checked'
                                                    : 'radio_button_unchecked'
                                            }}
                                        </span>
                                        {{ itemCategory }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="activeFilters.length > 0 || bids.total > 0"
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
                    {{ bids.total }} result{{ bids.total !== 1 ? 's' : '' }}
                </span>
            </div>
        </header>

        <div
            v-if="bids.data.length === 0"
            class="flex flex-col items-center justify-center py-24 text-center"
        >
            <span
                class="material-symbols-outlined mb-4 text-5xl text-muted-green"
                >search_off</span
            >
            <p class="font-condensed text-xl font-extrabold text-ink">
                No trending bids found
            </p>
            <p class="mt-2 text-sm text-muted-green">
                Try adjusting your search or filters.
            </p>
        </div>

        <div
            v-else
            class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4 xl:grid-cols-5"
        >
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
