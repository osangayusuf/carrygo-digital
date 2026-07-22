<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import {
    eventItems,
    leaderboard,
    openBids,
    tasks,
    trending,
    wallet,
    winners,
} from '@/routes';

defineOptions({ layout: PublicLayout });

type Step = {
    title: string;
    description: string;
    icon: string;
};

type Section = {
    title: string;
    description: string;
    route: string;
    icon: string;
};

type Faq = {
    question: string;
    answer: string;
};

type Support = {
    title: string;
    description: string;
};

const props = defineProps<{
    steps: Step[];
    sections: Section[];
    faqs: Faq[];
    support: Support;
}>();

function resolveSectionHref(routeName: string): string {
    const map: Record<string, string> = {
        'open-bids': openBids.url(),
        trending: trending.url(),
        'event-items': eventItems.url(),
        winners: winners.url(),
        leaderboard: leaderboard.url(),
        tasks: tasks.url(),
        wallet: wallet.url(),
    };

    return map[routeName] ?? openBids.url();
}
</script>

<template>
    <Head title="How To Play" />

    <div class="bg-sage-bg text-ink">
        <section
            class="border-b-4 border-lemon bg-navy px-4 py-14 sm:px-6 md:py-20"
        >
            <div class="mx-auto max-w-6xl text-center">
                <span
                    class="inline-block rounded-full border-2 border-lemon bg-lemon px-4 py-1 text-xs font-extrabold tracking-wide text-navy uppercase"
                >
                    Bidora Guide
                </span>
                <h1
                    class="mt-5 font-condensed text-4xl leading-tight font-black text-white md:text-6xl"
                >
                    How To Play and Win on
                    <span class="text-lemon">Bidora</span>
                </h1>
                <p
                    class="mx-auto mt-4 max-w-3xl text-sm leading-relaxed text-[#d7e3d4] sm:text-base"
                >
                    Learn the complete bidding flow from account setup to live
                    auction wins. Use this guide to bid smarter, stay ready, and
                    claim your prize without delays.
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
                        How It Works
                    </h2>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="(step, index) in props.steps"
                        :key="step.title"
                        class="relative rounded-xl border-2 border-sage-border-dark bg-white p-6 shadow-sm"
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
                        Key Sections
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <Link
                        v-for="section in props.sections"
                        :key="section.title"
                        :href="resolveSectionHref(section.route)"
                        class="rounded-xl border-2 border-sage-border-dark bg-sage-bg p-5 no-underline transition-colors hover:border-lemon hover:bg-white"
                    >
                        <div class="flex items-start gap-4">
                            <span
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border-2 border-navy bg-navy text-lemon"
                            >
                                <span
                                    class="material-symbols-outlined text-xl"
                                    >{{ section.icon }}</span
                                >
                            </span>
                            <div>
                                <h3
                                    class="font-headline text-base font-extrabold text-ink"
                                >
                                    {{ section.title }}
                                </h3>
                                <p
                                    class="mt-1 text-sm leading-relaxed text-muted-green"
                                >
                                    {{ section.description }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <section class="px-4 py-12 sm:px-6 md:py-16">
            <div class="mx-auto max-w-6xl">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <article
                        class="rounded-xl border-2 border-sage-border-dark bg-white p-6"
                    >
                        <h2 class="font-condensed text-2xl font-black text-ink">
                            Fair Play and Tips
                        </h2>
                        <p
                            class="mt-3 text-sm leading-relaxed text-muted-green"
                        >
                            {{ props.support.description }}
                        </p>
                        <div
                            class="mt-4 rounded-lg border-2 border-lemon bg-lemon/20 p-3 text-xs font-bold text-navy"
                        >
                            Keep your registered phone number and profile
                            details current to avoid winner-verification delays.
                        </div>
                    </article>

                    <article
                        class="rounded-xl border-2 border-sage-border-dark bg-white p-6"
                    >
                        <h2 class="font-condensed text-2xl font-black text-ink">
                            Quick FAQ
                        </h2>
                        <div class="mt-4 space-y-3">
                            <details
                                v-for="faq in props.faqs"
                                :key="faq.question"
                                class="group rounded-lg border border-sage-border bg-sage-bg/40 p-3"
                            >
                                <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-extrabold text-ink"
                                >
                                    <span>{{ faq.question }}</span>
                                    <span
                                        class="material-symbols-outlined text-lg text-muted-green transition-transform group-open:rotate-180"
                                    >
                                        expand_more
                                    </span>
                                </summary>
                                <p
                                    class="mt-2 border-t border-sage-border pt-2 text-sm leading-relaxed text-muted-green"
                                >
                                    {{ faq.answer }}
                                </p>
                            </details>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </div>
</template>
