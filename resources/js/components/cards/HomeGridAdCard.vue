<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    image?: string;
    url?: string;
    alt?: string;
}

const props = withDefaults(defineProps<Props>(), {
    image: '/images/trending-ad.png',
    url: '/tasks',
    alt: 'Earn Free Points',
});

const page = usePage();

const isExternal = computed(() => {
    if (!props.url) {
        return false;
    }

    return (
        props.url.startsWith('http://') ||
        props.url.startsWith('https://') ||
        props.url.startsWith('//')
    );
});

const imageUrl = computed(() => {
    if (!props.image) {
        return '';
    }

    const assetUrl = (page.props.asset_url as string) || '';

    if (
        props.image.startsWith('http') ||
        props.image.startsWith('//') ||
        props.image.startsWith('data:')
    ) {
        return props.image;
    }

    const prefix = assetUrl.endsWith('/') ? assetUrl.slice(0, -1) : assetUrl;
    const path = props.image.startsWith('/') ? props.image : `/${props.image}`;

    return `${prefix}${path}`;
});
</script>

<template>
    <component
        :is="!url ? 'div' : isExternal ? 'a' : Link"
        :href="url || undefined"
        :target="isExternal ? '_blank' : undefined"
        :rel="isExternal ? 'noopener noreferrer' : undefined"
        :aria-label="alt"
        class="group relative flex min-h-75 flex-col overflow-hidden rounded-lg border-2 border-sage-border bg-navy transition-all duration-300 hover:-translate-y-0.5 hover:border-lemon"
    >
        <img
            :src="imageUrl"
            :alt="alt"
            class="h-full w-auto aspect-1492/1054 object-contain transition-transform duration-700 group-hover:scale-105"
            loading="lazy"
        />
    </component>
</template>
