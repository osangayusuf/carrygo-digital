<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BidCard from '@/components/cards/BidCard.vue';
import HomeBidItemsSection from '@/components/home/HomeBidItemsSection.vue';
import HomeEventPopup from '@/components/home/HomeEventPopup.vue';
import HomeHeroSection from '@/components/home/HomeHeroSection.vue';
import HomePromoSection from '@/components/home/HomePromoSection.vue';
import HomeTestimonialsSection from '@/components/home/HomeTestimonialsSection.vue';
import HomeWinnerPopup from '@/components/home/HomeWinnerPopup.vue';
import HomeWinnersSection from '@/components/home/HomeWinnersSection.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { formatPrice } from '@/lib/utils';
import { trending, openBids as openBidsRoute, tasks } from '@/routes/index';
import type { Bid } from '@/types/auction';

defineOptions({ layout: PublicLayout });

export type { Bid, Bidder } from '@/types/auction';

export type Winner = {
    id: number;
    msisdn: string;
    total_points: number;
    bidid: number;
    created_at: string;
    bid: { id: number; name: string; image: string | null; url: string; price: string; } | null;
};

export type Review = {
    id: number;
    user_id: string;
    rating: number;
    comment: string;
    social_platform?: string | null;
    social_handle?: string | null;
    bidid: number | null;
    created_at: string;
    bid: { id: number; name: string; image: string | null; url: string; } | null;
};

const props = defineProps<{
    search?: string | null;
    heroBid?: Bid | null;
    bids?: Bid[];
    trendingBids?: Bid[];
    recentlyAddedBids?: Bid[];
    openBids?: Bid[];
    luxuryBids?: Bid[];
    categoryBids?: Record<string, Bid[]>;
    categories?: string[];
    winners?: Winner[];
    userPoints?: number | null;
    reviews?: Review[];
    winnerPopup?: Winner | null;
    eventPopupBid?: Bid | null;
}>();

const bidItemsSectionRef = ref<InstanceType<typeof HomeBidItemsSection> | null>(null);

function openBidModal(bid: Bid): void {
    bidItemsSectionRef.value?.openBidModal(bid);
}

const hasActiveSearch = computed(() => Boolean(props.search?.trim()));

const hasAnyBids = computed(() => {
    if ((props.bids?.length ?? 0) > 0) {
        return true;
    }

    if ((props.trendingBids?.length ?? 0) > 0) {
        return true;
    }

    if ((props.recentlyAddedBids?.length ?? 0) > 0) {
        return true;
    }

    if ((props.openBids?.length ?? 0) > 0) {
        return true;
    }

    if ((props.luxuryBids?.length ?? 0) > 0) {
        return true;
    }

    if (props.categoryBids) {
        for (const bids of Object.values(props.categoryBids)) {
            if (bids.length > 0) {
                return true;
            }
        }
    }

    return false;
});

const getCategoryIcon = (category: string): string => {
    const map: Record<string, string> = {
        Appliances: 'kitchen',
        Computing: 'computer',
        Electronics: 'devices',
        Fashion: 'checkroom',
        'Gadgets & Accessories': 'headphones',
        Gaming: 'sports_esports',
        'Health & Beauty': 'spa',
        'Home & Office': 'home_work',
        'Musical Instrument': 'piano',
        Supermarket: 'local_grocery_store',
        Bags: 'shopping_bag',
        Shoes: 'footprint',
        Watches: 'watch',
        Jewelry: 'diamond',
        Accessories: 'redeem',
        Beauty: 'spa',
        Sports: 'sports_soccer',
        Gadgets: 'smartphone',
    };

    return map[category] || 'category';
};
</script>

<template>
    <Head title="Home" />

    <HomeWinnerPopup v-if="props.winnerPopup" :winner="props.winnerPopup" />
    <HomeEventPopup
        v-if="props.eventPopupBid"
        :bid="props.eventPopupBid"
        @open-bid-modal="openBidModal"
    />

    <HomeBidItemsSection
        ref="bidItemsSectionRef"
        :bids="props.bids ?? []"
        :user-points="props.userPoints ?? null"
        class="hidden"
    />

    <div class="overflow-x-hidden text-left bg-sage-bg text-ink font-sans">
        <HomeHeroSection :get-category-icon="getCategoryIcon" />

        <!-- HYPE MARQUEE BANNER -->
        <div class="bg-linear-to-r from-forest to-navy border-y-2 border-lemon py-2 overflow-hidden my-2">
            <div class="flex items-center whitespace-nowrap animate-marquee">
                <span class="inline-flex items-center gap-2 px-8 text-xs font-black tracking-widest text-lemon uppercase">
                    🔥 50 people will win hot items this week!
                </span>
                <span class="inline-flex items-center gap-2 px-8 text-xs font-black tracking-widest text-white uppercase">
                    ⚡ Hot items are trending on the site - bid now!
                </span>
                <span class="inline-flex items-center gap-2 px-8 text-xs font-black tracking-widest text-lemon uppercase">
                    📱 Follow this month's jackpot winner on social media!
                </span>
                <span class="inline-flex items-center gap-2 px-8 text-xs font-black tracking-widest text-white uppercase">
                    👀 Check out the most visited item today!
                </span>
                <!-- Duplicate for seamless loop -->
                <span class="inline-flex items-center gap-2 px-8 text-xs font-black tracking-widest text-lemon uppercase">
                    🔥 50 people will win hot items this week!
                </span>
                <span class="inline-flex items-center gap-2 px-8 text-xs font-black tracking-widest text-white uppercase">
                    ⚡ Hot items are trending on the site - bid now!
                </span>
                <span class="inline-flex items-center gap-2 px-8 text-xs font-black tracking-widest text-lemon uppercase">
                    📱 Follow this month's jackpot winner on social media!
                </span>
                <span class="inline-flex items-center gap-2 px-8 text-xs font-black tracking-widest text-white uppercase">
                    👀 Check out the most visited item today!
                </span>
            </div>
        </div>

        <!-- LIVE OPPORTUNITIES (Prominent relocation) -->
        <div class="max-w-[1300px] mx-auto mt-4 mb-6 px-4" v-if="props.openBids && props.openBids.length > 0">
            <div class="flex items-center justify-between mb-3.5">
                <div class="font-condensed text-2xl font-extrabold text-ink flex items-center">
                    <span class="relative flex h-3 w-3 mr-2.5 align-middle">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                    <span class="pi pi-bolt text-lg text-lemon mr-1"></span> Live Opportunities
                </div>
                <Link :href="openBidsRoute.url()" class="text-forest text-sm font-bold hover:underline">View All →
                </Link>
            </div>
            <div class="grid gap-2.5 grid-cols-2 md:grid-cols-5">
                <BidCard v-for="bid in props.openBids.slice(0, 4)" :key="bid.id" :bid="bid"
                    :user-points="props.userPoints ?? null" />
            </div>
        </div>

        <div
            v-if="hasActiveSearch && !hasAnyBids"
            class="max-w-[1300px] mx-auto mt-4 mb-2 px-4"
        >
            <div class="rounded-xl border-2 border-sage-border-dark bg-white px-6 py-10 text-center shadow-sm">
                <span class="material-symbols-outlined mb-3 text-5xl text-muted-green">search_off</span>
                <p class="font-condensed text-xl font-extrabold text-ink">No auctions found</p>
                <p class="mt-2 text-sm text-muted-green">
                    Nothing matched "<span class="font-bold text-ink">{{ props.search }}</span>". Try a different keyword or
                    browse all categories below.
                </p>
            </div>
        </div>

        <!-- PRODUCT STRIP -->
        <div class="max-w-[1300px] mx-auto mt-2.5 px-4 grid gap-2.5 grid-cols-2 md:grid-cols-5"
            v-if="props.bids && props.bids.length > 0">
            <BidCard v-for="bid in props.bids.slice(0, 8)" :key="bid.id" :bid="bid"
                :user-points="props.userPoints ?? null" />
        </div>

        <!-- LIVE TICKER -->
        <div class="bg-navy border-y-2 border-lemon py-2 overflow-hidden my-3" v-if="props.bids && props.bids.length > 0">
            <div class="flex items-center whitespace-nowrap animate-marquee">
                <span class="inline-flex items-center gap-2 px-7 text-sm font-bold text-lemon"
                    v-for="bid in props.bids" :key="bid.id">
                    <span class="w-2 h-2 rounded-full bg-lemon inline-block"></span>
                    {{ bid.name }}
                    <span class="text-white font-extrabold">{{ formatPrice(bid.price) }}</span>
                    <span class="bg-forest text-lemon text-xs font-extrabold px-2 py-0.5 rounded-sm ml-1"
                        v-if="bid.status === 1">LIVE</span>
                </span>
            </div>
        </div>

        <!-- CATEGORIES -->
        <div class="max-w-[1300px] mx-auto mb-5 px-4" v-if="props.categories && props.categories.length > 0">
            <div class="flex items-center justify-between mb-3.5">
                <div class="font-condensed text-2xl font-extrabold text-ink flex items-center">
                    <span class="inline-block w-1 h-[22px] bg-forest rounded-sm mr-2 align-middle"></span> Browse
                    Categories
                </div>
                <Link :href="trending.url()" class="text-forest text-sm font-bold hover:underline">View All →</Link>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
                <Link :href="trending.url() + '?category=' + encodeURIComponent(cat)"
                    class="group bg-white rounded-lg p-3.5 px-2 text-center cursor-pointer border-2 border-transparent transition-all duration-200 shadow-sm hover:border-lemon hover:bg-navy hover:text-lemon hover:-translate-y-0.5"
                    v-for="cat in props.categories" :key="cat" style="text-decoration:none;">
                    <div class="text-3xl mb-1.5"><span class="material-symbols-outlined">{{ getCategoryIcon(cat)
                    }}</span></div>
                    <div class="text-xs font-extrabold text-ink group-hover:text-lemon">{{ cat }}</div>
                </Link>
            </div>
        </div>

        <!-- TRENDING BIDS -->
        <div class="max-w-[1300px] mx-auto mb-5 px-4" v-if="props.trendingBids && props.trendingBids.length > 0">
            <div class="flex items-center justify-between mb-3.5">
                <div class="font-condensed text-2xl font-extrabold text-ink flex items-center">
                    <span class="inline-block w-1 h-[22px] bg-forest rounded-sm mr-2 align-middle"></span>
                    <span class="pi pi-fire text-lg text-amber mr-1"></span> Trending Bids
                </div>
                <Link :href="trending.url()" class="text-forest text-sm font-bold hover:underline">View More →</Link>
            </div>
            <div class="grid gap-2.5 grid-cols-2 md:grid-cols-5">
                <BidCard v-for="bid in props.trendingBids.slice(0, 10)" :key="bid.id" :bid="bid"
                    :user-points="props.userPoints ?? null" />
            </div>
        </div>

        <!-- RECENTLY ADDED -->
        <div class="max-w-[1300px] mx-auto mb-5 px-4"
            v-if="props.recentlyAddedBids && props.recentlyAddedBids.length > 0">
            <div class="flex items-center justify-between mb-3.5">
                <div class="font-condensed text-2xl font-extrabold text-ink flex items-center">
                    <span class="inline-block w-1 h-[22px] bg-forest rounded-sm mr-2 align-middle"></span>
                    <span class="pi pi-sparkles text-lg text-amber mr-1"></span> Recently Added
                </div>
                <Link :href="trending.url() + '?sort=recent'"
                    class="text-forest text-sm font-bold hover:underline">View More →</Link>
            </div>
            <div class="grid gap-2.5 grid-cols-2 md:grid-cols-5">
                <BidCard v-for="bid in props.recentlyAddedBids.slice(0, 10)" :key="bid.id" :bid="bid"
                    :user-points="props.userPoints ?? null" />
            </div>
        </div>

        <!-- FEATURE BANNERS -->
        <div class="max-w-[1300px] mx-auto mb-5 px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
                <div
                    class="rounded-xl py-6 px-7 text-white flex items-center justify-between overflow-hidden relative bg-linear-to-br from-navy to-forest">
                    <div>
                        <div
                            class="bg-lemon/25 text-lemon text-[10px] font-extrabold px-2.5 py-1 rounded-xl inline-block mb-2 uppercase tracking-wider">
                            <span class="pi pi-bullseye mr-1"></span> Task Center
                        </div>
                        <div class="font-condensed text-3xl font-black leading-tight mb-1.5 text-white">Win
                            More<br>Points Free!</div>
                        <div class="text-xs opacity-85 mb-3.5 leading-snug text-[#cde]">Complete simple tasks to earn
                            bidding points and increase your chances of winning luxury items.</div>
                        <Link :href="tasks.url()"
                            class="bg-lemon text-navy py-2 px-4 rounded-md text-sm font-extrabold hover:bg-amber transition-colors">
                        Visit Task Center →</Link>
                    </div>
                    <div class="text-[90px] opacity-[0.18] font-black leading-none"><span class="pi pi-trophy"></span>
                    </div>
                </div>
                <div
                    class="rounded-xl py-6 px-7 text-white flex items-center justify-between overflow-hidden relative bg-linear-to-br from-ink to-forest">
                    <div>
                        <div
                            class="bg-lemon/25 text-lemon text-[10px] font-extrabold px-2.5 py-1 rounded-xl inline-block mb-2 uppercase tracking-wider">
                            <span class="pi pi-lock mr-1"></span> Verified Integrity
                        </div>
                        <div class="font-condensed text-3xl font-black leading-tight mb-1.5 text-white">100%
                            Fair<br>&amp; Secure!</div>
                        <div class="text-xs opacity-85 mb-3.5 leading-snug text-[#cde]">Every bid is recorded on our
                            secure database. Fully transparent, fully trustworthy auction process.</div>
                        <button
                            class="bg-lemon text-navy py-2 px-4 rounded-md text-sm font-extrabold hover:bg-amber transition-colors">Learn
                            More →</button>
                    </div>
                    <div class="text-[90px] opacity-[0.18] font-black leading-none"><span class="pi pi-shield"></span>
                    </div>
                </div>
            </div>
        </div>



        <!-- PREMIUM & LUXURY -->
        <div class="max-w-[1300px] mx-auto mb-5 px-4" v-if="props.luxuryBids && props.luxuryBids.length > 0">
            <div class="flex items-center justify-between mb-3.5">
                <div class="font-condensed text-2xl font-extrabold text-ink flex items-center">
                    <span class="inline-block w-1 h-[22px] bg-forest rounded-sm mr-2 align-middle"></span>
                    <span class="pi pi-star text-lg text-amber mr-1"></span> Premium &amp; Luxury
                </div>
                <Link :href="trending.url() + '?sort=price'"
                    class="text-forest text-sm font-bold hover:underline">View All →</Link>
            </div>
            <div class="grid gap-2.5 grid-cols-2 md:grid-cols-5">
                <BidCard v-for="bid in props.luxuryBids.slice(0, 10)" :key="bid.id" :bid="bid"
                    :user-points="props.userPoints ?? null" />
            </div>
        </div>

                <!-- BIDDING PROCESS SCROLLING TICKER -->
        <div class="bg-navy border-y-2 border-lemon py-4 overflow-hidden mb-5">
            <div class="flex items-center whitespace-nowrap animate-marquee">
                <!-- Step 1 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">payments</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 1</div>
                        <div class="text-sm font-extrabold text-white">Buy Points</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>

                <!-- Step 2 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">search</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 2</div>
                        <div class="text-sm font-extrabold text-white">Choose Item to Bid For</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>

                <!-- Step 3 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">gavel</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 3</div>
                        <div class="text-sm font-extrabold text-white">Bid Points</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>

                <!-- Step 4 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">emoji_events</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 4</div>
                        <div class="text-sm font-extrabold text-white">Highest Bidder Wins</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>

                <!-- Step 5 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">local_shipping</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 5</div>
                        <div class="text-sm font-extrabold text-white">Items Delivered</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>

                <!-- Step 6 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">price_check</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 6</div>
                        <div class="text-sm font-extrabold text-white">No Payment for Item Won</div>
                    </div>
                </div>

                <!-- Duplicate steps for seamless loop -->
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>
                <!-- Step 1 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">payments</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 1</div>
                        <div class="text-sm font-extrabold text-white">Buy Points</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>
                <!-- Step 2 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">search</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 2</div>
                        <div class="text-sm font-extrabold text-white">Choose Item to Bid For</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>
                <!-- Step 3 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">gavel</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 3</div>
                        <div class="text-sm font-extrabold text-white">Bid Points</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>
                <!-- Step 4 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">emoji_events</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 4</div>
                        <div class="text-sm font-extrabold text-white">Highest Bidder Wins</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>
                <!-- Step 5 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">local_shipping</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 5</div>
                        <div class="text-sm font-extrabold text-white">Items Delivered</div>
                    </div>
                </div>
                <!-- Arrow -->
                <i class="pi pi-chevron-right text-lemon/50 text-xs"></i>
                <!-- Step 6 -->
                <div class="inline-flex items-center gap-3 px-10">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-lemon/20 text-lemon border border-lemon/35">
                        <span class="material-symbols-outlined text-lg">price_check</span>
                    </span>
                    <div class="text-left">
                        <div class="text-xs font-black text-[#8aaa80] uppercase tracking-wider">Step 6</div>
                        <div class="text-sm font-extrabold text-white">No Payment for Item Won</div>
                    </div>
                </div>
            </div>
        </div>


        <!-- CATEGORY BIDS -->
        <template v-if="props.categoryBids">
            <div v-for="(bids, categoryName) in props.categoryBids" :key="String(categoryName)"
                class="max-w-[1300px] mx-auto mb-5 px-4">
                <template v-if="bids && bids.length > 0">
                    <div class="flex items-center justify-between mb-3.5">
                        <div class="font-condensed text-2xl font-extrabold text-ink flex items-center">
                            <span class="inline-block w-1 h-[22px] bg-forest rounded-sm mr-2 align-middle"></span>
                            <span
                                class="material-symbols-outlined text-[22px] text-forest mr-1.5 leading-none">{{ getCategoryIcon(String(categoryName)) }}</span>
                            {{ categoryName }}
                        </div>
                        <Link
                            :href="trending.url() + '?category=' + encodeURIComponent(String(categoryName))"
                            class="text-forest text-sm font-bold hover:underline">View All →</Link>
                    </div>
                    <div class="grid gap-2.5 grid-cols-2 md:grid-cols-5">
                        <BidCard v-for="bid in bids" :key="bid.id" :bid="bid" :user-points="props.userPoints ?? null" />
                    </div>
                </template>
            </div>
        </template>

        <!-- WINNERS MARQUEE (existing component) -->
        <HomeWinnersSection v-if="props.winners && props.winners.length > 0" :winners="props.winners" />


        <!-- TESTIMONIALS (existing Material Design component) -->
        <HomeTestimonialsSection v-if="props.reviews && props.reviews.length > 0" :reviews="props.reviews" />


        <HomePromoSection />
    </div>
</template>
