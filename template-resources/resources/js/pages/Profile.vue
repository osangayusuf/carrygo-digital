<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { home } from '@/routes';

const props = defineProps<{
    user: {
        msisdn: string;
        referral_code: string;
        points: number;
    };
    referral_url: string;
}>();

const copied = ref(false);

function copyToClipboard() {
    navigator.clipboard.writeText(props.referral_url);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}

function share() {
    if (navigator.share) {
        navigator
            .share({
                title: 'CarryGo Referral',
                text: `Join me on CarryGo and start bidding! My referral code: ${props.user.referral_code}`,
                url: props.referral_url,
            })
            .catch(() => {
                copyToClipboard();
            });
    } else {
        copyToClipboard();
    }
}
</script>

<template>
    <Head title="My Profile" />

    <div class="min-h-screen bg-background px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="mx-auto max-w-3xl">
            <!-- Profile Header -->
            <div
                class="relative mb-8 overflow-hidden rounded-3xl border border-outline-variant/30 bg-surface p-8 shadow-sm sm:p-10"
            >
                <!-- Background decoration -->
                <div
                    class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-primary/10 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-secondary/10 blur-3xl"
                ></div>

                <div
                    class="relative flex flex-col items-center gap-8 md:flex-row"
                >
                    <div
                        class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full border-4 border-white bg-primary/10 text-primary shadow-md dark:border-surface"
                    >
                        <span class="material-symbols-outlined text-5xl!"
                            >person</span
                        >
                    </div>

                    <div class="flex-1 text-center md:text-left">
                        <h1
                            class="font-headline text-3xl font-black tracking-tight text-on-surface"
                        >
                            My Profile
                        </h1>
                        <p class="mt-1 font-medium text-secondary/70">
                            Welcome back to CarryGo
                        </p>

                        <div
                            class="mt-4 flex flex-wrap justify-center gap-4 md:justify-start"
                        >
                            <div
                                class="flex items-center gap-2 rounded-full border border-outline-variant/20 bg-white px-4 py-1.5 text-sm font-bold shadow-xs dark:bg-surface-container"
                            >
                                <span
                                    class="material-symbols-outlined text-lg! text-primary"
                                    >phone_iphone</span
                                >
                                <span>{{ user.msisdn }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Points Card -->
                <div
                    class="group relative overflow-hidden rounded-3xl bg-primary p-8 text-white shadow-lg shadow-primary/20"
                >
                    <span
                        class="material-symbols-outlined absolute -bottom-4 -right-4 rotate-12 opacity-10 transition-transform group-hover:scale-110 text-8xl!"
                        >stars</span
                    >
                    <div class="relative">
                        <p
                            class="font-headline text-sm font-bold uppercase tracking-widest opacity-80"
                        >
                            Available Points
                        </p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-4xl font-black tracking-tighter">{{
                                user.points.toLocaleString()
                            }}</span>
                            <span class="text-sm font-bold opacity-70"
                                >PTS</span
                            >
                        </div>
                        <p
                            class="mt-4 inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-medium backdrop-blur-md"
                        >
                            Points updated in real-time
                        </p>
                    </div>
                </div>

                <!-- Referral Code Card -->
                <div
                    class="group relative overflow-hidden rounded-3xl bg-secondary p-8 text-white shadow-lg shadow-secondary/20"
                >
                    <span
                        class="material-symbols-outlined absolute -bottom-4 -right-4 -rotate-12 opacity-10 transition-transform group-hover:scale-110 text-8xl!"
                        >qr_code_2</span
                    >
                    <div class="relative">
                        <p
                            class="font-headline text-sm font-bold uppercase tracking-widest opacity-80"
                        >
                            Referral Code
                        </p>
                        <div class="mt-2 text-4xl font-black tracking-tighter">
                            {{ user.referral_code || '---' }}
                        </div>
                        <p
                            class="mt-4 inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-medium backdrop-blur-md"
                        >
                            Use this to invite friends
                        </p>
                    </div>
                </div>
            </div>

            <!-- Referral Link Card -->
            <div
                class="rounded-3xl border border-outline-variant/30 bg-surface p-8 shadow-sm"
            >
                <h2
                    class="mb-4 font-headline text-xl font-bold text-on-surface"
                >
                    Share & Earn
                </h2>
                <p class="mb-6 max-w-lg text-sm text-secondary/70">
                    Invite your friends to join CarryGo using your unique link.
                    When they subscribe and bid, you earn more points!
                </p>

                <div class="relative group">
                    <div
                        class="flex items-center gap-3 rounded-2xl border border-outline-variant/50 bg-background p-4 transition-colors group-hover:border-primary/50"
                    >
                        <span
                            class="material-symbols-outlined shrink-0 text-secondary/40"
                            >link</span
                        >
                        <span class="flex-1 truncate text-sm font-medium">{{
                            referral_url
                        }}</span>
                        <button
                            @click="copyToClipboard"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary transition hover:bg-primary/20 active:scale-95"
                            :title="copied ? 'Copied!' : 'Copy link'"
                        >
                            <span class="material-symbols-outlined text-xl!">{{
                                copied ? 'check' : 'content_copy'
                            }}</span>
                        </button>
                    </div>

                    <button
                        @click="share"
                        class="mt-6 flex w-full items-center justify-center gap-3 rounded-2xl bg-primary px-8 py-4 text-lg font-bold text-white shadow-md shadow-primary/20 transition transform hover:opacity-95 active:scale-95"
                    >
                        <span class="material-symbols-outlined">share</span>
                        Share Link Now
                    </button>

                    <transition
                        enter-active-class="transform transition duration-300 ease-out"
                        enter-from-class="translate-y-2 opacity-0"
                        enter-to-class="translate-y-0 opacity-100"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div
                            v-if="copied"
                            class="absolute -top-12 left-1/2 -translate-x-1/2 rounded-full bg-on-surface px-3 py-1 text-xs font-bold text-surface shadow-lg"
                        >
                            Link copied to clipboard!
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-12 text-center">
                <Link
                    :href="home.url()"
                    class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline"
                >
                    <span class="material-symbols-outlined text-lg!"
                        >arrow_back</span
                    >
                    Back to Bidding
                </Link>
            </div>
        </div>
    </div>
</template>
