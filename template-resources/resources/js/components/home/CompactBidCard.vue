<script setup lang="ts">
import { useNow } from '@vueuse/core';
import { formatPrice } from '@/lib/utils';
import type { Bid } from '@/pages/Home.vue';

const now = useNow();

const props = defineProps<{
    bid: Bid;
}>();

const emit = defineEmits<{
    (e: 'click'): void;
}>();

function progressPct(bid: Bid): number {
    if (!bid.open_points) {
        return 0;
    }

    return Math.min(100, Math.round(((bid.bid_entry_points ?? 0) / bid.open_points) * 100));
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
</script>

<template>
    <div
        class="group flex w-auto md:min-w-0 shrink-0 snap-start cursor-pointer flex-col overflow-hidden rounded-2xl border border-surface-container bg-surface-container-lowest transition-all hover:shadow-md"
        @click="emit('click')"
    >
        <div class="relative aspect-square overflow-hidden bg-surface-container-low">
            <img
                :alt="bid.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                :src="bid.image"
            />
            <div class="absolute bottom-2 left-2">
                <span
                    v-if="bid.status === 1"
                    class="rounded bg-primary-container px-1.5 py-0.5 text-[8px] font-black tracking-widest text-on-primary-container uppercase shadow"
                    >Live</span
                >
                <span
                    v-else-if="bid.status === 2"
                    class="rounded bg-error px-1.5 py-0.5 text-[8px] font-black tracking-widest text-white uppercase shadow"
                    >Closed</span
                >
                <span
                    v-else
                    class="rounded bg-secondary px-1.5 py-0.5 text-[8px] font-black tracking-widest text-white uppercase shadow"
                    >Upcoming</span
                >
            </div>
        </div>
        <div class="flex grow flex-col p-3">
            <h3 class="mb-1 truncate font-headline text-sm font-extrabold text-on-surface" :title="bid.name">
                {{ bid.name }}
            </h3>
            <p class="mb-2 text-sm font-black text-primary">
                {{ formatPrice(bid.price) }}
            </p>
            <div class="mt-auto">
                <div class="mb-1 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-secondary truncate">
                        {{ bid.bid_entry_points ?? 0 }}/{{ bid.open_points }}
                    </span>
                    <span
                        class="text-[10px] font-black"
                        :class="progressPct(bid) >= 100 ? 'text-error' : 'text-primary'"
                    >
                        {{ progressPct(bid) }}%
                    </span>
                </div>
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container-highest">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :style="{ width: progressPct(bid) + '%' }"
                        :class="progressPct(bid) >= 100 ? 'bg-error' : 'bg-linear-to-r from-primary to-tertiary'"
                    />
                </div>

                <div v-if="(bid.status === 1 || progressPct(bid) >= 100) && bid.ends_at"
                    class="mt-2 text-center text-[9px] font-bold text-outline">
                    <span class="material-symbols-outlined mr-1 align-middle text-[12px]">schedule</span>
                    <span class="align-middle">{{ getRemainingTime(bid.ends_at) }} left</span>
                </div>
            </div>
        </div>
    </div>
</template>
