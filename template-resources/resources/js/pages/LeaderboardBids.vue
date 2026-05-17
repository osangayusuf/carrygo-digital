<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LeaderboardBidsSection from '@/components/leaderboard/LeaderboardBidsSection.vue';
import type { Bid } from '@/pages/Home.vue';
import type { LengthAwarePaginator } from '@/types';

export interface LeaderboardBid extends Bid {
    top_bidders?: {
        msisdn: string;
        total_points: number;
    }[];
}

defineProps<{
    bids: LengthAwarePaginator<LeaderboardBid>;
}>();
</script>

<template>
    <Head title="Competition Leaderboard" />
    <LeaderboardBidsSection :bids="bids" />
</template>
