<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Coins, Search } from 'lucide-vue-next';
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
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <Coins class="h-6 w-6 text-primary" />
                        <span>Points Accounting Ledger</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Audit platform point ledger histories, including
                        deposits, bid debits, claims, and awards.
                    </p>
                </div>
            </div>

            <!-- Search and Filter Panel -->
            <div
                class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"
            >
                <form
                    @submit.prevent="handleSearch"
                    class="flex flex-col gap-3 md:flex-row"
                >
                    <div class="relative flex-1">
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH BY USER EMAIL OR REFERENCE..."
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-3 pr-4 pl-10 text-xs font-bold text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                        />
                        <Search
                            class="absolute top-3.5 left-3.5 h-4 w-4 text-on-surface-variant"
                        />
                    </div>

                    <div class="w-full md:w-48">
                        <select
                            v-model="searchForm.type"
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-xs font-bold text-on-surface uppercase focus:ring-1 focus:ring-secondary focus:outline-none"
                        >
                            <option value="">ALL TYPES</option>
                            <option
                                v-for="t in availableTypes"
                                :key="t"
                                :value="t"
                            >
                                {{ t.replace('_', ' ') }}
                            </option>
                        </select>
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

            <!-- Points Transactions List Table -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[900px] border-collapse text-left"
                    >
                        <thead
                            class="border-b border-outline-variant bg-surface-container-low"
                        >
                            <tr
                                class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                            >
                                <th class="px-6 py-4">Transaction ID</th>
                                <th class="px-6 py-4">User Info</th>
                                <th class="px-6 py-4">Type</th>
                                <th class="px-6 py-4 text-right">
                                    Points Amount
                                </th>
                                <th class="px-6 py-4">Reference / Rate</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr
                                v-for="tx in transactions.data"
                                :key="tx.id"
                                class="group transition-colors hover:bg-surface-container-low/20"
                            >
                                <td
                                    class="px-6 py-4 font-bold text-on-surface-variant"
                                >
                                    #{{ tx.id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="tx.user" class="flex flex-col">
                                        <span class="font-bold text-primary">{{
                                            tx.user.name
                                        }}</span>
                                        <span
                                            class="mt-0.5 text-[10px] text-on-surface-variant"
                                            >{{ tx.user.email }}</span
                                        >
                                    </div>
                                    <span
                                        v-else
                                        class="text-on-surface-variant italic"
                                        >Unknown User</span
                                    >
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'rounded-full border px-2.5 py-1 text-[9px] font-black tracking-widest whitespace-nowrap uppercase',
                                            tx.type === 'deposit' &&
                                                'border-secondary/20 bg-secondary-container text-on-secondary-container',
                                            tx.type === 'bid_debit' &&
                                                'border-error/20 bg-error-container/30 text-error',
                                            tx.type === 'bonus_award' &&
                                                'border-blue-200 bg-blue-50 text-blue-800',
                                            tx.type === 'bonus_claim' &&
                                                'text-amber-850 border-amber-200 bg-amber-50',
                                        ]"
                                    >
                                        {{ tx.type.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-bold text-primary"
                                >
                                    {{ tx.type === 'bid_debit' ? '-' : '+'
                                    }}{{ tx.amount }} PTS
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-semibold text-on-surface select-all"
                                            >{{
                                                tx.provider_reference || 'N/A'
                                            }}</span
                                        >
                                        <span
                                            class="mt-0.5 text-[9px] text-on-surface-variant"
                                            >RATE: 1:{{
                                                tx.exchange_rate
                                            }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'rounded-full border px-2.5 py-1 text-[9px] font-black tracking-widest uppercase',
                                            tx.status === 'completed' &&
                                                'border-secondary/20 bg-secondary-container text-on-secondary-container',
                                            tx.status === 'pending' &&
                                                'text-amber-850 animate-pulse border-amber-200 bg-amber-50',
                                            tx.status === 'failed' &&
                                                'border-error/20 bg-error-container/30 text-error',
                                        ]"
                                    >
                                        {{ tx.status }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-[10px] font-semibold text-on-surface-variant"
                                >
                                    {{
                                        new Date(tx.created_at).toLocaleString()
                                    }}
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    No ledger transactions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links -->
                <div
                    v-if="transactions.last_page > 1"
                    class="flex items-center justify-between border-t border-outline-variant bg-surface-container-low px-6 py-4"
                >
                    <p
                        class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                    >
                        Page {{ transactions.current_page }} of
                        {{ transactions.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in transactions.links"
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
