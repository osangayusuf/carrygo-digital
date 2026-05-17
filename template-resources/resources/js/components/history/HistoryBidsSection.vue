<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import HistoryBidCard from '@/components/cards/HistoryBidCard.vue';
import AppPaginator from '@/components/layout/AppPaginator.vue';
import type { WonBid } from '@/pages/HistoryBids.vue';
import { history } from '@/routes';
import type { LengthAwarePaginator } from '@/types';
import ReviewModal from './ReviewModal.vue';

defineProps<{
    bids: LengthAwarePaginator<WonBid>;
    userPoints: number | null;
}>();

function visit(extra: Record<string, string | number> = {}) {
    const currentSearch = new URLSearchParams(window.location.search).get('search') ?? '';
    router.get(
        history.url({
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

const selectedBid = ref<WonBid | null>(null);
const showModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const reviewMode = ref<'view' | 'write'>('view');

function openReviewModal(bid: WonBid, mode: 'view' | 'write') {
    selectedBid.value = bid;
    reviewMode.value = mode;
    showModal.value = true;
}

function onReviewSuccess() {
    showModal.value = false;
    toastMessage.value = 'Review submitted successfully!';
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
        router.reload();
    }, 5000);
}

</script>

<template>
    <section class="mx-auto max-w-screen-2xl px-6 pt-12 pb-24">
        <!-- Page header -->
        <header class="mb-12">
            <div class="flex flex-col justify-between gap-8 md:flex-row md:items-end">
                <div class="space-y-2">
                    <span class="font-label text-xs font-bold tracking-widest text-secondary uppercase">
                        Winning Moments
                    </span>
                    <h1 class="font-headline text-4xl font-extrabold tracking-tight text-on-surface md:text-5xl">
                        Won Bids History
                    </h1>
                </div>
            </div>

            <div v-if="bids.total > 0" class="mt-6 flex flex-wrap items-center gap-3">
                <span class="ml-auto text-xs font-bold text-outline">
                    {{ bids.total }} result{{ bids.total !== 1 ? 's' : '' }}
                </span>
            </div>
        </header>

        <!-- Empty state -->
        <div v-if="bids.data.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined mb-4 text-5xl text-outline">history_toggle_off</span>
            <p class="font-headline text-xl font-bold text-on-surface-variant">
                No past bids found
            </p>
            <p class="mt-2 text-sm text-outline">
                You haven't won any bids yet, or none matched your search.
            </p>
        </div>

        <!-- Bid cards grid -->
        <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4 xl:grid-cols-5">
            <HistoryBidCard v-for="bid in bids.data" :key="bid.id" :bid="bid"
                @open-review-modal="({ bid: b, mode }) => openReviewModal(b, mode)" />
        </div>

        <!-- Pagination -->
        <AppPaginator :current-page="bids.current_page" :last-page="bids.last_page" @page-change="goToPage" />

        <!-- Review Modal -->
        <ReviewModal :show="showModal" :bid="selectedBid" :mode="reviewMode" @close="showModal = false"
            @success="onReviewSuccess" />

        <!-- Toast Notification -->
        <Teleport to="body">
            <div v-if="showToast"
                class="fixed right-6 bottom-6 z-50 flex items-center gap-3 rounded-2xl border border-surface-container bg-surface-container-highest px-6 py-4 shadow-xl">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 text-primary">
                    <span class="material-symbols-outlined text-sm">check</span>
                </div>
                <span class="text-sm font-bold text-on-surface">{{ toastMessage }}</span>
            </div>
        </Teleport>

    </section>
</template>
