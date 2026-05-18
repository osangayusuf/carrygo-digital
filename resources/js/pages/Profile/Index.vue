<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import SettingsPanel from '@/components/settings/SettingsPanel.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';
import SettingsTextField from '@/components/settings/SettingsTextField.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

defineOptions({ layout: PublicLayout });

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Manage Profile" />

    <SettingsShell>
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

                <div v-if="mustVerifyEmail && !user?.email_verified_at" class="rounded-lg border border-outline-variant bg-surface-container-low p-4 text-sm text-on-surface-variant">
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
                        A new verification link has been sent to your email address.
                    </p>
                </div>

                <div class="mt-6 flex justify-end border-t border-outline-variant pt-6">
                    <button
                        type="submit"
                        :disabled="processing"
                        data-test="update-profile-button"
                        class="flex items-center gap-2 rounded-lg bg-primary px-8 py-3 text-xs font-bold text-on-primary shadow-md transition-all hover:bg-tertiary-container hover:shadow-lg disabled:opacity-60"
                    >
                        <span class="material-symbols-outlined text-sm">save</span>
                        Save Changes
                    </button>
                </div>
            </Form>
        </SettingsPanel>
    </SettingsShell>
</template>
