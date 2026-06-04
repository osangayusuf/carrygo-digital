<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { computed } from 'vue';
import { 
    Settings, 
    Check, 
    Coins, 
    Gift,
    Dices,
    AlertTriangle,
    Trash2,
    Plus
} from 'lucide-vue-next';

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
    return Object.entries(props.config.checkin_milestones).map(([day, points]) => ({
        day: parseInt(day),
        points: points
    })).sort((a, b) => a.day - b.day);
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
    spin_wheel_segments: JSON.parse(JSON.stringify(props.config.spin_wheel_segments)) as SpinSegment[],
});

// Helper form arrays for dynamic bindings
const milestonesModel = useForm({
    items: milestoneList.value
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
    return form.spin_wheel_segments.reduce((sum, item) => sum + (item.probability || 0), 0);
});

const isProbabilityValid = computed(() => {
    return totalProbability.value === 100;
});

const submitConfig = () => {
    // Reconstruct milestones object
    const milestonesObj: Record<string, number> = {};
    milestonesModel.items.forEach(item => {
        if (item.day > 0) {
            milestonesObj[item.day.toString()] = item.points;
        }
    });
    form.checkin_milestones = milestonesObj;

    form.post('/admin/rewards-config');
};
</script>

<template>
    <Head title="Admin - System Config" />

    <AdminLayout :breadcrumbs="[{ title: 'Rewards Config' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <Settings class="w-6 h-6 text-primary" />
                        <span>Rewards & Points Configuration</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Manage points ratios, daily check-in rewards, and spin-to-win wheel odds.</p>
                </div>
            </div>

            <!-- Error message if validation failed -->
            <div v-if="$page.props.errors?.spin_wheel_segments" class="p-4 bg-error-container/30 border-l-4 border-error text-on-error-container rounded-lg shadow-sm">
                <p class="font-bold flex items-center gap-2">
                    <AlertTriangle class="w-5 h-5 text-error" />
                    <span>{{ $page.props.errors.spin_wheel_segments }}</span>
                </p>
            </div>

            <form @submit.prevent="submitConfig" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Column 1: Points & Conversion Settings -->
                    <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm flex flex-col gap-6">
                        <h2 class="text-sm font-black text-primary uppercase tracking-widest pb-3 border-b border-outline-variant flex items-center gap-2">
                            <Coins class="w-5 h-5 text-primary" />
                            <span>Naira to Points & Limits</span>
                        </h2>

                        <div class="space-y-4">
                            <div class="flex flex-col gap-2 font-bold">
                                <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Points per Naira (₦1 = X Points)</label>
                                <input 
                                    v-model="form.points_per_naira" 
                                    type="number" 
                                    required
                                    min="1"
                                    class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                                />
                            </div>

                            <div class="flex flex-col gap-2 font-bold">
                                <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Bonus conversion rate (Multiplier)</label>
                                <input 
                                    v-model="form.bonus_conversion_rate" 
                                    type="number" 
                                    step="0.1"
                                    required
                                    min="0.1"
                                    class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                                />
                            </div>

                            <div class="flex flex-col gap-2 font-bold">
                                <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Minimum Bid Increment (Points)</label>
                                <input 
                                    v-model="form.min_bid_increment" 
                                    type="number" 
                                    required
                                    min="1"
                                    class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2 font-bold">
                                    <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Min Deposit (₦)</label>
                                    <input 
                                        v-model="form.min_deposit_naira" 
                                        type="number" 
                                        required
                                        min="1"
                                        class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                                    />
                                </div>
                                <div class="flex flex-col gap-2 font-bold">
                                    <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Max Deposit (₦)</label>
                                    <input 
                                        v-model="form.max_deposit_naira" 
                                        type="number" 
                                        required
                                        min="1000"
                                        class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Daily Check-in Milestones -->
                    <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm flex flex-col gap-6">
                        <div class="flex items-center justify-between border-b border-outline-variant pb-3">
                            <h2 class="text-sm font-black text-primary uppercase tracking-widest flex items-center gap-2">
                                <Gift class="w-5 h-5 text-primary" />
                                <span>Check-in Milestones</span>
                            </h2>
                            <button 
                                type="button" 
                                @click="addMilestone" 
                                class="px-2.5 py-1 border border-outline-variant hover:bg-surface-container-low rounded-lg text-primary text-[10px] font-black uppercase flex items-center gap-1.5 transition-colors"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>Add</span>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="flex flex-col gap-2 font-bold">
                                <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Base check-in Points (Day 1)</label>
                                <input 
                                    v-model="form.checkin_base_points" 
                                    type="number" 
                                    required
                                    min="0"
                                    class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                                />
                            </div>

                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px] block mt-4 font-bold">Consecutive Milestones</label>
                            
                            <div class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                                <div 
                                    v-for="(item, index) in milestonesModel.items" 
                                    :key="index"
                                    class="flex items-center gap-2 bg-surface-container-low border border-outline-variant p-2 rounded-lg"
                                >
                                    <span class="text-on-surface-variant uppercase text-[9px] font-bold w-12">Day:</span>
                                    <input 
                                        v-model="item.day" 
                                        type="number" 
                                        required
                                        min="2"
                                        class="bg-surface-container-lowest border border-outline-variant text-on-surface px-2 py-1.5 rounded-lg focus:outline-none focus:border-secondary w-16 text-center text-xs font-bold"
                                    />
                                    <span class="text-on-surface-variant uppercase text-[9px] font-bold w-16 text-right">Points:</span>
                                    <input 
                                        v-model="item.points" 
                                        type="number" 
                                        required
                                        min="1"
                                        class="bg-surface-container-lowest border border-outline-variant text-on-surface px-2 py-1.5 rounded-lg focus:outline-none focus:border-secondary w-20 text-center flex-1 text-xs font-bold"
                                    />
                                    <button 
                                        type="button" 
                                        @click="removeMilestone(index)"
                                        class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 border border-outline-variant rounded-lg transition-colors bg-surface-container-lowest"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <div v-if="milestonesModel.items.length === 0" class="text-center text-on-surface-variant uppercase tracking-widest text-[9px] py-6 font-bold">
                                    No milestone multipliers.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Spin Wheel Segment Odds Configurations -->
                <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm flex flex-col gap-6">
                    <div class="flex items-center justify-between border-b border-outline-variant pb-3">
                        <h2 class="text-sm font-black text-primary uppercase tracking-widest flex items-center gap-2">
                            <Dices class="w-5 h-5 text-primary" />
                            <span>Spin Wheel Segment Odds</span>
                        </h2>
                        <button 
                            type="button" 
                            @click="addSegment" 
                            class="px-2.5 py-1 border border-outline-variant hover:bg-surface-container-low rounded-lg text-primary text-[10px] font-black uppercase flex items-center gap-1.5 transition-colors"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>Add segment</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="flex flex-col gap-2 bg-surface-container-low border border-outline-variant p-4 rounded-xl font-bold">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Daily Spin Grant</label>
                            <input 
                                v-model="form.spin_wheel_daily_grant" 
                                type="number" 
                                required
                                min="0"
                                class="bg-surface-container-lowest border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary text-xs"
                            />
                        </div>

                        <div class="col-span-1 md:col-span-3 flex items-center justify-end">
                            <div 
                                :class="[
                                    'border px-6 py-4 flex flex-col gap-1 items-end w-56 rounded-xl font-sans',
                                    isProbabilityValid 
                                        ? 'bg-secondary-container/40 border-secondary/30 text-on-secondary-container' 
                                        : 'bg-error-container/20 border-error/20 text-error animate-pulse'
                                ]"
                            >
                                <span class="text-[9px] uppercase font-bold text-on-surface-variant">Total Probability</span>
                                <span class="text-2xl font-black">{{ totalProbability }}%</span>
                                <span class="text-[9px] font-semibold italic uppercase tracking-wider mt-0.5">
                                    {{ isProbabilityValid ? 'VALID (SUM = 100%)' : 'INVALID (MUST BE 100%)' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Segments Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 mt-2">
                        <div 
                            v-for="(item, index) in form.spin_wheel_segments" 
                            :key="index"
                            class="bg-surface-container-low border border-outline-variant p-4 rounded-xl flex flex-col gap-3 relative font-bold"
                        >
                            <div class="flex justify-between items-center pb-2 border-b border-outline-variant mb-1">
                                <span class="text-[10px] font-black text-primary uppercase tracking-widest">Segment #{{ index + 1 }}</span>
                                <button 
                                    type="button" 
                                    @click="removeSegment(index)"
                                    class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 border border-outline-variant rounded-lg transition-colors bg-surface-container-lowest"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-on-surface-variant uppercase text-[9px] font-bold">Reward points:</span>
                                <input 
                                    v-model="item.points" 
                                    type="number" 
                                    required
                                    min="0"
                                    class="bg-surface-container-lowest border border-outline-variant text-on-surface px-3 py-2 rounded-lg focus:outline-none focus:border-secondary text-xs"
                                />
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-on-surface-variant uppercase text-[9px] font-bold">Odds probability (%):</span>
                                <input 
                                    v-model="item.probability" 
                                    type="number" 
                                    required
                                    min="0"
                                    max="100"
                                    class="bg-surface-container-lowest border border-outline-variant text-on-surface px-3 py-2 rounded-lg focus:outline-none focus:border-secondary text-xs"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Action Block -->
                <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl flex justify-end gap-3 shadow-sm">
                    <button 
                        type="submit"
                        :disabled="form.processing || !isProbabilityValid"
                        class="px-8 py-3.5 bg-secondary text-on-secondary-fixed disabled:opacity-50 disabled:cursor-not-allowed font-black text-xs uppercase tracking-widest rounded-lg hover:scale-[1.01] active:scale-95 transition-all flex items-center gap-2 border border-secondary"
                    >
                        <Check class="w-4 h-4" />
                        <span>Save Config</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
