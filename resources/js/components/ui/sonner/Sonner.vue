<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Toaster as SonnerPrimitive } from 'vue-sonner';
import 'vue-sonner/style.css';

const { appearance } = useAppearance();
</script>

<template>
    <SonnerPrimitive
        :theme="appearance"
        class="carrygo-toaster group"
        position="bottom-right"
        :gap="12"
        :visible-toasts="4"
        :toast-options="{
            classNames: {
                toast: 'carrygo-toast',
                title: 'carrygo-toast__title',
                description: 'carrygo-toast__description',
                actionButton: 'carrygo-toast__action',
                cancelButton: 'carrygo-toast__cancel',
                closeButton: 'carrygo-toast__close',
                icon: 'carrygo-toast__icon',
            },
        }"
    />
</template>
