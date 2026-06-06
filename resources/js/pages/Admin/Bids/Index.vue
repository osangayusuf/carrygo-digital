<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    Search,
    TrendingUp
} from 'lucide-vue-next';
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <TrendingUp class="w-6 h-6 text-primary" />
                        <span>Bids Audit Ledger</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Audit complete historical records of bid placements, winning bids, and tie states.</p>
                </div>
            </div>

            <!-- Search bar -->
            <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl shadow-sm">
                <form @submit.prevent="handleSearch" class="flex gap-3">
                    <div class="flex-1 relative">
                        <input 
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH BY USER EMAIL OR AUCTION TITLE..."
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold"
                        />
                        <Search class="w-4 h-4 text-on-surface-variant absolute left-3.5 top-3.5" />
                    </div>

                    <div class="flex gap-2">
                        <button 
                            type="submit"
                            class="px-5 py-3 bg-secondary text-on-secondary-fixed font-black uppercase rounded-lg transition-all"
                        >
                            Filter
                        </button>
                        <button 
                            type="button"
                            @click="clearSearch"
                            class="px-5 py-3 border border-outline-variant bg-surface-container-lowest text-on-surface-variant hover:text-primary font-bold uppercase rounded-lg transition-all"
                        >
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bids List Table -->
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">
                                <th class="px-6 py-4">Bid ID</th>
                                <th class="px-6 py-4">Auction Item</th>
                                <th class="px-6 py-4">Bidder Info</th>
                                <th class="px-6 py-4 text-right">Points Placed</th>
                                <th class="px-6 py-4">Standing Status</th>
                                <th class="px-6 py-4">Placement Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="bid in bids.data" :key="bid.id" class="hover:bg-surface-container-low/20 transition-colors group">
                                <td class="px-6 py-4 font-bold text-on-surface-variant">#{{ bid.id }}</td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-primary">{{ bid.auction ? bid.auction.name : 'Unknown Auction' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="bid.user" class="flex flex-col">
                                        <span class="font-bold text-primary">{{ bid.user.name }}</span>
                                        <span class="text-[10px] text-on-surface-variant mt-0.5">{{ bid.user.email }}</span>
                                    </div>
                                    <span v-else class="text-on-surface-variant italic">Unknown Bidder</span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-primary">{{ bid.amount }} PTS</td>
                                <td class="px-6 py-4">
                                    <span 
                                        :class="[
                                            'px-2.5 py-1 text-[9px] font-black uppercase tracking-widest border rounded-full flex items-center gap-1.5 w-max',
                                            bid.is_winning 
                                                ? 'bg-secondary-container text-on-secondary-container border-secondary/20' 
                                                : 'bg-surface-container-low text-on-surface-variant border-outline-variant'
                                        ]"
                                    >
                                        <TrendingUp v-if="bid.is_winning" class="w-3.5 h-3.5" />
                                        <span>{{ bid.is_winning ? 'Winning' : 'Outbid / Tied' }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant font-semibold text-[10px]">
                                    {{ new Date(bid.created_at).toLocaleString() }}
                                </td>
                            </tr>
                            <tr v-if="bids.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant uppercase tracking-widest font-bold">
                                    No bid transactions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links -->
                <div v-if="bids.last_page > 1" class="px-6 py-4 bg-surface-container-low flex justify-between items-center border-t border-outline-variant">
                    <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-widest">
                        Page {{ bids.current_page }} of {{ bids.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link 
                            v-for="link in bids.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 border text-[10px] font-bold rounded-lg transition-all',
                                link.active 
                                    ? 'bg-primary text-on-primary border-primary' 
                                    : 'text-on-surface-variant border-outline-variant bg-surface-container-lowest hover:bg-surface-container-low',
                                !link.url && 'opacity-40 cursor-not-allowed'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
