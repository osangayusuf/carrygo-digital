<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PointsBadge from '@/components/PointsBadge.vue';
import auctions from '@/routes/auctions/index';
import CountdownBadge from './CountdownBadge.vue';
import ProgressBar from './ProgressBar.vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
    auction: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div
        class="group flex h-full flex-col overflow-hidden rounded-xl border border-[#DDE8CC] bg-white shadow-sm transition-shadow hover:shadow-md"
    >
        <!-- Image Container -->
        <div class="relative h-48 w-full overflow-hidden bg-gray-100">
            <img
                v-if="auction.image"
                :src="auction.image"
                :alt="auction.name"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-gray-300"
            >
                <svg
                    class="h-12 w-12"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    ></path>
                </svg>
            </div>

            <div class="absolute top-3 left-3">
                <StatusBadge :status="auction.status" />
            </div>

            <div
                class="absolute right-3 bottom-3"
                v-if="auction.status === 'triggered' && auction.expires_at"
            >
                <CountdownBadge :expires-at="auction.expires_at" />
            </div>
        </div>

        <!-- Content -->
        <div class="flex grow flex-col p-5">
            <div
                class="mb-1 text-[10px] font-bold tracking-widest text-gray-500 uppercase"
            >
                {{ auction.category }}
            </div>
            <h3
                class="mb-2 line-clamp-2 text-lg leading-tight font-bold text-gray-900"
            >
                <Link
                    :href="auctions.show.url(auction.id)"
                    class="hover:text-forest"
                    >{{ auction.name }}</Link
                >
            </h3>

            <div class="mt-auto space-y-4 pt-4">
                <!-- Stats Row -->
                <div class="flex items-end justify-between">
                    <div>
                        <div
                            class="mb-0.5 text-[10px] tracking-wider text-gray-500 uppercase"
                        >
                            Current Bid
                        </div>
                        <div class="text-xl text-forest">
                            <PointsBadge :points="auction.current_points" />
                        </div>
                    </div>
                    <div class="text-right">
                        <div
                            class="mb-0.5 text-[10px] tracking-wider text-gray-500 uppercase"
                        >
                            Bids
                        </div>
                        <div class="text-lg font-bold text-gray-700">
                            {{ auction.bid_count }}
                        </div>
                    </div>
                </div>

                <!-- Progress Bar (Only if active) -->
                <div v-if="auction.status === 'active'" class="space-y-1.5">
                    <div
                        class="flex justify-between text-xs font-medium text-gray-500"
                    >
                        <span>Progress</span>
                        <span>
                            <PointsBadge :points="auction.opening_points" />
                            Goal
                        </span>
                    </div>
                    <ProgressBar
                        :current="auction.current_points"
                        :target="auction.opening_points"
                    />
                </div>

                <!-- CTA -->
                <Link
                    :href="auctions.show.url(auction.id)"
                    class="mt-2 block w-full rounded-lg bg-lemon py-3 text-center font-bold text-forest shadow-sm transition-colors hover:bg-yellow-400"
                >
                    {{
                        auction.status === 'closed'
                            ? 'View Details'
                            : 'Place Bid'
                    }}
                </Link>
            </div>
        </div>
    </div>
</template>
