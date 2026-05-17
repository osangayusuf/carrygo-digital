<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed } from 'vue';
import { store as checkinStore } from '@/actions/App/Http/Controllers/CheckinController';

const props = defineProps<{
    checkin: {
        streak: number;
        last_date: string | null;
        can_checkin: boolean;
        week_days: Array<{
            label: string;
            date: string;
            checked: boolean;
            is_today: boolean;
        }>;
    };
}>();

const streakMilestones = [3, 7, 14, 30];

const nextMilestone = computed(() => {
    return streakMilestones.find((m) => m > props.checkin.streak) ?? null;
});
</script>

<template>
    <div class="rounded-3xl border border-outline-variant/30 bg-surface p-6 shadow-sm sm:p-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="font-headline text-xl font-bold text-on-surface">Daily Check-In</h2>
                <p class="mt-0.5 text-sm text-on-surface-variant">
                    Log in daily to build your streak and earn points
                </p>
            </div>
            <!-- Streak badge -->
            <div
                class="flex items-center gap-1.5 rounded-full bg-primary/10 px-4 py-2 text-primary"
            >
                <span class="text-lg">🔥</span>
                <span class="font-headline text-sm font-black">
                    {{ checkin.streak }} Day{{ checkin.streak !== 1 ? 's' : '' }}
                </span>
            </div>
        </div>

        <!-- 7-day week grid -->
        <div class="mb-6 grid grid-cols-7 gap-1.5 sm:gap-2">
            <div
                v-for="day in checkin.week_days"
                :key="day.date"
                class="flex flex-col items-center gap-1"
            >
                <span class="text-xs font-bold text-on-surface-variant">{{ day.label }}</span>
                <div
                    :class="[
                        'flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold transition-all sm:h-10 sm:w-10',
                        day.checked
                            ? 'bg-primary text-white shadow-md shadow-primary/30'
                            : day.is_today
                              ? 'border-2 border-primary/60 bg-primary/10 text-primary'
                              : 'bg-surface-container text-on-surface-variant opacity-50',
                    ]"
                >
                    <span v-if="day.checked" class="material-symbols-outlined text-sm!">check</span>
                    <span v-else-if="day.is_today" class="material-symbols-outlined text-sm!">star</span>
                    <span v-else class="material-symbols-outlined text-sm!">radio_button_unchecked</span>
                </div>
            </div>
        </div>

        <!-- Next milestone hint -->
        <p v-if="nextMilestone" class="mb-4 text-center text-xs text-on-surface-variant">
            🎯 Reach a <span class="font-bold text-primary">{{ nextMilestone }}-day streak</span> for a bonus!
        </p>

        <!-- Check-in button -->
        <Form :action="checkinStore.url()" method="post">
            <button
                type="submit"
                :disabled="!checkin.can_checkin"
                :class="[
                    'flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-4 text-base font-bold transition-all active:scale-[0.98]',
                    checkin.can_checkin
                        ? 'bg-primary text-white shadow-md shadow-primary/20 hover:opacity-90'
                        : 'cursor-not-allowed bg-surface-container text-on-surface-variant',
                ]"
            >
                <span class="material-symbols-outlined">
                    {{ checkin.can_checkin ? 'login' : 'check_circle' }}
                </span>
                {{ checkin.can_checkin ? 'Check In Now' : 'Come Back Tomorrow' }}
            </button>
        </Form>
    </div>
</template>
