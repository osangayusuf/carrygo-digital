<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import AppFooter from '@/components/AppFooter.vue';
import AppNavbar from '@/components/AppNavbar.vue';
import MobileBottomNav from '@/components/layout/MobileBottomNav.vue';
import PlaceBidModal from '@/components/modals/PlaceBidModal.vue';
import ChatWidget from '@/components/support/ChatWidget.vue';
import { Toaster } from '@/components/ui/sonner';

const showScrollTop = ref(false);

function handleScroll(): void {
    showScrollTop.value =
        (window.scrollY || document.documentElement.scrollTop) > 300;
}

function scrollToTop(): void {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div
        class="h-100vh flex min-h-screen flex-col bg-background pb-14 font-body text-on-surface antialiased md:pb-0"
    >
        <Toaster />
        <PlaceBidModal />
        <AppNavbar />
        <main class="flex-1">
            <slot />
        </main>
        <AppFooter />
        <ChatWidget />
        <MobileBottomNav />

        <!-- Scroll-to-Top Button -->
        <button
            type="button"
            :class="[
                'fixed left-4 z-50 flex h-9 w-9 items-center justify-center rounded-xl bg-lemon text-navy shadow-lg transition-all duration-300 hover:bg-amber md:right-8 md:left-auto',
                showScrollTop
                    ? 'bottom-20 opacity-100 md:bottom-30'
                    : 'pointer-events-none bottom-16 opacity-0 md:bottom-4',
            ]"
            @click="scrollToTop"
            aria-label="Scroll to top"
        >
            <span class="material-symbols-outlined text-lg font-bold"
                >arrow_upward</span
            >
        </button>
    </div>
</template>
