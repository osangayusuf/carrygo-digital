<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SettingsPanel from '@/components/settings/SettingsPanel.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

type TopBidder = {
    rank: number;
    name: string;
    msisdn: string;
    total_bid_pts: number;
};

defineProps<{
    topBidders: TopBidder[];
}>();

defineOptions({ layout: PublicLayout });

const rankMedals: Record<number, string> = {
    1: '🥇',
    2: '🥈',
    3: '🥉',
};
</script>

<template>
    <Head title="Weekly Leaderboard" />

    <SettingsShell>
        <SettingsPanel
            title="Weekly Leaderboard"
            description="Top bidders this week earn bonus points at week's end. Keep bidding to climb the ranks!"
        >
            <!-- Empty state -->
            <div
                v-if="topBidders.length === 0"
                class="flex flex-col items-center gap-3 py-16 text-center"
            >
                <span
                    class="material-symbols-outlined text-5xl text-on-surface-variant"
                    >leaderboard</span
                >
                <p class="font-semibold text-on-surface">
                    No bids this week yet.
                </p>
                <p class="text-sm text-on-surface-variant">
                    Be the first to bid and claim the top spot!
                </p>
            </div>

            <!-- Leaderboard table -->
            <div
                v-else
                class="overflow-hidden rounded-xl border border-outline-variant"
            >
                <!-- Top 3 podium cards -->
                <div
                    v-if="topBidders.length >= 1"
                    class="grid grid-cols-1 gap-3 border-b border-outline-variant bg-surface-container-low p-4 sm:grid-cols-3"
                >
                    <div
                        v-for="bidder in topBidders.slice(0, 3)"
                        :key="bidder.rank"
                        class="flex flex-col items-center gap-1 rounded-lg border border-outline-variant bg-surface-container-lowest p-4 text-center"
                        :class="
                            bidder.rank === 1 ? 'ring-2 ring-secondary' : ''
                        "
                    >
                        <span class="text-3xl">{{
                            rankMedals[bidder.rank] ?? `#${bidder.rank}`
                        }}</span>
                        <p class="mt-1 font-bold text-on-surface">
                            {{ bidder.name }}
                        </p>
                        <p class="text-xs text-on-surface-variant">
                            {{ bidder.msisdn }}
                        </p>
                        <p class="mt-1 text-sm font-black text-secondary">
                            {{ bidder.total_bid_pts.toLocaleString() }} pts
                        </p>
                    </div>
                </div>

                <!-- Ranks 4–10 table -->
                <table v-if="topBidders.length > 3" class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-outline-variant bg-surface-container-low text-left text-xs font-bold tracking-wider text-on-surface-variant uppercase"
                        >
                            <th class="px-4 py-3">Rank</th>
                            <th class="px-4 py-3">Bidder</th>
                            <th class="px-4 py-3 text-right">Points Bid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="bidder in topBidders.slice(3)"
                            :key="bidder.rank"
                            class="border-b border-outline-variant transition-colors last:border-0 hover:bg-surface-container"
                        >
                            <td
                                class="px-4 py-3 font-bold text-on-surface-variant"
                            >
                                #{{ bidder.rank }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-on-surface">
                                    {{ bidder.name }}
                                </p>
                                <p class="text-xs text-on-surface-variant">
                                    {{ bidder.msisdn }}
                                </p>
                            </td>
                            <td
                                class="px-4 py-3 text-right font-bold text-on-surface"
                            >
                                {{ bidder.total_bid_pts.toLocaleString() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mt-4 text-xs text-on-surface-variant">
                ℹ️ Rankings reset every Monday. Bonus points are credited
                automatically to top finishers.
            </p>
        </SettingsPanel>
    </SettingsShell>
</template>
