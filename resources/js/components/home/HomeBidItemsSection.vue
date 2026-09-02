<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BidCard from '@/components/cards/BidCard.vue';
import { trending } from '@/routes/index';
import type { Bid } from '@/types/auction';

defineProps<{
    bids: Bid[];
    userPoints: number | null;
}>();
</script>

<template>
    <section class="mx-auto max-w-screen-2xl px-8 pt-5 pb-8">
        <div
            class="mb-8 flex flex-col items-start justify-between gap-6 md:flex-row md:items-center"
        >
            <h2
                class="py-2 font-headline text-2xl font-extrabold tracking-tighter md:text-4xl"
            >
                Live Opportunities
            </h2>
        </div>

        <!-- Empty state -->
        <div
            v-if="bids.length === 0"
            class="flex flex-col items-center justify-center py-24 text-center"
        >
            <span class="material-symbols-outlined mb-4 text-5xl text-outline"
                >search_off</span
            >
            <p class="font-headline text-xl font-bold text-on-surface-variant">
                No bids found
            </p>
            <p class="mt-2 text-sm text-outline">
                Try adjusting your search or check back soon.
            </p>
        </div>

        <div v-else class="grid grid-cols-2 gap-2.5 md:grid-cols-4">
            <BidCard
                v-for="bid in bids.slice(0, 4)"
                :key="bid.id"
                :bid="bid"
                :user-points="userPoints"
            />
        </div>

        <div class="mt-16 text-center">
            <Link
                :href="trending.url()"
                class="rounded-full bg-surface-container-low px-12 py-4 font-bold text-on-surface transition-all hover:bg-surface-container-high"
            >
                View All Auction Items
            </Link>
        </div>
    </section>
</template>
