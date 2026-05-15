<script setup lang="ts">
import { computed, onUnmounted, ref, watch } from 'vue';
import { useAuctionStore } from '@/stores/useAuctionStore';

const store = useAuctionStore();

/** Remaining seconds, computed from server-provided expires_at — never calculated client-side. */
const secondsRemaining = ref(0);
let intervalId: ReturnType<typeof setInterval> | null = null;

function computeRemaining(): number {
    if (!store.auction?.expires_at) {
        return 0;
    }
    const diff = Math.floor(
        (new Date(store.auction.expires_at).getTime() - Date.now()) / 1000,
    );
    return Math.max(0, diff);
}

function startTimer(): void {
    if (intervalId !== null) {
        clearInterval(intervalId);
    }
    secondsRemaining.value = computeRemaining();
    intervalId = setInterval(() => {
        secondsRemaining.value = computeRemaining();
        if (secondsRemaining.value === 0 && intervalId !== null) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }, 1000);
}

// Start or restart the timer whenever expires_at changes (e.g. on AuctionTriggeredEvent).
watch(
    () => store.auction?.expires_at,
    (expiresAt) => {
        if (expiresAt) {
            startTimer();
        } else {
            if (intervalId !== null) {
                clearInterval(intervalId);
                intervalId = null;
            }
            secondsRemaining.value = 0;
        }
    },
    { immediate: true },
);

onUnmounted(() => {
    if (intervalId !== null) {
        clearInterval(intervalId);
    }
});

const isTriggered = computed(
    () => store.auction?.status === 'triggered' || store.auction?.status === 'closed',
);

const isClosed = computed(() => store.auction?.status === 'closed');

const displayMinutes = computed(() => String(Math.floor(secondsRemaining.value / 60)).padStart(2, '0'));
const displaySeconds = computed(() => String(secondsRemaining.value % 60).padStart(2, '0'));

const isUrgent = computed(() => secondsRemaining.value > 0 && secondsRemaining.value <= 30);
</script>

<template>
    <div v-if="isTriggered" class="auction-timer" :class="{ 'is-urgent': isUrgent, 'is-closed': isClosed }">
        <!-- Closed state -->
        <div v-if="isClosed" class="timer-closed">
            <span class="timer-label">Auction Ended</span>
        </div>

        <!-- Active countdown -->
        <div v-else class="timer-countdown">
            <span class="timer-label">CLOSING IN</span>
            <div class="timer-display">
                <span class="timer-unit">
                    <span class="timer-digits">{{ displayMinutes }}</span>
                    <span class="timer-unit-label">MIN</span>
                </span>
                <span class="timer-separator">:</span>
                <span class="timer-unit">
                    <span class="timer-digits">{{ displaySeconds }}</span>
                    <span class="timer-unit-label">SEC</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Pre-trigger state -->
    <div v-else class="timer-waiting">
        <span class="timer-label">Awaiting Trigger</span>
        <div class="timer-progress-bar">
            <div
                class="timer-progress-fill"
                :style="{
                    width: store.auction
                        ? `${Math.min(100, (store.auction.current_points / Math.max(1, store.auction.opening_points ?? 1)) * 100)}%`
                        : '0%',
                }"
            />
        </div>
    </div>
</template>

<style scoped>
.auction-timer {
    border: 3px solid #f5e642;
    background: #0a0a0a;
    padding: 1rem 1.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 180px;
    transition: border-color 0.3s;
}

.auction-timer.is-urgent {
    border-color: #f5e642;
    animation: pulse-amber 1s infinite;
}

.auction-timer.is-closed {
    border-color: #1a472a;
}

.timer-closed {
    text-align: center;
}

.timer-countdown {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.timer-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    color: #5a7a4a;
    text-transform: uppercase;
}

.timer-display {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.timer-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.timer-digits {
    font-size: 2rem;
    font-weight: 900;
    color: #f5e642;
    font-variant-numeric: tabular-nums;
    line-height: 1;
    letter-spacing: 0.02em;
}

.timer-separator {
    font-size: 2rem;
    font-weight: 900;
    color: #f5e642;
    margin-bottom: 0.75rem;
    line-height: 1;
}

.timer-unit-label {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: #5a7a4a;
    text-transform: uppercase;
}

.timer-waiting {
    border: 2px solid #dde8cc;
    background: #0a0a0a;
    padding: 0.75rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 180px;
}

.timer-progress-bar {
    height: 4px;
    background: #0d1b2a;
    border: 1px solid #dde8cc;
    overflow: hidden;
}

.timer-progress-fill {
    height: 100%;
    background: #c8e000;
    transition: width 0.4s ease;
}

@keyframes pulse-amber {
    0%, 100% { border-color: #f5e642; box-shadow: 0 0 0 0 rgba(245, 230, 66, 0.4); }
    50% { border-color: #f5e642; box-shadow: 0 0 0 6px rgba(245, 230, 66, 0); }
}
</style>
