<script setup lang="ts">
import { ref } from 'vue';
import { formatPrice, formatMsisdn } from '@/lib/utils';
import type { LeaderboardBid } from '@/pages/LeaderboardBids.vue';

defineProps<{ bid: LeaderboardBid }>();

const expandedImage = ref<string | null>(null);

</script>

<template>
    <div class="bg-white rounded-lg border-2 border-sage-border overflow-hidden transition-all duration-200 flex flex-col hover:border-lemon hover:-translate-y-0.5">
        <!-- Image -->
        <div class="h-36 relative overflow-hidden bg-sage-light cursor-pointer" @click="expandedImage = bid.image">
            <img :src="bid.image" :alt="bid.name" class="w-full h-full object-cover block transition-transform duration-700 hover:scale-110" />
            <div class="absolute bottom-2 left-2">
                <span v-if="bid.status === 0" class="rounded-lg bg-navy px-2 py-1 text-[9px] font-black tracking-widest text-lemon uppercase shadow-lg sm:px-3 sm:text-[10px]">
                    Live
                </span>

            </div>
        </div>

        <!-- Card body -->
        <div class="p-3 flex-1 flex flex-col">
            <div class="flex justify-between items-start mb-2">
                <div class="text-xs font-extrabold text-ink line-clamp-2 pr-2">{{ bid.name }}</div>
                <div class="text-right shrink-0">
                    <p class="mb-0.5 text-[8px] font-bold tracking-wider text-sage-dark uppercase">Value</p>
                    <p class="text-sm font-black text-forest whitespace-nowrap">{{ formatPrice(bid.price) }}</p>
                </div>
            </div>

            <div class="mt-auto">
                <h4 class="mb-2 text-[9px] font-black tracking-widest text-sage-dark uppercase">Top Bidders</h4>

                <div v-if="bid.top_bidders && bid.top_bidders.length > 0" class="flex flex-col gap-1.5">
                    <div v-for="(bidder, index) in bid.top_bidders" :key="index"
                         class="flex items-center justify-between rounded-xl px-2 py-1.5 sm:px-2.5"
                         :class="index === 0 ? 'bg-sage-light border border-sage-border' : 'bg-surface-container-low'">
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span class="flex h-4 w-4 items-center justify-center rounded-full text-[8px] font-black"
                                  :class="index === 0 ? 'bg-navy text-lemon shadow-md shadow-navy/30' : 'bg-surface-container-high text-on-surface-variant'">
                                {{ index + 1 }}
                            </span>
                            <span class="text-[10px] font-bold text-ink sm:text-xs">
                                {{ formatMsisdn(bidder.msisdn) }}
                            </span>
                        </div>
                        <span class="text-[10px] font-black sm:text-xs"
                              :class="index === 0 ? 'text-forest' : 'text-sage-dark'">
                            {{ bidder.total_points }} pts
                        </span>
                    </div>
                </div>
                <div v-else class="rounded-xl border border-dashed border-sage-border p-3 text-center sm:p-4">
                    <p class="text-[10px] font-bold text-sage-dark sm:text-xs">No bids recorded</p>
                    <p class="mt-0.5 text-[9px] text-sage-dark/80 sm:text-[10px]">Check back once the bidding war starts.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Overlay -->
    <Teleport to="body">
        <div v-if="expandedImage" class="fixed inset-0 z-[100] flex cursor-pointer items-center justify-center bg-black/90 p-4 backdrop-blur-sm" @click="expandedImage = null">
            <img :src="expandedImage" class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl" alt="Expanded image" />
        </div>
    </Teleport>
</template>
