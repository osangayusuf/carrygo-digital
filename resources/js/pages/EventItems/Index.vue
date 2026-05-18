<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EventItemsSection from '@/components/events/EventItemsSection.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { Bid, LengthAwarePaginator } from '@/types/auction';

defineOptions({ layout: PublicLayout });

defineProps<{
    bids: LengthAwarePaginator<Bid>;
    userPoints: number | null;
    search?: string | null;
    sort?: string | null;
    status?: string | null;
}>();
</script>

<template>
    <Head title="Event Items" />

    <EventItemsSection
        :bids="bids"
        :user-points="userPoints"
        :search="search"
        :sort="sort"
        :status="status"
    />
</template>
