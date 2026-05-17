<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { tasks } from '@/routes';

const isVisible = ref(false);

onMounted(() => {
    isVisible.value = true;
});

function dismiss(): void {
    isVisible.value = false;
}
</script>

<template>
    <Transition name="popup">
        <div v-if="isVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog"
            aria-modal="true" aria-labelledby="task-popup-title" @click.self="dismiss">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="dismiss" />

            <!-- Modal card — horizontal two-column layout -->
            <div
                class="relative z-10 flex max-md:flex-col w-full max-w-2xl overflow-hidden rounded-3xl bg-surface-container-lowest shadow-2xl">
                <!-- Close icon (top-right) -->
                <button type="button"
                    class="absolute top-4 right-4 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-black/10 text-ink/60 transition-colors hover:bg-black/20 hover:text-ink max-md:bg-white/20 max-md:text-white/80 max-md:hover:bg-white/40 max-md:hover:text-white"
                    aria-label="Close" @click="dismiss">
                    <span class="material-symbols-outlined text-xl leading-none">close</span>
                </button>

                <!-- Right — text content -->
                <div class="flex md:min-h-[300px] flex-1 flex-col justify-center px-10 py-12">
                    <!-- Icon -->
                    <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-lemon/15">
                        <span class="material-symbols-outlined text-3xl text-forest" data-weight="fill">military_tech</span>
                    </div>

                    <!-- Heading -->
                    <h2 id="task-popup-title"
                        class="mb-3 font-headline text-4xl font-extrabold max-md:text-2xl tracking-tight text-forest drop-shadow-[0_2px_8px_rgba(250,204,21,0.4)]">
                        Earn Free Points!
                    </h2>

                    <!-- Subtitle -->
                    <p class="mb-5 max-md:mb-4 text-sm font-medium leading-relaxed text-ink/80">
                        Engage tasks to earn yourself free points through:
                    </p>

                    <!-- Features List -->
                    <ul class="mb-6 space-y-3.5">
                        <li class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-forest text-2xl">check_circle</span>
                            <span class="text-sm font-bold text-ink">Daily Check-ins points</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-forest text-2xl">casino</span>
                            <span class="text-sm font-bold text-ink">Spin and Win points</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-forest text-2xl">group_add</span>
                            <span class="text-sm font-bold text-ink">Referral points (Refer a friend)</span>
                        </li>
                    </ul>

                    <Link :href="tasks.url()"
                        class="mt-auto w-full rounded-xl bg-lemon py-3 text-center font-extrabold text-navy transition-colors hover:bg-amber cursor-pointer block">
                        Visit Task Center
                    </Link>
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
