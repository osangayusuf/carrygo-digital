<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useAuctionStore } from '@/stores/useAuctionStore';

const props = defineProps<{
    auctionId: number;
}>();

const store = useAuctionStore();
const page = usePage();

/** The bid amount the user wants to place. */
const bidAmount = ref<number>(10);

const form = useForm({
    amount: 10,
});

/** Seconds remaining on the 5-second cooldown since last bid. */
const cooldownRemaining = ref(0);
let cooldownInterval: ReturnType<typeof setInterval> | null = null;

function startCooldown(): void {
    cooldownRemaining.value = 5;

    if (cooldownInterval !== null) {
        clearInterval(cooldownInterval);
    }

    cooldownInterval = setInterval(() => {
        cooldownRemaining.value -= 1;

        if (cooldownRemaining.value <= 0) {
            if (cooldownInterval !== null) {
                clearInterval(cooldownInterval);
                cooldownInterval = null;
            }

            cooldownRemaining.value = 0;
        }
    }, 1000);
}

const isCooldownActive = computed(() => cooldownRemaining.value > 0);

const isAuctionOpen = computed(
    () =>
        store.auction?.status === 'active' ||
        store.auction?.status === 'triggered',
);

const canBid = computed(
    () => isAuctionOpen.value && !form.processing && !isCooldownActive.value,
);

const userPointsBalance = computed(
    () =>
        (page.props.auth as { user?: { points_balance?: number } })?.user
            ?.points_balance ?? 0,
);

const hasEnoughPoints = computed(
    () => userPointsBalance.value >= bidAmount.value,
);

function submitBid(): void {
    if (!canBid.value || !hasEnoughPoints.value) {
        return;
    }

    form.amount = bidAmount.value;

    form.post(`/auctions/${props.auctionId}/bids`, {
        preserveScroll: true,
        onSuccess: () => {
            store.recordBidPlaced();
            startCooldown();
            // Reset to minimum bid after success.
            bidAmount.value = 10;
        },
    });
}
</script>

<template>
    <div class="bid-interface">
        <!-- Auction closed state -->
        <div v-if="store.auction?.status === 'closed'" class="bid-closed">
            <p class="bid-closed-text">This auction has ended.</p>
            <p v-if="store.auction.winner_id" class="bid-winner-text">
                🏆 Winner selected!
            </p>
        </div>

        <!-- Active bidding UI -->
        <div v-else-if="isAuctionOpen" class="bid-form">
            <!-- Points balance -->
            <div class="bid-balance">
                <span class="bid-balance-label">Your Balance</span>
                <span class="bid-balance-value"
                    >{{ userPointsBalance.toLocaleString() }} pts</span
                >
            </div>

            <!-- Bid amount input -->
            <div class="bid-amount-group">
                <label for="bid-amount-input" class="bid-input-label"
                    >Bid Amount (points)</label
                >
                <div class="bid-input-wrapper">
                    <button
                        type="button"
                        class="bid-stepper-btn"
                        :disabled="bidAmount <= 10"
                        @click="bidAmount = Math.max(10, bidAmount - 10)"
                    >
                        −
                    </button>
                    <input
                        id="bid-amount-input"
                        v-model.number="bidAmount"
                        type="number"
                        min="10"
                        step="10"
                        class="bid-input"
                    />
                    <button
                        type="button"
                        class="bid-stepper-btn"
                        @click="bidAmount += 10"
                    >
                        +
                    </button>
                </div>
                <p v-if="form.errors.amount" class="bid-error">
                    {{ form.errors.amount }}
                </p>
                <p v-if="!hasEnoughPoints" class="bid-error">
                    Insufficient points balance.
                </p>
            </div>

            <!-- Submit button with cooldown UX -->
            <button
                id="place-bid-button"
                type="button"
                class="bid-button"
                :class="{
                    'bid-button--cooldown': isCooldownActive,
                    'bid-button--processing': form.processing,
                }"
                :disabled="!canBid || !hasEnoughPoints"
                @click="submitBid"
            >
                <span v-if="form.processing">Placing Bid…</span>
                <span v-else-if="isCooldownActive">
                    Wait {{ cooldownRemaining }}s
                </span>
                <span v-else>Place Bid</span>
            </button>

            <p class="bid-policy-notice">
                <span class="material-symbols-outlined bid-policy-icon"
                    >info</span
                >
                Points bidden cannot be refunded and wallet credits are
                non-withdrawable.
            </p>
        </div>

        <!-- Pre-trigger state (auction active but not yet triggered) -->
        <div v-else class="bid-inactive">
            <p class="bid-inactive-text">Auction not yet started.</p>
        </div>
    </div>
</template>

<style scoped>
.bid-interface {
    background: var(--color-ink);
    border: 2px solid var(--color-sage-border);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    font-family: sans-serif;
}

.bid-closed,
.bid-inactive {
    text-align: center;
    padding: 1rem 0;
}

.bid-closed-text,
.bid-inactive-text {
    color: var(--color-sage-dark);
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.bid-winner-text {
    color: var(--color-lemon);
    font-size: 1rem;
    font-weight: 700;
    margin-top: 0.5rem;
}

.bid-balance {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--color-navy);
}

.bid-balance-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: var(--color-sage-dark);
    text-transform: uppercase;
}

.bid-balance-value {
    font-size: 1rem;
    font-weight: 800;
    color: var(--color-sage-light);
    letter-spacing: 0.02em;
}

.bid-amount-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.bid-input-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: var(--color-sage-dark);
    text-transform: uppercase;
}

.bid-input-wrapper {
    display: flex;
    align-items: center;
    border: 2px solid var(--color-sage-border);
    overflow: hidden;
}

.bid-stepper-btn {
    background: var(--color-navy);
    color: var(--color-sage-light);
    border: none;
    padding: 0.6rem 1rem;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
    line-height: 1;
}

.bid-stepper-btn:hover:not(:disabled) {
    background: var(--color-forest);
}

.bid-stepper-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.bid-input {
    flex: 1;
    background: var(--color-ink);
    color: var(--color-lemon);
    border: none;
    text-align: center;
    font-size: 1.1rem;
    font-weight: 800;
    padding: 0.6rem 0.5rem;
    outline: none;
    min-width: 0;
    /* Remove browser number arrows */
    -moz-appearance: textfield;
}

.bid-input::-webkit-outer-spin-button,
.bid-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.bid-error {
    font-size: 0.75rem;
    color: var(--color-amber);
    font-weight: 600;
}

.bid-button {
    width: 100%;
    background: var(--color-lemon);
    color: var(--color-ink);
    border: 3px solid var(--color-lemon);
    padding: 0.85rem 1rem;
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition:
        background 0.15s,
        transform 0.1s,
        border-color 0.15s;
    position: relative;
    overflow: hidden;
}

.bid-button:hover:not(:disabled) {
    background: #d9f010;
    transform: translateY(-1px);
}

.bid-button:active:not(:disabled) {
    transform: translateY(1px);
}

.bid-button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.bid-button--cooldown {
    background: var(--color-amber);
    border-color: var(--color-amber);
    color: var(--color-ink);
}

.bid-button--processing {
    background: var(--color-sage-dark);
    border-color: var(--color-sage-dark);
    color: var(--color-sage-light);
}

.bid-policy-notice {
    font-size: 0.7rem;
    color: var(--color-sage-dark);
    text-align: center;
    line-height: 1.4;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

.bid-policy-icon {
    font-size: 0.85rem;
    flex-shrink: 0;
}
</style>
