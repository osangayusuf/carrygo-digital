<script setup lang="ts">
import { computed } from 'vue';
import { useAuctionTimeline } from '@/composables/useAuctionTimeline';

const props = defineProps<{
    auctionId: number;
}>();

const auctionIdRef = computed(() => props.auctionId);

const { entries, isLoading, error, feedEl } = useAuctionTimeline(auctionIdRef);

function formatTime(iso: string): string {
    return new Date(iso).toLocaleTimeString(undefined, {
        hour: 'numeric',
        minute: '2-digit',
    });
}
</script>

<template>
    <div class="mb-4">
        <p
            class="mb-2 text-xs font-bold tracking-widest text-outline uppercase"
        >
            Live activity
        </p>

        <div
            ref="feedEl"
            class="max-h-52 overflow-y-auto rounded-2xl border-2 border-outline-variant bg-surface-container-low p-3"
        >
            <div v-if="isLoading" class="space-y-2">
                <div
                    v-for="n in 3"
                    :key="n"
                    class="h-10 animate-pulse rounded-xl bg-surface-container"
                />
            </div>

            <p
                v-else-if="error"
                class="py-6 text-center text-xs font-semibold text-error"
            >
                {{ error }}
            </p>

            <p
                v-else-if="entries.length === 0"
                class="py-6 text-center text-xs font-semibold text-on-surface-variant"
            >
                No activity yet — be the first to bid.
            </p>

            <ul v-else class="space-y-2">
                <li
                    v-for="entry in entries"
                    :key="entry.id"
                    :class="
                        entry.is_system
                            ? 'flex justify-center'
                            : entry.is_mine
                              ? 'flex justify-end'
                              : 'flex justify-start'
                    "
                >
                    <div
                        class="max-w-[85%] rounded-2xl px-3 py-2"
                        :class="
                            entry.is_system
                                ? 'bg-surface-container text-center'
                                : entry.is_mine
                                  ? 'border border-primary/30 bg-primary-container'
                                  : 'bg-surface-container'
                        "
                    >
                        <p
                            class="text-xs leading-snug font-semibold"
                            :class="
                                entry.is_system
                                    ? 'text-on-surface-variant'
                                    : 'text-on-surface'
                            "
                        >
                            {{ entry.message }}
                        </p>
                        <p class="mt-1 text-[10px] text-outline">
                            {{ formatTime(entry.occurred_at) }}
                        </p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
