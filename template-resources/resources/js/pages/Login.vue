<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { store as loginStore } from '@/routes/login';
</script>

<template>

    <Head title="Login" />

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-surface px-6 py-12">
        <div class="pointer-events-none absolute inset-0 opacity-40">
            <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-secondary-container blur-[120px]" />
            <div class="absolute top-1/2 -right-24 h-64 w-64 rounded-full bg-primary-fixed blur-[100px]" />
        </div>

        <div class="relative z-10 w-full max-w-md">
            <div
                class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest px-8 py-10 shadow-[0_20px_40px_rgba(0,104,118,0.06)]">
                <h1 class="mb-2 text-center font-headline text-3xl font-extrabold tracking-tight text-primary">
                    Welcome back
                </h1>
                <p class="mb-8 text-center text-sm text-primary">
                    Enter your MSISDN to continue
                </p>

                <Form :action="loginStore.url()" method="post" class="space-y-6"
                    #default="{ errors, processing, wasSuccessful }">
                    <div class="space-y-2">
                        <label for="msisdn" class="ml-1 block text-xs font-bold tracking-widest text-primary">
                            MSISDN
                        </label>
                        <input id="msisdn" name="msisdn" type="tel" inputmode="tel" autocomplete="tel" required
                            class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-4 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/60 focus:ring-2 focus:ring-primary-container focus:outline-none"
                            placeholder="e.g. 0803xxxxxxx or 234803xxxxxxx" />
                        <p v-if="errors.msisdn" class="mt-1 text-xs text-error">
                            {{ errors.msisdn }}
                        </p>
                    </div>

                    <button type="submit"
                        class="flex w-full items-center justify-center rounded-lg bg-linear-to-r from-primary to-primary-container px-4 py-4 text-sm font-bold text-on-primary shadow-lg shadow-primary/15 transition-all hover:opacity-90 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="processing">
                        <span v-if="processing">Logging in...</span>
                        <span v-else>Log In</span>
                    </button>

                    <p v-if="wasSuccessful" class="text-success text-center text-xs">
                        Login successful. Redirecting...
                    </p>

                    <div class="mt-4 text-center text-xs text-primary">
                        Not subscribed yet?
                        <a href="tel:*20790#" class="font-medium text-secondary underline-offset-2 hover:underline">
                            Dial *20790# to subscribe
                        </a>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>
