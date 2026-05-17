<script setup lang="ts">
import type { Bid } from '@/pages/Home.vue';

const props = defineProps<{
    bid: Bid;
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

function maskedPhone(msisdn: string): string {
    const digits = msisdn.replace(/\D/g, '');
    return `+${digits.slice(0, 3)}***${digits.slice(-4)}`;
}
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
            <!-- Backdrop -->
            <div class="absolute inset-0" @click="emit('close')"></div>
            
            <!-- Modal Content -->
            <div class="relative w-full max-w-md overflow-hidden rounded-3xl bg-surface-container-lowest p-6 shadow-2xl z-10">
                <button
                    type="button"
                    class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-surface-container-high"
                    @click="emit('close')"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
                
                <h2 class="mb-2 font-headline text-2xl font-extrabold text-on-surface">
                    Leaderboard
                </h2>
                <p class="mb-6 text-sm text-outline">
                    Top bidders for <span class="font-bold text-on-surface">{{ bid.name }}</span>
                </p>
                
                <div v-if="bid.top_bidders && bid.top_bidders.length > 0" class="flex flex-col gap-3">
                    <div 
                        v-for="(bidder, index) in bid.top_bidders" 
                        :key="bidder.msisdn"
                        class="flex items-center justify-between rounded-xl bg-surface-container-low p-4"
                        :class="{'border-2 border-tertiary/20 bg-tertiary/5': index === 0}"
                    >
                        <div class="flex items-center gap-3">
                            <div 
                                class="flex h-8 w-8 items-center justify-center rounded-full font-black text-sm"
                                :class="[
                                    index === 0 ? 'bg-tertiary text-on-tertiary shadow-lg' : 
                                    index === 1 ? 'bg-secondary text-on-secondary' : 
                                    'bg-surface-container-highest text-on-surface'
                                ]"
                            >
                                {{ index + 1 }}
                            </div>
                            <span class="font-bold text-on-surface">{{ maskedPhone(bidder.msisdn) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs font-bold text-outline">Points</span>
                            <span class="font-black text-primary">{{ parseInt(bidder.total_points).toLocaleString() }}</span>
                        </div>
                    </div>
                </div>
                
                <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                    <span class="material-symbols-outlined mb-2 text-4xl text-outline">leaderboard</span>
                    <p class="font-bold text-on-surface-variant">No bids yet</p>
                    <p class="text-sm text-outline">Be the first to place a bid on this item!</p>
                </div>
            </div>
        </div>
    </Teleport>
</template>
