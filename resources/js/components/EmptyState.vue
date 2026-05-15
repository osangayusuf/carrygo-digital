<script setup>
import { Inbox } from 'lucide-vue-next';

defineProps({
    message: {
        type: String,
        default: 'No items found.'
    }
});
</script>

<template>
    <div class="flex flex-col items-center justify-center p-12 text-center bg-white rounded-xl border border-dashed border-gray-300">
        <Inbox class="w-12 h-12 text-gray-300 mb-4" />
        <h3 class="text-lg font-medium text-gray-900">{{ message }}</h3>
        <p class="text-sm text-gray-500 mt-1"><slot name="description"></slot></p>
        <div class="mt-6" v-if="$slots.action">
            <slot name="action"></slot>
        </div>
    </div>
</template>
