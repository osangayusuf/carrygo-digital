<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppPaginator from '@/components/layout/AppPaginator.vue';
import TransactionTypeBadge from '@/components/wallet/TransactionTypeBadge.vue';
import auctions from '@/routes/auctions/index';
import { wallet as walletRoute } from '@/routes/index';
import type { WalletTransactionsPaginator } from '@/types/wallet';

const props = defineProps<{
    transactions: WalletTransactionsPaginator;
}>();

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function amountPrefix(direction: 'credit' | 'debit'): string {
    return direction === 'debit' ? '−' : '+';
}

function statusClass(status: string): string {
    return (
        {
            completed: 'bg-forest/15 text-forest',
            pending: 'bg-amber/20 text-ink',
            failed: 'bg-error-container text-error',
        }[status] ?? 'bg-surface-container text-on-surface-variant'
    );
}

function goToPage(page: number): void {
    router.get(
        walletRoute.url({ query: { page } }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <section class="mt-8">
        <h3 class="mb-4 text-lg font-semibold text-on-surface">
            Transaction history
        </h3>

        <div
            v-if="transactions.data.length === 0"
            class="rounded-xl border border-dashed border-outline-variant bg-surface-container-low p-10 text-center"
        >
            <span
                class="material-symbols-outlined mb-3 text-4xl text-on-surface-variant"
                >receipt_long</span
            >
            <p class="text-sm text-on-surface-variant">No transactions yet.</p>
        </div>

        <div
            v-else
            class="overflow-hidden rounded-xl border border-outline-variant"
        >
            <div class="hidden md:block">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-outline-variant bg-surface-container-low"
                    >
                        <tr>
                            <th
                                class="px-4 py-3 text-xs font-bold tracking-wide text-on-surface-variant uppercase"
                            >
                                Type
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-bold tracking-wide text-on-surface-variant uppercase"
                            >
                                Auction
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-bold tracking-wide text-on-surface-variant uppercase"
                            >
                                Amount
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-bold tracking-wide text-on-surface-variant uppercase"
                            >
                                Status
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-bold tracking-wide text-on-surface-variant uppercase"
                            >
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="tx in transactions.data"
                            :key="tx.id"
                            class="border-b border-outline-variant/60 last:border-0"
                        >
                            <td class="px-4 py-3">
                                <TransactionTypeBadge
                                    :type="tx.type"
                                    :label="tx.type_label"
                                />
                            </td>
                            <td class="px-4 py-3 text-on-surface-variant">
                                <Link
                                    v-if="tx.auction_id"
                                    :href="auctions.show.url(tx.auction_id)"
                                    class="font-medium text-forest hover:underline"
                                >
                                    #{{ tx.auction_id }}
                                </Link>
                                <span v-else class="text-on-surface-variant/50"
                                    >—</span
                                >
                            </td>
                            <td class="px-4 py-3 font-bold text-on-surface">
                                {{ amountPrefix(tx.direction)
                                }}{{ tx.amount.toLocaleString() }} pts
                                <span
                                    v-if="tx.naira_amount"
                                    class="mt-0.5 block text-xs font-normal text-on-surface-variant"
                                >
                                    ₦{{ tx.naira_amount.toLocaleString() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase"
                                    :class="statusClass(tx.status)"
                                >
                                    {{ tx.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-on-surface-variant">
                                {{ formatDate(tx.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-outline-variant md:hidden">
                <article
                    v-for="tx in transactions.data"
                    :key="tx.id"
                    class="bg-surface-container-lowest p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <TransactionTypeBadge
                            :type="tx.type"
                            :label="tx.type_label"
                        />
                        <span
                            class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase"
                            :class="statusClass(tx.status)"
                        >
                            {{ tx.status }}
                        </span>
                    </div>
                    <p
                        v-if="tx.auction_id"
                        class="mt-2 text-xs text-on-surface-variant"
                    >
                        Auction
                        <Link
                            :href="auctions.show.url(tx.auction_id)"
                            class="font-medium text-forest hover:underline"
                        >
                            #{{ tx.auction_id }}
                        </Link>
                    </p>
                    <p class="mt-2 font-bold text-on-surface">
                        {{ amountPrefix(tx.direction)
                        }}{{ tx.amount.toLocaleString() }} pts
                    </p>
                    <p
                        v-if="tx.naira_amount"
                        class="text-xs text-on-surface-variant"
                    >
                        ₦{{ tx.naira_amount.toLocaleString() }}
                    </p>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        {{ formatDate(tx.created_at) }}
                    </p>
                </article>
            </div>
        </div>

        <AppPaginator
            :current-page="transactions.current_page"
            :last-page="transactions.last_page"
            @page-change="goToPage"
        />
    </section>
</template>
