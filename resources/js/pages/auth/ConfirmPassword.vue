<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { store } from '@/routes/password/confirm';

defineOptions({ layout: null });

const page = usePage();
const showPassword = ref(false);
</script>

<template>
    <Head title="Confirm password" />

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
                        :src="`${page.props.asset_url}logo.png`"
                        alt="Bidora"
                        class="h-12 w-auto"
                    />
                </Link>
            </div>

            <div
                class="rounded-2xl border border-outline-variant/20 bg-surface-container-lowest px-8 py-10 shadow-[0_20px_60px_rgba(13,27,42,0.10)]"
            >
                <!-- Icon -->
                <div
                    class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-navy/10"
                >
                    <span class="pi pi-shield text-3xl text-navy"></span>
                </div>

                <h1
                    class="mb-2 text-center font-condensed text-3xl font-black tracking-tight text-ink"
                >
                    Confirm your password
                </h1>
                <p
                    class="mb-8 text-center text-sm leading-relaxed text-outline"
                >
                    This is a secure area. Please enter your password before
                    continuing.
                </p>

                <Form
                    v-bind="store.form()"
                    reset-on-success
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label
                                for="password"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase"
                            >
                                Password
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    name="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    required
                                    autofocus
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 pr-12 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-outline transition-colors hover:text-ink"
                                    :aria-label="
                                        showPassword
                                            ? 'Hide password'
                                            : 'Show password'
                                    "
                                    @click="showPassword = !showPassword"
                                >
                                    <span
                                        :class="
                                            showPassword
                                                ? 'pi pi-eye-slash'
                                                : 'pi pi-eye'
                                        "
                                        class="text-base"
                                    ></span>
                                </button>
                            </div>
                            <p
                                v-if="errors.password"
                                class="mt-1 text-xs font-medium text-error"
                            >
                                {{ errors.password }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            data-test="confirm-password-button"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-navy px-4 py-3.5 text-sm font-extrabold text-lemon shadow-lg shadow-navy/20 transition-all hover:bg-forest active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span
                                v-if="processing"
                                class="pi pi-spinner animate-spin text-base"
                            ></span>
                            <span>{{
                                processing ? 'Confirming…' : 'Confirm password'
                            }}</span>
                        </button>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>
