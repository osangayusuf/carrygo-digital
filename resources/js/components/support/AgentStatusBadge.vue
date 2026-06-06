<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { ChevronDown, Circle } from 'lucide-vue-next';
import { echo } from '@laravel/echo-vue';

type StatusType = 'online' | 'away' | 'offline';

const page = usePage();
const isOpen = ref(false);

const user = computed(() => page.props.auth?.user as any);
const currentStatus = computed<StatusType>(() => user.value?.agent_status?.status || 'offline');

const statusOptions = [
    { value: 'online', label: 'Online', colorClass: 'bg-emerald-500 text-emerald-500', dotClass: 'bg-emerald-500' },
    { value: 'away', label: 'Away', colorClass: 'bg-amber-500 text-amber-500', dotClass: 'bg-amber-500' },
    { value: 'offline', label: 'Offline', colorClass: 'bg-rose-500 text-rose-500', dotClass: 'bg-rose-500' }
];

const currentOption = computed(() => {
    return statusOptions.find(o => o.value === currentStatus.value) || statusOptions[2];
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

// Close when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('#status-dropdown')) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    
    // Subscribe to support.agents channel to keep presence synchronized
    if (typeof window !== 'undefined') {
        echo()
            .join('support.agents')
            .here((users: any) => {
                // Presence list initialization (useful if we want to display it elsewhere)
            })
            .joining((user: any) => {
                // Someone joined
            })
            .leaving((user: any) => {
                // Someone left
            })
            .listen('.AgentStatusChanged', (e: any) => {
                // If it's the current user, update status
                if (e.userId === user.value?.id) {
                    router.reload({ only: ['auth'] });
                }
            });
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    if (typeof window !== 'undefined') {
        echo().leave('support.agents');
    }
});

const changeStatus = (status: StatusType) => {
    closeDropdown();
    if (status === currentStatus.value) return;

    router.post('/support/chat/status', {
        status: status
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // Updated successfully
        }
    });
};
</script>

<template>
    <div class="relative font-sans text-xs" id="status-dropdown">
        <!-- Toggle button -->
        <button 
            @click="toggleDropdown"
            class="w-full flex items-center justify-between gap-2.5 px-3 py-2 border border-outline-variant/60 bg-surface-container-lowest hover:bg-surface-container-lowest/80 text-on-surface hover:text-primary rounded-lg font-bold transition-all cursor-pointer shadow-sm"
        >
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span 
                        v-if="currentStatus === 'online'"
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                    ></span>
                    <span :class="['relative inline-flex rounded-full h-2 w-2', currentOption.dotClass]"></span>
                </span>
                <span>{{ currentOption.label }}</span>
            </div>
            <ChevronDown class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" />
        </button>

        <!-- Dropdown menu -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div 
                v-if="isOpen" 
                class="absolute bottom-full left-0 z-50 mb-1 w-full min-w-[120px] rounded-lg border border-outline-variant bg-surface-container-lowest shadow-lg py-1 divide-y divide-outline-variant/30"
            >
                <button
                    v-for="option in statusOptions"
                    :key="option.value"
                    @click="changeStatus(option.value as StatusType)"
                    class="w-full flex items-center gap-2.5 px-3 py-2.5 text-left hover:bg-surface-variant/40 font-bold transition-all cursor-pointer text-on-surface hover:text-primary"
                >
                    <span :class="['w-2 h-2 rounded-full', option.dotClass]"></span>
                    <span>{{ option.label }}</span>
                </button>
            </div>
        </transition>
    </div>
</template>
