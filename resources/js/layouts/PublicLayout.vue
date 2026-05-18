<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import AppNavbar from '@/components/AppNavbar.vue';
import PlaceBidModal from '@/components/modals/PlaceBidModal.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background font-body text-on-surface antialiased">
        <Toaster />
        <PlaceBidModal />
        <AppNavbar />
        <main class="flex-1">
            <slot />
        </main>
        <AppFooter />
    </div>
</template>
