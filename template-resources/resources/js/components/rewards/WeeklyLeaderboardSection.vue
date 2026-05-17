<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    leaderboard: {
        top_users: Array<{
            rank: number;
            msisdn_masked: string;
            total_bid_pts: number;
        }>;
        week_ends_at: string;
        user_rank: number | null;
    };
}>();

const rankEmoji = ['🥇', '🥈', '🥉'];
const rankColors = [
    'bg-yellow-400/20 text-yellow-600 border-yellow-400/30',
    'bg-slate-300/30 text-slate-500 border-slate-300/40',
    'bg-orange-400/20 text-orange-600 border-orange-400/30',
];

// Countdown
const countdown = ref('');
let timer: ReturnType<typeof setInterval>;

function updateCountdown() {
    const end = new Date(props.leaderboard.week_ends_at).getTime();
    const now = Date.now();
    const diff = Math.max(0, end - now);

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

    if (days > 0) {
        countdown.value = `${days}d ${hours}h`;
    } else if (hours > 0) {
        countdown.value = `${hours}h ${minutes}m`;
    } else {
        countdown.value = `${minutes}m`;
    }
}

onMounted(() => {
    updateCountdown();
    timer = setInterval(updateCountdown, 60_000);
});

onUnmounted(() => clearInterval(timer));
</script>

<template>
    <div class="rounded-3xl border border-outline-variant/30 bg-surface p-6 shadow-sm sm:p-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="font-headline text-xl font-bold text-on-surface">Weekly Top 3</h2>
                <p class="mt-0.5 text-sm text-on-surface-variant">
                    Top bidders this week win bonus points
                </p>
            </div>
            <div class="rounded-full bg-surface-container px-3 py-1.5 text-xs font-bold text-on-surface-variant">
                Ends in {{ countdown }}
            </div>
        </div>

        <!-- Top users -->
        <div v-if="leaderboard.top_users.length > 0" class="space-y-3">
            <div
                v-for="user in leaderboard.top_users"
                :key="user.rank"
                :class="[
                    'flex items-center gap-4 rounded-2xl border p-4 transition-all',
                    rankColors[user.rank - 1] ?? 'bg-background border-outline-variant/20',
                ]"
            >
                <!-- Rank -->
                <div class="flex h-10 w-10 shrink-0 items-center justify-center text-2xl">
                    {{ rankEmoji[user.rank - 1] ?? user.rank }}
                </div>

                <!-- Avatar -->
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary"
                >
                    <span class="material-symbols-outlined text-xl!">person</span>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="truncate text-sm font-bold text-on-surface">{{ user.msisdn_masked }}</p>
                    <p class="text-xs text-on-surface-variant">
                        {{ user.total_bid_pts.toLocaleString() }} pts bid this week
                    </p>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="py-8 text-center">
            <span class="material-symbols-outlined text-4xl text-on-surface-variant/40">leaderboard</span>
            <p class="mt-2 text-sm text-on-surface-variant">No bidding activity yet this week</p>
        </div>

        <!-- User's own rank (if outside top 3) -->
        <div
            v-if="leaderboard.user_rank !== null && leaderboard.user_rank > 3"
            class="mt-4 flex items-center justify-center gap-2 rounded-2xl border border-outline-variant/20 bg-background p-3"
        >
            <span class="material-symbols-outlined text-sm! text-on-surface-variant">person</span>
            <span class="text-sm text-on-surface-variant">
                Your rank this week:
                <span class="font-black text-on-surface">#{{ leaderboard.user_rank }}</span>
            </span>
        </div>
    </div>
</template>
