<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import {
    Coins,
    Search
} from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { index as pointsIndex } from '@/routes/admin/point-transactions';

type PointTransaction = {
    id: number;
    user_id: number;
    type: 'deposit' | 'bid_debit' | 'bonus_award' | 'bonus_claim';
    amount: number;
    naira_amount: number | null;
    exchange_rate: number;
    provider_reference: string | null;
    status: 'pending' | 'completed' | 'failed';
    created_at: string;
    user: { name: string; email: string; phone: string } | null;
};

const props = defineProps<{
    transactions: {
        data: PointTransaction[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search: string | null;
        type: string | null;
    };
    availableTypes: string[];
}>();

const searchForm = useForm({
    search: props.filters.search || '',
    type: props.filters.type || '',
});

const handleSearch = () => {
    searchForm.get(pointsIndex.url(), {
        preserveState: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    searchForm.type = '';
    handleSearch();
};
</script>

<template>
    <Head title="Admin - Points Ledger" />

    <AdminLayout :breadcrumbs="[{ title: 'Points Ledger' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <Coins class="w-6 h-6 text-primary" />
                        <span>Points Accounting Ledger</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Audit platform point ledger histories, including deposits, bid debits, claims, and awards.</p>
                </div>
            </div>

            <!-- Search and Filter Panel -->
            <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl shadow-sm">
                <form @submit.prevent="handleSearch" class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH BY USER EMAIL OR REFERENCE..."
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold"
                        />
                        <Search class="w-4 h-4 text-on-surface-variant absolute left-3.5 top-3.5" />
                    </div>

                    <div class="w-full md:w-48">
                        <select
                            v-model="searchForm.type"
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold uppercase"
                        >
                            <option value="">ALL TYPES</option>
                            <option v-for="t in availableTypes" :key="t" :value="t">{{ t.replace('_', ' ') }}</option>
                        </select>
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

            <!-- Points Transactions List Table -->
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">
                                <th class="px-6 py-4">Transaction ID</th>
                                <th class="px-6 py-4">User Info</th>
                                <th class="px-6 py-4">Type</th>
                                <th class="px-6 py-4 text-right">Points Amount</th>
                                <th class="px-6 py-4">Reference / Rate</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-surface-container-low/20 transition-colors group">
                                <td class="px-6 py-4 font-bold text-on-surface-variant">#{{ tx.id }}</td>
                                <td class="px-6 py-4">
                                    <div v-if="tx.user" class="flex flex-col">
                                        <span class="font-bold text-primary">{{ tx.user.name }}</span>
                                        <span class="text-[10px] text-on-surface-variant mt-0.5">{{ tx.user.email }}</span>
                                    </div>
                                    <span v-else class="text-on-surface-variant italic">Unknown User</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 text-[9px] font-black uppercase tracking-widest border rounded-full whitespace-nowrap',
                                            tx.type === 'deposit' && 'bg-secondary-container text-on-secondary-container border-secondary/20',
                                            tx.type === 'bid_debit' && 'bg-error-container/30 border-error/20 text-error',
                                            tx.type === 'bonus_award' && 'bg-blue-50 text-blue-800 border-blue-200',
                                            tx.type === 'bonus_claim' && 'bg-amber-50 text-amber-850 border-amber-200',
                                        ]"
                                    >
                                        {{ tx.type.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-primary">
                                    {{ tx.type === 'bid_debit' ? '-' : '+' }}{{ tx.amount }} PTS
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-on-surface font-semibold select-all">{{ tx.provider_reference || 'N/A' }}</span>
                                        <span class="text-[9px] text-on-surface-variant mt-0.5">RATE: 1:{{ tx.exchange_rate }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 text-[9px] font-black uppercase tracking-widest border rounded-full',
                                            tx.status === 'completed' && 'bg-secondary-container text-on-secondary-container border-secondary/20',
                                            tx.status === 'pending' && 'bg-amber-50 text-amber-850 border-amber-200 animate-pulse',
                                            tx.status === 'failed' && 'bg-error-container/30 border-error/20 text-error'
                                        ]"
                                    >
                                        {{ tx.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant font-semibold text-[10px]">
                                    {{ new Date(tx.created_at).toLocaleString() }}
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-on-surface-variant uppercase tracking-widest font-bold">
                                    No ledger transactions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links -->
                <div v-if="transactions.last_page > 1" class="px-6 py-4 bg-surface-container-low flex justify-between items-center border-t border-outline-variant">
                    <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-widest">
                        Page {{ transactions.current_page }} of {{ transactions.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in transactions.links"
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
