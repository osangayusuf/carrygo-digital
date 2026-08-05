<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useTriggeredAuctionBanner } from '@/composables/useTriggeredAuctionBanner';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const {
    liveAuctions: bannerAuctions,
    activeAuction: activeTriggeredAuction,
    activeExpiresAt: bannerExpiresAt,
    activeIndex: bannerIndex,
    hasMultiple: hasMultipleBannerAuctions,
    pause: pauseBanner,
    resume: resumeBanner,
    goTo: goToBannerSlide,
} = useTriggeredAuctionBanner();

const bannerTimeLeft = ref('');
let bannerInterval: ReturnType<typeof setInterval> | null = null;

function updateBannerTimer() {
    if (!bannerExpiresAt.value) {
        bannerTimeLeft.value = '';

        return;
    }

    const diff = Math.floor(
        (new Date(bannerExpiresAt.value).getTime() - Date.now()) / 1000,
    );

    if (diff <= 0) {
        bannerTimeLeft.value = '';

        return;
    }

    const mins = Math.floor(diff / 60);
    const secs = diff % 60;
    bannerTimeLeft.value = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

watch(
    [activeTriggeredAuction, bannerExpiresAt],
    ([newVal]) => {
        if (newVal) {
            updateBannerTimer();

            if (!bannerInterval) {
                bannerInterval = setInterval(updateBannerTimer, 1000);
            }
        } else {
            if (bannerInterval) {
                clearInterval(bannerInterval);
                bannerInterval = null;
            }
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    if (bannerInterval) {
        clearInterval(bannerInterval);
    }
});
</script>

<template>
    <div class="flex w-full flex-col border-b border-sidebar-border/70">
        <!-- Persistent Countdown Banner -->
        <div
            v-if="activeTriggeredAuction"
            class="relative overflow-hidden bg-red-600 text-white"
            role="region"
            aria-label="Auctions closing soon"
            :aria-roledescription="
                hasMultipleBannerAuctions ? 'carousel' : undefined
            "
            @mouseenter="pauseBanner"
            @mouseleave="resumeBanner"
            @focusin="pauseBanner"
            @focusout="resumeBanner"
        >
            <Transition name="banner-slide">
                <div
                    :key="activeTriggeredAuction.id"
                    class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 px-4 py-2 text-center text-xs font-bold"
                >
                    <div class="flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px]"
                            >alarm</span
                        >
                        <span>
                            Closing Soon:
                            <strong class="text-lemon">{{
                                activeTriggeredAuction.name
                            }}</strong>
                            is ending soon!
                        </span>
                    </div>
                    <div class="flex items-center justify-center gap-3">
                        <span
                            class="flex items-center gap-1 rounded border border-white/15 bg-black/35 px-2 py-0.5 font-mono text-[11px] font-black tracking-wide shadow-inner"
                        >
                            <span class="material-symbols-outlined text-xs"
                                >schedule</span
                            >
                            <span>{{ bannerTimeLeft }}</span>
                        </span>
                        <Link
                            :href="`/auctions/${activeTriggeredAuction.id}`"
                            class="flex items-center gap-0.5 font-extrabold underline transition-colors hover:text-lemon"
                        >
                            Bid Now
                            <i class="pi pi-arrow-right text-[10px]"></i>
                        </Link>
                    </div>
                </div>
            </Transition>

            <!-- Slide indicators, only when there is more than one open auction -->
            <div
                v-if="hasMultipleBannerAuctions"
                class="flex items-center justify-center gap-1.5 pb-1.5"
            >
                <button
                    v-for="(auction, index) in bannerAuctions"
                    :key="auction.id"
                    type="button"
                    class="h-1.5 rounded-full transition-all duration-300 focus:ring-2 focus:ring-white focus:outline-none"
                    :class="
                        index === bannerIndex
                            ? 'w-4 bg-white'
                            : 'w-1.5 bg-gray-400 hover:bg-gray-300'
                    "
                    :aria-label="`Show ${auction.name}`"
                    :aria-current="index === bannerIndex"
                    @click="goToBannerSlide(index)"
                />
            </div>
        </div>

        <header
            class="flex h-16 shrink-0 items-center gap-2 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
        >
            <div class="flex items-center gap-2">
                <SidebarTrigger class="-ml-1" />
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
        </header>
    </div>
</template>

<style scoped>
/* Horizontal slide for the "closing soon" strip as it rotates between auctions. */
.banner-slide-enter-active,
.banner-slide-leave-active {
    transition:
        transform 0.45s cubic-bezier(0.4, 0, 0.2, 1),
        opacity 0.35s ease;
}

.banner-slide-enter-from {
    transform: translateX(100%);
    opacity: 0;
}

.banner-slide-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}

/* Overlay the outgoing slide so the strip never collapses mid-transition. */
.banner-slide-leave-active {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
}

@media (prefers-reduced-motion: reduce) {
    .banner-slide-enter-active,
    .banner-slide-leave-active {
        transition: opacity 0.2s ease;
    }

    .banner-slide-enter-from,
    .banner-slide-leave-to {
        transform: none;
        opacity: 0;
    }
}
</style>
