<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePlaceBidModal } from '@/composables/usePlaceBidModal';
import { formatPrice, calcProgress, getRemainingTime } from '@/lib/utils';
import auctions from '@/routes/auctions/index';
import type { Bid } from '@/types/auction';

const props = defineProps<{
    bid: Bid;
    userPoints?: number | null;
}>();

const { open } = usePlaceBidModal();
const expandedImage = ref<string | null>(null);

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
            <div class="text-lg font-black text-forest mb-1.5">{{ formatPrice(bid.price) }}</div>
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
                <Link
                    :href="auctions.show.url(bid.id)"
                    class="block w-full rounded-md border-2 border-sage-border bg-white p-2 text-center text-xs font-extrabold text-ink no-underline transition-colors hover:border-lemon hover:text-forest"
                >
                    View more details
                </Link>
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
</template>
