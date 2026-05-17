<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({ layout: null });

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Forgot Password" />

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-surface px-6 py-12">
        <!-- Background blobs -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -left-32 h-[500px] w-[500px] rounded-full bg-navy/20 blur-[140px]" />
            <div class="absolute bottom-0 -right-24 h-80 w-80 rounded-full bg-forest/20 blur-[100px]" />
        </div>

        <div class="relative z-10 w-full max-w-md">
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <Link href="/">
                    <img src="/logo.png" alt="CarryGo" class="h-12 w-auto" />
                </Link>
            </div>

            <div
                class="rounded-2xl border border-outline-variant/20 bg-surface-container-lowest px-8 py-10 shadow-[0_20px_60px_rgba(13,27,42,0.10)]">
                <!-- Icon -->
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-navy/10">
                    <span class="pi pi-lock text-3xl text-navy"></span>
                </div>

                <h1 class="mb-2 text-center font-condensed text-3xl font-black tracking-tight text-ink">
                    Forgot password?
                </h1>
                <p class="mb-8 text-center text-sm leading-relaxed text-outline">
                    No worries. Enter your email and we'll send you a link to reset your password.
                </p>

                <!-- Success message -->
                <div v-if="status"
                    class="mb-6 rounded-lg bg-forest/10 px-4 py-3 text-center text-sm font-medium text-forest">
                    <span class="pi pi-check-circle mr-1.5"></span>
                    {{ status }}
                </div>

                <Form :action="email.url()" method="post" v-slot="{ errors, processing }">
                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label for="email"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase">
                                Email Address
                            </label>
                            <input id="email" name="email" type="email" inputmode="email" autocomplete="email" required
                                autofocus
                                class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                placeholder="you@example.com" />
                            <p v-if="errors.email" class="mt-1 text-xs font-medium text-error">{{ errors.email }}</p>
                        </div>

                        <button type="submit" :disabled="processing"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-navy px-4 py-3.5 text-sm font-extrabold text-lemon shadow-lg shadow-navy/20 transition-all hover:bg-forest active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60">
                            <span v-if="processing" class="pi pi-spinner animate-spin text-base"></span>
                            <span>{{ processing ? 'Sending…' : 'Send Reset Link' }}</span>
                        </button>
                    </div>
                </Form>

                <p class="mt-7 text-center text-sm text-outline">
                    Remember your password?
                    <Link :href="login()" class="font-bold text-forest hover:underline">Back to login</Link>
                </p>
            </div>
        </div>
    </div>
</template>
