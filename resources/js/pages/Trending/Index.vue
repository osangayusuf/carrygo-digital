<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TrendingBidsSection from '@/components/trending/TrendingBidsSection.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { Bid, LengthAwarePaginator } from '@/types/auction';

defineOptions({ layout: PublicLayout });

defineProps<{
    bids: LengthAwarePaginator<Bid>;
    categories: string[];
    userPoints: number | null;
    search?: string | null;
    category?: string | null;
    sort?: string | null;
}>();
</script>

<template>
    <Head title="Trending Bids" />

    <TrendingBidsSection
        :bids="bids"
        :categories="categories"
        :user-points="userPoints"
        :search="search"
        :category="category"
        :sort="sort"
    />
</template>
