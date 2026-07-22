<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import SettingsPanel from '@/components/settings/SettingsPanel.vue';
import SettingsPasswordField from '@/components/settings/SettingsPasswordField.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { disable, enable } from '@/routes/two-factor';

type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

defineOptions({ layout: PublicLayout });

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref(false);

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <Head title="Security Settings" />

    <SettingsShell>
        <SettingsPanel
            title="Security Settings"
            description="Manage your password and security preferences."
        >
            <div class="max-w-3xl space-y-8">
                <Form
                    v-bind="SecurityController.update.form()"
                    :options="{ preserveScroll: true }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-on-surface">
                                Update password
                            </h3>
                            <p class="text-sm text-on-surface-variant">
                                Ensure your account is using a long, random
                                password to stay secure
                            </p>
                        </div>

                        <SettingsPasswordField
                            id="current_password"
                            name="current_password"
                            label="Current password"
                            placeholder="Current password"
                            autocomplete="current-password"
                            :error="errors.current_password"
                        />

                        <SettingsPasswordField
                            id="password"
                            name="password"
                            label="New password"
                            placeholder="New password"
                            autocomplete="new-password"
                            :error="errors.password"
                        />

                        <SettingsPasswordField
                            id="password_confirmation"
                            name="password_confirmation"
                            label="Confirm password"
                            placeholder="Confirm password"
                            autocomplete="new-password"
                            :error="errors.password_confirmation"
                        />

                        <button
                            type="submit"
                            :disabled="processing"
                            data-test="update-password-button"
                            class="rounded-lg bg-primary px-8 py-3 text-xs font-bold text-on-primary shadow-md transition-all hover:bg-tertiary-container hover:shadow-lg disabled:opacity-60"
                        >
                            Save password
                        </button>
                    </div>
                </Form>

                <div
                    v-if="canManageTwoFactor"
                    class="space-y-6 border-t border-outline-variant pt-8"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-on-surface">
                            Two-factor authentication
                        </h3>
                        <p class="text-sm text-on-surface-variant">
                            Manage your two-factor authentication settings
                        </p>
                    </div>

                    <div v-if="!twoFactorEnabled" class="space-y-4">
                        <p class="text-sm text-on-surface-variant">
                            When you enable two-factor authentication, you will
                            be prompted for a secure pin during login. This pin
                            can be retrieved from a TOTP-supported application
                            on your phone.
                        </p>

                        <button
                            v-if="hasSetupData"
                            type="button"
                            class="rounded-lg bg-primary px-8 py-3 text-xs font-bold text-on-primary shadow-md transition-all hover:bg-tertiary-container hover:shadow-lg"
                            @click="showSetupModal = true"
                        >
                            Continue setup
                        </button>
                        <Form
                            v-else
                            v-bind="enable.form()"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <button
                                type="submit"
                                :disabled="processing"
                                class="rounded-lg bg-primary px-8 py-3 text-xs font-bold text-on-primary shadow-md transition-all hover:bg-tertiary-container hover:shadow-lg disabled:opacity-60"
                            >
                                Enable 2FA
                            </button>
                        </Form>
                    </div>

                    <div v-else class="space-y-4">
                        <p class="text-sm text-on-surface-variant">
                            You will be prompted for a secure, random pin during
                            login, which you can retrieve from the
                            TOTP-supported application on your phone.
                        </p>

                        <Form v-bind="disable.form()" #default="{ processing }">
                            <button
                                type="submit"
                                :disabled="processing"
                                class="rounded-lg border border-error px-8 py-3 text-xs font-bold text-error transition-colors hover:bg-error-container disabled:opacity-60"
                            >
                                Disable 2FA
                            </button>
                        </Form>

                        <TwoFactorRecoveryCodes />
                    </div>

                    <TwoFactorSetupModal
                        v-model:isOpen="showSetupModal"
                        :requires-confirmation="requiresConfirmation"
                        :two-factor-enabled="twoFactorEnabled"
                    />
                </div>
            </div>
        </SettingsPanel>
    </SettingsShell>
</template>
