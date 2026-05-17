<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TrendingBidsSection from '@/components/trending/TrendingBidsSection.vue';
import type { Bid } from '@/pages/Home.vue';
import type { LengthAwarePaginator } from '@/types';

defineProps<{
    bids: LengthAwarePaginator<Bid>;
    categories: string[];
    userPoints: number | null;
}>();
</script>

<template>
    <Head title="Trending Bids" />
    <TrendingBidsSection
        :bids="bids"
        :categories="categories"
        :userPoints="userPoints"
    />
</template>
