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
                url: props.url || window.location.href
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
    <button @click="share"
        class="flex items-center text-gray-500 hover:text-forest transition-colors text-sm font-medium">
        <Share2 class="w-4 h-4 mr-1.5" />
        Share
    </button>
</template>
