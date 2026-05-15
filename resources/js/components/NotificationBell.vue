<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Bell } from 'lucide-vue-next';
import { Link, useHttp } from '@inertiajs/vue3';
import notifications from '@/routes/notifications/index';

const unreadCount = ref(0);
const http = useHttp({});

const fetchUnread = () => {
    http.get('/api/notifications/unread-count', {
        onSuccess: (response) => {
            if (response) {
                unreadCount.value = response.count || 0;
            }
        },
        onError: () => { }
    });
};

onMounted(() => {
    // We will wire this up fully later once the API is available
    // fetchUnread();
});
</script>

<template>
    <Link :href="notifications.index.url()"
        class="text-white hover:text-lemon relative inline-flex items-center justify-center">
        <Bell class="h-6 w-6" />
        <span v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-forest">
            {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
    </Link>
</template>
