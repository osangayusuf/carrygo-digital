<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Toaster as SonnerPrimitive } from 'vue-sonner';
import 'vue-sonner/style.css';

const { appearance } = useAppearance();
</script>

<template>
    <SonnerPrimitive :theme="appearance" class="bidora-toaster group" position="bottom-right" :gap="12"
        :visible-toasts="4" :toast-options="{
            classNames: {
                toast: 'bidora-toast',
                title: 'bidora-toast__title',
                description: 'bidora-toast__description',
                actionButton: 'bidora-toast__action',
                cancelButton: 'bidora-toast__cancel',
                closeButton: 'bidora-toast__close',
                icon: 'bidora-toast__icon',
            },
        }" />
</template>
