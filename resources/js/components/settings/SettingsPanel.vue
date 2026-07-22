<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <div
        class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm md:p-8"
    >
        <header class="mb-8 border-b border-outline-variant pb-4">
            <h1
                class="text-2xl font-bold tracking-tight text-on-surface md:text-3xl"
            >
                {{ title }}
            </h1>
            <p class="mt-2 text-sm text-on-surface-variant md:text-base">
                {{ description }}
            </p>
        </header>

        <slot />
    </div>
</template>
