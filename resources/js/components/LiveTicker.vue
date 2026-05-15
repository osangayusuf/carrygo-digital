<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, useHttp } from '@inertiajs/vue3';
import auctions from '@/routes/auctions/index';

const triggeredAuctions = ref([]);
let intervalId = null;

const http = useHttp({});

const fetchTriggered = () => {
    http.get('/search?status=triggered', {
        onSuccess: (response) => {
            if (response && response.data) {
                triggeredAuctions.value = response.data;
            }
        },
        onError: () => {
            // Silently fail if not ready yet
        }
    });
};

onMounted(() => {
    // We'll enable polling once the Search endpoint is built.
    // fetchTriggered();
    // intervalId = setInterval(fetchTriggered, 15000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});
</script>

<template>
    <div v-if="triggeredAuctions.length > 0"
        class="bg-[#F5E642] text-forest text-sm font-semibold overflow-hidden relative w-full flex items-center h-8">
        <div class="whitespace-nowrap animate-marquee flex space-x-8 px-4">
            <span v-for="auction in triggeredAuctions" :key="auction.id" class="flex items-center">
                🔥 <Link :href="auctions.show.url(auction.id)" class="hover:underline ml-2">{{ auction.name }} -
                    LIVE COUNTDOWN</Link>
            </span>
        </div>
    </div>
</template>

<style scoped>
.animate-marquee {
    animation: marquee 20s linear infinite;
}

@keyframes marquee {
    0% {
        transform: translateX(100vw);
    }

    100% {
        transform: translateX(-100%);
    }
}
</style>
