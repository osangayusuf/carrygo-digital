<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
    id: string;
    label: string;
    name: string;
    placeholder?: string;
    error?: string;
    autocomplete?: string;
}>();

const showPassword = ref(false);
</script>

<template>
    <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold text-on-surface" :for="id">{{ label }}</label>
        <div class="relative">
            <input
                :id="id"
                :name="name"
                :type="showPassword ? 'text' : 'password'"
                :placeholder="placeholder"
                :autocomplete="autocomplete"
                class="w-full rounded-lg border border-outline-variant bg-surface px-4 py-3 pr-12 text-sm text-on-surface transition-all focus:border-secondary focus:ring-2 focus:ring-secondary focus:outline-none"
            />
            <button
                type="button"
                class="absolute inset-y-0 right-0 flex cursor-pointer items-center pr-3 text-on-surface-variant transition-colors hover:text-on-surface"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                @click="showPassword = !showPassword"
            >
                <span class="material-symbols-outlined">
                    {{ showPassword ? 'visibility_off' : 'visibility' }}
                </span>
            </button>
        </div>
        <p v-if="error" class="text-xs font-medium text-error">{{ error }}</p>
    </div>
</template>
