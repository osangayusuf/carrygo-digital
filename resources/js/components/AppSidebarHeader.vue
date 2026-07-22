<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const triggeredAuctions = computed(
    () => (page.props.triggeredAuctions as any[]) ?? [],
);
const activeTriggeredAuction = computed(
    () => triggeredAuctions.value[0] ?? null,
);
const bannerTimeLeft = ref('');
const isExpired = ref(false);
let bannerInterval: ReturnType<typeof setInterval> | null = null;

function updateBannerTimer() {
    const auction = activeTriggeredAuction.value;

    if (!auction || !auction.expires_at) {
        bannerTimeLeft.value = '';
        isExpired.value = true;

        return;
    }

    const diff = Math.floor(
        (new Date(auction.expires_at).getTime() - Date.now()) / 1000,
    );

    if (diff <= 0) {
        bannerTimeLeft.value = '';
        isExpired.value = true;

        return;
    }

    isExpired.value = false;
    const mins = Math.floor(diff / 60);
    const secs = diff % 60;
    bannerTimeLeft.value = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

watch(
    activeTriggeredAuction,
    (newVal) => {
        if (newVal) {
            updateBannerTimer();

            if (!bannerInterval) {
                bannerInterval = setInterval(updateBannerTimer, 1000);
            }
        } else {
            if (bannerInterval) {
                clearInterval(bannerInterval);
                bannerInterval = null;
            }
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    if (bannerInterval) {
        clearInterval(bannerInterval);
    }
});
</script>

<template>
    <div class="flex w-full flex-col border-b border-sidebar-border/70">
        <!-- Persistent Countdown Banner -->
        <div
            v-if="activeTriggeredAuction && !isExpired"
            class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 bg-red-600 px-4 py-2 text-center text-xs font-bold text-white transition-all duration-300"
        >
            <div class="flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[15px]">alarm</span>
                <span>
                    Closing Soon:
                    <strong class="text-lemon">{{
                        activeTriggeredAuction.name
                    }}</strong>
                    is ending soon!
                </span>
            </div>
            <div class="flex items-center justify-center gap-3">
                <span
                    class="flex items-center gap-1 rounded border border-white/15 bg-black/35 px-2 py-0.5 font-mono text-[11px] font-black tracking-wide shadow-inner"
                >
                    <span class="material-symbols-outlined text-xs"
                        >schedule</span
                    >
                    <span>{{ bannerTimeLeft }}</span>
                </span>
                <Link
                    :href="`/auctions/${activeTriggeredAuction.id}`"
                    class="flex items-center gap-0.5 font-extrabold underline transition-colors hover:text-lemon"
                >
                    Bid Now <i class="pi pi-arrow-right text-[10px]"></i>
                </Link>
            </div>
        </div>

        <header
            class="flex h-16 shrink-0 items-center gap-2 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
        >
            <div class="flex items-center gap-2">
                <SidebarTrigger class="-ml-1" />
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
        </header>
    </div>
</template>
