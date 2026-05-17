<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const terms = [
    {
        title: 'Welcome & Engagement',
        points: [
            'Welcome to CarryGo, Nigeria\'s trusted e-bidding community—where your points are your power to win electronics, gadgets, home appliances and lots more.',
            'These Terms help protect everyone and make bidding smooth, fair, and rewarding.'
        ],
        icon: 'handshake',
    },
    {
        title: 'Bidding & Auto-Bid',
        points: [
            'Each bid is a serious commitment—by bidding, you intend to win if you emerge as the top bidder.',
            'Auctions do not extend if bids occur after the counter expires—stay alert and engaged!',
            'Auto-bid allows you to set a maximum amount, and the system bids for you up to that limit.'
        ],
        icon: 'gavel',
    },
    {
        title: 'Points Wallet',
        points: [
            'Your points balance indicates your bidding potential—points placed in active bids are not available until resolved.',
            'Points from unsuccessful bids return immediately.',
            'Bidding deducts your points (and any additional applicable fees).'
        ],
        icon: 'account_balance_wallet',
    },
    {
        title: 'Winning & Payment Obligation',
        points: [
            'If you\'re the top bidder when the timer stops, you win. Please follow through promptly.',
            'Winners must share contact details for delivery within 3 business days when contacted.'
        ],
        icon: 'emoji_events',
    },
    {
        title: 'Ratings & Transparency',
        points: [
            'Every winner must leave a rating and short review.',
            'This helps maintain trust and transparency on the platform.'
        ],
        icon: 'star',
    },
    {
        title: 'Inspect & Collect',
        points: [
            'All items are offered "As-Is." Review listings carefully.',
            'Items must be collected within 5 days after payment confirmation.'
        ],
        icon: 'manage_search',
    },
    {
        title: 'Fair Play & Integrity',
        points: [
            'Cheating, bid manipulation, collusion, or misuse is prohibited.',
            'Violations may lead to suspension, forfeiture of points, or legal action.',
            'Cooling Period Policy: Winners of "Grand Items" are ineligible to win another grand prize for a period of 3 months.'
        ],
        icon: 'gpp_good',
    },
    {
        title: 'Liability & Accessibility',
        points: [
            'CarryGo does not guarantee uninterrupted service.',
            'Not liable for technical issues or delays.'
        ],
        icon: 'security',
    },
    {
        title: 'Disputes & Governing Law',
        points: [
            'Disputes will go through mediation, then arbitration in Lagos under Nigerian law.'
        ],
        icon: 'balance',
    },
    {
        title: 'Amendments',
        points: [
            'Terms may be updated periodically.',
            'Continued use implies acceptance.'
        ],
        icon: 'update',
    },
];
</script>

<template>

    <Head title="Terms & Conditions | CarryGo" />

    <div class="min-h-screen bg-background">
        <!-- Hero Section -->
        <section class="hero-pattern py-20 px-8">
            <div class="mx-auto max-w-7xl text-center">
                <span
                    class="mb-4 inline-block rounded-full bg-primary-container px-4 py-1.5 text-xs font-bold tracking-widest text-on-primary-container uppercase">
                    Legal
                </span>
                <h1 class="font-headline mb-6 text-5xl font-extrabold tracking-tight text-on-surface md:text-7xl">
                    Terms & <span
                        class="bg-linear-to-r from-primary to-primary-container bg-clip-text text-transparent">Conditions</span>
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-secondary">
                    Please read these terms carefully before using our platform.
                    <br>
                    <strong>Effective date is April 2026</strong>
                </p>
            </div>
        </section>

        <!-- Terms Details -->
        <section class="py-20 px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-16 flex items-center gap-4">
                    <div class="h-10 w-1 bg-primary rounded-full"></div>
                    <h2 class="font-headline text-3xl font-bold text-on-surface">Platform Rules</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(term, index) in terms" :key="index"
                        class="group relative rounded-3xl bg-surface p-8 transition-all hover:bg-surface-container-low hover:shadow-xl">
                        <div
                            class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-container text-primary transition-transform group-hover:scale-110">
                            <span class="material-symbols-outlined text-3xl">{{ term.icon }}</span>
                        </div>
                        <div class="absolute top-8 right-8 text-6xl font-black text-on-surface/5">
                            {{ index + 1 }}
                        </div>
                        <h3 class="font-headline mb-3 text-xl font-bold text-on-surface">{{ term.title }}</h3>
                        <ul class="text-secondary leading-relaxed font-body space-y-3">
                            <li v-for="(point, ptIndex) in term.points" :key="ptIndex" class="flex items-start gap-3">
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-primary/60"></span>
                                <span>{{ point }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Important Note -->
        <section class="bg-surface-container-lowest py-20 px-8">
            <div class="mx-auto max-w-7xl">
                <div
                    class="flex flex-col md:flex-row items-center gap-8 rounded-3xl bg-secondary p-10 text-on-secondary shadow-2xl">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-white/20">
                        <span class="material-symbols-outlined text-4xl">contact_support</span>
                    </div>
                    <div>
                        <h2 class="font-headline mb-2 text-2xl font-bold">Have Questions?</h2>
                        <p class="text-lg opacity-90">
                            If you have any inquiries regarding these terms and conditions, please contact our support
                            team.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>

</template>
