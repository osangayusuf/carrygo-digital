<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { logout as logoutRoute } from '@/routes';
import { send as verificationSend } from '@/routes/verification';
defineOptions({ layout: null });
</script>

<template>
    <Head title="Verify Email - Bidora Support" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-surface px-6 py-12"
    >
        <!-- Abstract gradient backgrounds -->
        <div class="pointer-events-none absolute inset-0">
            <div
                class="absolute -top-32 -left-32 h-[500px] w-[500px] rounded-full bg-navy/20 blur-[140px]"
            />
            <div
                class="absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-forest/20 blur-[100px]"
            />
        </div>

        <div class="relative z-10 w-full max-w-md text-center">
            <!-- Brand -->
            <div class="mb-8 flex flex-col items-center justify-center">
                <img
                    :src="`${$page.props.asset_url}logo.png`"
                    alt="Bidora"
                    class="mb-2 h-12 w-auto"
                />
                <span
                    class="rounded border border-secondary/20 bg-secondary-container/60 px-2.5 py-1 text-xs font-semibold tracking-wider text-on-secondary-container uppercase"
                >
                    Support Agent Portal
                </span>
            </div>

            <div
                class="rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-8 shadow-[0_20px_60px_rgba(13,27,42,0.10)]"
            >
                <!-- Mail Icon -->
                <div
                    class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full border border-secondary/20 bg-secondary-container text-on-secondary-container"
                >
                    <svg
                        class="h-8 w-8 text-primary"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
                        />
                    </svg>
                </div>

                <h1
                    class="mb-2 font-condensed text-3xl font-black tracking-tight text-primary uppercase"
                >
                    Verify Your Email
                </h1>
                <p class="mb-6 text-sm leading-relaxed text-on-surface-variant">
                    Thanks for signing up! Before getting started, could you
                    verify your email address by clicking on the link we just
                    emailed to you?
                </p>

                <!-- Resend Form -->
                <Form
                    :action="verificationSend.url()"
                    method="post"
                    v-slot="{ processing, wasSuccessful }"
                >
                    <div class="space-y-4">
                        <div
                            v-if="wasSuccessful"
                            class="rounded-lg border border-secondary/20 bg-secondary-container/40 px-4 py-3 text-sm font-medium text-on-secondary-container"
                        >
                            A new verification link has been sent to the email
                            address you provided during registration.
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg bg-navy px-4 py-3.5 text-sm font-extrabold text-lemon shadow-lg shadow-navy/20 transition-all hover:bg-forest active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span
                                v-if="processing"
                                class="pi pi-spinner animate-spin text-base"
                            ></span>
                            <span>{{
                                processing
                                    ? 'Resending...'
                                    : 'Resend Verification Email'
                            }}</span>
                        </button>

                        <Link
                            :href="logoutRoute.url()"
                            method="post"
                            as="button"
                            class="w-full cursor-pointer rounded-lg border border-outline-variant/60 bg-surface-container-lowest py-2.5 text-center text-sm font-semibold text-on-surface-variant transition-all hover:bg-surface-container-low hover:text-primary"
                        >
                            Log Out
                        </Link>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>
