<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, ChevronLeft, ShieldCheck, Zap, Trophy } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AuctionGrid from '@/components/auction/AuctionGrid.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { trending, howToPlay } from '@/routes/index';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    stats: Object,
    categories: Array,
    liveAuctions: Object,
});

// Categories are deferred, so we might need a fallback or skeleton
const selectedCategory = ref('All');

const selectCategory = (cat) => {
    selectedCategory.value = cat;
};

// Promo carousel
const activeSlide = ref(0);
const slides = [
    { id: 1, image: 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80', title: 'MacBook Pro 16" Live Now!' },
    { id: 2, image: 'https://images.unsplash.com/photo-1523206489230-c012c64b2b48?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80', title: 'iPhone 15 Pro Max Bidding Open' },
];

const nextSlide = () => activeSlide.value = (activeSlide.value + 1) % slides.length;
const prevSlide = () => activeSlide.value = (activeSlide.value - 1 + slides.length) % slides.length;

// Filter auctions by category
const filteredAuctions = computed(() => {
    if (!props.liveAuctions?.data) return [];
    if (selectedCategory.value === 'All') return props.liveAuctions.data;
    return props.liveAuctions.data.filter(a => a.category === selectedCategory.value);
});
</script>

<template>
    <div class="space-y-12">
        <!-- Hero Section -->
        <section class="bg-forest rounded-3xl overflow-hidden relative shadow-xl">
            <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
            <div class="relative px-6 py-16 sm:px-12 sm:py-24 flex flex-col md:flex-row items-center">
                <div class="md:w-3/5 text-center md:text-left mb-10 md:mb-0 z-10">
                    <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mb-6 leading-tight">
                        Win Premium Tech <br />for a Fraction of the Price
                    </h1>
                    <p class="text-lg text-gray-300 mb-8 max-w-xl mx-auto md:mx-0">
                        Join Nigeria's trusted e-auction platform. Use your points to bid on exclusive gadgets and appliances. Last bidder standing wins!
                    </p>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 justify-center md:justify-start">
                        <Link :href="trending.url()" class="bg-lemin text-forest font-bold px-8 py-3.5 rounded-xl hover:bg-yellow-400 transition-colors shadow-lg text-center">
                            Start Bidding
                        </Link>
                        <Link :href="howToPlay.url()" class="bg-transparent border-2 border-sage-border text-white font-bold px-8 py-3.5 rounded-xl hover:bg-white/10 transition-colors text-center">
                            How It Works
                        </Link>
                    </div>
                </div>

                <div class="md:w-2/5 grid grid-cols-2 gap-4 z-10 w-full">
                    <div class="bg-white/10 backdrop-blur-sm p-4 rounded-xl text-center border border-white/20">
                        <Trophy class="w-8 h-8 text-lemin mx-auto mb-2" />
                        <div class="text-3xl font-bold text-white mb-1">{{ stats?.winnersCount?.toLocaleString() || '0' }}</div>
                        <div class="text-xs text-gray-300 uppercase tracking-wider">Happy Winners</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm p-4 rounded-xl text-center border border-white/20">
                        <Zap class="w-8 h-8 text-lemin mx-auto mb-2" />
                        <div class="text-3xl font-bold text-white mb-1">{{ stats?.totalBids?.toLocaleString() || '0' }}</div>
                        <div class="text-xs text-gray-300 uppercase tracking-wider">Total Bids Placed</div>
                    </div>
                    <div class="col-span-2 bg-white/10 backdrop-blur-sm p-4 rounded-xl text-center border border-white/20 flex flex-col items-center justify-center">
                        <ShieldCheck class="w-8 h-8 text-lemin mx-auto mb-2" />
                        <div class="text-sm font-bold text-white">100% Secure & Verified</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Promo Carousel -->
        <section class="relative rounded-2xl overflow-hidden h-64 md:h-80 shadow-md group">
            <div
                v-for="(slide, index) in slides"
                :key="slide.id"
                class="absolute inset-0 transition-opacity duration-500"
                :class="activeSlide === index ? 'opacity-100 z-10' : 'opacity-0 z-0'"
            >
                <img :src="slide.image" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/40 to-transparent flex items-end">
                    <div class="p-8">
                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-3 inline-block">Hot Auction</span>
                        <h2 class="text-3xl font-bold text-white">{{ slide.title }}</h2>
                    </div>
                </div>
            </div>

            <button @click="prevSlide" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-black/50 hover:bg-black/80 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all">
                <ChevronLeft class="w-6 h-6" />
            </button>
            <button @click="nextSlide" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-black/50 hover:bg-black/80 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all">
                <ChevronRight class="w-6 h-6" />
            </button>

            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex space-x-2">
                <button
                    v-for="(_, index) in slides"
                    :key="index"
                    @click="activeSlide = index"
                    class="w-2.5 h-2.5 rounded-full transition-colors"
                    :class="activeSlide === index ? 'bg-lemin' : 'bg-white/50'"
                ></button>
            </div>
        </section>

        <!-- Main Content: Sidebar + Grid -->
        <section class="flex flex-col lg:flex-row gap-8">
            <!-- Desktop Sidebar -->
            <aside class="hidden lg:block w-64 shrink-0">
                <div class="sticky top-24 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Categories</h3>
                    <ul class="space-y-2">
                        <li>
                            <button
                                @click="selectCategory('All')"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                :class="selectedCategory === 'All' ? 'bg-forest text-white' : 'text-gray-600 hover:bg-gray-100'"
                            >
                                All Auctions
                            </button>
                        </li>
                        <template v-if="categories">
                            <li v-for="category in categories" :key="category">
                                <button
                                    @click="selectCategory(category)"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                    :class="selectedCategory === category ? 'bg-forest text-white' : 'text-gray-600 hover:bg-gray-100'"
                                >
                                    {{ category }}
                                </button>
                            </li>
                        </template>
                        <li v-else class="text-sm text-gray-400 py-2">Loading categories...</li>
                    </ul>
                </div>
            </aside>

            <!-- Grid Area -->
            <div class="grow">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ selectedCategory === 'All' ? 'Live Auctions' : `${selectedCategory} Auctions` }}
                    </h2>
                    <div class="lg:hidden">
                        <!-- Mobile Category Dropdown (Simplified) -->
                        <select
                            v-model="selectedCategory"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-forest focus:border-forest block w-full p-2.5"
                        >
                            <option value="All">All Categories</option>
                            <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                        </select>
                    </div>
                </div>

                <div v-if="!liveAuctions" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Skeleton Loaders for deferred prop -->
                    <div v-for="i in 6" :key="i" class="bg-white rounded-xl border border-gray-200 h-80 animate-pulse flex flex-col">
                        <div class="h-48 bg-gray-200 rounded-t-xl w-full"></div>
                        <div class="p-5 space-y-4 grow">
                            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                            <div class="h-6 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-10 bg-gray-200 rounded mt-auto w-full"></div>
                        </div>
                    </div>
                </div>

                <AuctionGrid v-else :auctions="filteredAuctions" empty-message="No live auctions available in this category." />

                <div class="mt-10 text-center" v-if="liveAuctions?.meta?.last_page > 1">
                    <Link :href="trending.url()" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        View All Auctions
                    </Link>
                </div>
            </div>
        </section>
    </div>
</template>
