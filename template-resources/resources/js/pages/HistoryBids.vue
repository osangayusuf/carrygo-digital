<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import HistoryBidsSection from '@/components/history/HistoryBidsSection.vue';
import type { Bid } from '@/pages/Home.vue';
import type { LengthAwarePaginator } from '@/types';

export interface WonBid extends Bid {
    bid_winner?: {
        msisdn: string;
        total_points: number;
        created_at: string;
    } | null;
    reviews?: {
        id: number;
        rating: number;
        comment: string;
        user_id: string;
        created_at: string;
    }[];
}

defineProps<{
    bids: LengthAwarePaginator<WonBid>;
    userPoints: number | null;
}>();
</script>

<template>
    <Head title="Won Bids History" />
    <HistoryBidsSection
        :bids="bids"
        :userPoints="userPoints"
    />
</template>
