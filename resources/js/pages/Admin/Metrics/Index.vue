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
    Award
} from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { index as metricsIndex } from '@/routes/admin/metrics';
import { computed } from 'vue';

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
        const label = date.toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase();
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
    if (props.metrics.dailyPurchases.length === 0) return 1;
    return Math.max(...props.metrics.dailyPurchases.map(d => d.points));
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <BarChart3 class="w-6 h-6 text-primary" />
                        <span>Platform Metrics & Analytics</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Audit platform point ledger sales, user signups, bid traffic, and auction outcomes.</p>
                </div>
            </div>

            <!-- Filters Panel -->
            <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl shadow-sm">
                <form @submit.prevent="handleSearch" class="flex flex-col gap-4">
                    <!-- Filter mode toggle -->
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="searchForm.filter_mode = 'month'"
                            :class="[
                                'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg border transition-all duration-150',
                                searchForm.filter_mode === 'month'
                                    ? 'bg-secondary text-primary border-secondary shadow-sm'
                                    : 'bg-surface-container-low text-on-surface-variant border-outline-variant hover:bg-surface-variant/40'
                            ]"
                        >
                            Month Selection
                        </button>
                        <button
                            type="button"
                            @click="searchForm.filter_mode = 'custom'"
                            :class="[
                                'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg border transition-all duration-150',
                                searchForm.filter_mode === 'custom'
                                    ? 'bg-secondary text-primary border-secondary shadow-sm'
                                    : 'bg-surface-container-low text-on-surface-variant border-outline-variant hover:bg-surface-variant/40'
                            ]"
                        >
                            Custom Date Range
                        </button>
                    </div>

                    <!-- Filter inputs -->
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <!-- Month Dropdown -->
                        <div v-if="searchForm.filter_mode === 'month'" class="flex-1">
                            <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Select Month</label>
                            <select
                                v-model="searchForm.month"
                                class="w-full bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold uppercase"
                            >
                                <option v-for="m in availableMonths" :key="m.value" :value="m.value">
                                    {{ m.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Custom Range Date Pickers -->
                        <template v-else>
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Start Date</label>
                                <div class="relative">
                                    <input
                                        v-model="searchForm.start_date"
                                        type="date"
                                        required
                                        class="w-full bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold"
                                    />
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">End Date</label>
                                <div class="relative">
                                    <input
                                        v-model="searchForm.end_date"
                                        type="date"
                                        required
                                        class="w-full bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold"
                                    />
                                </div>
                            </div>
                        </template>

                        <!-- Actions -->
                        <div class="flex gap-2 md:mt-5">
                            <button
                                type="submit"
                                class="flex-1 md:flex-none px-6 py-3 bg-primary text-white border-2 border-primary font-black uppercase tracking-wider rounded-lg hover:bg-primary/95 transition-all"
                            >
                                Apply Filter
                            </button>
                            <button
                                type="button"
                                @click="clearSearch"
                                class="px-6 py-3 border-2 border-outline-variant bg-surface-container-lowest text-on-surface-variant hover:text-primary font-black uppercase tracking-wider rounded-lg transition-all"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Dashboard Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Points Purchased KPI -->
                <div class="bg-navy text-white p-5 rounded-xl border-2 border-secondary shadow-md flex items-center justify-between group">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-secondary uppercase tracking-widest">Points Purchased</p>
                        <p class="text-2xl font-black text-white tracking-tight">{{ metrics.totalPointsBought.toLocaleString() }} PTS</p>
                        <p class="text-[10px] text-secondary/80 font-bold">{{ formattedNaira(metrics.totalNairaSpent) }} Revenue</p>
                    </div>
                    <div class="p-3.5 rounded-lg bg-secondary text-navy group-hover:scale-105 transition-transform duration-200">
                        <Coins class="w-6 h-6" />
                    </div>
                </div>

                <!-- Bids & Spent KPI -->
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center justify-between group hover:border-primary/50 transition-all duration-200">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-on-surface-variant uppercase tracking-widest">Bidding Activity</p>
                        <p class="text-2xl font-black text-primary tracking-tight">{{ metrics.totalBidsPlaced.toLocaleString() }} Bids</p>
                        <p class="text-[10px] text-on-surface-variant font-bold">{{ metrics.totalPointsSpent.toLocaleString() }} PTS Debited</p>
                    </div>
                    <div class="p-3.5 rounded-lg bg-surface-container-low text-primary group-hover:scale-105 transition-transform duration-200">
                        <TrendingUp class="w-6 h-6" />
                    </div>
                </div>

                <!-- User Registrations KPI -->
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center justify-between group hover:border-primary/50 transition-all duration-200">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-on-surface-variant uppercase tracking-widest">User Registrations</p>
                        <p class="text-2xl font-black text-primary tracking-tight">+{{ metrics.newUsersCount }} Users</p>
                        <p class="text-[10px] text-on-surface-variant font-bold">New Registrations</p>
                    </div>
                    <div class="p-3.5 rounded-lg bg-surface-container-low text-primary group-hover:scale-105 transition-transform duration-200">
                        <Users class="w-6 h-6" />
                    </div>
                </div>

                <!-- Auction Activity KPI -->
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center justify-between group hover:border-primary/50 transition-all duration-200">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-on-surface-variant uppercase tracking-widest">Auctions Settled</p>
                        <p class="text-2xl font-black text-primary tracking-tight">{{ metrics.completedAuctionsCount }} Closed</p>
                        <p class="text-[10px] text-on-surface-variant font-bold">{{ metrics.avgBidsPerAuction }} Avg Bids/Auction</p>
                    </div>
                    <div class="p-3.5 rounded-lg bg-surface-container-low text-primary group-hover:scale-105 transition-transform duration-200">
                        <Gavel class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <!-- Lower Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Daily Purchases Visual Plot -->
                <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm lg:col-span-2 space-y-5">
                    <div class="flex items-center justify-between border-b border-outline-variant/30 pb-3">
                        <h2 class="text-sm font-black uppercase tracking-wider text-primary flex items-center gap-1.5">
                            <Calendar class="w-4 h-4 text-primary" />
                            <span>Daily Points Purchased Sales</span>
                        </h2>
                        <span class="text-[10px] text-on-surface-variant uppercase tracking-wider font-bold bg-surface-container-low px-2.5 py-1 rounded">Volume Chart</span>
                    </div>

                    <div v-if="metrics.dailyPurchases.length === 0" class="py-12 text-center text-on-surface-variant font-bold uppercase tracking-wider">
                        No purchase transactions recorded in this period.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="d in metrics.dailyPurchases" :key="d.date" class="flex items-center gap-3">
                            <!-- Date Label -->
                            <div class="w-14 font-black text-on-surface-variant text-right uppercase tracking-wider">{{ d.date }}</div>
                            <!-- Bar Container -->
                            <div class="flex-1 bg-surface-container-low h-6 rounded overflow-hidden relative border border-outline-variant/50">
                                <div
                                    class="bg-linear-to-r from-navy to-secondary h-full transition-all duration-500"
                                    :style="{ width: `${Math.max(5, (d.points / maxDailyPoints) * 100)}%` }"
                                ></div>
                                <span class="absolute inset-y-0 right-2 flex items-center text-[9px] font-black text-primary uppercase tracking-widest">
                                    {{ formattedNaira(d.naira) }}
                                </span>
                            </div>
                            <!-- Points Amount -->
                            <div class="w-20 font-black text-primary text-right whitespace-nowrap">{{ d.points.toLocaleString() }} PTS</div>
                        </div>
                    </div>
                </div>

                <!-- Top Purchasers Leaderboard -->
                <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-outline-variant/30 pb-3">
                        <h2 class="text-sm font-black uppercase tracking-wider text-primary flex items-center gap-1.5">
                            <Award class="w-4 h-4 text-secondary" />
                            <span>Top Points Purchasers</span>
                        </h2>
                    </div>

                    <div v-if="metrics.topPurchasers.length === 0" class="py-12 text-center text-on-surface-variant font-bold uppercase tracking-wider">
                        No purchase leaders found.
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="(tp, idx) in metrics.topPurchasers" :key="tp.user_email" class="flex items-center justify-between border-2 border-sage-border hover:border-secondary transition-all p-3 rounded-lg bg-surface-container-lowest">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-navy text-secondary font-black flex items-center justify-center text-[10px]">#{{ idx + 1 }}</span>
                                    <span class="font-black text-primary truncate block">{{ tp.user_name }}</span>
                                </div>
                                <span class="text-[9px] text-on-surface-variant block mt-1 truncate pl-7 font-mono">{{ tp.user_email }}</span>
                            </div>
                            <div class="text-right pl-3">
                                <span class="font-black text-primary block whitespace-nowrap">{{ tp.points.toLocaleString() }} PTS</span>
                                <span class="text-[9px] font-bold text-secondary-fixed block mt-0.5 bg-secondary-container text-on-secondary-container px-1.5 py-0.5 rounded uppercase tracking-wider font-sans whitespace-nowrap">
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
