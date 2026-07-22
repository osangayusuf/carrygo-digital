<script setup>
import { computed } from 'vue';

const props = defineProps({
    points: {
        type: Number,
        required: true,
    },
});

const formatted = computed(() => {
    return props.points.toLocaleString() + ' pts';
});
</script>

<template>
    <span class="font-bold whitespace-nowrap">{{ formatted }}</span>
</template>
