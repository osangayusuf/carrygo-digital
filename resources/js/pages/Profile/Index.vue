<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import SettingsPanel from '@/components/settings/SettingsPanel.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';
import SettingsTextField from '@/components/settings/SettingsTextField.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { send } from '@/routes/verification';

type WonAuction = {
    id: number;
    name: string;
    image: string | null;
    price: string;
    updated_at: string;
};

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
    wonAuctions: WonAuction[];
};

defineProps<Props>();

defineOptions({ layout: PublicLayout });

const page = usePage();
const user = computed(() => page.props.auth.user);

const claimSteps = [
    {
        key: 'verification',
        label: 'Winner Verification',
        icon: 'verified_user',
    },
    { key: 'processing', label: 'Processing', icon: 'inventory_2' },
    { key: 'shipped', label: 'Shipped', icon: 'local_shipping' },
    { key: 'delivered', label: 'Delivered', icon: 'check_circle' },
] as const;
</script>

<template>
    <Head title="Manage Profile" />

    <SettingsShell>
        <!-- Prize Claim Pipeline for active winners -->
        <div
            v-if="wonAuctions.length > 0"
            class="mb-8 rounded-xl border border-secondary/30 bg-secondary-container/20 p-5"
        >
            <div class="mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary"
                    >emoji_events</span
                >
                <h3 class="font-semibold text-on-surface">Your Won Auctions</h3>
            </div>

            <div class="space-y-6">
                <div
                    v-for="auction in wonAuctions"
                    :key="auction.id"
                    class="rounded-lg border border-outline-variant bg-surface-container-lowest p-4"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <img
                            v-if="auction.image"
                            :src="auction.image"
                            :alt="auction.name"
                            class="h-12 w-12 rounded-lg object-cover"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-on-surface">
                                {{ auction.name }}
                            </p>
                            <p class="text-xs text-on-surface-variant">
                                Market value: ₦{{
                                    Number(auction.price).toLocaleString()
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Tracking pipeline -->
                    <div class="flex items-center justify-between gap-1">
                        <template
                            v-for="(step, idx) in claimSteps"
                            :key="step.key"
                        >
                            <div
                                class="flex flex-col items-center gap-1 text-center"
                            >
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full border-2 transition-colors"
                                    :class="
                                        idx === 0
                                            ? 'border-secondary bg-secondary text-on-secondary'
                                            : 'border-outline-variant bg-surface-container text-on-surface-variant'
                                    "
                                >
                                    <span
                                        class="material-symbols-outlined text-[18px]"
                                        >{{ step.icon }}</span
                                    >
                                </div>
                                <span
                                    class="max-w-[64px] text-[10px] leading-tight text-on-surface-variant"
                                >
                                    {{ step.label }}
                                </span>
                            </div>
                            <div
                                v-if="idx < claimSteps.length - 1"
                                class="h-0.5 flex-1 rounded bg-outline-variant"
                            ></div>
                        </template>
                    </div>

                    <p class="mt-3 text-xs text-on-surface-variant">
                        Contact support with your registered phone number to
                        track your prize delivery.
                    </p>
                </div>
            </div>
        </div>

        <SettingsPanel
            title="Manage your profile"
            description="Update your personal details and how we can reach you."
        >
            <Form
                v-bind="ProfileController.update.form()"
                class="max-w-2xl space-y-6"
                v-slot="{ errors, processing }"
            >
                <SettingsTextField
                    id="name"
                    name="name"
                    label="Name"
                    icon="badge"
                    placeholder="Enter your full name"
                    :default-value="user?.name"
                    autocomplete="name"
                    required
                    :error="errors.name"
                />

                <SettingsTextField
                    id="email"
                    name="email"
                    type="email"
                    label="Email"
                    icon="mail"
                    placeholder="Enter your email address"
                    :default-value="user?.email"
                    autocomplete="username"
                    required
                    :error="errors.email"
                    hint="This email will be used for auction notifications."
                />

                <SettingsTextField
                    id="phone"
                    name="phone"
                    type="tel"
                    label="Phone (MSISDN)"
                    icon="phone_iphone"
                    placeholder="e.g. 08031234567 or 2348031234567"
                    :default-value="user?.phone"
                    autocomplete="tel"
                    inputmode="tel"
                    required
                    :error="errors.phone"
                    hint="Winners are contacted through this number. Keep it up to date."
                />

                <div
                    v-if="mustVerifyEmail && !user?.email_verified_at"
                    class="rounded-lg border border-outline-variant bg-surface-container-low p-4 text-sm text-on-surface-variant"
                >
                    <p>
                        Your email address is unverified.
                        <Link
                            :href="send()"
                            as="button"
                            class="font-semibold text-on-surface underline underline-offset-4"
                        >
                            Click here to resend the verification email.
                        </Link>
                    </p>
                    <p
                        v-if="status === 'verification-link-sent'"
                        class="mt-2 font-medium text-forest"
                    >
                        A new verification link has been sent to your email
                        address.
                    </p>
                </div>

                <div
                    class="mt-6 flex justify-end border-t border-outline-variant pt-6"
                >
                    <button
                        type="submit"
                        :disabled="processing"
                        data-test="update-profile-button"
                        class="flex items-center gap-2 rounded-lg bg-primary px-8 py-3 text-xs font-bold text-on-primary shadow-md transition-all hover:bg-tertiary-container hover:shadow-lg disabled:opacity-60"
                    >
                        <span class="material-symbols-outlined text-sm"
                            >save</span
                        >
                        Save Changes
                    </button>
                </div>
            </Form>
        </SettingsPanel>
    </SettingsShell>
</template>
