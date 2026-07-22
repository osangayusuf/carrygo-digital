<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Search, TrendingUp } from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { index as bidsIndex } from '@/routes/admin/bids';

type Bid = {
    id: number;
    auction_id: number;
    user_id: number;
    amount: number;
    is_winning: boolean;
    created_at: string;
    user: { name: string; email: string } | null;
    auction: { name: string } | null;
};

const props = defineProps<{
    bids: {
        data: Bid[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search: string | null;
    };
}>();

const searchForm = useForm({
    search: props.filters.search || '',
});

const handleSearch = () => {
    searchForm.get(bidsIndex.url(), {
        preserveState: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    handleSearch();
};
</script>

<template>
    <Head title="Admin - Bids Audit" />

    <AdminLayout :breadcrumbs="[{ title: 'Bids Audit' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <TrendingUp class="h-6 w-6 text-primary" />
                        <span>Bids Audit Ledger</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Audit complete historical records of bid placements,
                        winning bids, and tie states.
                    </p>
                </div>
            </div>

            <!-- Search bar -->
            <div
                class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"
            >
                <form @submit.prevent="handleSearch" class="flex gap-3">
                    <div class="relative flex-1">
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH BY USER EMAIL OR AUCTION TITLE..."
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-3 pr-4 pl-10 text-xs font-bold text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                        />
                        <Search
                            class="absolute top-3.5 left-3.5 h-4 w-4 text-on-surface-variant"
                        />
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="rounded-lg bg-secondary px-5 py-3 font-black text-on-secondary-fixed uppercase transition-all"
                        >
                            Filter
                        </button>
                        <button
                            type="button"
                            @click="clearSearch"
                            class="rounded-lg border border-outline-variant bg-surface-container-lowest px-5 py-3 font-bold text-on-surface-variant uppercase transition-all hover:text-primary"
                        >
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bids List Table -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[800px] border-collapse text-left"
                    >
                        <thead
                            class="border-b border-outline-variant bg-surface-container-low"
                        >
                            <tr
                                class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                            >
                                <th class="px-6 py-4">Bid ID</th>
                                <th class="px-6 py-4">Auction Item</th>
                                <th class="px-6 py-4">Bidder Info</th>
                                <th class="px-6 py-4 text-right">
                                    Points Placed
                                </th>
                                <th class="px-6 py-4">Standing Status</th>
                                <th class="px-6 py-4">Placement Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr
                                v-for="bid in bids.data"
                                :key="bid.id"
                                class="group transition-colors hover:bg-surface-container-low/20"
                            >
                                <td
                                    class="px-6 py-4 font-bold text-on-surface-variant"
                                >
                                    #{{ bid.id }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-primary">{{
                                        bid.auction
                                            ? bid.auction.name
                                            : 'Unknown Auction'
                                    }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="bid.user" class="flex flex-col">
                                        <span class="font-bold text-primary">{{
                                            bid.user.name
                                        }}</span>
                                        <span
                                            class="mt-0.5 text-[10px] text-on-surface-variant"
                                            >{{ bid.user.email }}</span
                                        >
                                    </div>
                                    <span
                                        v-else
                                        class="text-on-surface-variant italic"
                                        >Unknown Bidder</span
                                    >
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-bold text-primary"
                                >
                                    {{ bid.amount }} PTS
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'flex w-max items-center gap-1.5 rounded-full border px-2.5 py-1 text-[9px] font-black tracking-widest uppercase',
                                            bid.is_winning
                                                ? 'border-secondary/20 bg-secondary-container text-on-secondary-container'
                                                : 'border-outline-variant bg-surface-container-low text-on-surface-variant',
                                        ]"
                                    >
                                        <TrendingUp
                                            v-if="bid.is_winning"
                                            class="h-3.5 w-3.5"
                                        />
                                        <span>{{
                                            bid.is_winning
                                                ? 'Winning'
                                                : 'Outbid / Tied'
                                        }}</span>
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-[10px] font-semibold text-on-surface-variant"
                                >
                                    {{
                                        new Date(
                                            bid.created_at,
                                        ).toLocaleString()
                                    }}
                                </td>
                            </tr>
                            <tr v-if="bids.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    No bid transactions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links -->
                <div
                    v-if="bids.last_page > 1"
                    class="flex items-center justify-between border-t border-outline-variant bg-surface-container-low px-6 py-4"
                >
                    <p
                        class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                    >
                        Page {{ bids.current_page }} of {{ bids.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in bids.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'rounded-lg border px-3 py-1.5 text-[10px] font-bold transition-all',
                                link.active
                                    ? 'border-primary bg-primary text-on-primary'
                                    : 'border-outline-variant bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-low',
                                !link.url && 'cursor-not-allowed opacity-40',
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
