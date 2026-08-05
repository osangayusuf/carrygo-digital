<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { home } from '@/routes';
import { login } from '@/routes/support';
import { store as registerStore } from '@/routes/support/register';

const showPassword = ref(false);
defineOptions({ layout: null });
</script>

<template>
    <Head title="Agent Registration - Bidora Support" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-y-auto bg-surface px-6 py-12"
    >
        <!-- Abstract gradient backgrounds -->
        <div class="pointer-events-none absolute inset-0">
            <div
                class="absolute -top-32 -left-32 h-125 w-125 rounded-full bg-navy/20 blur-[140px]"
            />
            <div
                class="absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-forest/20 blur-[100px]"
            />
        </div>

        <div class="relative z-10 my-8 w-full max-w-lg">
            <!-- Logo / Brand -->
            <div class="mb-8 flex flex-col items-center justify-center">
                <Link :href="home.url()">
                    <img
                        :src="`${$page.props.asset_url}logo.png`"
                        alt="Bidora"
                        class="mb-2 h-12 w-auto"
                    />
                </Link>
                <span
                    class="rounded border border-secondary/20 bg-secondary-container/60 px-2.5 py-1 text-xs font-semibold tracking-wider text-on-secondary-container uppercase"
                >
                    Support Agent Portal
                </span>
            </div>

            <div
                class="rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-8 shadow-[0_20px_60px_rgba(13,27,42,0.10)]"
            >
                <h1
                    class="mb-1 text-center font-condensed text-3xl font-black tracking-tight text-primary uppercase"
                >
                    Agent Registration
                </h1>
                <p class="mb-8 text-center text-sm text-on-surface-variant">
                    Register your credentials for admin review and approval
                </p>

                <Form
                    :action="registerStore.url()"
                    method="post"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-5">
                        <!-- Name -->
                        <div class="space-y-1.5">
                            <label
                                for="name"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-on-surface-variant uppercase"
                            >
                                Full Name
                            </label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                autocomplete="name"
                                required
                                autofocus
                                class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-secondary/40 focus:outline-none"
                                placeholder="John Doe"
                            />
                            <p
                                v-if="errors.name"
                                class="mt-1 text-xs font-medium text-error"
                            >
                                {{ errors.name }}
                            </p>
                        </div>

                        <!-- Email & Phone Grid -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Email -->
                            <div class="space-y-1.5">
                                <label
                                    for="email"
                                    class="ml-0.5 block text-xs font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    Email Address
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-secondary/40 focus:outline-none"
                                    placeholder="you@bidora.com"
                                />
                                <p
                                    v-if="errors.email"
                                    class="mt-1 text-xs font-medium text-error"
                                >
                                    {{ errors.email }}
                                </p>
                            </div>

                            <!-- Phone -->
                            <div class="space-y-1.5">
                                <label
                                    for="phone"
                                    class="ml-0.5 block text-xs font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    Phone Number
                                </label>
                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    autocomplete="tel"
                                    required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-secondary/40 focus:outline-none"
                                    placeholder="08031234567"
                                />
                                <p
                                    v-if="errors.phone"
                                    class="mt-1 text-xs font-medium text-error"
                                >
                                    {{ errors.phone }}
                                </p>
                            </div>
                        </div>

                        <!-- Department & Employee ID Grid -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Department -->
                            <div class="space-y-1.5">
                                <label
                                    for="department"
                                    class="ml-0.5 block text-xs font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    Department
                                </label>
                                <input
                                    id="department"
                                    name="department"
                                    type="text"
                                    required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-secondary/40 focus:outline-none"
                                    placeholder="Customer Operations"
                                />
                                <p
                                    v-if="errors.department"
                                    class="mt-1 text-xs font-medium text-error"
                                >
                                    {{ errors.department }}
                                </p>
                            </div>

                            <!-- Employee ID -->
                            <div class="space-y-1.5">
                                <label
                                    for="employee_id"
                                    class="ml-0.5 block text-xs font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    Employee ID
                                </label>
                                <input
                                    id="employee_id"
                                    name="employee_id"
                                    type="text"
                                    required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-secondary/40 focus:outline-none"
                                    placeholder="EMP-00123"
                                />
                                <p
                                    v-if="errors.employee_id"
                                    class="mt-1 text-xs font-medium text-error"
                                >
                                    {{ errors.employee_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Password & Confirmation Grid -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Password -->
                            <div class="space-y-1.5">
                                <label
                                    for="password"
                                    class="ml-0.5 block text-xs font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    Password
                                </label>
                                <div class="relative">
                                    <input
                                        id="password"
                                        name="password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        required
                                        class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 pr-12 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-secondary/40 focus:outline-none"
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center px-4 text-outline transition-colors hover:text-ink"
                                    >
                                        <span v-if="showPassword">Hide</span>
                                        <span v-else>Show</span>
                                    </button>
                                </div>
                                <p
                                    v-if="errors.password"
                                    class="mt-1 text-xs font-medium text-error"
                                >
                                    {{ errors.password }}
                                </p>
                            </div>

                            <!-- Password Confirmation -->
                            <div class="space-y-1.5">
                                <label
                                    for="password_confirmation"
                                    class="ml-0.5 block text-xs font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    Confirm Password
                                </label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-secondary/40 focus:outline-none"
                                    placeholder="••••••••"
                                />
                                <p
                                    v-if="errors.password_confirmation"
                                    class="mt-1 text-xs font-medium text-error"
                                >
                                    {{ errors.password_confirmation }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="mt-4 flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg bg-navy px-4 py-3.5 text-sm font-extrabold text-lemon shadow-lg shadow-navy/20 transition-all hover:bg-forest active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span
                                v-if="processing"
                                class="pi pi-spinner animate-spin text-base"
                            ></span>
                            <span>{{
                                processing ? 'Registering...' : 'Register'
                            }}</span>
                        </button>
                    </div>
                </Form>

                <div class="mt-6 text-center text-xs text-on-surface-variant">
                    Already registered?
                    <Link
                        :href="login.url()"
                        class="font-bold text-primary hover:underline"
                    >
                        Sign in to Portal
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
