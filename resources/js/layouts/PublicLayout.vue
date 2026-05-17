<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import AppNavbar from '@/components/AppNavbar.vue';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background font-body text-on-surface antialiased">
        <AppNavbar />
        <main class="flex-1">
            <slot />
        </main>
        <AppFooter />
    </div>
</template>
