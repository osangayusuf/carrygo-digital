<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
    ShieldAlert,
    Search,
    Lock,
    Unlock,
    Globe,
    Activity,
    AlertTriangle,
    CheckCircle,
    XCircle,
    UserMinus
} from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { index as activityLogIndex } from '@/routes/admin/activity-log';
import { toggleActive as usersToggleActive } from '@/routes/admin/users';

type User = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
};

type UserActivity = {
    id: number;
    user_id: number | null;
    type: string;
    subject_type: string | null;
    subject_id: number | null;
    metadata: any;
    ip_address: string | null;
    created_at: string;
    user: { id: number; name: string; email: string } | null;
};

type SharedIpAlert = {
    ip: string;
    users: User[];
    count: number;
};

type HighBidAlert = {
    user: User;
    minute: string;
    bid_count: number;
};

const props = defineProps<{
    activities: {
        data: UserActivity[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search: string | null;
        type: string | null;
        ip: string | null;
    };
    availableTypes: string[];
    alerts: {
        sharedIps: SharedIpAlert[];
        highBidRate: HighBidAlert[];
    };
}>();

const searchForm = useForm({
    search: props.filters.search || '',
    type: props.filters.type || '',
    ip: props.filters.ip || '',
});

const handleSearch = () => {
    searchForm.get(activityLogIndex.url(), {
        preserveState: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    searchForm.type = '';
    searchForm.ip = '';
    handleSearch();
};

const toggleUserActive = (user: User) => {
    const action = user.is_active ? 'disable' : 'enable';
    if (confirm(`Are you sure you want to ${action} this user's account?`)) {
        router.post(usersToggleActive.url(user.id), {}, {
            preserveScroll: true,
        });
    }
};

const formatMetadata = (metadata: any) => {
    if (!metadata) return 'N/A';
    let data = metadata;
    if (typeof metadata === 'string') {
        try {
            data = JSON.parse(metadata);
        } catch (e) {
            return metadata;
        }
    }
    if (typeof data !== 'object') return String(data);
    return Object.entries(data)
        .map(([k, v]) => `${k.replace(/_/g, ' ')}: ${typeof v === 'object' ? JSON.stringify(v) : v}`)
        .join(' | ');
};

const getBadgeStyle = (type: string) => {
    const lower = type.toLowerCase();
    if (lower.includes('failed') || lower.includes('alert')) {
        return 'bg-error-container/30 border-error/20 text-error';
    }
    if (lower.includes('success') || lower.includes('deposit') || lower.includes('claimed') || lower.includes('win')) {
        return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
    }
    if (lower.includes('bid')) {
        return 'bg-secondary-container text-on-secondary-container border-secondary/20';
    }
    return 'bg-surface-container-high text-primary border-outline-variant/50';
};
</script>

<template>
    <Head title="Admin - User Activity Audit & Spam Protection" />

    <AdminLayout :breadcrumbs="[{ title: 'Spam Protection & Activity Logs' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <ShieldAlert class="w-6 h-6 text-primary" />
                        <span>Security & Spam Protection Audit</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Audit real-time user activities, identify potential bot biddings, and handle multiple registration threats from shared IP addresses.</p>
                </div>
            </div>

            <!-- Alerts Panel (Visible only when threats are found) -->
            <div v-if="alerts.sharedIps.length > 0 || alerts.highBidRate.length > 0" class="flex flex-col gap-4">
                <h2 class="text-xs font-black uppercase text-error tracking-widest flex items-center gap-1">
                    <AlertTriangle class="w-4 h-4 text-error" />
                    <span>Suspicious Activity Security Alerts</span>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Shared IP Threat Card -->
                    <div v-if="alerts.sharedIps.length > 0" class="bg-surface-container-lowest border-2 border-error p-5 rounded-xl shadow-md space-y-4">
                        <div class="flex items-start gap-3 border-b border-outline-variant/30 pb-3">
                            <div class="p-2 bg-error-container/20 text-error rounded-lg">
                                <Globe class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-black text-primary text-sm uppercase">IP Sharing Alert</h3>
                                <p class="text-[10px] text-on-surface-variant mt-0.5">IP addresses with 3+ unique active/registered user accounts in 24 hours.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div v-for="alert in alerts.sharedIps" :key="alert.ip" class="border border-outline-variant p-3.5 rounded-lg bg-surface-container-low/40">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-bold text-primary text-xs font-mono select-all">IP: {{ alert.ip }}</span>
                                    <span class="bg-error text-on-error px-2 py-0.5 rounded-full font-black text-[9px] uppercase tracking-wider">{{ alert.count }} accounts</span>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="user in alert.users" :key="user.id" class="flex justify-between items-center bg-surface-container-lowest p-2 rounded border border-outline-variant/40">
                                        <div class="min-w-0 flex-1">
                                            <span class="font-bold text-primary block truncate">{{ user.name }}</span>
                                            <span class="text-[9px] text-on-surface-variant font-mono block truncate mt-0.5">{{ user.email }}</span>
                                        </div>
                                        <div class="pl-2">
                                            <button
                                                @click="toggleUserActive(user)"
                                                :class="[
                                                    'px-2 py-1 border text-[9px] font-black uppercase rounded transition-colors flex items-center gap-1',
                                                    user.is_active
                                                        ? 'border-error/20 text-error hover:bg-error-container/20'
                                                        : 'border-secondary/20 text-on-secondary-container hover:bg-secondary-container/40'
                                                ]"
                                            >
                                                <component :is="user.is_active ? Lock : Unlock" class="w-3 h-3" />
                                                <span>{{ user.is_active ? 'Disable' : 'Enable' }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- High Bid Frequency / Potential Bot Card -->
                    <div v-if="alerts.highBidRate.length > 0" class="bg-surface-container-lowest border-2 border-error p-5 rounded-xl shadow-md space-y-4">
                        <div class="flex items-start gap-3 border-b border-outline-variant/30 pb-3">
                            <div class="p-2 bg-error-container/20 text-error rounded-lg">
                                <Activity class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-black text-primary text-sm uppercase">Bidding Speed Limit Trigger</h3>
                                <p class="text-[10px] text-on-surface-variant mt-0.5">Users placing 20+ bids within a single minute window (potential bots).</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div v-for="alert in alerts.highBidRate" :key="alert.user.id + alert.minute" class="border border-outline-variant p-3.5 rounded-lg bg-surface-container-low/40 flex justify-between items-center">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-error text-on-error text-[8px] font-black uppercase px-1.5 py-0.5 rounded tracking-wider">{{ alert.bid_count }} BIDS/MIN</span>
                                        <span class="font-black text-primary truncate block">{{ alert.user.name }}</span>
                                    </div>
                                    <span class="text-[9px] text-on-surface-variant font-mono block mt-1 truncate pl-0 font-bold uppercase tracking-wider">Trigger: {{ alert.minute }}</span>
                                    <span class="text-[9px] text-on-surface-variant font-mono block truncate mt-0.5">{{ alert.user.email }}</span>
                                </div>
                                <div class="pl-3">
                                    <button
                                        @click="toggleUserActive(alert.user)"
                                        :class="[
                                            'px-3 py-1.5 border text-[9px] font-black uppercase rounded transition-colors flex items-center gap-1',
                                            alert.user.is_active
                                                ? 'border-error/20 text-error hover:bg-error-container/20'
                                                : 'border-secondary/20 text-on-secondary-container hover:bg-secondary-container/40'
                                        ]"
                                    >
                                        <component :is="alert.user.is_active ? Lock : Unlock" class="w-3 h-3" />
                                        <span>{{ alert.user.is_active ? 'Disable' : 'Enable' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Panel -->
            <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl shadow-sm">
                <form @submit.prevent="handleSearch" class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH BY USER NAME OR EMAIL..."
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold"
                        />
                        <Search class="w-4 h-4 text-on-surface-variant absolute left-3.5 top-3.5" />
                    </div>

                    <div class="w-full md:w-48">
                        <input
                            v-model="searchForm.ip"
                            type="text"
                            placeholder="FILTER BY IP ADDRESS..."
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold"
                        />
                    </div>

                    <div class="w-full md:w-56">
                        <select
                            v-model="searchForm.type"
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold uppercase"
                        >
                            <option value="">ALL ACTIVITY TYPES</option>
                            <option v-for="type in availableTypes" :key="type" :value="type">
                                {{ type.replace(/_/g, ' ') }}
                            </option>
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

            <!-- Activity Logs Table -->
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">
                                <th class="px-6 py-4">Log ID</th>
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">Action Type</th>
                                <th class="px-6 py-4 font-mono">IP Address</th>
                                <th class="px-6 py-4">Details / Context</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Moderate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="act in activities.data" :key="act.id" class="hover:bg-surface-container-low/20 transition-colors group">
                                <td class="px-6 py-4 font-bold text-on-surface-variant">#{{ act.id }}</td>
                                <td class="px-6 py-4">
                                    <div v-if="act.user" class="flex flex-col">
                                        <span class="font-bold text-primary">{{ act.user.name }}</span>
                                        <span class="text-[10px] text-on-surface-variant mt-0.5">{{ act.user.email }}</span>
                                    </div>
                                    <span v-else class="text-on-surface-variant italic">Guest Session</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 text-[8px] font-black uppercase tracking-widest border rounded-full whitespace-nowrap',
                                            getBadgeStyle(act.type)
                                        ]"
                                    >
                                        {{ act.type.replace(/_/g, ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-on-surface select-all">
                                    {{ act.ip_address || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-on-surface text-[10px] max-w-sm truncate select-all" :title="formatMetadata(act.metadata)">
                                    {{ formatMetadata(act.metadata) }}
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant font-semibold text-[10px]">
                                    {{ new Date(act.created_at).toLocaleString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="act.user" class="flex items-center">
                                        <button
                                            @click="toggleUserActive(act.user as any)"
                                            :class="[
                                                'px-2 py-1.5 border text-[9px] font-bold uppercase rounded-lg transition-colors flex items-center gap-1',
                                                act.user.is_active
                                                    ? 'border-error/20 text-error hover:bg-error-container/20'
                                                    : 'border-secondary/25 text-on-secondary-container hover:bg-secondary-container/45'
                                            ]"
                                        >
                                            <component :is="act.user.is_active ? Lock : Unlock" class="w-3.5 h-3.5" />
                                            <span>{{ act.user.is_active ? 'Disable' : 'Enable' }}</span>
                                        </button>
                                    </div>
                                    <span v-else class="text-on-surface-variant italic">-</span>
                                </td>
                            </tr>
                            <tr v-if="activities.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-on-surface-variant uppercase tracking-widest font-bold">
                                    No user activity logs found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links -->
                <div v-if="activities.last_page > 1" class="px-6 py-4 bg-surface-container-low flex justify-between items-center border-t border-outline-variant">
                    <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-widest">
                        Page {{ activities.current_page }} of {{ activities.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in activities.links"
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
