<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { trending, openBids, eventItems, winners, profile, wallet, tasks, login, register, howToPlay } from '@/routes/index';
</script>

<template>
    <footer class="bg-forest text-white py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-1 md:col-span-1">
                    <h2 class="text-2xl font-bold text-lemon mb-4">Carrygo</h2>
                    <p class="text-sm text-gray-300">
                        Nigeria's premier digital e-auction platform.
                    </p>
                </div>

                <!-- Auctions -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Auctions</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li>
                            <Link :href="trending.url()" class="hover:text-lemon transition-colors">Trending
                            </Link>
                        </li>
                        <li>
                            <Link :href="openBids.url()" class="hover:text-lemon transition-colors">Open Bids
                            </Link>
                        </li>
                        <li>
                            <Link :href="eventItems.url()" class="hover:text-lemon transition-colors">Event
                                Items</Link>
                        </li>
                        <li>
                            <Link :href="winners.url()" class="hover:text-lemon transition-colors">Winners</Link>
                        </li>
                    </ul>
                </div>

                <!-- Account -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Account</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <template v-if="$page.props.auth.user">
                            <li>
                                <Link :href="profile.url()" class="hover:text-lemon transition-colors">My Profile
                                </Link>
                            </li>
                            <li>
                                <Link :href="wallet.url()" class="hover:text-lemon transition-colors">Wallet
                                </Link>
                            </li>
                            <li>
                                <Link :href="tasks.url()" class="hover:text-lemon transition-colors">Tasks</Link>
                            </li>
                        </template>
                        <template v-else>
                            <li>
                                <Link :href="login.url()" class="hover:text-lemon transition-colors">Log In
                                </Link>
                            </li>
                            <li>
                                <Link :href="register.url()" class="hover:text-lemon transition-colors">Sign Up
                                </Link>
                            </li>
                        </template>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Support</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li>
                            <Link :href="howToPlay.url()" class="hover:text-lemon transition-colors">How to
                                Play</Link>
                        </li>
                        <li><a href="#" class="hover:text-lemon transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-lemon transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-lemon transition-colors">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-700 text-center text-sm text-gray-400">
                &copy; {{ new Date().getFullYear() }} Carrygo Digital. All rights reserved.
            </div>
        </div>
    </footer>
</template>
