<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { store } from '@/routes/login';
import { register } from '@/routes';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
    canRegister?: boolean;
}>();

const showPassword = ref(false);

defineOptions({ layout: null });
</script>

<template>
    <Head title="Log In" />

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
                <!-- Status message (e.g. password reset success) -->
                <div v-if="status" class="mb-6 rounded-lg bg-forest/10 px-4 py-3 text-center text-sm font-medium text-forest">
                    {{ status }}
                </div>

                <h1 class="mb-1 text-center font-condensed text-3xl font-black tracking-tight text-ink">
                    Welcome back
                </h1>
                <p class="mb-8 text-center text-sm text-outline">
                    Enter your credentials to continue bidding
                </p>

                <Form :action="store.url()" method="post" :reset-on-success="['password']"
                    v-slot="{ errors, processing }">
                    <div class="space-y-5">
                        <!-- Email -->
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

                        <!-- Password -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label for="password"
                                    class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase">
                                    Password
                                </label>
                                <Link v-if="canResetPassword" :href="request.url()"
                                    class="text-xs font-medium text-forest hover:underline">
                                    Forgot password?
                                </Link>
                            </div>
                            <div class="relative">
                                <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password" required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 pr-12 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                    placeholder="••••••••" />
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

                        <!-- Remember me -->
                        <div class="flex items-center gap-3">
                            <input id="remember" name="remember" type="checkbox"
                                class="h-4 w-4 rounded border-sage-border text-forest accent-forest focus:ring-forest" />
                            <label for="remember" class="text-sm text-outline cursor-pointer">Remember me</label>
                        </div>

                        <!-- Submit -->
                        <button type="submit" :disabled="processing"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-navy px-4 py-3.5 text-sm font-extrabold text-lemon shadow-lg shadow-navy/20 transition-all hover:bg-forest active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60">
                            <span v-if="processing" class="pi pi-spinner animate-spin text-base"></span>
                            <span>{{ processing ? 'Logging in…' : 'Log In' }}</span>
                        </button>
                    </div>
                </Form>

                <!-- Register link -->
                <p v-if="canRegister !== false" class="mt-7 text-center text-sm text-outline">
                    Don't have an account?
                    <Link :href="register()" class="font-bold text-forest hover:underline">
                        Create one
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
