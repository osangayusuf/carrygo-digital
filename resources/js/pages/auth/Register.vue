<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { login, home } from '@/routes';
import { redirect } from '@/routes/auth/social';
import { store } from '@/routes/register';

defineProps<{
    redirected?: boolean;
}>();

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const referralCode = ref('');

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const refParam = params.get('ref');

    if (refParam) {
        referralCode.value = refParam;
    }
});

defineOptions({ layout: null });
</script>

<template>
    <Head title="Create Account" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-surface px-6 py-12"
    >
        <!-- Background blobs -->
        <div class="pointer-events-none absolute inset-0">
            <div
                class="absolute -top-32 -left-32 h-125 w-125 rounded-full bg-navy/20 blur-[140px]"
            />
            <div
                class="absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-forest/20 blur-[100px]"
            />
        </div>

        <div class="relative z-10 w-full max-w-md">
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <Link :href="home.url()">
                    <img
                        :src="`${$page.props.asset_url}logo.png`"
                        alt="Bidora"
                        class="h-12 w-auto"
                    />
                </Link>
            </div>

            <div
                class="rounded-2xl border border-outline-variant/20 bg-surface-container-lowest px-8 py-10 shadow-[0_20px_60px_rgba(13,27,42,0.10)]"
            >
                <!-- Marketing CTA Banner (Shown when redirected) -->
                <div
                    v-if="redirected"
                    class="mb-6 overflow-hidden rounded-xl border border-forest/30 bg-gradient-to-br from-forest/10 to-navy/10 p-4 shadow-[0_8px_32px_rgba(40,167,69,0.05)] backdrop-blur-md"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-forest/20 text-forest"
                        >
                            <span
                                class="pi pi-gift animate-pulse text-base"
                            ></span>
                        </div>
                        <div class="space-y-0.5">
                            <h3
                                class="font-condensed text-sm font-bold tracking-tight text-ink"
                            >
                                Exclusive Gated Content
                            </h3>
                            <p class="text-xs leading-relaxed text-outline">
                                Register to enjoy spectacular items! Bid small,
                                win big.
                            </p>
                        </div>
                    </div>
                </div>

                <h1
                    class="mb-1 text-center font-condensed text-3xl font-black tracking-tight text-ink"
                >
                    Create your account
                </h1>
                <p
                    v-if="!redirected"
                    class="mb-8 text-center text-sm text-outline"
                >
                    Register to enjoy spectacular items! Bid small, win big.
                </p>
                <div v-else class="mb-8"></div>

                <Form
                    :action="store.url()"
                    method="post"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-5">
                        <!-- Full Name -->
                        <div class="space-y-1.5">
                            <label
                                for="name"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase"
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
                                class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                placeholder="John Doe"
                            />
                            <p
                                v-if="errors.name"
                                class="mt-1 text-xs font-medium text-error"
                            >
                                {{ errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label
                                for="email"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase"
                            >
                                Email Address
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                inputmode="email"
                                autocomplete="email"
                                required
                                class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                placeholder="you@example.com"
                            />
                            <p
                                v-if="errors.email"
                                class="mt-1 text-xs font-medium text-error"
                            >
                                {{ errors.email }}
                            </p>
                        </div>

                        <!-- Phone (MSISDN) -->
                        <div class="space-y-1.5">
                            <label
                                for="phone"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase"
                            >
                                Phone Number (MSISDN)
                            </label>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                inputmode="tel"
                                autocomplete="tel"
                                required
                                class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                placeholder="e.g. 08031234567 or 2348031234567"
                            />
                            <p
                                v-if="errors.phone"
                                class="mt-1 text-xs font-medium text-error"
                            >
                                {{ errors.phone }}
                            </p>
                        </div>

                        <!-- Password -->
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
                                    autocomplete="new-password"
                                    required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 pr-12 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                    placeholder="Min. 8 characters"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-outline transition-colors hover:text-ink"
                                    :aria-label="
                                        showPassword
                                            ? 'Hide password'
                                            : 'Show password'
                                    "
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

                        <!-- Confirm Password -->
                        <div class="space-y-1.5">
                            <label
                                for="password_confirmation"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase"
                            >
                                Confirm Password
                            </label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    :type="
                                        showConfirmPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    autocomplete="new-password"
                                    required
                                    class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 pr-12 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                    placeholder="Re-enter your password"
                                />
                                <button
                                    type="button"
                                    @click="
                                        showConfirmPassword =
                                            !showConfirmPassword
                                    "
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-outline transition-colors hover:text-ink"
                                    :aria-label="
                                        showConfirmPassword
                                            ? 'Hide password'
                                            : 'Show password'
                                    "
                                >
                                    <span
                                        :class="
                                            showConfirmPassword
                                                ? 'pi pi-eye-slash'
                                                : 'pi pi-eye'
                                        "
                                        class="text-base"
                                    ></span>
                                </button>
                            </div>
                            <p
                                v-if="errors.password_confirmation"
                                class="mt-1 text-xs font-medium text-error"
                            >
                                {{ errors.password_confirmation }}
                            </p>
                        </div>

                        <!-- Referral Code -->
                        <div class="space-y-1.5">
                            <label
                                for="referral_code"
                                class="ml-0.5 block text-xs font-bold tracking-widest text-ink/70 uppercase"
                            >
                                Referral Code (Optional)
                            </label>
                            <input
                                id="referral_code"
                                name="referral_code"
                                type="text"
                                v-model="referralCode"
                                class="block w-full rounded-lg border-none bg-surface-container-low px-5 py-3.5 text-sm text-on-surface shadow-sm transition-all placeholder:text-outline/50 focus:ring-2 focus:ring-forest/40 focus:outline-none"
                                placeholder="e.g. CARRY123"
                            />
                            <p
                                v-if="errors.referral_code"
                                class="mt-1 text-xs font-medium text-error"
                            >
                                {{ errors.referral_code }}
                            </p>
                        </div>

                        <!-- Submit -->
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
                                    ? 'Creating account…'
                                    : 'Create Account'
                            }}</span>
                        </button>
                    </div>
                </Form>

                <!-- Social Auth Buttons -->
                <div class="mt-6">
                    <div class="relative mb-6 flex items-center justify-center">
                        <div class="absolute inset-0 flex items-center">
                            <div
                                class="w-full border-t border-outline-variant/30"
                            ></div>
                        </div>
                        <span
                            class="relative bg-surface-container-lowest px-3 text-xs font-bold tracking-widest text-outline uppercase"
                        >
                            or continue with
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <!-- Google Signup -->
                        <a
                            :href="redirect.url('google')"
                            class="flex items-center justify-center gap-2.5 rounded-lg border border-outline-variant/40 bg-surface-container-low px-4 py-3 text-sm font-bold text-ink shadow-sm transition-all hover:border-outline-variant/80 hover:bg-surface-container-high active:scale-[0.98]"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                width="24"
                                height="24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M21.35,11.1H12v2.7h5.38c-0.24,1.28 -0.96,2.37 -2.04,3.1v2.58h3.3c1.93,-1.78 3.04,-4.4 3.04,-7.48c0,-0.6 -0.05,-1.17 -0.15,-1.7c-0.12,-0.4 -0.12,-0.4 -0.18,-0.8z"
                                    fill="#4285F4"
                                />
                                <path
                                    d="M12,20.6c2.43,0 4.47,-0.8 5.96,-2.18l-3.3,-2.58c-0.9,0.6 -2.07,0.98 -3.3,0.98c-2.34,0 -4.33,-1.58 -5.04,-3.7H2.9v2.66c1.5,2.98 4.6,4.98 8.16,4.98c0.3,0 0.64,-0.03 0.94,-0.16z"
                                    fill="#34A853"
                                />
                                <path
                                    d="M6.96,13.12c-0.18,-0.54 -0.28,-1.1 -0.28,-1.68s0.1,-1.14 0.28,-1.68V7.1H2.9c-0.6,1.2 -0.9,2.56 -0.9,3.9c0,1.43 0.3,2.78 0.9,3.98l4.06,-3.2c0,-0.22 0,-0.44 0,-0.66z"
                                    fill="#FBBC05"
                                />
                                <path
                                    d="M12,6.18c1.3,0 2.48,0.45 3.4,1.33l2.55,-2.55C16.43,3.58 14.4,2.9 12,2.9c-3.56,0 -6.66,2 -8.16,4.98l4.06,3.12c0.7,-2.12 2.7,-3.72 5.04,-3.72c0.3,0 0.74,0.02 1.06,0.1z"
                                    fill="#EA4335"
                                />
                            </svg>
                            <span>Google</span>
                        </a>

                        <!-- Facebook Signup -->
                        <a
                            :href="redirect.url('facebook')"
                            class="flex items-center justify-center gap-2.5 rounded-lg border border-outline-variant/40 bg-surface-container-low px-4 py-3 text-sm font-bold text-ink shadow-sm transition-all hover:border-outline-variant/80 hover:bg-surface-container-high active:scale-[0.98]"
                        >
                            <svg
                                class="h-4 w-4 fill-[#1877F2]"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                                />
                            </svg>
                            <span>Facebook</span>
                        </a>
                    </div>
                </div>

                <!-- Login link -->
                <p class="mt-7 text-center text-sm text-outline">
                    Already have an account?
                    <Link
                        :href="login.url() + (redirected ? '?redirected=1' : '')"
                        class="font-bold text-forest hover:underline"
                    >
                        Log in
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
