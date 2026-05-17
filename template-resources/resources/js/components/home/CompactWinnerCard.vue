<script setup lang="ts">
import type { Winner } from '@/pages/Home.vue';

const props = defineProps<{
    winner: Winner;
}>();

function maskedPhone(msisdn: string | undefined): { prefix: string; suffix: string } {
    if (!msisdn) return { prefix: '+234', suffix: 'xxxx' };

    const digits = msisdn.replace(/\D/g, '');

    return {
        prefix: `+${digits.slice(0, 3)} ${digits.slice(3, 6)}`,
        suffix: digits.slice(-4),
    };
}
</script>

<template>
    <div
        class="group flex w-auto md:min-w-0 shrink-0 snap-start flex-col overflow-hidden rounded-2xl border border-surface-container bg-surface-container-lowest transition-all hover:shadow-md">
        <div class="relative aspect-square overflow-hidden bg-surface-container-low">
            <img v-if="winner.bid" :alt="winner.bid.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                :src="winner.bid.image" />
            <div v-else class="flex h-full w-full items-center justify-center text-outline">
                <span class="material-symbols-outlined text-4xl">inventory_2</span>
            </div>

            <div class="absolute top-2 right-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-tertiary text-on-tertiary shadow">
                    <span class="material-symbols-outlined text-[14px]">emoji_events</span>
                </div>
            </div>
        </div>
        <div class="flex grow flex-col p-3">
            <h3 class="mb-1 truncate font-headline text-sm font-extrabold text-on-surface" :title="winner.bid?.name">
                {{ winner.bid?.name ?? 'Unknown Item' }}
            </h3>
            <p class="mb-2 truncate text-xs font-bold text-on-surface-variant">
                {{ maskedPhone(winner.msisdn).prefix }}
                <span class="text-secondary opacity-50">***</span>
                {{ maskedPhone(winner.msisdn).suffix }}
            </p>
            <div class="mt-auto pt-2 border-t border-surface-container">
                <p class="text-[10px] font-bold text-secondary uppercase tracking-wider">Winning Points</p>
                <p class="text-sm font-black text-tertiary">{{ winner.total_points.toLocaleString() }}</p>
            </div>
        </div>
    </div>
</template>
