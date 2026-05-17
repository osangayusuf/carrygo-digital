<script setup lang="ts">
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import BidCard from '@/components/cards/BidCard.vue';
import type { Bid } from '@/pages/Home/Index.vue';
import { login, trending } from '@/routes/index';

const props = defineProps<{
    bids: Bid[];
    userPoints: number | null;
}>();

const page = usePage();
const selectedBid = ref<Bid | null>(null);
const showModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');

const form = useForm({
    points: 0,
});

onMounted(() => {
    if (page.props.auth?.user) {
        const pendingBidId = sessionStorage.getItem('pendingBidId');

        if (pendingBidId) {
            const bid = props.bids.find((b) => b.id.toString() === pendingBidId);

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

    form.post(`/auctions/${selectedBid.value.id}/bids`, {
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

defineExpose({ openBidModal });
</script>

<template>
    <section class="mx-auto max-w-screen-2xl px-8 pt-5 pb-8">
        <div class="mb-8 flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
            <h2 class="font-headline text-2xl md:text-4xl font-extrabold tracking-tighter py-2">Live Opportunities</h2>
        </div>

        <!-- Empty state -->
        <div v-if="bids.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined mb-4 text-5xl text-outline">search_off</span>
            <p class="font-headline text-xl font-bold text-on-surface-variant">No bids found</p>
            <p class="mt-2 text-sm text-outline">Try adjusting your search or check back soon.</p>
        </div>

        <div v-else class="grid grid-cols-2 gap-2.5 md:grid-cols-5">
            <BidCard
                v-for="bid in bids"
                :key="bid.id"
                :bid="bid"
                @open-bid-modal="openBidModal"
            />
        </div>

        <div class="mt-16 text-center">
            <Link
                :href="trending.url()"
                class="rounded-full bg-surface-container-low px-12 py-4 font-bold text-on-surface transition-all hover:bg-surface-container-high"
            >
                View All Auction Items
            </Link>
        </div>

        <!-- Bid Modal -->
        <Teleport to="body">
            <div v-if="showModal && selectedBid" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
                <div class="absolute inset-0" @click="showModal = false"></div>
                <div class="relative w-full max-w-md overflow-hidden rounded-3xl bg-surface-container-lowest p-6 shadow-2xl">
                    <button
                        type="button"
                        class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-surface-container-high"
                        @click="showModal = false"
                    >
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <h2 class="mb-2 font-headline text-2xl font-extrabold text-on-surface">Place Your Bid</h2>
                    <p class="mb-6 text-sm text-outline">
                        Bid on <span class="font-bold text-on-surface">{{ selectedBid.name }}</span>
                    </p>

                    <form @submit.prevent="submitBid">
                        <div class="mb-4">
                            <label class="mb-2 block text-xs font-bold tracking-widest text-outline uppercase">Bid Points</label>
                            <input
                                v-model="form.points"
                                type="number"
                                required
                                min="1"
                                class="w-full rounded-2xl border border-surface-container bg-surface-container-low px-4 py-3 text-lg font-bold focus:border-primary focus:ring-2 focus:ring-primary-container disabled:opacity-50"
                                :disabled="form.processing"
                            />
                            <p v-if="form.errors.points" class="mt-2 text-xs font-bold text-error">
                                {{ form.errors.points }}
                            </p>
                        </div>

                        <div class="mb-6 rounded-xl border border-secondary/20 bg-secondary-container/30 p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-semibold text-on-surface-variant">Your Active Points</span>
                                <span class="text-sm font-black text-on-surface">{{ userPoints ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-on-surface-variant">Points Threshold</span>
                                <span class="text-sm font-black text-on-surface">{{ selectedBid.open_points }}</span>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center rounded-2xl bg-primary py-4 font-bold text-on-primary transition-all hover:bg-on-primary-fixed active:scale-95 disabled:opacity-50"
                        >
                            <span v-if="form.processing" class="material-symbols-outlined mr-2 animate-spin">progress_activity</span>
                            Confirm Bid
                        </button>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Toast Notification -->
        <Teleport to="body">
            <div
                v-if="showToast"
                class="fixed right-6 bottom-6 z-50 flex items-center gap-3 rounded-2xl border border-surface-container bg-surface-container-highest px-6 py-4 shadow-xl"
            >
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 text-primary">
                    <span class="material-symbols-outlined text-sm">check</span>
                </div>
                <span class="text-sm font-bold text-on-surface">{{ toastMessage }}</span>
            </div>
        </Teleport>

    </section>
</template>
