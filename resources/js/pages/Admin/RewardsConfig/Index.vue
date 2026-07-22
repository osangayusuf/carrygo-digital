<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Settings,
    Check,
    Coins,
    Gift,
    Dices,
    AlertTriangle,
    Trash2,
    Plus,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { update as rewardsConfigUpdate } from '@/routes/admin/rewards-config';

type SpinSegment = {
    points: number;
    probability: number;
};

const props = defineProps<{
    config: {
        points_per_naira: number;
        bonus_conversion_rate: number;
        min_bid_increment: number;
        min_deposit_naira: number;
        max_deposit_naira: number;
        checkin_base_points: number;
        checkin_milestones: Record<string, number>;
        spin_wheel_daily_grant: number;
        spin_wheel_segments: SpinSegment[];
    };
}>();

// Map milestones dictionary to array of objects for easier form binding
const milestoneList = computed(() => {
    return Object.entries(props.config.checkin_milestones)
        .map(([day, points]) => ({
            day: parseInt(day),
            points: points,
        }))
        .sort((a, b) => a.day - b.day);
});

const form = useForm({
    points_per_naira: props.config.points_per_naira,
    bonus_conversion_rate: props.config.bonus_conversion_rate,
    min_bid_increment: props.config.min_bid_increment,
    min_deposit_naira: props.config.min_deposit_naira,
    max_deposit_naira: props.config.max_deposit_naira,

    checkin_base_points: props.config.checkin_base_points,
    checkin_milestones: props.config.checkin_milestones, // Will be updated on submit

    spin_wheel_daily_grant: props.config.spin_wheel_daily_grant,
    spin_wheel_segments: JSON.parse(
        JSON.stringify(props.config.spin_wheel_segments),
    ) as SpinSegment[],
});

// Helper form arrays for dynamic bindings
const milestonesModel = useForm({
    items: milestoneList.value,
});

const addMilestone = () => {
    milestonesModel.items.push({ day: 7, points: 10 });
    milestonesModel.items.sort((a, b) => a.day - b.day);
};

const removeMilestone = (index: number) => {
    milestonesModel.items.splice(index, 1);
};

const addSegment = () => {
    form.spin_wheel_segments.push({ points: 10, probability: 10 });
};

const removeSegment = (index: number) => {
    form.spin_wheel_segments.splice(index, 1);
};

const totalProbability = computed(() => {
    return form.spin_wheel_segments.reduce(
        (sum, item) => sum + (item.probability || 0),
        0,
    );
});

const isProbabilityValid = computed(() => {
    return totalProbability.value === 100;
});

const submitConfig = () => {
    // Reconstruct milestones object
    const milestonesObj: Record<string, number> = {};
    milestonesModel.items.forEach((item) => {
        if (item.day > 0) {
            milestonesObj[item.day.toString()] = item.points;
        }
    });
    form.checkin_milestones = milestonesObj;

    form.post(rewardsConfigUpdate.url());
};
</script>

<template>
    <Head title="Admin - System Config" />

    <AdminLayout :breadcrumbs="[{ title: 'Rewards Config' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <Settings class="h-6 w-6 text-primary" />
                        <span>Rewards & Points Configuration</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Manage points ratios, daily check-in rewards, and
                        spin-to-win wheel odds.
                    </p>
                </div>
            </div>

            <!-- Error message if validation failed -->
            <div
                v-if="$page.props.errors?.spin_wheel_segments"
                class="rounded-lg border-l-4 border-error bg-error-container/30 p-4 text-on-error-container shadow-sm"
            >
                <p class="flex items-center gap-2 font-bold">
                    <AlertTriangle class="h-5 w-5 text-error" />
                    <span>{{ $page.props.errors.spin_wheel_segments }}</span>
                </p>
            </div>

            <form @submit.prevent="submitConfig" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Column 1: Points & Conversion Settings -->
                    <div
                        class="flex flex-col gap-6 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm"
                    >
                        <h2
                            class="flex items-center gap-2 border-b border-outline-variant pb-3 text-sm font-black tracking-widest text-primary uppercase"
                        >
                            <Coins class="h-5 w-5 text-primary" />
                            <span>Naira to Points & Limits</span>
                        </h2>

                        <div class="space-y-4">
                            <div class="flex flex-col gap-2 font-bold">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Points per Naira (₦1 = X Points)</label
                                >
                                <input
                                    v-model="form.points_per_naira"
                                    type="number"
                                    required
                                    min="1"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>

                            <div class="flex flex-col gap-2 font-bold">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Bonus conversion rate (Multiplier)</label
                                >
                                <input
                                    v-model="form.bonus_conversion_rate"
                                    type="number"
                                    step="0.1"
                                    required
                                    min="0.1"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>

                            <div class="flex flex-col gap-2 font-bold">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Minimum Bid Increment (Points)</label
                                >
                                <input
                                    v-model="form.min_bid_increment"
                                    type="number"
                                    required
                                    min="1"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2 font-bold">
                                    <label
                                        class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                        >Min Deposit (₦)</label
                                    >
                                    <input
                                        v-model="form.min_deposit_naira"
                                        type="number"
                                        required
                                        min="1"
                                        class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                    />
                                </div>
                                <div class="flex flex-col gap-2 font-bold">
                                    <label
                                        class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                        >Max Deposit (₦)</label
                                    >
                                    <input
                                        v-model="form.max_deposit_naira"
                                        type="number"
                                        required
                                        min="1000"
                                        class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Daily Check-in Milestones -->
                    <div
                        class="flex flex-col gap-6 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b border-outline-variant pb-3"
                        >
                            <h2
                                class="flex items-center gap-2 text-sm font-black tracking-widest text-primary uppercase"
                            >
                                <Gift class="h-5 w-5 text-primary" />
                                <span>Check-in Milestones</span>
                            </h2>
                            <button
                                type="button"
                                @click="addMilestone"
                                class="flex items-center gap-1.5 rounded-lg border border-outline-variant px-2.5 py-1 text-[10px] font-black text-primary uppercase transition-colors hover:bg-surface-container-low"
                            >
                                <Plus class="h-3.5 w-3.5" />
                                <span>Add</span>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="flex flex-col gap-2 font-bold">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Base check-in Points (Day 1)</label
                                >
                                <input
                                    v-model="form.checkin_base_points"
                                    type="number"
                                    required
                                    min="0"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>

                            <label
                                class="mt-4 block text-[10px] font-bold tracking-wider text-on-surface-variant uppercase"
                                >Consecutive Milestones</label
                            >

                            <div
                                class="max-h-[220px] space-y-2 overflow-y-auto pr-1"
                            >
                                <div
                                    v-for="(
                                        item, index
                                    ) in milestonesModel.items"
                                    :key="index"
                                    class="flex items-center gap-2 rounded-lg border border-outline-variant bg-surface-container-low p-2"
                                >
                                    <span
                                        class="w-12 text-[9px] font-bold text-on-surface-variant uppercase"
                                        >Day:</span
                                    >
                                    <input
                                        v-model="item.day"
                                        type="number"
                                        required
                                        min="2"
                                        class="w-16 rounded-lg border border-outline-variant bg-surface-container-lowest px-2 py-1.5 text-center text-xs font-bold text-on-surface focus:border-secondary focus:outline-none"
                                    />
                                    <span
                                        class="w-16 text-right text-[9px] font-bold text-on-surface-variant uppercase"
                                        >Points:</span
                                    >
                                    <input
                                        v-model="item.points"
                                        type="number"
                                        required
                                        min="1"
                                        class="w-20 flex-1 rounded-lg border border-outline-variant bg-surface-container-lowest px-2 py-1.5 text-center text-xs font-bold text-on-surface focus:border-secondary focus:outline-none"
                                    />
                                    <button
                                        type="button"
                                        @click="removeMilestone(index)"
                                        class="rounded-lg border border-outline-variant bg-surface-container-lowest p-2 text-on-surface-variant transition-colors hover:bg-error-container/20 hover:text-error"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <div
                                    v-if="milestonesModel.items.length === 0"
                                    class="py-6 text-center text-[9px] font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    No milestone multipliers.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Spin Wheel Segment Odds Configurations -->
                <div
                    class="flex flex-col gap-6 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-outline-variant pb-3"
                    >
                        <h2
                            class="flex items-center gap-2 text-sm font-black tracking-widest text-primary uppercase"
                        >
                            <Dices class="h-5 w-5 text-primary" />
                            <span>Spin Wheel Segment Odds</span>
                        </h2>
                        <button
                            type="button"
                            @click="addSegment"
                            class="flex items-center gap-1.5 rounded-lg border border-outline-variant px-2.5 py-1 text-[10px] font-black text-primary uppercase transition-colors hover:bg-surface-container-low"
                        >
                            <Plus class="h-3.5 w-3.5" />
                            <span>Add segment</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div
                            class="flex flex-col gap-2 rounded-xl border border-outline-variant bg-surface-container-low p-4 font-bold"
                        >
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >Daily Spin Grant</label
                            >
                            <input
                                v-model="form.spin_wheel_daily_grant"
                                type="number"
                                required
                                min="0"
                                class="rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-xs text-on-surface focus:border-secondary focus:outline-none"
                            />
                        </div>

                        <div
                            class="col-span-1 flex items-center justify-end md:col-span-3"
                        >
                            <div
                                :class="[
                                    'flex w-56 flex-col items-end gap-1 rounded-xl border px-6 py-4 font-sans',
                                    isProbabilityValid
                                        ? 'border-secondary/30 bg-secondary-container/40 text-on-secondary-container'
                                        : 'animate-pulse border-error/20 bg-error-container/20 text-error',
                                ]"
                            >
                                <span
                                    class="text-[9px] font-bold text-on-surface-variant uppercase"
                                    >Total Probability</span
                                >
                                <span class="text-2xl font-black"
                                    >{{ totalProbability }}%</span
                                >
                                <span
                                    class="mt-0.5 text-[9px] font-semibold tracking-wider uppercase italic"
                                >
                                    {{
                                        isProbabilityValid
                                            ? 'VALID (SUM = 100%)'
                                            : 'INVALID (MUST BE 100%)'
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Segments Grid -->
                    <div
                        class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4"
                    >
                        <div
                            v-for="(item, index) in form.spin_wheel_segments"
                            :key="index"
                            class="relative flex flex-col gap-3 rounded-xl border border-outline-variant bg-surface-container-low p-4 font-bold"
                        >
                            <div
                                class="mb-1 flex items-center justify-between border-b border-outline-variant pb-2"
                            >
                                <span
                                    class="text-[10px] font-black tracking-widest text-primary uppercase"
                                    >Segment #{{ index + 1 }}</span
                                >
                                <button
                                    type="button"
                                    @click="removeSegment(index)"
                                    class="rounded-lg border border-outline-variant bg-surface-container-lowest p-2 text-on-surface-variant transition-colors hover:bg-error-container/20 hover:text-error"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-[9px] font-bold text-on-surface-variant uppercase"
                                    >Reward points:</span
                                >
                                <input
                                    v-model="item.points"
                                    type="number"
                                    required
                                    min="0"
                                    class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-xs text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>

                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-[9px] font-bold text-on-surface-variant uppercase"
                                    >Odds probability (%):</span
                                >
                                <input
                                    v-model="item.probability"
                                    type="number"
                                    required
                                    min="0"
                                    max="100"
                                    class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-xs text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Action Block -->
                <div
                    class="flex justify-end gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"
                >
                    <button
                        type="submit"
                        :disabled="form.processing || !isProbabilityValid"
                        class="flex items-center gap-2 rounded-lg border border-secondary bg-secondary px-8 py-3.5 text-xs font-black tracking-widest text-on-secondary-fixed uppercase transition-all hover:scale-[1.01] active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Check class="h-4 w-4" />
                        <span>Save Config</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
