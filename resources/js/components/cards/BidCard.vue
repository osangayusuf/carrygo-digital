<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ShareModal from '@/components/modals/ShareModal.vue';
import { usePlaceBidModal } from '@/composables/usePlaceBidModal';
import { formatPrice, calcProgress, getRemainingTime } from '@/lib/utils';
import auctions from '@/routes/auctions/index';
import type { Bid } from '@/types/auction';

const props = defineProps<{
    bid: Bid;
    userPoints?: number | null;
    isPreview?: boolean;
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
        return {
            text: '🚨 Closing Soon',
            class: 'bg-red-600 text-white animate-pulse',
        };
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

    if (openDate && now - openDate < 86400 && (props.bid.bid_count ?? 0) > 0) {
        return { text: '⚡ Fast Progress', class: 'bg-emerald-500 text-white' };
    }

    return null;
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
        class="flex flex-col overflow-hidden rounded-lg border-2 border-sage-border bg-white transition-all duration-200 hover:-translate-y-0.5 hover:border-lemon"
    >
        <div
            class="relative h-36 overflow-hidden bg-sage-light"
            :class="{ 'cursor-pointer': !isPreview }"
            @click="!isPreview && (expandedImage = bid.image)"
        >
            <img
                :src="bid.image ?? ''"
                :alt="bid.name"
                class="block h-full w-full object-cover transition-transform duration-700 hover:scale-110"
            />
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
                <span
                    v-if="bid.status === 0"
                    class="rounded-lg bg-navy px-2 py-1 text-[9px] font-black tracking-widest text-lemon uppercase shadow-lg sm:px-3 sm:text-[10px]"
                >
                    Live
                </span>
                <span
                    v-else-if="bid.status === 1"
                    class="rounded-lg bg-navy px-2 py-1 text-[9px] font-black tracking-widest text-lemon uppercase shadow-lg sm:px-3 sm:text-[10px]"
                >
                    Open
                </span>
                <span
                    v-else-if="bid.status === 2"
                    class="rounded-lg bg-error px-2 py-1 text-[9px] font-black tracking-widest text-white uppercase shadow-lg sm:px-3 sm:text-[10px]"
                >
                    Closed
                </span>
            </div>
        </div>
        <div class="flex flex-1 flex-col p-3">
            <div class="mb-1 truncate text-xs font-extrabold text-ink">
                {{ bid.name }}
            </div>
            <div class="mb-1.5 flex items-center justify-between">
                <span class="text-lg font-black text-forest">{{
                    formatPrice(bid.price)
                }}</span>
                <span class="text-[10px] font-bold text-muted-green"
                    >{{ bid.bid_count ?? 0 }} bids</span
                >
            </div>
            <div class="mb-1.5 h-1 overflow-hidden rounded-sm bg-sage-mid">
                <div
                    class="h-full rounded-sm bg-linear-to-r from-forest to-lemon"
                    :style="{ width: calcProgress(bid) + '%' }"
                ></div>
            </div>
            <div class="mb-2 flex items-center justify-between">
                <span class="text-[9px] font-bold text-forest sm:text-[10px]">
                    Progress: {{ bid.current_points ?? 0 }}/{{
                        bid.opening_points
                    }}
                    Points
                </span>
                <span
                    class="text-[9px] font-black sm:text-[10px]"
                    :class="
                        calcProgress(bid) >= 100 ? 'text-error' : 'text-primary'
                    "
                >
                    {{ calcProgress(bid) }}%
                </span>
            </div>

            <div
                v-if="
                    (bid.status === 1 || calcProgress(bid) >= 100) &&
                    bid.expires_at
                "
                class="mb-3 text-center text-[9px] font-bold text-outline sm:text-[10px]"
            >
                <span
                    class="material-symbols-outlined mr-1 align-middle text-[12px]"
                    >schedule</span
                >
                <span class="align-middle"
                    >{{ getRemainingTime(bid.expires_at) }} left</span
                >
            </div>
            <div class="mt-auto flex flex-col gap-2">
                <button
                    type="button"
                    :disabled="bid.status === 2 || isPreview"
                    class="w-full rounded-md bg-navy p-2 text-xs font-extrabold text-lemon transition-colors hover:bg-forest disabled:opacity-60"
                    :class="{ 'cursor-not-allowed': isPreview }"
                    @click="!isPreview && openBidModal()"
                >
                    {{ buttonLabel(bid) }}
                </button>
                <div class="flex gap-2">
                    <span
                        v-if="isPreview"
                        class="flex-1 cursor-not-allowed rounded-md border-2 border-sage-border bg-white p-2 text-center text-xs font-extrabold text-ink/50"
                    >
                        View more details
                    </span>
                    <Link
                        v-else
                        :href="auctions.show.url(bid.id)"
                        class="flex-1 rounded-md border-2 border-sage-border bg-white p-2 text-center text-xs font-extrabold text-ink no-underline transition-colors hover:border-lemon hover:text-forest"
                    >
                        View more details
                    </Link>
                    <button
                        type="button"
                        class="flex items-center justify-center rounded-md border-2 border-sage-border bg-white px-3 text-ink transition-colors hover:border-lemon hover:text-forest"
                        :class="
                            isPreview
                                ? 'cursor-not-allowed opacity-50'
                                : 'cursor-pointer'
                        "
                        :disabled="isPreview"
                        title="Share"
                        @click="!isPreview && (isShareOpen = true)"
                    >
                        <i class="pi pi-share-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Overlay -->
    <Teleport to="body">
        <div
            v-if="expandedImage"
            class="fixed inset-0 z-100 flex cursor-pointer items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
            @click="expandedImage = null"
        >
            <img
                :src="expandedImage"
                class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl"
                alt="Expanded image"
            />
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
