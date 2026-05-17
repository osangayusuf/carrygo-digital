<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EventBidsSection from '@/components/events/EventBidsSection.vue';
import type { Bid } from '@/pages/Home.vue';
import type { LengthAwarePaginator } from '@/types';

defineProps<{
    bids: LengthAwarePaginator<Bid>;
    userPoints: number | null;
}>();
</script>

<template>
    <Head title="Event Items" />
    <EventBidsSection
        :bids="bids"
        :userPoints="userPoints"
    />
</template>
