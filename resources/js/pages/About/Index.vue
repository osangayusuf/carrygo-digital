<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { openBids } from '@/routes';

defineOptions({ layout: PublicLayout });

type Step = {
    title: string;
    description: string;
    icon: string;
};

type Highlight = {
    title: string;
    description: string;
    icon: string;
};

const steps: Step[] = [
    {
        title: 'Threshold Is Reached',
        description:
            'Each item has a threshold point that must be reached before the auction becomes active, ensuring sufficient participation before bidding begins.',
        icon: 'target',
    },
    {
        title: 'Auction Opens',
        description:
            'Once the threshold is achieved, the auction officially opens and the countdown timer starts.',
        icon: 'lock_open',
    },
    {
        title: 'Bidding Continues',
        description:
            'Users continue placing competitive bids while the countdown timer is active.',
        icon: 'gavel',
    },
    {
        title: 'Winner Is Declared',
        description:
            'When the timer expires, the highest valid bidder is automatically declared the winner.',
        icon: 'emoji_events',
    },
];

const highlights: Highlight[] = [
    {
        title: "Nigeria's First Digital E-Auction Platform",
        description:
            'Pioneering live, competitive auctions built for Nigerian bidders.',
        icon: 'flag',
    },
    {
        title: 'Bid Anytime, Anywhere',
        description: 'Using your smartphone or other mobile devices.',
        icon: 'smartphone',
    },
    {
        title: 'Fair & Transparent Auctions',
        description: 'With clearly defined rules and real-time bidding.',
        icon: 'balance',
    },
    {
        title: 'Exciting Products',
        description: 'Across various categories.',
        icon: 'redeem',
    },
    {
        title: 'Simple User Experience',
        description: 'Designed for everyone.',
        icon: 'touch_app',
    },
    {
        title: 'Secure Platform',
        description:
            'Built to provide a reliable and trustworthy auction experience.',
        icon: 'shield',
    },
];
</script>

<template>
    <Head title="About Bidora" />

    <div class="bg-sage-bg text-ink">
        <section
            class="border-b-4 border-lemon bg-navy px-4 py-14 sm:px-6 md:py-20"
        >
            <div class="mx-auto max-w-6xl text-center">
                <span
                    class="inline-block rounded-full border-2 border-lemon bg-lemon px-4 py-1 text-xs font-extrabold tracking-wide text-navy uppercase"
                >
                    About Bidora
                </span>
                <h1
                    class="mt-5 font-condensed text-4xl leading-tight font-black text-white md:text-6xl"
                >
                    About <span class="text-lemon">BIDORA</span>
                </h1>
                <p
                    class="mx-auto mt-4 max-w-3xl text-sm font-extrabold tracking-wide text-lemon uppercase sm:text-base"
                >
                    Nigeria's First Digital E-Auction Platform
                </p>
                <p
                    class="mx-auto mt-4 max-w-3xl text-sm leading-relaxed text-[#d7e3d4] sm:text-base"
                >
                    Welcome to BIDORA, the platform that brings the thrill of
                    live auctions to your fingertips. Built for convenience,
                    transparency, and excitement, BIDORA allows you to
                    participate in competitive online auctions anytime, anywhere
                    using your mobile device.
                </p>
            </div>
        </section>

        <section class="px-4 py-12 sm:px-6 md:py-16">
            <div class="mx-auto max-w-4xl">
                <article
                    class="rounded-xl border-2 border-sage-border-dark bg-white p-6 md:p-8"
                >
                    <p
                        class="text-sm leading-relaxed text-muted-green sm:text-base"
                    >
                        BIDORA offers a wide selection of exciting products for
                        users to bid on using points. Every auction is conducted
                        in real time, creating a fair and engaging experience
                        where every bid matters.
                    </p>
                </article>
            </div>
        </section>

        <section
            class="border-y-2 border-sage-border-dark bg-white px-4 py-12 sm:px-6 md:py-16"
        >
            <div class="mx-auto max-w-6xl">
                <div class="mb-8 flex items-center gap-3">
                    <span
                        class="inline-block h-8 w-1 rounded-full bg-forest"
                    ></span>
                    <h2 class="font-condensed text-3xl font-black text-ink">
                        How It Works
                    </h2>
                </div>

                <p
                    class="mb-8 max-w-3xl text-sm leading-relaxed text-muted-green sm:text-base"
                >
                    Each item listed on BIDORA has a threshold point that must
                    be reached before the auction becomes active. This ensures
                    sufficient participation before the bidding countdown
                    begins. Once the threshold is achieved, the following
                    happens:
                </p>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                >
                    <article
                        v-for="(step, index) in steps"
                        :key="step.title"
                        class="relative rounded-xl border-2 border-sage-border-dark bg-sage-bg p-6"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg border-2 border-lemon bg-lemon/30 text-navy"
                        >
                            <span class="material-symbols-outlined text-2xl">{{
                                step.icon
                            }}</span>
                        </div>
                        <p
                            class="absolute top-4 right-5 text-4xl font-black text-sage-border-dark/60"
                        >
                            {{ index + 1 }}
                        </p>
                        <h3
                            class="font-headline text-lg font-extrabold text-ink"
                        >
                            {{ step.title }}
                        </h3>
                        <p
                            class="mt-2 text-sm leading-relaxed text-muted-green"
                        >
                            {{ step.description }}
                        </p>
                    </article>
                </div>

                <p
                    class="mt-8 max-w-3xl text-sm leading-relaxed text-muted-green sm:text-base"
                >
                    This process mirrors the excitement and competitiveness of a
                    traditional live auction while offering the convenience of
                    participating digitally.
                </p>
            </div>
        </section>

        <section class="px-4 py-12 sm:px-6 md:py-16">
            <div class="mx-auto max-w-6xl">
                <div class="mb-8 flex items-center gap-3">
                    <span
                        class="inline-block h-8 w-1 rounded-full bg-forest"
                    ></span>
                    <h2 class="font-condensed text-3xl font-black text-ink">
                        Why BIDORA?
                    </h2>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="highlight in highlights"
                        :key="highlight.title"
                        class="rounded-xl border-2 border-sage-border-dark bg-white p-6 shadow-sm"
                    >
                        <div
                            class="mb-4 flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border-2 border-navy bg-navy text-lemon"
                        >
                            <span class="material-symbols-outlined text-xl">{{
                                highlight.icon
                            }}</span>
                        </div>
                        <h3
                            class="font-headline text-base font-extrabold text-ink"
                        >
                            {{ highlight.title }}
                        </h3>
                        <p
                            class="mt-1 text-sm leading-relaxed text-muted-green"
                        >
                            {{ highlight.description }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section
            class="border-y-2 border-sage-border-dark bg-white px-4 py-12 sm:px-6 md:py-16"
        >
            <div class="mx-auto max-w-6xl">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <article
                        class="rounded-xl border-2 border-sage-border-dark bg-sage-bg p-6"
                    >
                        <div
                            class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg border-2 border-lemon bg-lemon/30 text-navy"
                        >
                            <span class="material-symbols-outlined text-xl"
                                >rocket_launch</span
                            >
                        </div>
                        <h2 class="font-condensed text-2xl font-black text-ink">
                            Our Mission
                        </h2>
                        <p
                            class="mt-3 text-sm leading-relaxed text-muted-green"
                        >
                            To redefine online auctions in Nigeria by creating a
                            transparent, engaging, and rewarding platform where
                            everyone has an equal opportunity to compete for
                            quality products.
                        </p>
                    </article>

                    <article
                        class="rounded-xl border-2 border-sage-border-dark bg-sage-bg p-6"
                    >
                        <div
                            class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg border-2 border-lemon bg-lemon/30 text-navy"
                        >
                            <span class="material-symbols-outlined text-xl"
                                >visibility</span
                            >
                        </div>
                        <h2 class="font-condensed text-2xl font-black text-ink">
                            Our Vision
                        </h2>
                        <p
                            class="mt-3 text-sm leading-relaxed text-muted-green"
                        >
                            To become Africa's leading digital auction platform,
                            connecting millions of users through a trusted,
                            innovative, and rewarding bidding experience.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="px-4 py-12 sm:px-6 md:py-16">
            <div class="mx-auto max-w-4xl text-center">
                <p
                    class="text-base leading-relaxed font-extrabold text-ink sm:text-lg"
                >
                    BIDORA isn't just about bidding; it's about creating
                    exciting opportunities, rewarding participation, and making
                    every auction an experience worth joining.
                </p>
                <Link
                    :href="openBids.url()"
                    class="mt-6 inline-flex h-12 cursor-pointer items-center justify-center rounded-xl border-none bg-lemon px-6 font-sans text-sm font-extrabold whitespace-nowrap text-navy no-underline transition-colors hover:bg-amber"
                >
                    Start Bidding Now
                </Link>
            </div>
        </section>
    </div>
</template>
