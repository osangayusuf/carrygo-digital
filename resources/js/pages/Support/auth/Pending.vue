<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { logout as logoutRoute } from '@/routes';
defineOptions({ layout: null });
</script>

<template>
    <Head title="Approval Pending - Bidora Support" />

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
                <!-- Clock / Hourglass Icon -->
                <div
                    class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full border border-amber/30 bg-amber/15 text-primary"
                >
                    <svg
                        class="h-8 w-8 animate-pulse text-primary"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>

                <h1
                    class="mb-2 font-condensed text-3xl font-black tracking-tight text-primary uppercase"
                >
                    Approval Pending
                </h1>
                <p class="mb-6 text-sm leading-relaxed text-on-surface-variant">
                    Thank you for registering. Your agent account is currently
                    pending administrator review and approval.
                </p>

                <div
                    class="mb-6 space-y-2 rounded-lg border border-outline-variant/60 bg-surface-container-low p-4 text-left text-xs text-on-surface-variant"
                >
                    <p
                        class="text-[10px] font-bold tracking-wider text-primary uppercase"
                    >
                        Next Steps:
                    </p>
                    <ul class="list-disc space-y-1 pl-4">
                        <li>Ensure you have verified your email address.</li>
                        <li>
                            An administrator will review your department and
                            Employee ID.
                        </li>
                        <li>
                            You will receive an email once your account is
                            activated.
                        </li>
                    </ul>
                </div>

                <div class="flex flex-col gap-3">
                    <Link
                        :href="logoutRoute.url()"
                        method="post"
                        as="button"
                        class="w-full cursor-pointer rounded-lg border border-outline-variant/60 bg-surface-container-lowest py-2.5 text-center text-sm font-semibold text-on-surface-variant transition-all hover:bg-surface-container-low hover:text-primary"
                    >
                        Sign Out
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
