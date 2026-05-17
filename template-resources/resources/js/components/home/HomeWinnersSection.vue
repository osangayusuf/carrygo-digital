<script setup lang="ts">
import { computed } from 'vue';
import type { Winner } from '@/pages/Home.vue';

const props = defineProps<{
    winners: Winner[];
}>();

const latestWinner = computed(() => props.winners[0] || null);
const remainingWinners = computed(() => props.winners.slice(1));

const loopWinners = computed(() => {
    if (remainingWinners.value.length <= 1) {
        return remainingWinners.value;
    }

    return [...remainingWinners.value, ...remainingWinners.value];
});

const marqueeDuration = computed(() => {
    return Math.max(20, remainingWinners.value.length * 5);
});

function maskedPhone(msisdn: string): { prefix: string; suffix: string } {
    const digits = msisdn.replace(/\D/g, '');

    return {
        prefix: `+${digits.slice(0, 3)} ${digits.slice(3, 6)}`,
        suffix: digits.slice(-4),
    };
}

function relativeTime(dateStr: string): string {
    const diff = Date.now() - new Date(dateStr).getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });

    if (days < 1) {
        return rtf.format(-Math.floor(diff / (1000 * 60 * 60)), 'hour');
    }

    return rtf.format(-days, 'day');
}
</script>

<template>
    <section class="overflow-hidden bg-surface-container-low py-20">
        <div
            class="mx-auto mb-12 flex max-w-screen-2xl items-end justify-between px-8"
        >
            <div>
                <h2
                    class="mb-2 font-headline text-3xl font-extrabold tracking-tight"
                >
                    Latest Winners
                </h2>
                <p class="font-medium text-secondary">
                    Real success stories from our global community
                </p>
            </div>
        </div>
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 overflow-hidden px-4 md:px-8 pb-2">
            <div
                v-if="latestWinner"
                class="w-full lg:w-auto lg:min-w-[320px] shrink-0 rounded-2xl border-2 border-primary-container/20 bg-surface-container-lowest p-6 shadow-sm transition-shadow hover:shadow-md"
            >
                <div class="mb-4 flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full text-primary bg-primary-container/30"
                    >
                        <span
                            class="material-symbols-outlined"
                            data-weight="fill"
                            >person</span
                        >
                    </div>
                    <div>
                        <p class="text-sm font-bold">
                            {{ maskedPhone(latestWinner.msisdn).prefix }}
                            <span class="text-secondary opacity-50">***</span>
                            {{ maskedPhone(latestWinner.msisdn).suffix }}
                        </p>
                        <p class="text-xs text-secondary">Verified Winner</p>
                    </div>
                </div>
                <h3 class="mb-1 font-headline text-lg font-bold">
                    {{ (latestWinner.bid?.name ?? '—').length > 25 ? (latestWinner.bid?.name ?? '—').substring(0, 25) + '...' : (latestWinner.bid?.name ?? '—') }}
                </h3>
                <div
                    class="mt-4 flex items-center justify-between border-t border-surface-container pt-4"
                >
                    <div class="flex items-center gap-1 text-tertiary">
                        <span class="material-symbols-outlined text-sm"
                            >stars</span
                        >
                        <span class="text-sm font-bold">
                            Won with {{ latestWinner.total_points.toLocaleString() }} points

                        </span>
                    </div>
                    <span class="text-xs font-medium text-secondary italic">
                        {{ relativeTime(latestWinner.created_at) }}
                    </span>
                </div>
            </div>

            <div
                v-if="remainingWinners.length"
                class="winners-marquee flex-1 min-w-0"
                :class="{ 'winners-marquee--static': remainingWinners.length <= 1 }"
                :style="{ '--winners-marquee-duration': `${marqueeDuration}s` }"
            >
                <div class="winners-marquee__track">
                <div
                    v-for="(winner, i) in loopWinners"
                    :key="`${winner.id}-${i}`"
                    class="min-w-[320px] shrink-0 rounded-2xl bg-surface-container-lowest p-6 shadow-sm transition-shadow hover:shadow-md"
                >
                    <div class="mb-4 flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full text-primary bg-secondary-container"
                        >
                            <span
                                class="material-symbols-outlined"
                                data-weight="fill"
                                >person</span
                            >
                        </div>
                        <div>
                            <p class="text-sm font-bold">
                                {{ maskedPhone(winner.msisdn).prefix }}
                                <span class="text-secondary opacity-50">***</span>
                                {{ maskedPhone(winner.msisdn).suffix }}
                            </p>
                            <p class="text-xs text-secondary">Verified Winner</p>
                        </div>
                    </div>
                    <h3 class="mb-1 font-headline text-lg font-bold">
                        {{ (winner.bid?.name ?? '—').length > 25 ? (winner.bid?.name ?? '—').substring(0, 25) + '...' : (winner.bid?.name ?? '—') }}
                    </h3>
                    <div
                        class="mt-4 flex items-center justify-between border-t border-surface-container pt-4"
                    >
                        <div class="flex items-center gap-1 text-tertiary">
                            <span class="material-symbols-outlined text-sm"
                                >stars</span
                            >
                            <span class="text-sm font-bold">
                                Won with {{ winner.total_points.toLocaleString() }} points

                            </span>
                        </div>
                        <span class="text-xs font-medium text-secondary italic">
                            {{ relativeTime(winner.created_at) }}
                        </span>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.winners-marquee {
    overflow: hidden;
}

.winners-marquee__track {
    display: flex;
    gap: 1.5rem;
    width: max-content;
    animation: winners-marquee var(--winners-marquee-duration, 24s) linear infinite;
    will-change: transform;
}

.winners-marquee:hover .winners-marquee__track {
    animation-play-state: paused;
}

.winners-marquee--static .winners-marquee__track {
    animation: none;
}

@keyframes winners-marquee {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-50%);
    }
}
</style>
