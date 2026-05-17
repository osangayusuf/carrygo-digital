<script setup lang="ts">
import { computed, ref } from 'vue';
import { store as spinStore } from '@/actions/App/Http/Controllers/SpinController';

const props = defineProps<{
    spin: {
        available_spins: number;
        segment_labels: string[];
    };
}>();

const emit = defineEmits<{
    (e: 'spun', pointsWon: number): void;
}>();

// Reactive local state (optimistic)
const localSpins = ref(props.spin.available_spins);
const isSpinning = ref(false);
const showResult = ref(false);
const resultPoints = ref(0);
const rotationDeg = ref(0);
const showProbabilities = ref(false);

const segmentCount = computed(() => props.spin.segment_labels.length);

// Colours cycling through the design palette
const segmentColors = [
    '#4f7cac', // primary
    '#9eefe5', // aqua
    '#3c474b', // slate
    '#7eb8d4', // primary-fixed-dim
    '#c0e0de', // mint
    '#162521', // ink
];

function getSegmentPath(index: number): string {
    const n = segmentCount.value;
    const angle = (2 * Math.PI) / n;
    const start = angle * index - Math.PI / 2;
    const end = start + angle;
    const r = 100;
    const x1 = r * Math.cos(start);
    const y1 = r * Math.sin(start);
    const x2 = r * Math.cos(end);
    const y2 = r * Math.sin(end);
    const largeArc = angle > Math.PI ? 1 : 0;

    return `M 0 0 L ${x1} ${y1} A ${r} ${r} 0 ${largeArc} 1 ${x2} ${y2} Z`;
}

function getLabelPosition(index: number): { x: number; y: number } {
    const n = segmentCount.value;
    const angle = (2 * Math.PI) / n;
    const midAngle = angle * index + angle / 2 - Math.PI / 2;
    const r = 65;

    return {
        x: r * Math.cos(midAngle),
        y: r * Math.sin(midAngle),
    };
}

async function doSpin() {
    if (localSpins.value < 1 || isSpinning.value) {
        return;
    }

    isSpinning.value = true;
    localSpins.value--;

    try {
        const response = await fetch(spinStore.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });

        const data: { points_won: number; segment_index: number; error?: string } = await response.json();

        if (!response.ok || data.error) {
            localSpins.value++;
            isSpinning.value = false;

            return;
        }

        const segAngle = 360 / segmentCount.value;
        const targetAngle = 360 - (data.segment_index * segAngle + segAngle / 2);

        const currentMod = rotationDeg.value % 360;
        let advance = targetAngle - currentMod;
        
        if (advance < 0) advance += 360;

        rotationDeg.value += 5 * 360 + advance;
        resultPoints.value = data.points_won;

        setTimeout(() => {
            isSpinning.value = false;
            showResult.value = true;
            emit('spun', resultPoints.value);
        }, 4500);
    } catch {
        localSpins.value++;
        isSpinning.value = false;
    }
}

function dismissResult() {
    showResult.value = false;
}
</script>

<template>
    <div class="rounded-3xl border border-outline-variant/30 bg-surface p-6 shadow-sm sm:p-8">
        <!-- Header -->
        <div class="mb-2 text-center">
            <h2 class="font-headline text-xl font-bold text-on-surface">Spin &amp; Win</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Use your spins to win up to 50 points!</p>
        </div>

        <!-- Wheel -->
        <div
            class="relative mx-auto my-6 flex items-center justify-center"
            style="width: 240px; height: 240px"
        >
            <!-- Pointer arrow at top -->
            <div class="absolute -top-3 left-1/2 z-10 -translate-x-1/2 text-2xl">▼</div>

            <svg
                viewBox="-110 -110 220 220"
                class="h-full w-full drop-shadow-xl"
                :style="{
                    transform: `rotate(${rotationDeg}deg)`,
                    transition: isSpinning ? 'transform 4.5s cubic-bezier(0.17, 0.67, 0.12, 0.99)' : 'none',
                }"
            >
                <g v-for="(label, i) in spin.segment_labels" :key="i">
                    <path
                        :d="getSegmentPath(i)"
                        :fill="segmentColors[i % segmentColors.length]"
                        stroke="white"
                        stroke-width="1.5"
                    />
                    <text
                        :x="getLabelPosition(i).x"
                        :y="getLabelPosition(i).y"
                        text-anchor="middle"
                        dominant-baseline="middle"
                        fill="white"
                        font-size="11"
                        font-weight="bold"
                        font-family="Inter, sans-serif"
                    >{{ label }}</text>
                </g>
                <!-- Center hub -->
                <circle cx="0" cy="0" r="18" fill="white" />
                <text x="0" y="0" text-anchor="middle" dominant-baseline="middle" font-size="14">⭐</text>
            </svg>
        </div>

        <!-- Spin count -->
        <p class="mb-4 text-center text-sm font-medium text-on-surface-variant">
            Available Spins:
            <span class="ml-1 font-black text-on-surface">{{ localSpins }}</span>
        </p>

        <!-- Spin button -->
        <button
            @click="doSpin"
            :disabled="localSpins < 1 || isSpinning"
            :class="[
                'flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-4 text-base font-black uppercase tracking-wide transition-all active:scale-[0.98]',
                localSpins > 0 && !isSpinning
                    ? 'bg-primary text-white shadow-lg shadow-primary/25 hover:opacity-90'
                    : 'cursor-not-allowed bg-surface-container text-on-surface-variant',
            ]"
        >
            <span :class="['material-symbols-outlined', isSpinning ? 'animate-spin' : '']">
                {{ isSpinning ? 'refresh' : 'casino' }}
            </span>
            {{ isSpinning ? 'Spinning...' : localSpins > 0 ? 'Spin Now' : 'No Spins Left Today' }}
        </button>

        <!-- Probabilities link -->
        <button
            @click="showProbabilities = true"
            class="mt-3 block w-full text-center text-xs text-on-surface-variant underline-offset-2 hover:underline"
        >
            View Reward Values
        </button>

        <!-- Probabilities modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showProbabilities"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center"
                @click.self="showProbabilities = false"
            >
                <div class="w-full max-w-sm rounded-3xl bg-surface p-6 shadow-2xl">
                    <h3 class="mb-4 font-headline text-lg font-bold text-on-surface">Wheel Rewards</h3>
                    <div class="space-y-2">
                        <div
                            v-for="(label, i) in spin.segment_labels"
                            :key="i"
                            class="flex items-center gap-3 rounded-xl bg-background p-3"
                        >
                            <div
                                class="h-3 w-3 shrink-0 rounded-full"
                                :style="{ backgroundColor: segmentColors[i % segmentColors.length] }"
                            />
                            <span class="text-sm font-medium text-on-surface">{{ label }}</span>
                        </div>
                    </div>
                    <button
                        @click="showProbabilities = false"
                        class="mt-4 w-full rounded-2xl bg-primary py-3 text-sm font-bold text-white"
                    >
                        Close
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Result overlay -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-75"
            enter-to-class="opacity-100 scale-100"
        >
            <div
                v-if="showResult"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-6"
            >
                <div
                    class="flex w-full max-w-xs flex-col items-center rounded-3xl bg-surface p-8 text-center shadow-2xl"
                >
                    <div class="mb-2 text-5xl">🎉</div>
                    <h3 class="font-headline text-2xl font-black text-on-surface">You Won!</h3>
                    <p class="mt-2 text-on-surface-variant">Added to your reward wallet</p>
                    <div class="my-6 rounded-2xl bg-primary px-8 py-4 text-white">
                        <span class="text-4xl font-black">{{ resultPoints }}</span>
                        <span class="ml-1 text-lg font-bold opacity-80">pts</span>
                    </div>
                    <button
                        @click="dismissResult"
                        class="w-full rounded-2xl bg-surface-container py-3 text-sm font-bold text-on-surface"
                    >
                        Awesome!
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
