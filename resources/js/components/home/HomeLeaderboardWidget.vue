<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { leaderboard } from '@/routes/profile';

type TopBidder = {
    rank: number;
    name: string;
    msisdn: string;
    total_bid_pts: number;
};

defineProps<{
    topBidders: TopBidder[] | null | undefined;
}>();

const rankEmoji: Record<number, string> = {
    1: '🥇',
    2: '🥈',
    3: '🥉',
};
</script>

<template>
    <aside
        class="sticky top-20 overflow-hidden rounded-xl border-2 border-sage-border bg-navy/95 font-sans"
    >
        <!-- Header -->
        <div
            class="flex items-center justify-between border-b border-sage-border bg-linear-to-br from-forest to-navy px-4 py-3"
        >
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-2xl text-lemon"
                    >emoji_events</span
                >
                <div>
                    <p
                        class="text-base font-black tracking-widest text-lemon uppercase"
                    >
                        Weekly Leaderboard
                    </p>
                    <p class="text-xs text-sage-dark">Top bidders this week</p>
                </div>
            </div>
            <Link
                :href="leaderboard()"
                class="rounded border border-lemon px-2 py-1 text-xs font-black tracking-wide text-lemon transition-colors hover:bg-lemon hover:text-navy"
            >
                Full →
            </Link>
        </div>

        <!-- Skeleton while loading -->
        <div v-if="!topBidders" class="py-1.5">
            <div
                v-for="n in 5"
                :key="n"
                class="flex items-center gap-3 border-b border-white/5 px-4 py-2.5 last:border-0"
            >
                <div class="h-5 w-6 animate-pulse rounded bg-white/10"></div>
                <div class="flex flex-1 flex-col gap-1">
                    <div
                        class="h-2.5 w-3/4 animate-pulse rounded bg-white/10"
                    ></div>
                    <div
                        class="h-2 w-2/5 animate-pulse rounded bg-white/6"
                        style="animation-delay: 0.3s"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-else-if="topBidders.length === 0"
            class="flex flex-col items-center gap-1.5 px-4 py-8 text-center"
        >
            <span class="material-symbols-outlined text-4xl text-sage-border"
                >leaderboard</span
            >
            <p class="text-[12px] font-bold text-sage-light">
                No bids this week yet.
            </p>
            <p class="text-[11px] text-sage-dark">Be first on the board!</p>
        </div>

        <!-- Bidder rows -->
        <ul v-else class="py-1.5">
            <li
                v-for="bidder in topBidders"
                :key="bidder.rank"
                class="flex items-center gap-3 border-b border-white/5 px-4 py-2.5 transition-colors last:border-0 hover:bg-white/4"
                :class="bidder.rank <= 3 ? 'bg-lemon/3' : ''"
            >
                <!-- Rank -->
                <span class="min-w-[24px] text-center text-base">
                    {{ rankEmoji[bidder.rank] ?? `#${bidder.rank}` }}
                </span>

                <!-- Info -->
                <div class="min-w-0 flex-1">
                    <p
                        class="truncate leading-snug font-extrabold text-sage-light"
                    >
                        {{ bidder.name }}
                    </p>
                    <p class="text-xs leading-snug text-sage-dark">
                        {{ bidder.msisdn }}
                    </p>
                </div>

                <!-- Points -->
                <div class="text-right">
                    <p class="text-xs leading-tight font-black text-lemon">
                        {{ bidder.total_bid_pts.toLocaleString() }}
                    </p>
                    <p
                        class="text-xs font-bold tracking-wide text-sage-dark uppercase"
                    >
                        pts
                    </p>
                </div>
            </li>
        </ul>

        <!-- Footer -->
        <div
            class="flex items-center gap-1 border-t border-sage-border bg-black/20 px-4 py-2 text-[10px] text-sage-dark"
        >
            <span class="material-symbols-outlined text-[13px]">info</span>
            Top 10 earn bonus points every Monday.
        </div>
    </aside>
</template>
