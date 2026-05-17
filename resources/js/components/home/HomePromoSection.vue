<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { tasks } from '@/routes/index';
</script>

<template>
    <section class="mx-auto max-w-screen-2xl px-8 py-12">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="group relative overflow-hidden rounded-[2.5rem] bg-linear-to-br from-primary to-primary-container p-12 text-white md:col-span-2">
                <div class="relative z-10">
                    <h2 class="font-headline mb-4 text-4xl leading-tight font-extrabold">Win More<br />Points.</h2>
                    <p class="mb-8 max-w-md text-primary-fixed">Visit the interactive task center to complete tasks and win points.</p>
                    <Link :href="tasks.url()" class="rounded-xl bg-white px-8 py-3.5 font-bold text-primary transition-all hover:bg-primary-fixed">
                        Visit task center
                    </Link>
                </div>
                <div class="absolute -right-20 -bottom-20 h-80 w-80 rounded-full bg-white/10 blur-[60px] transition-transform duration-1000 group-hover:scale-150" />
                <span class="material-symbols-outlined absolute top-12 right-12 rotate-12 text-[120px] opacity-10 transition-transform duration-1000 group-hover:rotate-0">
                    workspace_premium
                </span>
            </div>
            <div class="flex flex-col justify-between rounded-[2.5rem] border border-outline-variant/30 bg-surface-container-high p-12">
                <span class="material-symbols-outlined text-5xl text-primary">verified_user</span>
                <div>
                    <h3 class="font-headline mb-2 text-2xl font-extrabold">Verified Integrity</h3>
                    <p class="text-sm font-medium text-secondary">
                        Every bid is recorded on a secure database to ensure 100% platform fairness.
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
