<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePlaceBidModal } from '@/composables/usePlaceBidModal';
import { formatPrice, calcProgress, getRemainingTime } from '@/lib/utils';
import auctions from '@/routes/auctions/index';
import type { Bid } from '@/types/auction';
import ShareModal from '@/components/modals/ShareModal.vue';

const props = defineProps<{
    bid: Bid;
    userPoints?: number | null;
}>();

const { open } = usePlaceBidModal();
const expandedImage = ref<string | null>(null);
const isShareOpen = ref(false);

const cardUrl = computed(() => {
    if (typeof window !== 'undefined') {
        return `${window.location.origin}/auctions/${props.bid.id}`;
    }
    return `/auctions/${props.bid.id}`;
});

const dynamicTag = computed(() => {
    if (props.bid.status === 1) {
        return { text: '🚨 Closing Soon', class: 'bg-red-600 text-white animate-pulse' };
    }
    if ((props.bid.bid_count ?? 0) >= 20) {
        return { text: '🔥 Hot Bid', class: 'bg-red-500 text-white' };
    }
    if ((props.bid.bid_count ?? 0) >= 10) {
        return { text: '👀 Most Visited', class: 'bg-orange-500 text-white' };
    }
    const progress = calcProgress(props.bid);
    if (progress >= 75 && progress < 100) {
        return { text: '⚡ Nearly Full', class: 'bg-amber-500 text-navy' };
    }
    const openDate = props.bid.open_date;
    const now = Date.now() / 1000;
    if (openDate && (now - openDate) < 86400 && (props.bid.bid_count ?? 0) > 0) {
        return { text: '⚡ Fast Progress', class: 'bg-emerald-500 text-white' };
    }
    return null;
});

const biddingNowCount = ref(calculateBiddingNow());

function calculateBiddingNow(): number {
    const id = props.bid.id;
    const bidCount = props.bid.bid_count ?? 0;
    const base = bidCount >= 20 ? 12 : 3;
    const range = bidCount >= 20 ? 15 : 6;
    const rand = Math.floor(Math.abs(Math.sin(id + Date.now() / 100000)) * range);
    return base + rand;
}

let biddingNowInterval: ReturnType<typeof setInterval> | null = null;
onMounted(() => {
    biddingNowInterval = setInterval(() => {
        biddingNowCount.value = calculateBiddingNow();
    }, 8000);
});

onBeforeUnmount(() => {
    if (biddingNowInterval) {
        clearInterval(biddingNowInterval);
    }
});

function openBidModal(): void {
    open(props.bid, props.userPoints ?? null);
}

function buttonLabel(bid: Bid): string {
    if (bid.status === 2) {
        return 'Closed';
    }

    if (calcProgress(bid) >= 100) {
        return 'Final Moment Bid';
    }

    return 'Place Bid';
}
</script>

<template>
    <div
        class="bg-white rounded-lg border-2 border-sage-border overflow-hidden transition-all duration-200 flex flex-col hover:border-lemon hover:-translate-y-0.5">
        <div class="h-36 relative overflow-hidden bg-sage-light cursor-pointer" @click="expandedImage = bid.image">
            <img :src="bid.image ?? ''" :alt="bid.name"
                class="w-full h-full object-cover block transition-transform duration-700 hover:scale-110">
            <!-- Dynamic Badges -->
            <div class="absolute top-2 left-2" v-if="dynamicTag">
                <span
                    class="rounded-lg px-2 py-0.5 text-[8px] font-black tracking-wider uppercase shadow-lg sm:px-2.5 sm:text-[9px]"
                    :class="dynamicTag.class"
                >
                    {{ dynamicTag.text }}
                </span>
            </div>
            <div class="absolute top-2 right-2">
                <span v-if="bid.status === 0"
                    class="rounded-lg bg-navy px-2 py-1 text-[9px] font-black tracking-widest text-lemon uppercase shadow-lg sm:px-3 sm:text-[10px]">
                    Live
                </span>
                <span v-else-if="bid.status === 1"
                    class="rounded-lg bg-navy px-2 py-1 text-[9px] font-black tracking-widest text-lemon uppercase shadow-lg sm:px-3 sm:text-[10px]">
                    Open
                </span>
                <span v-else-if="bid.status === 2"
                    class="rounded-lg bg-error px-2 py-1 text-[9px] font-black tracking-widest text-white uppercase shadow-lg sm:px-3 sm:text-[10px]">
                    Closed
                </span>
            </div>
        </div>
        <div class="p-3 flex-1 flex flex-col">
            <div class="text-xs font-extrabold text-ink truncate mb-1">{{ bid.name }}</div>
            <div class="mb-1.5 flex items-center justify-between">
                <span class="text-lg font-black text-forest">{{ formatPrice(bid.price) }}</span>
                <span class="text-[10px] font-bold text-muted-green">{{ bid.bid_count ?? 0 }} bids</span>
            </div>
            <div class="h-1 bg-sage-mid rounded-sm overflow-hidden mb-1.5">
                <div class="h-full bg-linear-to-r from-forest to-lemon rounded-sm"
                    :style="{ width: calcProgress(bid) + '%' }"></div>
            </div>
            <div class="mb-2 flex items-center justify-between">
                <span class="text-[9px] font-bold text-forest sm:text-[10px]">
                    Progress: {{ bid.current_points ?? 0 }}/{{ bid.opening_points }} Points
                </span>
                <span class="text-[9px] font-black sm:text-[10px]"
                    :class="calcProgress(bid) >= 100 ? 'text-error' : 'text-primary'">
                    {{ calcProgress(bid) }}%
                </span>
            </div>
            <!-- Live Bidding Counter -->
            <div v-if="bid.status !== 2" class="mb-2.5 flex items-center gap-1.5 text-[9px] font-bold text-forest sm:text-[10px]">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>{{ biddingNowCount }} people bidding now</span>
            </div>
            <div v-if="(bid.status === 1 || calcProgress(bid) >= 100) && bid.expires_at"
                class="mb-3 text-center text-[9px] font-bold text-outline sm:text-[10px]">
                <span class="material-symbols-outlined mr-1 align-middle text-[12px]">schedule</span>
                <span class="align-middle">{{ getRemainingTime(bid.expires_at) }} left</span>
            </div>
            <div class="mt-auto flex flex-col gap-2">
                <button
                    type="button"
                    :disabled="bid.status === 2"
                    class="w-full rounded-md bg-navy p-2 text-xs font-extrabold text-lemon transition-colors hover:bg-forest disabled:opacity-60"
                    @click="openBidModal"
                >
                    {{ buttonLabel(bid) }}
                </button>
                <div class="flex gap-2">
                    <Link
                        :href="auctions.show.url(bid.id)"
                        class="flex-1 rounded-md border-2 border-sage-border bg-white p-2 text-center text-xs font-extrabold text-ink no-underline transition-colors hover:border-lemon hover:text-forest"
                    >
                        View more details
                    </Link>
                    <button
                        type="button"
                        class="px-3 rounded-md border-2 border-sage-border bg-white text-ink hover:border-lemon hover:text-forest transition-colors flex items-center justify-center cursor-pointer"
                        title="Share"
                        @click="isShareOpen = true"
                    >
                        <i class="pi pi-share-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Overlay -->
    <Teleport to="body">
        <div v-if="expandedImage"
            class="fixed inset-0 z-100 flex cursor-pointer items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
            @click="expandedImage = null">
            <img :src="expandedImage" class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl"
                alt="Expanded image" />
        </div>
    </Teleport>

    <!-- Share Modal -->
    <ShareModal
        :is-open="isShareOpen"
        :url="cardUrl"
        :name="bid.name"
        @close="isShareOpen = false"
    />
</template>

