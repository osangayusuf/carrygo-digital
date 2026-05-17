<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title: string;
    viewMoreRoute?: string;
    viewMoreText?: string;
}>();
</script>

<template>
    <section class="mx-auto max-w-screen-2xl px-8 py-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-headline text-2xl md:text-4xl font-extrabold tracking-tighter py-2">
                {{ title }}
            </h2>
            <Link v-if="viewMoreRoute" :href="viewMoreRoute"
                class="group flex items-center gap-1 text-sm font-bold text-primary hover:text-primary/80 transition-colors">
                {{ viewMoreText ?? 'View More' }}
                <span
                    class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">chevron_right</span>
            </Link>
        </div>

        <!-- Mobile: 2-column grid, Desktop: up to 5-column grid -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-5 md:gap-6">
            <slot />
        </div>
    </section>
</template>
