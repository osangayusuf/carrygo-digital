<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import BidCard from '@/components/cards/BidCard.vue';
import HomeBidItemsSection from '@/components/home/HomeBidItemsSection.vue';
import HomeEventPopup from '@/components/home/HomeEventPopup.vue';
import HomeHeroSection from '@/components/home/HomeHeroSection.vue';
import HomePromoSection from '@/components/home/HomePromoSection.vue';
import HomeTestimonialsSection from '@/components/home/HomeTestimonialsSection.vue';
import HomeWinnerPopup from '@/components/home/HomeWinnerPopup.vue';
import HomeWinnersSection from '@/components/home/HomeWinnersSection.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { formatPrice, formatMsisdn, getDaysAgo } from '@/lib/utils';
import { trending, openBids as openBidsRoute, tasks } from '@/routes/index';

defineOptions({ layout: PublicLayout });

export type Bidder = {
    msisdn: string;
    total_points: string;
};

export type Bid = {
    id: number;
    name: string;
    image: string | null;
    url: string;
    price: string;
    opening_points: number;
    rating: string | null;
    open_date: number;
    status: 0 | 1 | 2;
    created_at: string;
    current_points: number | null;
    expires_at: string | null;
    top_bidders?: Bidder[];
};

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

function openBidModal(bid: Bid) {
    if (bidItemsSectionRef.value) {
        bidItemsSectionRef.value.openBidModal(bid);
    }
}

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
    <HomeEventPopup v-if="props.eventPopupBid" :bid="props.eventPopupBid" @open-bid-modal="openBidModal" />

    <!-- Hidden — provides the bid modal for all BidCard sections -->
    <HomeBidItemsSection ref="bidItemsSectionRef" :bids="props.bids ?? []" :user-points="props.userPoints ?? null"
        class="hidden" />

    <div class="overflow-x-hidden text-left bg-sage-bg text-ink font-sans">
        <HomeHeroSection :get-category-icon="getCategoryIcon" />

        <!-- PRODUCT STRIP -->
        <div class="max-w-[1300px] mx-auto mt-2.5 px-4 grid gap-2.5 grid-cols-2 md:grid-cols-5"
            v-if="props.bids && props.bids.length > 0">
            <BidCard v-for="bid in props.bids.slice(0, 8)" :key="bid.id" :bid="bid"
                @open-bid-modal="openBidModal" />
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
                    @open-bid-modal="openBidModal" />
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
                    @open-bid-modal="openBidModal" />
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

        <!-- LIVE OPPORTUNITIES -->
        <div class="max-w-[1300px] mx-auto mb-5 px-4" v-if="props.openBids && props.openBids.length > 0">
            <div class="flex items-center justify-between mb-3.5">
                <div class="font-condensed text-2xl font-extrabold text-ink flex items-center">
                    <span class="inline-block w-1 h-[22px] bg-forest rounded-sm mr-2 align-middle"></span>
                    <span class="pi pi-bolt text-lg text-lemon mr-1"></span> Live Opportunities
                </div>
                <Link :href="openBidsRoute.url()" class="text-forest text-sm font-bold hover:underline">View All →
                </Link>
            </div>
            <div class="grid gap-2.5 grid-cols-2 md:grid-cols-5">
                <BidCard v-for="bid in props.openBids.slice(0, 4)" :key="bid.id" :bid="bid"
                    @open-bid-modal="openBidModal" />
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
                    @open-bid-modal="openBidModal" />
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
                        <BidCard v-for="bid in bids" :key="bid.id" :bid="bid" @open-bid-modal="openBidModal" />
                    </div>
                </template>
            </div>
        </template>

        <!-- WINNERS MARQUEE (existing component) -->
        <HomeWinnersSection v-if="props.winners && props.winners.length > 0" :winners="props.winners" />

        <!-- RECENT WINNERS CARD GRID -->
        <div class="max-w-[1300px] mx-auto mb-5 px-4" v-if="props.winners && props.winners.length > 0">
            <div class="flex items-center justify-between mb-3.5">
                <div class="font-condensed text-2xl font-extrabold text-ink flex items-center">
                    <span class="inline-block w-1 h-[22px] bg-forest rounded-sm mr-2 align-middle"></span>
                    <span class="pi pi-trophy text-lg mr-1"></span> Recent Winners
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
                <div v-for="winner in props.winners" :key="winner.id"
                    class="bg-white rounded-lg p-3 shadow-sm border border-sage-border-dark flex flex-col relative overflow-hidden group hover:border-lemon transition-colors">
                    <div class="h-[120px] mb-3.5 rounded-md bg-sage-bg flex items-center justify-center p-2">
                        <img v-if="winner.bid?.image" :src="winner.bid.image" :alt="winner.bid.name"
                            class="max-h-full max-w-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <span v-else class="pi pi-image text-4xl text-gray-300"></span>
                    </div>
                    <div class="font-bold text-sm text-ink mb-1 line-clamp-1"
                        :title="winner.bid?.name || 'Luxury Item'">{{ winner.bid?.name || 'Luxury Item' }}</div>
                    <div class="text-xs text-muted-green mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="pi pi-user text-[10px]"></span> {{ formatMsisdn(winner.msisdn) }}
                        </div>
                        <div class="text-[10px]">{{ getDaysAgo(winner.created_at) }}</div>
                    </div>
                    <div
                        class="mt-auto bg-sage-bg border border-sage-border rounded py-1.5 px-2 text-xs font-extrabold text-forest">
                        Won with {{ winner.total_points }} pts
                    </div>
                </div>
            </div>
        </div>

        <!-- TESTIMONIALS (existing Material Design component) -->
        <HomeTestimonialsSection v-if="props.reviews && props.reviews.length > 0" :reviews="props.reviews" />

        <!-- COMMUNITY REVIEWS GRID -->
        <div class="max-w-[1300px] mx-auto mb-5 px-4" v-if="props.reviews && props.reviews.length > 0">
            <div class="flex items-center justify-between mb-3.5">
                <div class="font-condensed text-2xl font-extrabold text-ink flex items-center">
                    <span class="inline-block w-1 h-[22px] bg-forest rounded-sm mr-2 align-middle"></span>
                    <span class="pi pi-comments text-lg text-forest mr-1"></span> What Our Community Says
                </div>
            </div>
            <div class="text-center text-[13px] text-muted-green mb-4.5">Hear from winners who have scored amazing bids
                on CarryGo</div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                <div class="bg-white rounded-lg p-4.5 shadow-sm border border-sage-border-dark flex flex-col h-full"
                    v-for="review in props.reviews" :key="review.id">
                    <div class="text-amber text-sm mb-2.5">
                        <span v-for="i in review.rating" :key="i" class="pi pi-star-fill"></span>
                        <span v-for="i in 5 - review.rating" :key="'e' + i" class="pi pi-star"></span>
                    </div>
                    <div class="text-sm text-gray-800 leading-relaxed mb-3.5 italic">"{{ review.comment }}"</div>
                    <div class="mt-auto">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full bg-lemon flex items-center justify-center text-base">
                                <span class="pi pi-user"></span>
                            </div>
                            <div>
                                <div class="text-sm font-extrabold text-ink">{{ formatMsisdn(review.user_id) ||
                                    'Anonymous' }}</div>
                                <div class="text-xs text-muted-green">Verified Bidder</div>
                            </div>
                        </div>
                        <span
                            class="mt-2.5 bg-[#e8f5e0] text-forest text-xs font-bold px-2.5 py-1 rounded-md inline-block">Item:
                            {{ review.bid?.name || 'Luxury Item' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TRUST BAR -->
        <div class="bg-navy border-y-2 border-lemon py-4.5 px-4 mb-5">
            <div class="max-w-[1300px] mx-auto grid grid-cols-2 lg:grid-cols-4 gap-5 text-center">
                <div>
                    <div class="text-[26px] mb-1.5"><span class="pi pi-bolt text-white"></span></div>
                    <div class="text-sm font-extrabold text-lemon">Fast Delivery</div>
                    <div class="text-xs text-[#8aaa80]">Winners receive items within 3–5 working days nationwide</div>
                </div>
                <div>
                    <div class="text-[26px] mb-1.5"><span class="pi pi-lock text-white"></span></div>
                    <div class="text-sm font-extrabold text-lemon">Secure Payments</div>
                    <div class="text-xs text-[#8aaa80]">All transactions protected with bank-grade encryption</div>
                </div>
                <div>
                    <div class="text-[26px] mb-1.5"><span class="pi pi-verified text-white"></span></div>
                    <div class="text-sm font-extrabold text-lemon">Verified Winners</div>
                    <div class="text-xs text-[#8aaa80]">Every winner is verified before item dispatch</div>
                </div>
                <div>
                    <div class="text-[26px] mb-1.5"><span class="pi pi-headphones text-white"></span></div>
                    <div class="text-sm font-extrabold text-lemon">24/7 Support</div>
                    <div class="text-xs text-[#8aaa80]">Our team is available round-the-clock for assistance</div>
                </div>
            </div>
        </div>

        <HomePromoSection />
    </div>
</template>
