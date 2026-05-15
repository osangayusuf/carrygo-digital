<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    expiresAt: {
        type: String,
        required: true
    }
});

const timeLeft = ref('');
let timer = null;

const calculateTimeLeft = () => {
    const end = new Date(props.expiresAt).getTime();
    const now = new Date().getTime();
    const distance = end - now;

    if (distance <= 0) {
        timeLeft.value = '00:00:00';
        clearInterval(timer);
        return;
    }

    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    timeLeft.value = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

onMounted(() => {
    calculateTimeLeft();
    timer = setInterval(calculateTimeLeft, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div
        class="bg-[#F5E642] text-forest px-2 py-1 rounded-md text-xs font-bold flex items-center justify-center shadow-sm whitespace-nowrap">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 animate-pulse" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ timeLeft }}
    </div>
</template>
