<script setup lang="ts">
defineProps<{
    achievements: Array<{
        key: string;
        label: string;
        description: string;
        icon: string;
        points: number;
        target: number;
        progress: number;
        completed_at: string | null;
    }>;
}>();
</script>

<template>
    <div
        class="rounded-3xl border border-outline-variant/30 bg-surface p-6 shadow-sm sm:p-8"
    >
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="font-headline text-xl font-bold text-on-surface">
                    Achievement Badges
                </h2>
                <p class="mt-0.5 text-sm text-on-surface-variant">
                    Complete challenges to earn bonus points
                </p>
            </div>
            <span
                class="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary"
            >
                {{ achievements.filter((a) => a.completed_at).length }}/{{
                    achievements.length
                }}
                done
            </span>
        </div>

        <!-- Achievement list -->
        <div class="space-y-4">
            <div
                v-for="achievement in achievements"
                :key="achievement.key"
                class="group flex items-center gap-4 rounded-2xl border border-outline-variant/20 bg-background p-4 transition-all hover:border-primary/30"
            >
                <!-- Icon -->
                <div
                    :class="[
                        'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-all',
                        achievement.completed_at
                            ? 'bg-primary text-white shadow-md shadow-primary/30'
                            : 'bg-surface-container text-on-surface-variant',
                    ]"
                >
                    <span class="material-symbols-outlined">{{
                        achievement.icon
                    }}</span>
                </div>

                <!-- Info -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <span
                            class="font-headline text-sm font-bold text-on-surface"
                        >
                            {{ achievement.label }}
                        </span>
                        <!-- Status badge -->
                        <span
                            v-if="achievement.completed_at"
                            class="shrink-0 rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-bold text-primary"
                        >
                            ✓ Done
                        </span>
                        <span
                            v-else
                            class="shrink-0 rounded-full bg-surface-container px-2.5 py-0.5 text-xs font-medium text-on-surface-variant"
                        >
                            +{{ achievement.points }} pts
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-on-surface-variant">
                        {{ achievement.description }}
                    </p>

                    <!-- Progress bar (only for counted achievements) -->
                    <template v-if="achievement.target > 1">
                        <div class="mt-2 flex items-center gap-2">
                            <div
                                class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface-container"
                            >
                                <div
                                    class="h-full rounded-full bg-primary transition-all duration-500"
                                    :style="{
                                        width: `${Math.min((achievement.progress / achievement.target) * 100, 100)}%`,
                                    }"
                                />
                            </div>
                            <span
                                class="text-xs font-medium text-on-surface-variant"
                            >
                                {{ achievement.progress }}/{{
                                    achievement.target
                                }}
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
