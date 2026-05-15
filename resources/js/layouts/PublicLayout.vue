<script setup>
import { Head } from '@inertiajs/vue3';
import AppNavbar from '@/components/AppNavbar.vue';
import AppFooter from '@/components/AppFooter.vue';
import LiveTicker from '@/components/LiveTicker.vue';

defineProps({
    title: String,
});
</script>

<template>

    <Head :title="title" />
    <div class="min-h-screen bg-[#F5F5F5] flex flex-col font-sans">
        <LiveTicker />
        <AppNavbar />

        <main class="grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <AppFooter />
    </div>
</template>
