<script setup lang="ts">
import { ref } from 'vue';
import { formatMsisdn, formatPrice, formatSlashedPrice } from '@/lib/utils';
import type { LeaderboardAuction } from '@/types/auction';

defineProps<{ bid: LeaderboardAuction }>();

const expandedImage = ref<string | null>(null);
</script>

<template>
    <article
        class="flex flex-col overflow-hidden rounded-xl border-2 border-sage-border bg-white transition-all duration-200 hover:-translate-y-0.5 hover:border-lemon"
    >
        <div
            class="relative h-36 cursor-pointer overflow-hidden bg-sage-bg"
            @click="expandedImage = bid.image"
        >
            <img
                v-if="bid.image"
                :src="bid.image"
                :alt="bid.name"
                class="block h-full w-full object-cover transition-transform duration-700 hover:scale-110"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-muted-green"
            >
                <span class="pi pi-image text-4xl"></span>
            </div>
            <div class="absolute bottom-2 left-2">
                <span
                    v-if="bid.status === 1"
                    class="rounded-lg bg-navy px-2 py-1 text-[9px] font-black tracking-widest text-lemon uppercase shadow-lg sm:px-3 sm:text-[10px]"
                >
                    Live
                </span>
                <span
                    v-else-if="bid.status === 0"
                    class="rounded-lg border-2 border-sage-border bg-white px-2 py-1 text-[9px] font-black tracking-widest text-ink uppercase sm:px-3 sm:text-[10px]"
                >
                    Upcoming
                </span>
            </div>
        </div>

        <div class="flex flex-1 flex-col p-3">
            <div class="mb-2 flex items-start justify-between">
                <h2 class="line-clamp-2 pr-2 text-xs font-extrabold text-ink">
                    {{ bid.name }}
                </h2>
                <div class="shrink-0 text-right">
                    <p
                        class="mb-0.5 text-[8px] font-bold tracking-wider text-muted-green uppercase"
                    >
                        Value
                    </p>
                    <p class="text-[10px] font-black whitespace-nowrap text-forest">
                        {{ formatPrice(bid.price) }}
                    </p>
                    <p
                        class="text-[8px] font-bold whitespace-nowrap text-muted-green/70 line-through"
                    >
                        {{ formatSlashedPrice(bid.price) }}
                    </p>
                    <p class="mt-0.5 text-[9px] font-bold text-muted-green/80">
                        {{ bid.bid_count ?? 0 }} bids
                    </p>
                </div>
            </div>

            <div class="mt-auto">
                <h3
                    class="mb-2 text-[9px] font-black tracking-widest text-muted-green uppercase"
                >
                    Top Bidders
                </h3>

                <div
                    v-if="bid.top_bidders.length > 0"
                    class="flex flex-col gap-1.5"
                >
                    <div
                        v-for="(bidder, index) in bid.top_bidders"
                        :key="index"
                        class="flex items-center justify-between rounded-xl px-2 py-1.5 sm:px-2.5"
                        :class="
                            index === 0
                                ? 'border border-sage-border bg-sage-bg'
                                : 'bg-sage-bg/50'
                        "
                    >
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span
                                class="flex h-4 w-4 items-center justify-center rounded-full text-[8px] font-black"
                                :class="
                                    index === 0
                                        ? 'bg-navy text-lemon shadow-md shadow-navy/30'
                                        : 'bg-white text-ink'
                                "
                            >
                                {{ index + 1 }}
                            </span>
                            <span
                                class="text-[10px] font-bold text-ink sm:text-xs"
                            >
                                {{ formatMsisdn(bidder.msisdn) }}
                            </span>
                        </div>
                        <span
                            class="text-xs font-black sm:text-sm"
                            :class="
                                index === 0 ? 'text-forest' : 'text-muted-green'
                            "
                        >
                            {{ Number(bidder.total_points).toLocaleString() }}
                            pts
                        </span>
                    </div>
                </div>
                <div
                    v-else
                    class="rounded-xl border border-dashed border-sage-border p-3 text-center sm:p-4"
                >
                    <p
                        class="text-[10px] font-bold text-muted-green sm:text-xs"
                    >
                        No bids recorded
                    </p>
                    <p
                        class="mt-0.5 text-[9px] text-muted-green/80 sm:text-[10px]"
                    >
                        Check back once the bidding war starts.
                    </p>
                </div>
            </div>
        </div>
    </article>

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
