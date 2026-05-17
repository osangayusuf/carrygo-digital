<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { store } from '@/routes/register';
import { login } from '@/routes';

const showPassword = ref(false);
const showConfirmPassword = ref(false);

defineOptions({ layout: null });
</script>

<template>
    <Head title="Create Account" />

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
                <h1 class="mb-1 text-center font-condensed text-3xl font-black tracking-tight text-ink">
                    Create your account
                </h1>
                <p class="mb-8 text-center text-sm text-outline">
                    Join thousands of bidders and win luxury items
                </p>

                <Form :action="store.url()" method="post"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }">
                    <div class="space-y-5">
                        <!-- Full Name -->
                        <div class="space-y-1.5">
                            <label for="name"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase">
                                Full Name
                            </label>
                            <input id="name" name="name" type="text" autocomplete="name" required autofocus
                                class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                placeholder="John Doe" />
                            <p v-if="errors.name" class="mt-1 text-xs font-medium text-error">{{ errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label for="email"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase">
                                Email Address
                            </label>
                            <input id="email" name="email" type="email" inputmode="email" autocomplete="email" required
                                class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                placeholder="you@example.com" />
                            <p v-if="errors.email" class="mt-1 text-xs font-medium text-error">{{ errors.email }}</p>
                        </div>

                        <!-- Phone (MSISDN) -->
                        <div class="space-y-1.5">
                            <label for="phone"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase">
                                Phone Number (MSISDN)
                            </label>
                            <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required
                                class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                placeholder="e.g. 08031234567 or 2348031234567" />
                            <p v-if="errors.phone" class="mt-1 text-xs font-medium text-error">{{ errors.phone }}</p>
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5">
                            <label for="password"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase">
                                Password
                            </label>
                            <div class="relative">
                                <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                                    autocomplete="new-password" required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 pr-12 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                    placeholder="Min. 8 characters" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-outline hover:text-ink transition-colors"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'">
                                    <span :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                        class="text-base"></span>
                                </button>
                            </div>
                            <p v-if="errors.password" class="mt-1 text-xs font-medium text-error">
                                {{ errors.password }}
                            </p>
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-1.5">
                            <label for="password_confirmation"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase">
                                Confirm Password
                            </label>
                            <div class="relative">
                                <input id="password_confirmation" name="password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'" autocomplete="new-password"
                                    required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 pr-12 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                    placeholder="Re-enter your password" />
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-outline hover:text-ink transition-colors"
                                    :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'">
                                    <span :class="showConfirmPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                        class="text-base"></span>
                                </button>
                            </div>
                            <p v-if="errors.password_confirmation" class="mt-1 text-xs font-medium text-error">
                                {{ errors.password_confirmation }}
                            </p>
                        </div>

                        <!-- Submit -->
                        <button type="submit" :disabled="processing"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-navy px-4 py-3.5 text-sm font-extrabold text-lemon shadow-lg shadow-navy/20 transition-all hover:bg-forest active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60">
                            <span v-if="processing" class="pi pi-spinner animate-spin text-base"></span>
                            <span>{{ processing ? 'Creating account…' : 'Create Account' }}</span>
                        </button>
                    </div>
                </Form>

                <!-- Login link -->
                <p class="mt-7 text-center text-sm text-outline">
                    Already have an account?
                    <Link :href="login()" class="font-bold text-forest hover:underline">
                        Log in
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
