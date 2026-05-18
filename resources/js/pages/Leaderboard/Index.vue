<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LeaderboardSection from '@/components/leaderboard/LeaderboardSection.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { LeaderboardAuction, LengthAwarePaginator } from '@/types/auction';

defineOptions({ layout: PublicLayout });

defineProps<{
    auctions: LengthAwarePaginator<LeaderboardAuction>;
    search?: string | null;
}>();
</script>

<template>
    <Head title="Leaderboard" />

    <LeaderboardSection :auctions="auctions" :search="search" />
</template>
