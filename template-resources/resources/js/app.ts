import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';

import AppLayout from './layouts/AppLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#4f7cac',
    },
    layout: () => AppLayout,
});
