<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import OpenBidsSection from '@/components/open-bids/OpenBidsSection.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { Bid, LengthAwarePaginator } from '@/types/auction';

defineOptions({ layout: PublicLayout });

defineProps<{
    bids: LengthAwarePaginator<Bid>;
    userPoints: number | null;
    search?: string | null;
    sort?: string | null;
}>();
</script>

<template>
    <Head title="Open Bids" />

    <OpenBidsSection
        :bids="bids"
        :user-points="userPoints"
        :search="search"
        :sort="sort"
    />
</template>
