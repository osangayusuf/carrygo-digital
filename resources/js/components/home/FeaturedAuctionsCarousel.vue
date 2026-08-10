<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useIntervalFn } from '@vueuse/core';
import { computed, ref } from 'vue';
import { usePlaceBidModal } from '@/composables/usePlaceBidModal';
import {
    formatPrice,
    formatSlashedPrice,
    calcProgress,
    getRemainingTime,
} from '@/lib/utils';
import auctions from '@/routes/auctions/index';
import type { Bid } from '@/types/auction';

const props = defineProps<{
    bids: Bid[];
    userPoints: number | null;
}>();

const { open } = usePlaceBidModal();

const currentSlideIndex = ref(0);

const activeBids = computed(() => props.bids || []);

const { pause, resume } = useIntervalFn(() => {
    if (activeBids.value.length > 1) {
        currentSlideIndex.value =
            (currentSlideIndex.value + 1) % activeBids.value.length;
    }
}, 4000);

const stopAutoPlay = () => pause();
const startAutoPlay = () => resume();

const nextSlide = () => {
    stopAutoPlay();
    currentSlideIndex.value =
        (currentSlideIndex.value + 1) % activeBids.value.length;
    startAutoPlay();
};

const prevSlide = () => {
    stopAutoPlay();
    currentSlideIndex.value =
        (currentSlideIndex.value - 1 + activeBids.value.length) %
        activeBids.value.length;
    startAutoPlay();
};

const setSlide = (idx: number) => {
    stopAutoPlay();
    currentSlideIndex.value = idx;
    startAutoPlay();
};

function openBidModal(bid: Bid): void {
    open(bid, props.userPoints ?? null);
}

function activeBidders(bidId: number, bidCount: number): number {
    return ((bidId * 11 + bidCount * 5) % 17) + 4;
}
</script>

<template>
    <div v-if="activeBids.length > 0" class="carousel-section">
        <div class="carousel-header">
            <span class="carousel-accent-bar"></span>
            <h2 class="carousel-title">
                <i class="pi pi-sparkles carousel-title-icon"></i>
                Featured Premium Deals
            </h2>
        </div>

        <div class="carousel-outer">
            <!-- Glow blobs -->
            <div class="carousel-blob carousel-blob--top"></div>
            <div class="carousel-blob carousel-blob--bottom"></div>

            <!-- Slides viewport: overflow hidden, exact width -->
            <div class="carousel-viewport">
                <!-- Track: each slide is 100% of the viewport, shifted by index -->
                <div
                    v-for="(bid, index) in activeBids"
                    :key="bid.id"
                    class="carousel-slide"
                    :style="{
                        transform: `translateX(${(index - currentSlideIndex) * 100}%)`,
                    }"
                >
                    <!-- Image: top on mobile, right on desktop -->
                    <div class="slide-image-wrap">
                        <div class="slide-image-box">
                            <img
                                :src="bid.image ?? ''"
                                :alt="bid.name"
                                class="slide-image"
                            />
                        </div>
                    </div>

                    <!-- Details: below image on mobile, left on desktop -->
                    <div class="slide-details">
                        <!-- Badges -->
                        <div class="slide-badges">
                            <span
                                v-if="bid.status === 1"
                                class="badge badge--closing"
                            >
                                🚨 CLOSING SOON
                            </span>
                            <span v-else class="badge badge--featured">
                                ⭐ FEATURED
                            </span>
                            <span class="badge badge--live">
                                <span class="live-dot-wrap">
                                    <span class="live-dot-ping"></span>
                                    <span class="live-dot"></span>
                                </span>
                                {{ activeBidders(bid.id, bid.bid_count ?? 0) }}
                                bidding now
                            </span>
                        </div>

                        <h3 class="slide-name">{{ bid.name }}</h3>

                        <p v-if="bid.description" class="slide-description">
                            {{ bid.description }}
                        </p>

                        <div class="slide-price-row">
                            <span class="slide-price">{{
                                formatPrice(bid.price)
                            }}</span>
                            <span class="slide-price-slashed">{{
                                formatSlashedPrice(bid.price)
                            }}</span>
                            <span class="slide-price-label">Market Price</span>
                        </div>

                        <!-- Progress -->
                        <div class="slide-progress-box">
                            <div class="slide-progress-header">
                                <span class="slide-progress-pts"
                                    >Progress: {{ bid.current_points ?? 0 }}/{{
                                        bid.opening_points
                                    }}
                                    pts</span
                                >
                                <span class="slide-progress-pct"
                                    >{{ calcProgress(bid) }}%</span
                                >
                            </div>
                            <div class="slide-progress-track">
                                <div
                                    class="slide-progress-fill"
                                    :style="{ width: calcProgress(bid) + '%' }"
                                ></div>
                            </div>
                            <div class="slide-progress-meta">
                                <span>{{ bid.bid_count ?? 0 }} total bids</span>
                                <span
                                    v-if="bid.expires_at"
                                    class="slide-countdown"
                                >
                                    <i class="pi pi-clock"></i>
                                    {{ getRemainingTime(bid.expires_at) }} left
                                </span>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="slide-actions">
                            <button
                                type="button"
                                class="btn-bid"
                                @click="openBidModal(bid)"
                            >
                                Place Bid
                            </button>
                            <Link
                                :href="auctions.show.url(bid.id)"
                                class="btn-view"
                            >
                                View Details <i class="pi pi-arrow-right"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop prev/next arrows -->
            <button
                v-if="activeBids.length > 1"
                type="button"
                class="carousel-arrow carousel-arrow--prev"
                @click="prevSlide"
                aria-label="Previous Slide"
            >
                <i class="pi pi-chevron-left"></i>
            </button>
            <button
                v-if="activeBids.length > 1"
                type="button"
                class="carousel-arrow carousel-arrow--next"
                @click="nextSlide"
                aria-label="Next Slide"
            >
                <i class="pi pi-chevron-right"></i>
            </button>

            <!-- Dot indicators -->
            <div v-if="activeBids.length > 1" class="carousel-dots">
                <button
                    v-for="(_, index) in activeBids"
                    :key="index"
                    type="button"
                    class="carousel-dot"
                    :class="{
                        'carousel-dot--active': currentSlideIndex === index,
                    }"
                    @click="setSlide(index)"
                    :aria-label="`Go to slide ${index + 1}`"
                ></button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── Outer wrapper ───────────────────────────────────── */
.carousel-section {
    margin: 2rem auto;
    max-width: 1300px;
    padding: 0 1rem;
}

/* ── Header ──────────────────────────────────────────── */
.carousel-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.carousel-accent-bar {
    display: inline-block;
    width: 4px;
    height: 1.5rem;
    border-radius: 2px;
    background: var(--color-forest, #2d6a4f);
}
.carousel-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--color-ink, #1a1a1a);
    display: flex;
    align-items: center;
    gap: 0.375rem;
    margin: 0;
}
.carousel-title-icon {
    color: var(--color-amber, #f59e0b);
    font-size: 1.125rem;
}

/* ── Carousel shell ──────────────────────────────────── */
.carousel-outer {
    position: relative;
    border-radius: 1.25rem;
    border: 2px solid var(--color-lemon, #fde047);
    background: linear-gradient(
        135deg,
        var(--color-navy, #101828) 0%,
        var(--color-forest-mid, #152031) 60%,
        var(--color-forest, #1d2939) 100%
    );
    color: #fff;
    box-shadow: 0 8px 40px rgba(0, 0, 0, 0.35);
    overflow: hidden; /* ← this is the one true clip boundary */
}

/* ── Decorative blobs ────────────────────────────────── */
.carousel-blob {
    pointer-events: none;
    position: absolute;
    width: 16rem;
    height: 16rem;
    border-radius: 9999px;
    filter: blur(60px);
    z-index: 0;
}
.carousel-blob--top {
    top: -8rem;
    left: -8rem;
    background: rgba(45, 106, 79, 0.2);
}
.carousel-blob--bottom {
    bottom: -8rem;
    right: -8rem;
    background: rgba(253, 224, 71, 0.1);
}

/* ── Viewport (no overflow:hidden here — handled by .carousel-outer) ── */
.carousel-viewport {
    position: relative;
    z-index: 1;
    width: 100%;
    /* height is determined by the tallest slide naturally */
}

/* ── Each slide ──────────────────────────────────────── */
.carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    /* stack vertically on mobile */
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.25rem 1rem 3.5rem; /* bottom pad for dots */
    box-sizing: border-box;
    transition: transform 0.5s ease-in-out;
    /* prevent text overflow from the slide leaking into adjacent slides */
    overflow: hidden;
}

/* Make the viewport tall enough to show the first slide */
.carousel-viewport {
    /* height is set by the first slide acting as a block-level spacer */
    display: grid; /* grid makes only the first slide take up space */
}
.carousel-slide:first-child {
    position: relative; /* first slide is in normal flow to set height */
}

/* Image block — appears first on mobile */
.slide-image-wrap {
    width: 100%;
    display: flex;
    justify-content: center;
}
.slide-image-box {
    width: 100%;
    max-width: 180px;
    height: 9rem;
    border-radius: 0.75rem;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
}
.slide-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 0.5rem;
    display: block;
    transition: transform 0.4s ease;
}
.slide-image:hover {
    transform: scale(1.05);
}

/* Details block */
.slide-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Badges */
.slide-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.625rem;
    font-weight: 900;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
}
.badge--closing {
    background: #dc2626;
    color: #fff;
    animation: pulse 1.5s infinite;
}
.badge--featured {
    background: var(--color-forest, #2d6a4f);
    color: var(--color-lemon, #fde047);
}
.badge--live {
    background: rgba(255, 255, 255, 0.12);
    color: var(--color-lemon, #fde047);
}
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.6;
    }
}

.live-dot-wrap {
    position: relative;
    display: flex;
    width: 6px;
    height: 6px;
}
.live-dot-ping {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    background: #f87171;
    opacity: 0.75;
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}
.live-dot {
    position: relative;
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #ef4444;
}
@keyframes ping {
    75%,
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Name */
.slide-name {
    font-size: 1.75rem;
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -0.02em;
    color: #fff;
    margin: 0;
}

/* Description */
.slide-description {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.65);
    line-height: 1.5;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Price */
.slide-price-row {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}
.slide-price {
    font-size: 1.75rem;
    font-weight: 900;
    color: var(--color-lemon, #fde047);
}
.slide-price-slashed {
    font-size: 1rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.45);
    text-decoration: line-through;
}
.slide-price-label {
    font-size: 0.6875rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.45);
}

/* Progress */
.slide-progress-box {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    padding: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}
.slide-progress-header {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    font-weight: 700;
}
.slide-progress-pts {
    color: var(--color-lemon, #fde047);
}
.slide-progress-pct {
    color: #fff;
}
.slide-progress-track {
    height: 8px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.12);
    overflow: hidden;
}
.slide-progress-fill {
    height: 100%;
    border-radius: 9999px;
    background: linear-gradient(
        90deg,
        var(--color-forest, #2d6a4f),
        var(--color-lemon, #fde047)
    );
    transition: width 0.4s ease;
}
.slide-progress-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.625rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.5);
}
.slide-countdown {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #f87171;
}

/* Buttons */
.slide-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.625rem;
    padding-top: 0.25rem;
}
.btn-bid {
    border: none;
    border-radius: 0.75rem;
    background: var(--color-lemon, #fde047);
    color: var(--color-navy, #0f172a);
    font-size: 0.875rem;
    font-weight: 800;
    padding: 0.75rem 2rem;
    cursor: pointer;
    transition: background 0.2s;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
}
.btn-bid:hover {
    background: var(--color-amber, #f59e0b);
}
.btn-view {
    border-radius: 0.75rem;
    border: 2px solid rgba(255, 255, 255, 0.25);
    background: transparent;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 700;
    padding: 0.625rem 1.25rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    cursor: pointer;
    transition: border-color 0.2s;
}
.btn-view:hover {
    border-color: #fff;
}

/* ── Desktop prev/next arrows ───────────────────────── */
.carousel-arrow {
    display: none; /* hidden on mobile */
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 9999px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(0, 0, 0, 0.35);
    color: #fff;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 30;
    transition: background 0.2s;
}
.carousel-arrow:hover {
    background: rgba(0, 0, 0, 0.55);
}
.carousel-arrow--prev {
    left: 0.75rem;
}
.carousel-arrow--next {
    right: 0.75rem;
}

/* ── Dot indicators ──────────────────────────────────── */
.carousel-dots {
    position: absolute;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    z-index: 30;
}
.carousel-dot {
    border: none;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.3);
    width: 8px;
    height: 8px;
    padding: 0;
    cursor: pointer;
    transition:
        width 0.3s,
        background 0.3s;
}
.carousel-dot--active {
    background: var(--color-lemon, #fde047);
    width: 1.5rem;
}

/* ── Desktop (md ≥ 768px) ───────────────────────────── */
@media (min-width: 768px) {
    .carousel-slide {
        flex-direction: row;
        align-items: center;
        gap: 2rem;
        padding: 2.5rem 5rem;
    }
    .slide-image-wrap {
        width: 40%;
        flex-shrink: 0;
        order: 2;
    }
    .slide-image-box {
        max-width: 100%;
        height: 18rem;
    }
    .slide-details {
        flex: 1;
        order: 1;
    }
    .slide-name {
        font-size: 2.25rem;
    }
    .carousel-arrow {
        display: flex;
    }
}
</style>
