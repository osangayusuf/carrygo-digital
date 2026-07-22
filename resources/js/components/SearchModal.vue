<script setup>
import { ref } from 'vue';

const isOpen = ref(false);

const open = () => (isOpen.value = true);
const close = () => (isOpen.value = false);

defineExpose({ open, close });
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-[100] flex items-start justify-center bg-gray-900/50 px-4 pt-16 backdrop-blur-sm sm:pt-24"
        @click.self="close"
    >
        <div
            class="animate-in fade-in zoom-in-95 w-full max-w-2xl overflow-hidden rounded-xl bg-white shadow-2xl duration-200"
        >
            <div class="flex items-center border-b border-gray-100 p-4">
                <input
                    type="text"
                    class="w-full border-0 px-2 py-1 text-lg outline-none focus:ring-0"
                    placeholder="Search auctions..."
                    autofocus
                />
                <button
                    @click="close"
                    class="rounded-md p-2 text-gray-400 hover:text-gray-600"
                >
                    Esc
                </button>
            </div>
            <div class="bg-gray-50 p-6 text-center text-sm text-gray-500">
                Start typing to search across all live and closed auctions.
            </div>
        </div>
    </div>
</template>
