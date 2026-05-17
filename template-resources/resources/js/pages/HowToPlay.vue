<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { history, leaderboard, openBids, trending } from '@/routes';

const steps = [
    {
        title: 'Earn Points',
        description: 'Earn points when you initiate a subscription to start your bidding journey.',
        icon: 'payments',
    },
    {
        title: 'Opening Points',
        description: 'Items require a specific number of "Opening Points" to become available for bidding.',
        icon: 'inventory_2',
    },
    {
        title: 'Auction Starts',
        description: 'Once an item reaches its opening points, it becomes open for bidding and a countdown begins.',
        icon: 'timer',
    },
    {
        title: 'Place Your Bids',
        description: 'During the countdown, you can bid on the items you want using your earned points.',
        icon: 'gavel',
    },
    {
        title: 'Win & Save',
        description: 'The highest bidder wins when the countdown ends. Winners get premium items at a fraction of the cost.',
        icon: 'emoji_events',
    },
    {
        title: 'Track Progress',
        description: 'Track the countdown by clicking on the item\'s name under "Open Bids".',
        icon: 'search',
    },
];

const sections = [
    {
        title: 'History of Bids Won',
        description: 'View items won by highest bidders on CarryGo.',
        icon: 'history',
        href: history.url(),
    },
    {
        title: 'Open Bids',
        description: 'View items whose counter has been activated and ready for bidders to claim.',
        icon: 'door_open',
        href: openBids.url(),
    },
    {
        title: 'Trending Items',
        description: 'View items that are currently being engaged by subscribers.',
        icon: 'trending_up',
        href: trending.url(),
    },
    {
        title: 'Leaderboard',
        description: 'View items and its top bidders. See who is leading the race.',
        icon: 'leaderboard',
        href: leaderboard.url(),
    },
    {
        title: 'Review',
        description: 'Share your experience! Send reviews for items you have won.',
        icon: 'rate_review',
        href: history.url(),
    },
    {
        title: 'My Bids',
        description: 'View items you initiated a bid on. Quick access to your active engagements.',
        icon: 'person',
        href: '#',
    },
];
</script>

<template>
    <Head title="How to Play" />

    <div class="min-h-screen bg-background">
        <!-- Hero Section -->
        <section class="hero-pattern py-20 px-8">
            <div class="mx-auto max-w-7xl text-center">
                <span class="mb-4 inline-block rounded-full bg-primary-container px-4 py-1.5 text-xs font-bold tracking-widest text-on-primary-container uppercase">
                    Getting Started
                </span>
                <h1 class="font-headline mb-6 text-5xl font-extrabold tracking-tight text-on-surface md:text-7xl">
                    How to Play with
                    <span class="bg-linear-to-r from-primary to-primary-container bg-clip-text text-transparent">Bid Points</span>
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-primary">
                    Master the art of executive bidding. Follow our simple steps to start winning curated luxury items today.
                </p>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-20 px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-16 flex items-center gap-4">
                    <div class="h-10 w-1 bg-primary rounded-full"></div>
                    <h2 class="font-headline text-3xl font-bold text-on-surface">How It Works</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="(step, index) in steps"
                        :key="index"
                        class="group relative rounded-3xl bg-surface p-8 transition-all hover:bg-surface-container-low hover:shadow-xl"
                    >
                        <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-container text-primary transition-transform group-hover:scale-110">
                            <span class="material-symbols-outlined text-3xl">{{ step.icon }}</span>
                        </div>
                        <div class="absolute top-8 right-8 text-6xl font-black text-on-surface/5">
                            {{ index + 1 }}
                        </div>
                        <h3 class="font-headline mb-3 text-xl font-bold text-on-surface">{{ step.title }}</h3>
                        <p class="text-primary leading-relaxed font-body">
                            {{ step.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Key Sections -->
        <section class="bg-surface-container-lowest py-20 px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-16 flex items-center gap-4">
                    <div class="h-10 w-1 bg-primary rounded-full"></div>
                    <h2 class="font-headline text-3xl font-bold text-on-surface">Key Sections</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Link
                        v-for="section in sections"
                        :key="section.title"
                        :href="section.href"
                        class="flex items-start gap-6 rounded-3xl border border-outline-variant/30 p-8 transition-all hover:bg-white hover:shadow-lg active:scale-[0.99]"
                    >
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-secondary-container text-secondary">
                            <span class="material-symbols-outlined text-2xl">{{ section.icon }}</span>
                        </div>
                        <div>
                            <h3 class="font-headline mb-2 text-lg font-bold text-on-surface">{{ section.title }}</h3>
                            <p class="text-sm text-primary leading-relaxed">
                                {{ section.description }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Important Note -->
        <section class="py-20 px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col md:flex-row items-center gap-8 rounded-3xl bg-secondary p-10 text-on-secondary shadow-2xl">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-white/20">
                        <span class="material-symbols-outlined text-4xl">info</span>
                    </div>
                    <div>
                        <h2 class="font-headline mb-2 text-2xl font-bold">Important Note</h2>
                        <p class="text-lg">
                            Winners will be contacted officially through their registered phone number. Ensure your phone number is up to date to avoid any delays in claiming your prize.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
