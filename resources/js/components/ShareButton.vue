<script setup>
import { Share2 } from 'lucide-vue-next';

const props = defineProps({
    title: String,
    url: String,
});

const share = async () => {
    if (navigator.share) {
        try {
            await navigator.share({
                title: props.title,
                url: props.url || window.location.href,
            });
        } catch (err) {
            console.error('Error sharing:', err);
        }
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(props.url || window.location.href);
        alert('Link copied to clipboard!');
    }
};
</script>

<template>
    <button
        @click="share"
        class="flex items-center text-sm font-medium text-gray-500 transition-colors hover:text-forest"
    >
        <Share2 class="mr-1.5 h-4 w-4" />
        Share
    </button>
</template>
