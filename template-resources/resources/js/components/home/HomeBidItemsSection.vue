<script setup lang="ts">
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { useNow } from '@vueuse/core';
import { ref, onMounted } from 'vue';
import { formatPrice } from '@/lib/utils';
import type { Bid } from '@/pages/Home.vue';
import { trending, login } from '@/routes';
import { place } from '@/routes/bids';

const props = defineProps<{
    bids: Bid[];
    categories: string[];
    userPoints: number | null;
}>();

function progressPct(bid: Bid): number {
    if (!bid.open_points) {
        return 0;
    }

    return Math.min(
        100,
        Math.round(((bid.bid_entry_points ?? 0) / bid.open_points) * 100),
    );
}

function buttonLabel(bid: Bid): string {
    const pct = progressPct(bid);

    if (bid.status === 0) {
        return 'Place Bid';
    }

    if (pct >= 100) {
        return 'Final Moment Bid';
    }

    return 'Place Bid';
}


const now = useNow();
const page = usePage();
const selectedBid = ref<Bid | null>(null);
const showModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const expandedImage = ref<string | null>(null);

const form = useForm({
    points: 0,
});

onMounted(() => {
    if (page.props.auth?.user) {
        const pendingBidId = sessionStorage.getItem('pendingBidId');

        if (pendingBidId) {
            const bid = props.bids.find(
                (b) => b.id.toString() === pendingBidId,
            );

            if (bid) {
                openBidModal(bid);
                sessionStorage.removeItem('pendingBidId');
            }
        }
    }
});

function openBidModal(bid: Bid) {
    if (!page.props.auth?.user) {
        sessionStorage.setItem('pendingBidId', bid.id.toString());
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

function getRemainingTime(endsAt: string | null | undefined): string {
    if (!endsAt) {
        return '';
    }

    const diffInMs = new Date(endsAt).getTime() - now.value.getTime();

    if (diffInMs <= 0) {
        return '0 hour(s), 0 minute(s)';
    }

    const totalMinutes = Math.floor(diffInMs / (1000 * 60));
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${hours} hour(s), ${minutes} minute(s)`;
}

defineExpose({
    openBidModal,
});
</script>

<template>
    <section class="mx-auto max-w-screen-2xl px-8 pt-5 pb-8">
        <div class="mb-8 flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
            <h2 class="font-headline text-2xl md:text-4xl font-extrabold tracking-tighter py-2">
                Live Opportunities
            </h2>
        </div>

        <!-- Empty state -->
        <div v-if="bids.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined mb-4 text-5xl text-outline">search_off</span>
            <p class="font-headline text-xl font-bold text-on-surface-variant">
                No bids found
            </p>
            <p class="mt-2 text-sm text-outline">
                Try adjusting your search or check back soon.
            </p>
        </div>

        <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4 xl:grid-cols-5">
            <div v-for="bid in props.bids" :key="bid.id"
                class="group flex h-full flex-col overflow-hidden rounded-2xl border border-surface-container bg-surface-container-lowest transition-all duration-500 hover:shadow-2xl hover:shadow-primary/5 sm:rounded-3xl">
                <div class="relative aspect-4/3 overflow-hidden">
                    <img :alt="bid.name"
                        class="h-full w-full cursor-pointer object-cover transition-transform duration-700 group-hover:scale-110"
                        :src="bid.image" @click="expandedImage = bid.image" />
                    <div
                        class="absolute top-4 right-4 cursor-pointer rounded-full bg-white/90 p-2 shadow-lg backdrop-blur transition-all hover:bg-primary hover:text-white">
                        <span class="material-symbols-outlined text-[20px]">open_in_new</span>
                    </div>
                    <div class="absolute bottom-4 left-4">
                        <span v-if="bid.status === 1"
                            class="rounded-lg bg-primary-container px-2 py-1 text-[9px] font-black tracking-widest text-on-primary-container uppercase shadow-lg sm:px-3 sm:text-[10px]">Live</span>
                        <span v-else-if="bid.status === 2"
                            class="rounded-lg bg-error px-2 py-1 text-[9px] font-black tracking-widest text-white uppercase shadow-lg sm:px-3 sm:text-[10px]">Closed</span>
                        <span v-else
                            class="rounded-lg bg-secondary px-2 py-1 text-[9px] font-black tracking-widest text-white uppercase shadow-lg sm:px-3 sm:text-[10px]">Upcoming</span>
                    </div>
                </div>
                <div class="flex grow flex-col p-3 sm:p-4">
                    <div class="mb-3 flex items-start justify-between">
                        <h3 class="font-headline text-sm leading-tight font-extrabold sm:text-base line-clamp-2">
                            {{ bid.name }}
                        </h3>
                        <div class="text-right">
                            <p class="mb-0.5 text-[8px] font-bold tracking-wider text-secondary uppercase sm:text-[9px]">
                                Price
                            </p>
                            <p class="text-base font-black text-primary whitespace-nowrap sm:text-lg">
                                {{ formatPrice(bid.price) }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-auto py-2">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[9px] font-bold text-secondary sm:text-[10px]">
                                Progress: {{ bid.bid_entry_points ?? 0 }}/{{
                                    bid.open_points
                                }}
                                Points
                            </span>
                            <span class="text-[9px] font-black sm:text-[10px]" :class="progressPct(bid) >= 100
                                    ? 'text-error'
                                    : 'text-primary'
                                ">
                                {{ progressPct(bid) }}%
                            </span>
                        </div>
                        <div class="mb-2 h-1.5 w-full overflow-hidden rounded-full bg-surface-container-highest">
                            <div class="h-full rounded-full transition-all duration-500"
                                :style="{ width: progressPct(bid) + '%' }" :class="progressPct(bid) >= 100
                                        ? 'bg-error'
                                        : 'bg-linear-to-r from-primary to-tertiary'
                                    " />
                        </div>

                        <div v-if="(bid.status === 1 || progressPct(bid) >= 100) && bid.ends_at"
                            class="mb-3 text-center text-[9px] font-bold text-outline sm:text-[10px]">
                            <span class="material-symbols-outlined mr-1 align-middle text-[12px]">schedule</span>
                            <span class="align-middle">{{ getRemainingTime(bid.ends_at) }} left</span>
                        </div>
                        <div v-else class="mb-3 h-[14px]"></div>

                        <button type="button" :disabled="bid.status === 2" :class="[
                            'w-full rounded-xl py-2 text-xs font-bold transition-all sm:py-2.5 sm:text-sm',
                            bid.status === 2
                                ? 'cursor-not-allowed bg-surface-container-low text-on-surface-variant'
                                : progressPct(bid) >= 100
                                    ? 'bg-primary font-bold text-on-primary shadow-lg shadow-primary/20 group-hover:scale-[1.02] hover:bg-on-primary-fixed active:scale-95'
                                    : 'bg-primary text-on-primary group-hover:scale-[1.02] hover:bg-on-primary-fixed active:scale-95',
                        ]" @click="openBidModal(bid)">
                            {{ buttonLabel(bid) }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-16 text-center">
            <Link :href="trending.url()"
                class="rounded-full bg-surface-container-low px-12 py-4 font-bold text-on-surface transition-all hover:bg-surface-container-high">
                View All Auction Items
            </Link>
        </div>

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
                                class="w-full rounded-2xl border border-surface-container bg-surface-container-low text-black px-4 py-3 text-lg font-bold focus:border-primary focus:ring-2 focus:ring-primary-container disabled:opacity-50"
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

        <!-- Image Overlay -->
        <Teleport to="body">
            <div v-if="expandedImage"
                class="fixed inset-0 z-[100] flex cursor-pointer items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
                @click="expandedImage = null">
                <img :src="expandedImage" class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl"
                    alt="Expanded image" />
            </div>
        </Teleport>
    </section>
</template>
