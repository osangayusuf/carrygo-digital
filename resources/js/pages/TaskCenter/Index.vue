<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AchievementBadgesSection from '@/components/rewards/AchievementBadgesSection.vue';
import DailyCheckinSection from '@/components/rewards/DailyCheckinSection.vue';
import SpinWheelSection from '@/components/rewards/SpinWheelSection.vue';
import WeeklyLeaderboardSection from '@/components/rewards/WeeklyLeaderboardSection.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { home, profile } from '@/routes';
import RewardClaimController from '@/actions/App/Http/Controllers/RewardClaimController';

defineOptions({ layout: PublicLayout });

defineProps<{
    checkin: {
        streak: number;
        last_date: string | null;
        can_checkin: boolean;
        week_days: Array<{
            label: string;
            date: string;
            checked: boolean;
            is_today: boolean;
        }>;
    };
    achievements: Array<{
        key: string;
        label: string;
        description: string;
        icon: string;
        points: number;
        target: number;
        progress: number;
        completed_at: string | null;
    }>;
    spin: {
        available_spins: number;
        segment_labels: string[];
    };
    leaderboard: {
        top_users: Array<{
            rank: number;
            msisdn_masked: string;
            total_bid_pts: number;
        }>;
        week_ends_at: string;
        user_rank: number | null;
    };
    wallet: {
        unclaimed_points: number;
        spendable_on_claim?: number;
        recent_rewards: Array<{
            source: string;
            description: string;
            points: number;
            claimed_at: string | null;
            created_at: string;
        }>;
    };
    user_points: number;
}>();

const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null);
let toastTimer: ReturnType<typeof setTimeout>;

function showToast(message: string, type: 'success' | 'error' = 'success') {
    toast.value = { message, type };
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.value = null;
    }, 4000);
}

const page = usePage();
watch(
    () => page.props.flash as Record<string, string> | undefined,
    (flash) => {
        if (flash?.success) {
            showToast(flash.success, 'success');
        } else if (flash?.error) {
            showToast(flash.error, 'error');
        }
    },
    { immediate: true },
);

function onSpun(pointsWon: number) {
    showToast(`You won ${pointsWon} bonus pts! Claim them above when ready.`);
    router.reload({ only: ['wallet', 'spin'] });
}
</script>

<template>
    <Head title="Task Center" />

    <div class="min-h-screen bg-background px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="mx-auto max-w-3xl">
            <div class="mb-8">
                <h1 class="font-headline text-3xl font-black tracking-tight text-on-surface">Task Center</h1>
                <p class="mt-1 text-on-surface-variant">Complete tasks to earn bonus points, then claim to bid</p>
                <p class="mt-2 text-sm font-bold text-primary">
                    Spendable balance: {{ user_points }} pts
                </p>
            </div>

            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="-translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
            >
                <div
                    v-if="wallet.unclaimed_points > 0"
                    class="mb-6 overflow-hidden rounded-2xl border-2 border-primary bg-primary p-5 text-on-primary shadow-lg"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🎁</span>
                            <div>
                                <p class="font-headline font-bold">Unclaimed Bonus Points</p>
                                <p class="text-sm opacity-90">
                                    <span class="font-black">{{ wallet.unclaimed_points }} bonus pts</span>
                                    <template v-if="wallet.spendable_on_claim">
                                        → {{ wallet.spendable_on_claim }} spendable on claim
                                    </template>
                                </p>
                            </div>
                        </div>
                        <Form :action="RewardClaimController.url()" method="post">
                            <button
                                type="submit"
                                class="shrink-0 rounded-xl border-2 border-secondary bg-secondary px-4 py-2 text-sm font-black text-on-tertiary shadow-sm transition hover:opacity-90 active:scale-95"
                            >
                                Claim Now →
                            </button>
                        </Form>
                    </div>
                </div>
            </Transition>

            <div class="space-y-6">
                <DailyCheckinSection :checkin="checkin" />
                <AchievementBadgesSection :achievements="achievements" />

                <div class="rounded-3xl border-2 border-outline-variant/50 bg-surface p-6 shadow-sm sm:p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-headline text-xl font-bold text-on-surface">Invite &amp; Earn</h2>
                            <p class="mt-1 text-sm text-on-surface-variant">
                                Share your referral code and earn when friends join and bid
                            </p>
                        </div>
                        <Link
                            :href="profile.url()"
                            class="flex items-center gap-1.5 rounded-xl border-2 border-primary/20 bg-primary/10 px-4 py-2 text-sm font-bold text-primary transition hover:bg-primary/20"
                        >
                            <span class="material-symbols-outlined text-base!">share</span>
                            Share
                        </Link>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <div class="flex items-center gap-2 rounded-xl bg-background px-4 py-2 text-sm border border-outline-variant/40">
                            <span class="h-2 w-2 rounded-full bg-primary"></span>
                            <span class="text-on-surface-variant">Friend registers:</span>
                            <span class="font-bold text-on-surface">10 pts</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-xl bg-background px-4 py-2 text-sm border border-outline-variant/40">
                            <span class="h-2 w-2 rounded-full bg-secondary"></span>
                            <span class="text-on-surface-variant">Friend buys points:</span>
                            <span class="font-bold text-on-surface">20 pts</span>
                        </div>
                    </div>
                </div>

                <SpinWheelSection :spin="spin" @spun="onSpun" />
                <WeeklyLeaderboardSection :leaderboard="leaderboard" />
            </div>

            <div class="mt-10 text-center">
                <Link
                    :href="home.url()"
                    class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline"
                >
                    <span class="material-symbols-outlined text-lg!">arrow_back</span>
                    Back to Bidding
                </Link>
            </div>
        </div>
    </div>

    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="toast"
            class="fixed right-4 bottom-24 z-50 flex max-w-xs items-start gap-3 rounded-2xl border-2 p-4 shadow-2xl sm:right-6 sm:bottom-8"
            :class="
                toast.type === 'success'
                    ? 'border-primary bg-on-surface text-surface'
                    : 'border-error bg-error-container text-on-error-container'
            "
        >
            <span class="material-symbols-outlined shrink-0 text-xl!">
                {{ toast.type === 'success' ? 'check_circle' : 'error' }}
            </span>
            <p class="text-sm font-medium leading-snug">{{ toast.message }}</p>
        </div>
    </Transition>
</template>
