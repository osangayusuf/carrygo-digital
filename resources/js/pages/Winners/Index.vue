<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WinnersSection from '@/components/winners/WinnersSection.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { LengthAwarePaginator, WinnerListing } from '@/types/auction';

defineOptions({ layout: PublicLayout });

defineProps<{
    winners: LengthAwarePaginator<WinnerListing>;
    search?: string | null;
}>();
</script>

<template>
    <Head title="Winners" />

    <WinnersSection :winners="winners" :search="search" />
</template>
