<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { getRemainingTime } from '@/lib/utils';
import type { Bid } from '@/pages/Home.vue';

const props = defineProps<{
    bid: Bid;
}>();

const emit = defineEmits<{
    (e: 'open-bid-modal', bid: Bid): void;
}>();

const isVisible = ref(false);

onMounted(() => {
    isVisible.value = true;
});

function dismiss(): void {
    isVisible.value = false;
}

function bidNow(): void {
    emit('open-bid-modal', props.bid);
    dismiss();
}
</script>

<template>
    <Transition name="popup">
        <div v-if="isVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog"
            aria-modal="true" aria-labelledby="event-popup-title" @click.self="dismiss">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="dismiss" />

            <!-- Modal card — horizontal two-column layout -->
            <div
                class="relative z-10 flex max-md:flex-col w-full max-w-2xl overflow-hidden rounded-3xl bg-surface-container-lowest shadow-2xl">
                <!-- Close icon (top-right) -->
                <button type="button"
                    class="absolute top-4 right-4 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-black/20 text-white/80 transition-colors hover:bg-black/40 hover:text-white"
                    aria-label="Close" @click="dismiss">
                    <span class="material-symbols-outlined text-xl leading-none">close</span>
                </button>

                <!-- Left — product image panel -->
                <div class="relative flex max-md:w-full w-2/5 shrink-0 items-center justify-center bg-surface-container p-8 pt-10">
                    <!-- Top accent bar -->
                    <div class="absolute inset-x-0 top-0 h-1.5 bg-linear-to-r from-forest via-lemon to-forest" />

                    <div
                        class="rounded-2xl border-4 border-lemon bg-surface-container-high p-4 shadow-lg shadow-lemon/20">
                        <img v-if="props.bid.image" :src="props.bid.image" :alt="props.bid.name"
                            class="h-56 w-full object-contain" />
                        <div v-else class="flex h-56 w-full items-center justify-center">
                            <span class="material-symbols-outlined text-6xl text-secondary">
                                stars
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right — text content -->
                <div class="flex md:min-h-[420px] flex-1 flex-col justify-center px-10 py-12">
                    <!-- Icon -->
                    <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-lemon/15">
                        <span class="material-symbols-outlined text-3xl text-forest" data-weight="fill">stars</span>
                    </div>

                    <!-- Heading -->
                    <h2 id="event-popup-title"
                        class="mb-3 font-headline text-4xl font-extrabold max-md:text-2xl tracking-tight text-forest drop-shadow-[0_2px_8px_rgba(250,204,21,0.4)]">
                        Special Event!
                    </h2>

                    <!-- Subtitle -->
                    <p class="mb-6 max-md:mb-3 text-sm max-md:text-xs font-medium leading-relaxed text-on-surface/70">
                        Don't miss out on
                        <span class="font-bold text-on-surface">{{ props.bid.name }}</span>
                    </p>

                    <!-- Divider -->
                    <div class="mb-6 h-px w-full bg-surface-container" />

                    <!-- Ends at -->
                    <p class="mb-4 max-md:mb-2 text-base max-md:text-sm font-bold text-forest">
                        Closes in: {{ getRemainingTime(props.bid.ends_at) }}
                    </p>

                    <button @click="bidNow"
                        class="mt-auto w-full rounded-xl bg-lemon py-3 text-center font-extrabold text-navy transition-colors hover:bg-amber cursor-pointer">
                        Bid Now
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.popup-enter-active {
    transition: opacity 0.3s ease;
}

.popup-leave-active {
    transition: opacity 0.2s ease;
}

.popup-enter-from,
.popup-leave-to {
    opacity: 0;
}

.popup-enter-active .relative.z-10 {
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.popup-enter-from .relative.z-10 {
    transform: scale(0.9) translateY(12px);
}

.popup-enter-to .relative.z-10 {
    transform: scale(1) translateY(0);
}
</style>
