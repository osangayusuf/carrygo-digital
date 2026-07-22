<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    BarChart3,
    Coins,
    TrendingUp,
    Users,
    Gavel,
    Calendar,
    ArrowUpRight,
    Award,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { index as metricsIndex } from '@/routes/admin/metrics';

type PurchaseBreakdown = {
    date: string;
    points: number;
    naira: number;
};

type TopPurchaser = {
    user_name: string;
    user_email: string;
    points: number;
    naira: number;
};

type MetricsData = {
    totalPointsBought: number;
    totalNairaSpent: number;
    totalBidsPlaced: number;
    totalPointsSpent: number;
    activeAuctionsCount: number;
    completedAuctionsCount: number;
    avgBidsPerAuction: number;
    newUsersCount: number;
    dailyPurchases: PurchaseBreakdown[];
    topPurchasers: TopPurchaser[];
};

const props = defineProps<{
    metrics: MetricsData;
    filters: {
        month: string | null;
        start_date: string | null;
        end_date: string | null;
        filter_mode: 'month' | 'custom';
    };
}>();

const searchForm = useForm({
    month: props.filters.month || new Date().toISOString().substring(0, 7),
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    filter_mode: props.filters.filter_mode || 'month',
});

// Generate past 12 months for dropdown selection
const availableMonths = computed(() => {
    const list = [];
    const date = new Date();

    for (let i = 0; i < 12; i++) {
        const yyyymm = date.toISOString().substring(0, 7);
        const label = date
            .toLocaleString('default', { month: 'long', year: 'numeric' })
            .toUpperCase();
        list.push({ value: yyyymm, label });
        date.setMonth(date.getMonth() - 1);
    }

    return list;
});

const handleSearch = () => {
    if (searchForm.filter_mode === 'custom') {
        searchForm.month = '';
    } else {
        searchForm.start_date = '';
        searchForm.end_date = '';
    }

    searchForm.get(metricsIndex.url(), {
        preserveState: true,
    });
};

const clearSearch = () => {
    const currentMonth = new Date().toISOString().substring(0, 7);
    searchForm.month = currentMonth;
    searchForm.start_date = '';
    searchForm.end_date = '';
    searchForm.filter_mode = 'month';
    handleSearch();
};

const maxDailyPoints = computed(() => {
    if (props.metrics.dailyPurchases.length === 0) {
        return 1;
    }

    return Math.max(...props.metrics.dailyPurchases.map((d) => d.points));
});

const formattedNaira = (amount: number) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="Admin - Platform Metrics" />

    <AdminLayout :breadcrumbs="[{ title: 'Metrics & Analytics' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header section -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <BarChart3 class="h-6 w-6 text-primary" />
                        <span>Platform Metrics & Analytics</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Audit platform point ledger sales, user signups, bid
                        traffic, and auction outcomes.
                    </p>
                </div>
            </div>

            <!-- Filters Panel -->
            <div
                class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm"
            >
                <form
                    @submit.prevent="handleSearch"
                    class="flex flex-col gap-4"
                >
                    <!-- Filter mode toggle -->
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="searchForm.filter_mode = 'month'"
                            :class="[
                                'rounded-lg border px-4 py-2 text-[10px] font-black tracking-wider uppercase transition-all duration-150',
                                searchForm.filter_mode === 'month'
                                    ? 'border-secondary bg-secondary text-primary shadow-sm'
                                    : 'border-outline-variant bg-surface-container-low text-on-surface-variant hover:bg-surface-variant/40',
                            ]"
                        >
                            Month Selection
                        </button>
                        <button
                            type="button"
                            @click="searchForm.filter_mode = 'custom'"
                            :class="[
                                'rounded-lg border px-4 py-2 text-[10px] font-black tracking-wider uppercase transition-all duration-150',
                                searchForm.filter_mode === 'custom'
                                    ? 'border-secondary bg-secondary text-primary shadow-sm'
                                    : 'border-outline-variant bg-surface-container-low text-on-surface-variant hover:bg-surface-variant/40',
                            ]"
                        >
                            Custom Date Range
                        </button>
                    </div>

                    <!-- Filter inputs -->
                    <div
                        class="flex flex-col gap-3 md:flex-row md:items-center"
                    >
                        <!-- Month Dropdown -->
                        <div
                            v-if="searchForm.filter_mode === 'month'"
                            class="flex-1"
                        >
                            <label
                                class="mb-1.5 block text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                                >Select Month</label
                            >
                            <select
                                v-model="searchForm.month"
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-xs font-bold text-on-surface uppercase focus:ring-1 focus:ring-secondary focus:outline-none"
                            >
                                <option
                                    v-for="m in availableMonths"
                                    :key="m.value"
                                    :value="m.value"
                                >
                                    {{ m.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Custom Range Date Pickers -->
                        <template v-else>
                            <div class="flex-1">
                                <label
                                    class="mb-1.5 block text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                                    >Start Date</label
                                >
                                <div class="relative">
                                    <input
                                        v-model="searchForm.start_date"
                                        type="date"
                                        required
                                        class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-xs font-bold text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                                    />
                                </div>
                            </div>
                            <div class="flex-1">
                                <label
                                    class="mb-1.5 block text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                                    >End Date</label
                                >
                                <div class="relative">
                                    <input
                                        v-model="searchForm.end_date"
                                        type="date"
                                        required
                                        class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-xs font-bold text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                                    />
                                </div>
                            </div>
                        </template>

                        <!-- Actions -->
                        <div class="flex gap-2 md:mt-5">
                            <button
                                type="submit"
                                class="flex-1 rounded-lg border-2 border-primary bg-primary px-6 py-3 font-black tracking-wider text-white uppercase transition-all hover:bg-primary/95 md:flex-none"
                            >
                                Apply Filter
                            </button>
                            <button
                                type="button"
                                @click="clearSearch"
                                class="rounded-lg border-2 border-outline-variant bg-surface-container-lowest px-6 py-3 font-black tracking-wider text-on-surface-variant uppercase transition-all hover:text-primary"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Dashboard Stats Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Points Purchased KPI -->
                <div
                    class="group flex items-center justify-between rounded-xl border-2 border-secondary bg-navy p-5 text-white shadow-md"
                >
                    <div class="space-y-1">
                        <p
                            class="text-[9px] font-black tracking-widest text-secondary uppercase"
                        >
                            Points Purchased
                        </p>
                        <p
                            class="text-2xl font-black tracking-tight text-white"
                        >
                            {{ metrics.totalPointsBought.toLocaleString() }} PTS
                        </p>
                        <p class="text-[10px] font-bold text-secondary/80">
                            {{ formattedNaira(metrics.totalNairaSpent) }}
                            Revenue
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-secondary p-3.5 text-navy transition-transform duration-200 group-hover:scale-105"
                    >
                        <Coins class="h-6 w-6" />
                    </div>
                </div>

                <!-- Bids & Spent KPI -->
                <div
                    class="group flex items-center justify-between rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all duration-200 hover:border-primary/50"
                >
                    <div class="space-y-1">
                        <p
                            class="text-[9px] font-black tracking-widest text-on-surface-variant uppercase"
                        >
                            Bidding Activity
                        </p>
                        <p
                            class="text-2xl font-black tracking-tight text-primary"
                        >
                            {{ metrics.totalBidsPlaced.toLocaleString() }} Bids
                        </p>
                        <p
                            class="text-[10px] font-bold text-on-surface-variant"
                        >
                            {{ metrics.totalPointsSpent.toLocaleString() }} PTS
                            Debited
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-surface-container-low p-3.5 text-primary transition-transform duration-200 group-hover:scale-105"
                    >
                        <TrendingUp class="h-6 w-6" />
                    </div>
                </div>

                <!-- User Registrations KPI -->
                <div
                    class="group flex items-center justify-between rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all duration-200 hover:border-primary/50"
                >
                    <div class="space-y-1">
                        <p
                            class="text-[9px] font-black tracking-widest text-on-surface-variant uppercase"
                        >
                            User Registrations
                        </p>
                        <p
                            class="text-2xl font-black tracking-tight text-primary"
                        >
                            +{{ metrics.newUsersCount }} Users
                        </p>
                        <p
                            class="text-[10px] font-bold text-on-surface-variant"
                        >
                            New Registrations
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-surface-container-low p-3.5 text-primary transition-transform duration-200 group-hover:scale-105"
                    >
                        <Users class="h-6 w-6" />
                    </div>
                </div>

                <!-- Auction Activity KPI -->
                <div
                    class="group flex items-center justify-between rounded-xl border border-outline-variant bg-surface-container-lowest p-5 shadow-sm transition-all duration-200 hover:border-primary/50"
                >
                    <div class="space-y-1">
                        <p
                            class="text-[9px] font-black tracking-widest text-on-surface-variant uppercase"
                        >
                            Auctions Settled
                        </p>
                        <p
                            class="text-2xl font-black tracking-tight text-primary"
                        >
                            {{ metrics.completedAuctionsCount }} Closed
                        </p>
                        <p
                            class="text-[10px] font-bold text-on-surface-variant"
                        >
                            {{ metrics.avgBidsPerAuction }} Avg Bids/Auction
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-surface-container-low p-3.5 text-primary transition-transform duration-200 group-hover:scale-105"
                    >
                        <Gavel class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Lower Layout Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Daily Purchases Visual Plot -->
                <div
                    class="space-y-5 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm lg:col-span-2"
                >
                    <div
                        class="flex items-center justify-between border-b border-outline-variant/30 pb-3"
                    >
                        <h2
                            class="flex items-center gap-1.5 text-sm font-black tracking-wider text-primary uppercase"
                        >
                            <Calendar class="h-4 w-4 text-primary" />
                            <span>Daily Points Purchased Sales</span>
                        </h2>
                        <span
                            class="rounded bg-surface-container-low px-2.5 py-1 text-[10px] font-bold tracking-wider text-on-surface-variant uppercase"
                            >Volume Chart</span
                        >
                    </div>

                    <div
                        v-if="metrics.dailyPurchases.length === 0"
                        class="py-12 text-center font-bold tracking-wider text-on-surface-variant uppercase"
                    >
                        No purchase transactions recorded in this period.
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="d in metrics.dailyPurchases"
                            :key="d.date"
                            class="flex items-center gap-3"
                        >
                            <!-- Date Label -->
                            <div
                                class="w-14 text-right font-black tracking-wider text-on-surface-variant uppercase"
                            >
                                {{ d.date }}
                            </div>
                            <!-- Bar Container -->
                            <div
                                class="relative h-6 flex-1 overflow-hidden rounded border border-outline-variant/50 bg-surface-container-low"
                            >
                                <div
                                    class="h-full bg-linear-to-r from-navy to-secondary transition-all duration-500"
                                    :style="{
                                        width: `${Math.max(5, (d.points / maxDailyPoints) * 100)}%`,
                                    }"
                                ></div>
                                <span
                                    class="absolute inset-y-0 right-2 flex items-center text-[9px] font-black tracking-widest text-primary uppercase"
                                >
                                    {{ formattedNaira(d.naira) }}
                                </span>
                            </div>
                            <!-- Points Amount -->
                            <div
                                class="w-20 text-right font-black whitespace-nowrap text-primary"
                            >
                                {{ d.points.toLocaleString() }} PTS
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Purchasers Leaderboard -->
                <div
                    class="space-y-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-outline-variant/30 pb-3"
                    >
                        <h2
                            class="flex items-center gap-1.5 text-sm font-black tracking-wider text-primary uppercase"
                        >
                            <Award class="h-4 w-4 text-secondary" />
                            <span>Top Points Purchasers</span>
                        </h2>
                    </div>

                    <div
                        v-if="metrics.topPurchasers.length === 0"
                        class="py-12 text-center font-bold tracking-wider text-on-surface-variant uppercase"
                    >
                        No purchase leaders found.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="(tp, idx) in metrics.topPurchasers"
                            :key="tp.user_email"
                            class="flex items-center justify-between rounded-lg border-2 border-sage-border bg-surface-container-lowest p-3 transition-all hover:border-secondary"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex h-5 w-5 items-center justify-center rounded-full bg-navy text-[10px] font-black text-secondary"
                                        >#{{ idx + 1 }}</span
                                    >
                                    <span
                                        class="block truncate font-black text-primary"
                                        >{{ tp.user_name }}</span
                                    >
                                </div>
                                <span
                                    class="mt-1 block truncate pl-7 font-mono text-[9px] text-on-surface-variant"
                                    >{{ tp.user_email }}</span
                                >
                            </div>
                            <div class="pl-3 text-right">
                                <span
                                    class="block font-black whitespace-nowrap text-primary"
                                    >{{ tp.points.toLocaleString() }} PTS</span
                                >
                                <span
                                    class="mt-0.5 block rounded bg-secondary-container px-1.5 py-0.5 font-sans text-[9px] font-bold tracking-wider whitespace-nowrap text-on-secondary-container text-secondary-fixed uppercase"
                                >
                                    {{ formattedNaira(tp.naira) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
