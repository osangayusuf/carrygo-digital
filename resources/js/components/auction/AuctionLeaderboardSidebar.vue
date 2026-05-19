<script setup lang="ts">
import { formatMsisdn } from '@/lib/utils';
import type { Bidder } from '@/types/auction';

defineProps<{
    bidders: Bidder[];
}>();
</script>

<template>
    <aside class="flex flex-col rounded-xl border-2 border-sage-border bg-white lg:sticky lg:top-24">
        <header class="border-b-2 border-sage-border px-4 py-3">
            <h2 class="text-xs font-black tracking-widest text-muted-green uppercase">Top Bidders</h2>
            <p class="mt-0.5 text-[10px] font-semibold text-muted-green/80">By cumulative points bid</p>
        </header>

        <div class="flex flex-1 flex-col p-3">
            <div v-if="bidders.length > 0" class="flex max-h-[min(70vh,32rem)] flex-col gap-1.5 overflow-y-auto">
                <div
                    v-for="(bidder, index) in bidders"
                    :key="`${bidder.msisdn}-${index}`"
                    class="flex items-center justify-between rounded-xl px-2.5 py-2"
                    :class="index === 0 ? 'border-2 border-sage-border bg-sage-bg' : 'bg-sage-bg/50'"
                >
                    <div class="flex min-w-0 items-center gap-2">
                        <span
                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[9px] font-black"
                            :class="index === 0 ? 'bg-navy text-lemon shadow-md shadow-navy/30' : 'bg-white text-ink'"
                        >
                            {{ index + 1 }}
                        </span>
                        <span class="truncate text-xs font-bold text-ink">
                            {{ formatMsisdn(bidder.msisdn) }}
                        </span>
                    </div>
                    <span
                        class="ml-2 shrink-0 text-xs font-black"
                        :class="index === 0 ? 'text-forest' : 'text-muted-green'"
                    >
                        {{ Number(bidder.total_points).toLocaleString() }} pts
                    </span>
                </div>
            </div>

            <div v-else class="rounded-xl border border-dashed border-sage-border p-6 text-center">
                <p class="text-xs font-bold text-muted-green">No bids recorded</p>
                <p class="mt-1 text-[10px] text-muted-green/80">Be the first to place a bid.</p>
            </div>
        </div>
    </aside>
</template>
