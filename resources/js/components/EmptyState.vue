<script setup>
import { Inbox } from 'lucide-vue-next';

defineProps({
    message: {
        type: String,
        default: 'No items found.',
    },
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center"
    >
        <Inbox class="mb-4 h-12 w-12 text-gray-300" />
        <h3 class="text-lg font-medium text-gray-900">{{ message }}</h3>
        <p class="mt-1 text-sm text-gray-500">
            <slot name="description"></slot>
        </p>
        <div class="mt-6" v-if="$slots.action">
            <slot name="action"></slot>
        </div>
    </div>
</template>
