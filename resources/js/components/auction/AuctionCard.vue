<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import StatusBadge from './StatusBadge.vue';
import ProgressBar from './ProgressBar.vue';
import CountdownBadge from './CountdownBadge.vue';
import PointsBadge from '@/components/PointsBadge.vue';
import auctions from '@/routes/auctions/index';

const props = defineProps({
    auction: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <div
        class="bg-white rounded-xl overflow-hidden border border-[#DDE8CC] shadow-sm hover:shadow-md transition-shadow flex flex-col h-full group">
        <!-- Image Container -->
        <div class="relative h-48 w-full bg-gray-100 overflow-hidden">
            <img v-if="auction.image" :src="auction.image" :alt="auction.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
            </div>

            <div class="absolute top-3 left-3">
                <StatusBadge :status="auction.status" />
            </div>

            <div class="absolute bottom-3 right-3" v-if="auction.status === 'triggered' && auction.expires_at">
                <CountdownBadge :expires-at="auction.expires_at" />
            </div>
        </div>

        <!-- Content -->
        <div class="p-5 grow flex flex-col">
            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-1">{{ auction.category }}</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 leading-tight">
                <Link :href="auctions.show.url(auction.id)" class="hover:text-forest">{{ auction.name }}</Link>
            </h3>

            <div class="mt-auto pt-4 space-y-4">
                <!-- Stats Row -->
                <div class="flex justify-between items-end">
                    <div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-0.5">Current Bid</div>
                        <div class="text-forest text-xl">
                            <PointsBadge :points="auction.current_points" />
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-0.5">Bids</div>
                        <div class="font-bold text-gray-700 text-lg">{{ auction.bid_count }}</div>
                    </div>
                </div>

                <!-- Progress Bar (Only if active) -->
                <div v-if="auction.status === 'active'" class="space-y-1.5">
                    <div class="flex justify-between text-xs text-gray-500 font-medium">
                        <span>Progress</span>
                        <span>
                            <PointsBadge :points="auction.opening_points" /> Goal
                        </span>
                    </div>
                    <ProgressBar :current="auction.current_points" :target="auction.opening_points" />
                </div>

                <!-- CTA -->
                <Link :href="auctions.show.url(auction.id)"
                    class="block w-full text-center bg-lemon text-forest font-bold py-3 rounded-lg hover:bg-yellow-400 transition-colors shadow-sm mt-2">
                    {{ auction.status === 'closed' ? 'View Details' : 'Place Bid' }}
                </Link>
            </div>
        </div>
    </div>
</template>
