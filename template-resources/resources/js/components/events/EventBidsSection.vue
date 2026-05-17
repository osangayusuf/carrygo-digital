<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BidCard from '@/components/cards/BidCard.vue';
import AppPaginator from '@/components/layout/AppPaginator.vue';
import type { Bid } from '@/pages/Home.vue';
import { events, login } from '@/routes';
import { place } from '@/routes/bids';
import type { LengthAwarePaginator } from '@/types';

const params = new URLSearchParams(window.location.search);

const props = defineProps<{
    bids: LengthAwarePaginator<Bid>;
    userPoints: number | null;
}>();

const sort = ref(params.get('sort') ?? 'recent');
const status = ref(params.get('status') ?? '');
const showFilters = ref(false);

const sortOptions: { value: string; label: string }[] = [
    { value: 'recent', label: 'Most Recent Activity' },
    { value: 'value_desc', label: 'Value: High to Low' },
];

const sortLabel = computed(
    () => sortOptions.find((o) => o.value === sort.value)?.label ?? 'Sort By',
);

const activeFilters = computed(() => {
    const chips: { key: string; label: string }[] = [];

    if (status.value === 'live') {
        chips.push({ key: 'status', label: 'Status: Live' });
    }

    if (status.value === 'upcoming') {
        chips.push({ key: 'status', label: 'Status: Upcoming' });
    }

    return chips;
});

function visit(extra: Record<string, string | number> = {}) {
    const currentSearch = new URLSearchParams(window.location.search).get('search') ?? '';

    router.get(
        events.url({
            query: {
                ...(currentSearch ? { search: currentSearch } : {}),
                ...(sort.value !== 'recent' ? { sort: sort.value } : {}),
                ...(status.value ? { status: status.value } : {}),
                ...extra,
            }
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function onSortChange() {
    visit({ page: 1 });
}

function setStatus(value: string) {
    status.value = value;
    visit({ page: 1 });
}

function removeFilter(key: string) {
    if (key === 'status') {
        status.value = '';
        visit({ page: 1 });
    }
}

function goToPage(page: number): void {
    visit({ page });
}

const page = usePage();
const selectedBid = ref<Bid | null>(null);
const showModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');

const form = useForm({
    points: 0,
});

function openBidModal(bid: Bid) {
    if (!page.props.auth?.user) {
        router.get(login.url());

        return;
    }

    selectedBid.value = bid;
    form.points = props.userPoints ?? 0;
    showModal.value = true;
}

function submitBid() {
    if (!selectedBid.value) {
        return;
    }

    form.post(place.url({ bid: selectedBid.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            toastMessage.value = 'Bid placed successfully!';
            showToast.value = true;

            setTimeout(() => {
                showToast.value = false;
                router.reload();
            }, 5000);
        },
    });
}
</script>

<template>
    <section class="mx-auto max-w-screen-2xl px-6 pt-12 pb-24">
        <!-- Page header -->
        <header class="mb-12">
            <div class="flex flex-col justify-between gap-8 md:flex-row md:items-end">
                <div class="space-y-2">
                    <span class="font-label text-xs font-bold tracking-widest text-secondary uppercase">
                        Curated Selection
                    </span>
                    <h1 class="font-headline text-4xl font-extrabold tracking-tight text-on-surface md:text-5xl">
                        Event Items
                    </h1>
                </div>

                <div class="flex w-full flex-col gap-4 sm:flex-row md:w-auto">
                    <!-- Sort — pill button with invisible select overlay, matches home page -->
                    <div class="relative">
                        <div
                            class="flex cursor-pointer items-center rounded-full bg-surface-container-low px-6 py-3.5 transition-colors hover:bg-surface-container-high">
                            <span class="mr-4 text-sm font-bold text-on-surface-variant">{{ sortLabel }}</span>
                            <span class="material-symbols-outlined text-sm">expand_more</span>
                        </div>
                        <select v-model="sort" class="absolute inset-0 w-full cursor-pointer appearance-none opacity-0"
                            @change="onSortChange">
                            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Filters button with dropdown panel -->
                    <div class="relative">
                        <button type="button"
                            class="flex cursor-pointer items-center rounded-full px-6 py-3.5 transition-colors" :class="activeFilters.length > 0
                                ? 'bg-primary text-on-primary'
                                : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'
                                " @click="showFilters = !showFilters">
                            <span class="material-symbols-outlined mr-2 text-sm">tune</span>
                            <span class="text-sm font-bold">Filters</span>
                            <span v-if="activeFilters.length > 0"
                                class="ml-2 flex h-4 w-4 items-center justify-center rounded-full bg-white/30 text-[10px] font-black">
                                {{ activeFilters.length }}
                            </span>
                        </button>

                        <!-- Filter dropdown panel -->
                        <div v-if="showFilters"
                            class="absolute top-full right-0 z-20 mt-2 w-64 overflow-hidden rounded-2xl border border-surface-container bg-surface-container-lowest shadow-xl shadow-primary/10">
                            <div class="p-3">
                                <p class="mb-2 px-2 text-[10px] font-black tracking-widest text-outline uppercase">
                                    Status
                                </p>
                                <button v-for="opt in [
                                    { value: '', label: 'All' },
                                    { value: 'live', label: 'Live' },
                                    {
                                        value: 'upcoming',
                                        label: 'Upcoming',
                                    },
                                ]" :key="opt.value" type="button"
                                    class="mb-1 flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold transition-colors"
                                    :class="status === opt.value
                                        ? 'bg-primary text-on-primary'
                                        : 'text-on-surface-variant hover:bg-surface-container-low'
                                        " @click="setStatus(opt.value)">
                                    <span class="material-symbols-outlined text-base">{{
                                        status === opt.value
                                            ? 'radio_button_checked'
                                            : 'radio_button_unchecked'
                                    }}</span>
                                    {{ opt.label }}
                                </button>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active filter chips + result count -->
            <div v-if="activeFilters.length > 0 || bids.total > 0" class="mt-6 flex flex-wrap items-center gap-3">
                <div v-for="filter in activeFilters" :key="filter.key"
                    class="flex items-center gap-2 rounded-full bg-secondary-container/50 px-4 py-2 text-xs font-bold text-primary">
                    <span>{{ filter.label }}</span>
                    <span class="material-symbols-outlined cursor-pointer text-sm"
                        @click="removeFilter(filter.key)">close</span>
                </div>

                <span class="ml-auto text-xs font-bold text-outline">
                    {{ bids.total }} result{{ bids.total !== 1 ? 's' : '' }}
                </span>
            </div>
        </header>

        <!-- Empty state -->
        <div v-if="bids.data.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined mb-4 text-5xl text-outline">search_off</span>
            <p class="font-headline text-xl font-bold text-on-surface-variant">
                No Event Items found
            </p>
            <p class="mt-2 text-sm text-outline">
                Try adjusting your search or check back soon.
            </p>
        </div>

        <!-- Bid cards grid -->
        <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4 xl:grid-cols-5">
            <BidCard v-for="bid in bids.data" :key="bid.id" :bid="bid" @open-bid-modal="openBidModal" />
        </div>

        <!-- Pagination -->
        <AppPaginator :current-page="bids.current_page" :last-page="bids.last_page" @page-change="goToPage" />

        <!-- Teleport Modal to body -->
        <Teleport to="body">
            <div v-if="showModal && selectedBid"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
                <!-- Modal Backdrop -->
                <div class="absolute inset-0" @click="showModal = false"></div>

                <!-- Modal Content -->
                <div
                    class="relative w-full max-w-md overflow-hidden rounded-3xl bg-surface-container-lowest p-6 shadow-2xl">
                    <button type="button"
                        class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-surface-container-high"
                        @click="showModal = false">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <h2 class="mb-2 font-headline text-2xl font-extrabold text-on-surface">
                        Place Your Bid
                    </h2>
                    <p class="mb-6 text-sm text-outline">
                        Bid on
                        <span class="font-bold text-on-surface">{{
                            selectedBid.name
                            }}</span>
                    </p>

                    <form @submit.prevent="submitBid">
                        <div class="mb-4">
                            <label class="mb-2 block text-xs font-bold tracking-widest text-outline uppercase">Bid
                                Points</label>
                            <input v-model="form.points" type="number" required min="1"
                                class="w-full rounded-2xl border border-surface-container bg-surface-container-low px-4 py-3 text-lg font-bold focus:border-primary focus:ring-2 focus:ring-primary-container disabled:opacity-50"
                                :disabled="form.processing" />
                            <p v-if="form.errors.points" class="mt-2 text-xs font-bold text-error">
                                {{ form.errors.points }}
                            </p>
                        </div>

                        <div class="mb-6 rounded-xl border border-secondary/20 bg-secondary-container/30 p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-semibold text-on-surface-variant">Your Active Points</span>
                                <span class="text-sm font-black text-on-surface">{{ props.userPoints ?? 0 }}</span>
                            </div>
                            <div v-if="selectedBid.status === 0" class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-on-surface-variant">Points Needed to
                                    Unlock</span>
                                <span class="text-sm font-black text-on-surface">{{ selectedBid.open_points }}</span>
                            </div>
                            <div v-if="selectedBid.status === 1" class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-on-surface-variant">Total Bidded Points</span>
                                <span class="text-sm font-black text-on-surface">{{ selectedBid.bid_entry_points
                                    }}</span>
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="flex w-full items-center justify-center rounded-2xl bg-primary py-4 font-bold text-on-primary transition-all hover:bg-on-primary-fixed active:scale-95 disabled:opacity-50">
                            <span v-if="form.processing"
                                class="material-symbols-outlined mr-2 animate-spin">progress_activity</span>
                            Confirm Bid
                        </button>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Toast Notification -->
        <Teleport to="body">
            <div v-if="showToast"
                class="fixed right-6 bottom-6 z-50 flex items-center gap-3 rounded-2xl border border-surface-container bg-surface-container-highest px-6 py-4 shadow-xl">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 text-primary">
                    <span class="material-symbols-outlined text-sm">check</span>
                </div>
                <span class="text-sm font-bold text-on-surface">{{
                    toastMessage
                    }}</span>
            </div>
        </Teleport>

    </section>
</template>
