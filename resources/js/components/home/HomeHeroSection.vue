<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useIntervalFn } from '@vueuse/core';
import { computed, ref } from 'vue';
import { howToPlay, trending } from '@/routes/index';

const page = usePage();

defineProps<{ getCategoryIcon: (category: string) => string }>();

const categories = computed(() => (page.props.categories as string[]) ?? []);

const bannerImages = ['/images/banner1.jpeg', '/images/banner2.png', '/images/banner3.png'];
const currentBannerIndex = ref(0);

const { pause } = useIntervalFn(() => {
    currentBannerIndex.value = (currentBannerIndex.value + 1) % bannerImages.length;
}, 3000);

const stopAutoSlide = () => pause();

const goToSlide = (index: number) => {
    stopAutoSlide();
    currentBannerIndex.value = index;
};

const nextSlide = () => {
    stopAutoSlide();
    currentBannerIndex.value = (currentBannerIndex.value + 1) % bannerImages.length;
};

const prevSlide = () => {
    stopAutoSlide();
    currentBannerIndex.value = (currentBannerIndex.value - 1 + bannerImages.length) % bannerImages.length;
};
</script>

<template>
    <!-- HERO -->
    <section class="max-w-7xl mx-auto mt-2.5 px-4 md:grid gap-2.5 items-stretch" style="grid-template-columns: 1fr 4fr">
        <!-- LEFT CATEGORIES NAV -->
        <div class="bg-white rounded-xl overflow-hidden border border-outline-variant flex-col hidden md:flex">
            <Link
                v-for="cat in categories"
                :key="cat"
                :href="trending.url() + '?category=' + encodeURIComponent(cat)"
                class="flex items-center gap-2.5 px-3.5 text-sm font-bold text-ink no-underline border-b border-sage-tint transition-all duration-150 flex-1 whitespace-nowrap last:border-b-0 hover:bg-sage-tint hover:text-primary hover:pl-4.5"
            >
                <span v-if="getCategoryIcon(cat)" class="material-symbols-outlined mr-1 text-sm">
                    {{ getCategoryIcon(cat) }}
                </span>
                {{ cat }} <i class="pi pi-chevron-right ml-auto text-xs"></i>
            </Link>
        </div>

        <!-- MAIN BANNER -->
        <div class="relative rounded-xl overflow-hidden bg-forest-dark flex items-stretch min-h-84">
            <div class="grow py-7.5 px-8 flex flex-col justify-center z-2">
                <div class="bg-lemon text-navy text-xs font-extrabold py-1 px-3 rounded-full inline-flex items-center gap-1 uppercase tracking-wider self-start mb-3.5">
                    🇳🇬 Nigeria's #1 Auction Platform
                </div>
                <h1 class="font-condensed text-5xl font-black text-white leading-none mb-2.5">
                    Win More.<span class="text-lemon block">Carry More.</span>
                </h1>
                <p class="text-sm text-white/65 mb-5.5 leading-relaxed">Bid on premium luxury items. Pay nothing if you win.</p>
                <div class="flex gap-2.5 mb-6">
                    <Link :href="trending.url()" class="bg-lemon text-navy border-none py-2.5 px-6 rounded-lg text-sm font-extrabold cursor-pointer whitespace-nowrap">
                        Start Bidding
                    </Link>
                    <Link :href="howToPlay.url()" class="bg-transparent text-lemon border-2 border-lemon py-2 px-5 rounded-lg text-sm font-bold cursor-pointer whitespace-nowrap">
                        How It Works
                    </Link>
                </div>
                <div class="flex gap-7">
                    <div>
                        <span class="text-2xl font-black text-lemon block leading-none">50K+</span>
                        <span class="text-xs text-white/55 block mt-0.5">Happy Winners</span>
                    </div>
                    <div>
                        <span class="text-2xl font-black text-lemon block leading-none">₦0</span>
                        <span class="text-xs text-white/55 block mt-0.5">Lost Bid Cost</span>
                    </div>
                    <div>
                        <span class="text-2xl font-black text-lemon block leading-none">100%</span>
                        <span class="text-xs text-white/55 block mt-0.5">Transparent</span>
                    </div>
                </div>
            </div>

            <div class="shrink-0 relative overflow-hidden hero-banner-right-bg w-1/2 select-none hidden md:flex group">
                <div id="heroCarousel" class="w-full h-full relative overflow-hidden flex items-center justify-center">
                    <div
                        class="flex w-full h-full items-center transition-transform duration-500 ease-in-out"
                        :style="{ transform: `translateX(-${currentBannerIndex * 100}%)` }"
                    >
                        <div v-for="(img, index) in bannerImages" :key="index" class="min-w-full flex flex-col items-center justify-center">
                            <img :src="`${$page.props.asset_url}${img}`" alt="Banner" class="w-full h-auto object-contain animate-float-img rounded-md px-7" />
                        </div>
                    </div>

                    <button
                        class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/30 text-white flex items-center justify-center hover:bg-black/50 transition-colors z-10 cursor-pointer border-none opacity-0 group-hover:opacity-100"
                        @click="prevSlide"
                    >
                        <i class="pi pi-chevron-left text-sm"></i>
                    </button>
                    <button
                        class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/30 text-white flex items-center justify-center hover:bg-black/50 transition-colors z-10 cursor-pointer border-none opacity-0 group-hover:opacity-100"
                        @click="nextSlide"
                    >
                        <i class="pi pi-chevron-right text-sm"></i>
                    </button>

                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 z-10 drop-shadow-md">
                        <button
                            v-for="(_, index) in bannerImages"
                            :key="`dot-${index}`"
                            class="rounded-full transition-all duration-300 border-none cursor-pointer p-0"
                            :class="currentBannerIndex === index ? 'bg-lemon w-6 h-2' : 'bg-white/50 hover:bg-white/80 w-2 h-2'"
                            @click="goToSlide(index)"
                        ></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile banner carousel -->
        <div class="overflow-hidden w-full select-none hidden max-md:flex my-5 relative group">
            <div id="heroCarouselMobile" class="w-full h-full relative overflow-hidden flex items-center justify-center">
                <div
                    class="flex w-full h-full items-center transition-transform duration-500 ease-in-out"
                    :style="{ transform: `translateX(-${currentBannerIndex * 100}%)` }"
                >
                    <div v-for="(img, index) in bannerImages" :key="index" class="min-w-full flex flex-col items-center justify-center">
                        <img :src="`${$page.props.asset_url}${img}`" alt="Banner" class="w-full h-auto object-contain animate-float-img rounded-md px-7" />
                    </div>
                </div>

                <button
                    class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/30 text-white flex items-center justify-center hover:bg-black/50 transition-colors z-10 cursor-pointer border-none"
                    @click="prevSlide"
                >
                    <i class="pi pi-chevron-left text-sm"></i>
                </button>
                <button
                    class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/30 text-white flex items-center justify-center hover:bg-black/50 transition-colors z-10 cursor-pointer border-none"
                    @click="nextSlide"
                >
                    <i class="pi pi-chevron-right text-sm"></i>
                </button>

                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 flex items-center gap-2 z-10">
                    <button
                        v-for="(_, index) in bannerImages"
                        :key="`dot-mobile-${index}`"
                        class="rounded-full transition-all duration-300 border-none cursor-pointer p-0"
                        :class="currentBannerIndex === index ? 'bg-ink w-6 h-2' : 'bg-ink/30 hover:bg-ink/50 w-2 h-2'"
                        @click="goToSlide(index)"
                    ></button>
                </div>
            </div>
        </div>
    </section>
</template>
