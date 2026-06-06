<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { 
    Gavel, 
    Users, 
    Coins, 
    CreditCard, 
    Star, 
    Settings, 
    LogOut,
    Menu,
    X,
    UserCheck,
    ListFilter
} from 'lucide-vue-next';
import { ref } from 'vue';
import { logout as logoutRoute, home } from '@/routes';
import { index as auctionsIndex } from '@/routes/admin/auctions';
import { index as usersIndex } from '@/routes/admin/users';
import { index as agentsIndex } from '@/routes/admin/agents';
import { index as bidsIndex } from '@/routes/admin/bids';
import { index as pointTransactionsIndex } from '@/routes/admin/point-transactions';
import { index as paystackTransactionsIndex } from '@/routes/admin/paystack-transactions';
import { index as reviewsIndex } from '@/routes/admin/reviews';
import { index as rewardsConfigIndex } from '@/routes/admin/rewards-config';
import { useCurrentUrl } from '@/composables/useCurrentUrl';

type Breadcrumb = {
    title: string;
    href?: string;
};

defineProps<{
    breadcrumbs?: Breadcrumb[];
}>();

const page = usePage();
const user = ref(page.props.auth?.user);

const isMobileMenuOpen = ref(false);

const { isCurrentOrParentUrl } = useCurrentUrl();

const navItems = [
    { name: 'Auctions', href: auctionsIndex.url(), icon: Gavel },
    { name: 'Users', href: usersIndex.url(), icon: Users },
    { name: 'Agents', href: agentsIndex.url(), icon: UserCheck },
    { name: 'Bids Audit', href: bidsIndex.url(), icon: ListFilter },
    { name: 'Points Ledger', href: pointTransactionsIndex.url(), icon: Coins },
    { name: 'Paystack Logs', href: paystackTransactionsIndex.url(), icon: CreditCard },
    { name: 'Reviews', href: reviewsIndex.url(), icon: Star },
    { name: 'Rewards Config', href: rewardsConfigIndex.url(), icon: Settings },
];

const logout = () => {
    router.post(logoutRoute.url());
};
</script>

<template>
    <div class="min-h-screen bg-background text-on-surface flex flex-col font-sans antialiased">
        <!-- Top bar for Mobile -->
        <header class="lg:hidden flex items-center justify-between px-4 py-3 bg-surface-container-low border-b border-outline-variant sticky top-0 z-40">
            <div class="flex items-center gap-2">
                <span class="text-primary font-black tracking-tight text-sm uppercase">CARRYGO ADMIN</span>
            </div>
            <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="text-on-surface-variant focus:outline-none p-1 border border-outline-variant rounded bg-surface-container-lowest">
                <Menu v-if="!isMobileMenuOpen" class="w-5 h-5" />
                <X v-else class="w-5 h-5" />
            </button>
        </header>

        <div class="flex flex-1 relative min-h-screen lg:min-h-0">
            <!-- Sidebar Panel (Desktop & Mobile Drawer) -->
            <aside 
                :class="[
                    'fixed inset-y-0 left-0 z-40 w-[280px] bg-surface-container-low border-r border-outline-variant flex flex-col transition-transform duration-300 transform lg:translate-x-0 lg:static lg:h-auto',
                    isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
                ]"
            >
                <!-- Brand logo Header -->
                <div class="hidden lg:flex flex-col p-6 border-b border-outline-variant">
                    <h1 class="text-xl font-black text-primary tracking-tight uppercase">CARRYGO ADMIN</h1>
                    <p class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest mt-1">Management Suite</p>
                </div>

                <!-- Navigation List -->
                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                    <Link 
                        v-for="item in navItems" 
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-4 py-3 text-xs font-bold rounded-lg transition-all',
                            isCurrentOrParentUrl(item.href) 
                                ? 'bg-secondary-container text-on-secondary-container shadow-sm border border-secondary/20' 
                                : 'text-on-surface-variant hover:bg-surface-variant/40'
                        ]"
                        @click="isMobileMenuOpen = false"
                    >
                        <component :is="item.icon" class="w-4 h-4" />
                        <span>{{ item.name }}</span>
                        <span v-if="item.name === 'Agents' && $page.props.auth?.pending_agents_count > 0"
                            class="ml-auto bg-error text-on-error text-[10px] px-2 py-0.5 rounded-full font-bold">
                            {{ $page.props.auth.pending_agents_count }}
                        </span>
                    </Link>
                </nav>

                <!-- User Footer & Logout -->
                <div class="p-4 border-t border-outline-variant bg-surface-container-low flex flex-col gap-2 mt-auto">
                    <div class="flex items-center gap-3 px-2 py-1 mb-2">
                        <div class="w-10 h-10 rounded-full bg-surface-container-highest flex items-center justify-center overflow-hidden border border-outline-variant">
                            <span class="text-primary font-black text-sm">{{ user?.name ? user.name[0].toUpperCase() : 'A' }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-on-surface truncate">{{ user?.name }}</p>
                            <p class="text-[10px] text-on-surface-variant truncate">{{ user?.email }}</p>
                        </div>
                    </div>
                    
                    <button 
                        @click="logout"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-error bg-transparent hover:bg-error-container/20 border border-error/20 rounded-lg transition-all"
                    >
                        <LogOut class="w-4 h-4" />
                        <span>Exit Admin</span>
                    </button>
                </div>
            </aside>

            <!-- Main Scroll Area -->
            <main class="flex-1 flex flex-col min-w-0 bg-background overflow-y-auto">
                <!-- Desktop Top Bar -->
                <header class="hidden lg:flex h-16 justify-between items-center px-8 bg-surface border-b border-outline-variant sticky top-0 z-40">
                    <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                        <Link :href="home.url()" class="hover:text-primary transition-colors">Home</Link>
                        <span class="text-on-surface-variant/30 text-[12px] font-normal">/</span>
                        <span class="hover:text-primary transition-colors">Admin</span>
                        <template v-for="crumb in breadcrumbs" :key="crumb.title">
                            <span class="text-on-surface-variant/30 text-[12px] font-normal">/</span>
                            <Link v-if="crumb.href" :href="crumb.href" class="hover:text-primary transition-colors font-bold">{{ crumb.title }}</Link>
                            <span v-else class="text-primary font-bold">{{ crumb.title }}</span>
                        </template>
                    </nav>
                </header>

                <div class="flex-1 p-6 lg:p-8 space-y-6">
                    <!-- Mobile Breadcrumbs only -->
                    <div class="lg:hidden flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-2">
                        <Link :href="home.url()" class="hover:text-primary transition-colors">Home</Link>
                        <span class="text-on-surface-variant/30 text-[12px] font-normal">/</span>
                        <span class="text-primary font-bold">Admin</span>
                        <template v-for="crumb in breadcrumbs" :key="crumb.title">
                            <span class="text-on-surface-variant/30 text-[12px] font-normal">/</span>
                            <span class="text-primary font-bold">{{ crumb.title }}</span>
                        </template>
                    </div>

                    <!-- Flash Notifications -->
                    <div v-if="$page.props.flash?.success" class="p-4 bg-secondary-container/40 border-l-4 border-secondary text-on-secondary-container text-xs font-bold rounded-lg flex items-center justify-between shadow-sm">
                        <span>{{ $page.props.flash.success }}</span>
                    </div>
                    <div v-if="$page.props.errors?.error" class="p-4 bg-error-container/30 border-l-4 border-error text-on-error-container text-xs font-bold rounded-lg flex items-center justify-between shadow-sm">
                        <span>{{ $page.props.errors.error }}</span>
                    </div>

                    <!-- Actual View Content slot -->
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
