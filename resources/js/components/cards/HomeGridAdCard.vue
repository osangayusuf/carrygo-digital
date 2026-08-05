<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        image?: string;
        url?: string;
        buttonText?: string;
        badgeText?: string;
    }>(),
    {
        title: 'Earn Free Points',
        description:
            'Complete daily tasks to boost your bidding power and win premium items!',
        image: '/images/trending-ad.png',
        url: '/tasks',
        buttonText: 'Task Center',
        badgeText: 'Sponsored',
    },
);

const page = usePage();

const imageUrl = computed(() => {
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
    <div
        class="group relative flex min-h-75 flex-col overflow-hidden rounded-lg border-2 border-sage-border bg-navy transition-all duration-300 hover:-translate-y-0.5 hover:border-lemon"
    >
        <!-- Background Image with zoom on hover -->
        <div
            class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105"
            :style="{ backgroundImage: `url(${imageUrl})` }"
        ></div>

        <!-- Rich Gradient Overlay -->
        <!-- <div
            class="absolute inset-0 bg-linear-to-r from-navy/70 via-navy/45 to-transparent transition-opacity duration-300 group-hover:from-navy/80 group-hover:via-navy/55"
        ></div> -->

        <!-- Glowing border effect on hover -->
        <div
            class="pointer-events-none absolute inset-0 rounded-lg border border-transparent transition-colors duration-300 group-hover:border-lemon/35"
        ></div>

        Floating Badge
        <!-- <div class="absolute top-3 left-3 z-10">
            <span
                class="rounded-full border border-lemon/20 bg-lemon px-2.5 py-0.5 text-[9px] font-black tracking-widest text-navy uppercase shadow-md select-none"
            >
                {{ badgeText }}
            </span>
        </div> -->

        <!-- Content Area -->
        <div
            class="relative z-10 flex flex-1 flex-col justify-end p-5 text-left md:p-6"
        >
            <div class="max-w-[90%] sm:max-w-[80%] md:max-w-[70%]">
                <!-- <h3
                    class="mb-2 font-condensed text-xl font-black tracking-wide text-white uppercase drop-shadow-sm transition-colors duration-200 group-hover:text-lemon md:text-2xl"
                >
                    {{ title }}
                </h3>
                <p
                    class="mb-4 line-clamp-3 text-xs leading-relaxed text-[#cde] opacity-90"
                >
                    {{ description }}
                </p>
                <div class="flex">
                    <Link
                        :href="url"
                        class="inline-flex items-center gap-2 rounded-lg bg-lemon px-5 py-2.5 text-center text-xs font-black text-navy shadow-lg transition-all duration-200 hover:bg-amber hover:shadow-lemon/10"
                    >
                        <span>{{ buttonText }}</span>
                        <i
                            class="pi pi-arrow-right text-[9px] transition-transform duration-200 group-hover:translate-x-0.5"
                        ></i>
                    </Link>
                </div> -->
            </div>
        </div>
    </div>
</template>
