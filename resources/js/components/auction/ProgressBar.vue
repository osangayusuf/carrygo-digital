<script setup>
import { computed } from 'vue';

const props = defineProps({
    current: {
        type: Number,
        required: true
    },
    target: {
        type: Number,
        required: true
    }
});

const percentage = computed(() => {
    if (props.target <= 0) return 100;
    const p = (props.current / props.target) * 100;
    return Math.min(100, Math.max(0, p));
});
</script>

<template>
    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
        <div class="bg-forest h-2 rounded-full transition-all duration-500 ease-out"
            :style="{ width: percentage + '%' }"></div>
    </div>
</template>
