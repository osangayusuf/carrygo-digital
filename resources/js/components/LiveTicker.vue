<script setup lang="ts">
import { Link, useHttp } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { search } from '@/routes';
import auctions from '@/routes/auctions/index';

const triggeredAuctions = ref([]);
const intervalId = null;

const http = useHttp({});

const fetchTriggered = () => {
    http.get(search.url({ query: { status: 'triggered' } }), {
        onSuccess: (response) => {
            if (response && response.data) {
                triggeredAuctions.value = response.data;
            }
        },
        onError: () => {
            // Silently fail if not ready yet
        },
    });
};

onMounted(() => {
    // We'll enable polling once the Search endpoint is built.
    // fetchTriggered();
    // intervalId = setInterval(fetchTriggered, 15000);
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>

<template>
    <div
        v-if="triggeredAuctions.length > 0"
        class="relative flex h-8 w-full items-center overflow-hidden bg-[#F5E642] text-sm font-semibold text-forest"
    >
        <div class="flex animate-marquee space-x-8 px-4 whitespace-nowrap">
            <span
                v-for="auction in triggeredAuctions"
                :key="auction.id"
                class="flex items-center"
            >
                🔥
                <Link
                    :href="auctions.show.url(auction.id)"
                    class="ml-2 hover:underline"
                    >{{ auction.name }} - LIVE COUNTDOWN</Link
                >
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
