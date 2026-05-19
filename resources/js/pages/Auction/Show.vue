<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { store as storeBid } from '@/actions/App/Http/Controllers/BidController';
import AuctionHistoryFeed from '@/components/modals/AuctionHistoryFeed.vue';
import AuctionLeaderboardSidebar from '@/components/auction/AuctionLeaderboardSidebar.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { calcProgress, formatPrice, getRemainingTime } from '@/lib/utils';
import { home, login } from '@/routes/index';
import type { Bid, Bidder } from '@/types/auction';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    auction: Bid;
    topBidders: Bidder[];
    userPoints: number | null;
}>();

const page = usePage();

const expandedImage = ref<string | null>(null);

const canBid = computed(() => props.auction.status === 0 || props.auction.status === 1);

const authUserPoints = computed(
    () =>
        props.userPoints ??
        (page.props.auth as { user?: { points_balance?: number | string } })?.user?.points_balance ??
        null,
);

const form = useForm({
    points: Number(authUserPoints.value ?? 0) || 0,
});

function statusLabel(): string {
    if (props.auction.status === 2) {
        return 'Closed';
    }

    if (props.auction.status === 1) {
        return 'Open';
    }

    return 'Live';
}

function submitBid(): void {
    if (!canBid.value) {
        return;
    }

    if (!page.props.auth?.user) {
        sessionStorage.setItem('pendingBidId', props.auction.id.toString());
        router.get(login.url());

        return;
    }

    form.post(storeBid.url(props.auction.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="auction.name" />

    <div class="mx-auto max-w-7xl px-4 py-8">
        <Link
            :href="home.url()"
            class="mb-6 inline-flex items-center gap-1.5 text-sm font-bold text-forest no-underline hover:text-forest-dark"
        >
            <i class="pi pi-arrow-left text-xs"></i>
            Back to home
        </Link>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
            <div class="flex flex-col gap-6 lg:col-span-2">
                <article class="overflow-hidden rounded-xl border-2 border-sage-border bg-white">
                    <div
                        class="relative h-48 cursor-pointer overflow-hidden bg-sage-light sm:h-64"
                        @click="expandedImage = auction.image"
                    >
                        <img
                            v-if="auction.image"
                            :src="auction.image"
                            :alt="auction.name"
                            class="block h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-muted-green">
                            <span class="pi pi-image text-5xl"></span>
                        </div>
                        <div class="absolute top-3 right-3">
                            <span
                                v-if="auction.status === 0"
                                class="rounded-lg bg-navy px-3 py-1 text-[10px] font-black tracking-widest text-lemon uppercase shadow-lg"
                            >
                                Live
                            </span>
                            <span
                                v-else-if="auction.status === 1"
                                class="rounded-lg bg-navy px-3 py-1 text-[10px] font-black tracking-widest text-lemon uppercase shadow-lg"
                            >
                                Open
                            </span>
                            <span
                                v-else
                                class="rounded-lg bg-error px-3 py-1 text-[10px] font-black tracking-widest text-white uppercase shadow-lg"
                            >
                                Closed
                            </span>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        <p class="mb-1 text-[10px] font-black tracking-widest text-muted-green uppercase">
                            {{ statusLabel() }} auction
                        </p>
                        <h1 class="mb-2 text-xl font-extrabold text-ink sm:text-2xl">{{ auction.name }}</h1>
                        <p class="mb-4 text-2xl font-black text-forest">{{ formatPrice(auction.price) }}</p>

                        <div class="mb-2 h-1.5 overflow-hidden rounded-sm bg-sage-mid">
                            <div
                                class="h-full rounded-sm bg-linear-to-r from-forest to-lemon"
                                :style="{ width: calcProgress(auction) + '%' }"
                            ></div>
                        </div>
                        <div class="mb-4 flex items-center justify-between text-xs font-bold">
                            <span class="text-forest">
                                Progress: {{ auction.current_points ?? 0 }}/{{ auction.opening_points }} pts
                            </span>
                            <span :class="calcProgress(auction) >= 100 ? 'text-error' : 'text-primary'">
                                {{ calcProgress(auction) }}%
                            </span>
                        </div>

                        <p
                            v-if="(auction.status === 1 || calcProgress(auction) >= 100) && auction.expires_at"
                            class="mb-0 text-center text-xs font-bold text-outline"
                        >
                            <span class="material-symbols-outlined mr-1 align-middle text-sm">schedule</span>
                            <span class="align-middle">{{ getRemainingTime(auction.expires_at) }} left</span>
                        </p>
                    </div>
                </article>

                <section class="rounded-xl border-2 border-sage-border bg-white p-4 sm:p-6">
                    <h2 class="mb-4 font-headline text-lg font-extrabold text-ink">
                        {{ canBid ? 'Place your bid' : 'Auction history' }}
                    </h2>

                    <AuctionHistoryFeed :auction-id="auction.id" class="mb-4" />

                    <form v-if="canBid" class="border-t-2 border-sage-border pt-4" @submit.prevent="submitBid">
                        <div class="mb-4">
                            <label class="mb-2 block text-xs font-bold tracking-widest text-muted-green uppercase">
                                Bid points
                            </label>
                            <input
                                v-model="form.points"
                                type="number"
                                required
                                min="1"
                                class="w-full rounded-xl border-2 border-sage-border bg-sage-bg px-4 py-3 text-lg font-bold text-ink focus:border-lemon focus:ring-2 focus:ring-lemon/30 disabled:opacity-50"
                                :class="form.errors.points ? 'border-error' : ''"
                                :disabled="form.processing"
                            />
                            <p v-if="form.errors.points" class="mt-2 text-xs font-bold text-error">
                                {{ form.errors.points }}
                            </p>
                        </div>

                        <div class="mb-6 rounded-xl border-2 border-sage-border bg-sage-bg/50 p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-semibold text-muted-green">Your active points</span>
                                <span class="text-sm font-black text-ink">{{ authUserPoints ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-muted-green">Points threshold</span>
                                <span class="text-sm font-black text-ink">{{ auction.opening_points }}</span>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center rounded-xl bg-navy py-3 text-sm font-extrabold text-lemon transition-colors hover:bg-forest disabled:opacity-50"
                        >
                            <span v-if="form.processing" class="material-symbols-outlined mr-2 animate-spin">
                                progress_activity
                            </span>
                            Confirm bid
                        </button>
                    </form>

                    <p v-else class="border-t-2 border-sage-border pt-4 text-center text-sm font-semibold text-muted-green">
                        This auction has ended. Bidding is no longer available.
                    </p>
                </section>
            </div>

            <div class="lg:col-span-1">
                <AuctionLeaderboardSidebar :bidders="topBidders" />
            </div>
        </div>
    </div>

    <Teleport to="body">
        <div
            v-if="expandedImage"
            class="fixed inset-0 z-100 flex cursor-pointer items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
            @click="expandedImage = null"
        >
            <img
                :src="expandedImage"
                class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl"
                alt="Expanded auction image"
            />
        </div>
    </Teleport>
</template>
