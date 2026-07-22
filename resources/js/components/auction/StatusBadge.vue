<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const config = computed(() => {
    switch (props.status) {
        case 'active':
            return { text: 'LIVE', class: 'bg-lemon text-forest' };
        case 'triggered':
            return {
                text: 'TRIGGERED',
                class: 'bg-[#F5E642] text-forest animate-pulse',
            };
        case 'closed':
            return { text: 'CLOSED', class: 'bg-gray-300 text-gray-700' };
        default:
            return { text: props.status, class: 'bg-gray-200 text-gray-800' };
    }
});
</script>

<template>
    <span
        :class="[
            'rounded-full px-2.5 py-0.5 text-[10px] font-bold tracking-wider uppercase',
            config.class,
        ]"
    >
        {{ config.text }}
    </span>
</template>
