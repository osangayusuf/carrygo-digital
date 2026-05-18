<script setup lang="ts">
withDefaults(
    defineProps<{
        id: string;
        label: string;
        name: string;
        type?: string;
        icon: string;
        placeholder?: string;
        defaultValue?: string | number | null;
        hint?: string;
        error?: string;
        required?: boolean;
        autocomplete?: string;
        inputmode?: string;
    }>(),
    {
        type: 'text',
        required: false,
    },
);
</script>

<template>
    <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold text-on-surface" :for="id">{{ label }}</label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="material-symbols-outlined text-on-surface-variant">{{ icon }}</span>
            </div>
            <input
                :id="id"
                :name="name"
                :type="type"
                :value="defaultValue ?? undefined"
                :placeholder="placeholder"
                :required="required"
                :autocomplete="autocomplete"
                :inputmode="inputmode"
                class="w-full rounded-lg border border-outline-variant bg-surface py-3 pr-4 pl-10 text-sm text-on-surface transition-all focus:border-secondary focus:ring-2 focus:ring-secondary focus:outline-none"
            />
        </div>
        <p v-if="hint && !error" class="text-xs text-on-surface-variant">{{ hint }}</p>
        <p v-if="error" class="text-xs font-medium text-error">{{ error }}</p>
    </div>
</template>
