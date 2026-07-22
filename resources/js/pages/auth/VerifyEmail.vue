<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({ layout: null });

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Verify Email" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-surface px-6 py-12"
    >
        <!-- Background blobs -->
        <div class="pointer-events-none absolute inset-0">
            <div
                class="absolute -top-32 -left-32 h-[500px] w-[500px] rounded-full bg-navy/20 blur-[140px]"
            />
            <div
                class="absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-forest/20 blur-[100px]"
            />
        </div>

        <div class="relative z-10 w-full max-w-md">
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <Link href="/">
                    <img
                        :src="`${$page.props.asset_url}logo.png`"
                        alt="Bidora"
                        class="h-12 w-auto"
                    />
                </Link>
            </div>

            <div
                class="rounded-2xl border border-outline-variant/20 bg-surface-container-lowest px-8 py-10 text-center shadow-[0_20px_60px_rgba(13,27,42,0.10)]"
            >
                <!-- Icon -->
                <div
                    class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-forest/10"
                >
                    <span class="pi pi-envelope text-3xl text-forest"></span>
                </div>

                <h1
                    class="mb-2 font-condensed text-3xl font-black tracking-tight text-ink"
                >
                    Verify your email
                </h1>
                <p class="mb-6 text-sm leading-relaxed text-outline">
                    Thanks for signing up! Please verify your email address by
                    clicking the link we sent to you. Didn't receive it? Request
                    a new one below.
                </p>

                <!-- Success message -->
                <div
                    v-if="status === 'verification-link-sent'"
                    class="mb-6 rounded-lg bg-forest/10 px-4 py-3 text-sm font-medium text-forest"
                >
                    <span class="pi pi-check-circle mr-1.5"></span>
                    A new verification link has been sent to your email address.
                </div>

                <Form
                    :action="send.url()"
                    method="post"
                    v-slot="{ processing }"
                >
                    <button
                        type="submit"
                        :disabled="processing"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-navy px-4 py-3.5 text-sm font-extrabold text-lemon shadow-lg shadow-navy/20 transition-all hover:bg-forest active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span
                            v-if="processing"
                            class="pi pi-spinner animate-spin text-base"
                        ></span>
                        <span>{{
                            processing
                                ? 'Sending…'
                                : 'Resend Verification Email'
                        }}</span>
                    </button>
                </Form>

                <Link
                    :href="logout()"
                    as="button"
                    class="mt-5 block text-sm text-outline transition-colors hover:text-ink"
                >
                    Log out instead
                </Link>
            </div>
        </div>
    </div>
</template>
