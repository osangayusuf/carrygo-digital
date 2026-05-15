<script setup>
import AuctionCard from './AuctionCard.vue';
import EmptyState from '@/components/EmptyState.vue';

const props = defineProps({
    auctions: {
        type: Array,
        required: true
    },
    emptyMessage: {
        type: String,
        default: 'No auctions found.'
    }
});
</script>

<template>
    <div>
        <div v-if="auctions.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <div v-for="auction in auctions" :key="auction.id">
                <AuctionCard :auction="auction" />
            </div>
        </div>
        <div v-else class="py-12">
            <EmptyState :message="emptyMessage" />
        </div>
    </div>
</template>
