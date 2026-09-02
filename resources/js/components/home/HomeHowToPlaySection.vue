<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { howToPlay } from '@/routes/index';

const isVideoLoaded = ref(false);
const isPlaying = ref(false);
const videoRef = ref<HTMLVideoElement | null>(null);

function startVideo(): void {
    isVideoLoaded.value = true;
    isPlaying.value = true;
    nextTick(() => {
        if (videoRef.value) {
            videoRef.value.play().catch(() => {
                // Autoplay may be restricted if user interaction isn't recognized
            });
        }
    });
}

function handleVideoEnded(): void {
    isPlaying.value = false;
}

const steps = [
    {
        num: '01',
        title: 'Buy Bidding Points',
        desc: 'Top up your account or complete daily tasks to get bid points instantly.',
        icon: 'payments',
    },
    {
        num: '02',
        title: 'Select Live Auction',
        desc: 'Browse trending gadgets, vehicles, and electronics with active countdowns.',
        icon: 'search',
    },
    {
        num: '03',
        title: 'Place Your Bids',
        desc: 'Strategically enter bids before the timer reaches 00:00:00.',
        icon: 'gavel',
    },
    {
        num: '04',
        title: 'Win & Free Delivery',
        desc: 'Highest total bidder wins! The item is shipped to your doorstep 100% free.',
        icon: 'local_shipping',
    },
];
</script>

<template>
    <section id="how-to-play-section" class="mx-auto my-8 max-w-[1300px] px-4">
        <!-- Section Header -->
        <div
            class="mb-6 flex flex-col justify-between gap-2 sm:flex-row sm:items-end"
        >
            <div>
                <div
                    class="mb-1.5 flex items-center font-condensed text-xs font-black tracking-widest text-lemon uppercase"
                >
                    <span class="pi pi-play-circle mr-1.5 text-sm"></span>
                    Video Guide &amp; Walkthrough
                </div>
                <h2
                    class="font-condensed text-3xl font-black text-ink md:text-4xl"
                >
                    See How Bidora Works
                </h2>
                <p class="mt-1 text-sm font-medium text-slate-600">
                    Watch the full guide or follow our simple 4-step process to
                    start winning today.
                </p>
            </div>
            <Link
                :href="howToPlay.url()"
                class="inline-flex items-center gap-1.5 self-start text-sm font-extrabold text-forest hover:text-navy hover:underline sm:self-auto"
            >
                Read Full Rules &amp; FAQ
                <i class="pi pi-arrow-right text-xs"></i>
            </Link>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-center">
            <!-- Video Player Box (7 cols on lg) -->
            <div class="lg:col-span-7">
                <div
                    class="group relative aspect-video w-full overflow-hidden rounded-2xl border-2 border-slate-200 bg-forest-dark shadow-xl ring-1 ring-black/5"
                >
                    <!-- Deferred Loaded Video -->
                    <video
                        v-if="isVideoLoaded"
                        ref="videoRef"
                        class="h-full w-full object-cover"
                        controls
                        playsinline
                        preload="none"
                        @ended="handleVideoEnded"
                        @pause="isPlaying = false"
                        @play="isPlaying = true"
                    >
                        <source src="/how-to-play.mp4" type="video/mp4" />
                        Your browser does not support the video tag.
                    </video>

                    <!-- Poster & Deferred Play Overlay (Shown when video is not playing / not loaded) -->
                    <div
                        v-if="!isVideoLoaded || !isPlaying"
                        class="group-hover:bg-opacity-90 absolute inset-0 z-10 flex cursor-pointer flex-col items-center justify-between bg-radial from-forest-mid/90 via-navy/95 to-forest-dark p-6 text-center transition-all duration-300"
                        @click="startVideo"
                    >

                        <!-- Center Play Button Trigger -->
                        <div class="my-auto flex flex-col gap-5 items-center">
                            <div
                                class="relative mb-3 flex items-center justify-center"
                            >
                                <!-- Ping Ring animation -->
                                <div
                                    class="absolute h-16 w-16 animate-ping rounded-full bg-lemon/30"
                                ></div>
                                <!-- Glowing button -->
                                <button
                                    type="button"
                                    class="relative flex h-16 w-16 cursor-pointer items-center justify-center rounded-full border-2 border-lemon bg-lemon text-navy shadow-lg shadow-lemon/25 transition-transform duration-200 group-hover:scale-110 active:scale-95"
                                    aria-label="Play how to play video"
                                >
                                    <i
                                        class="pi pi-play ml-1 text-2xl font-bold text-navy"
                                    ></i>
                                </button>
                            </div>
                            <h3
                                class="font-condensed font-black text-white sm:text-xl"
                            >
                                Click to Watch How It Works
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Steps Column (5 cols on lg) -->
            <div class="flex flex-col gap-3 lg:col-span-5">
                <div
                    v-for="step in steps"
                    :key="step.num"
                    class="group flex items-start gap-3.5 rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:border-lemon hover:bg-slate-50 hover:shadow-md"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-navy font-condensed font-black text-lemon transition-colors group-hover:bg-forest"
                    >
                        <span class="material-symbols-outlined text-xl">{{
                            step.icon
                        }}</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4
                                class="font-condensed text-base font-extrabold text-ink"
                            >
                                {{ step.title }}
                            </h4>
                            <span class="text-[11px] font-black text-slate-400">
                                {{ step.num }}
                            </span>
                        </div>
                        <p
                            class="mt-0.5 text-xs leading-relaxed text-slate-600"
                        >
                            {{ step.desc }}
                        </p>
                    </div>
                </div>

                <!-- Action CTA Card -->
                <div
                    class="mt-1 flex items-center justify-between rounded-xl border border-forest/20 bg-linear-to-r from-forest to-navy p-4 text-white"
                >
                    <div>
                        <div class="text-xs font-black text-lemon uppercase">
                            Ready to try it?
                        </div>
                        <div class="text-sm font-extrabold text-white">
                            Claim daily points &amp; bid now
                        </div>
                    </div>
                    <Link
                        :href="howToPlay.url()"
                        class="rounded-lg bg-lemon px-4 py-2 text-xs font-black text-navy transition-colors hover:bg-amber"
                    >
                        Learn More
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
