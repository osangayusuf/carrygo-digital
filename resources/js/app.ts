import { createInertiaApp } from '@inertiajs/vue3';
import { configureEcho, echo } from '@laravel/echo-vue';
import Pusher from 'pusher-js';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

if (typeof window !== 'undefined') {
    configureEcho({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT
            ? parseInt(import.meta.env.VITE_REVERB_PORT)
            : 80,
        wssPort: import.meta.env.VITE_REVERB_PORT
            ? parseInt(import.meta.env.VITE_REVERB_PORT)
            : 443,
        forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    (window as any).Pusher = Pusher;
    (window as any).Echo = echo();
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name === 'auth/Login':
            case name === 'auth/Register':
            case name === 'auth/VerifyEmail':
            case name === 'auth/ForgotPassword':
            case name === 'auth/ConfirmPassword':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('Admin/'):
                return null;
            case name.startsWith('Support/'):
                return null;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
