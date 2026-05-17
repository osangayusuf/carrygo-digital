<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { eventItems, howToPlay, leaderboard, openBids, profile, tasks, trending, winners } from '@/routes/index';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="bg-[#050e05] text-[#8aaa80] mt-7">
        <div class="max-w-[1300px] mx-auto pt-10 px-4 pb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1.5fr] gap-8">
            <div>
                <div class="h-12 w-auto mb-2">
                    <img :src="`${$page.props.asset_url}logo.png`" alt="CarryGo" class="h-full w-auto" />
                </div>
                <div class="text-sm text-[#4a6a40] leading-relaxed mb-3.5">
                    Redefining the digital auction experience through transparency, curation, and elite service. Your gateway to exclusive opportunities.
                </div>
                <div class="flex gap-2.5">
                    <a
                        href="https://www.instagram.com/carrygo_nigeria?igsh=MTdiNHM2ZTJldnp1cA%3D%3D&utm_source=qr"
                        target="_blank"
                        class="w-9 h-9 rounded-full bg-lemon/10 flex items-center justify-center text-[15px] cursor-pointer border border-lemon/20 hover:bg-forest transition-colors text-lemon"
                    >
                        <span class="pi pi-instagram"></span>
                    </a>
                    <a
                        href="https://www.facebook.com/share/18TeWUCG19/"
                        target="_blank"
                        class="w-9 h-9 rounded-full bg-lemon/10 flex items-center justify-center text-[15px] cursor-pointer border border-lemon/20 hover:bg-forest transition-colors text-lemon"
                    >
                        <span class="pi pi-facebook"></span>
                    </a>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-extrabold text-lemon mb-3.5 uppercase tracking-wider">Auctions</h4>
                <Link :href="trending.url()" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">Trending Now</Link>
                <Link :href="openBids.url()" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">Open Bids</Link>
                <Link :href="eventItems.url()" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">Event Items</Link>
                <Link :href="leaderboard.url()" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">Leaderboard</Link>
                <Link :href="howToPlay.url()" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">How to Play</Link>
            </div>
            <div>
                <h4 class="text-sm font-extrabold text-lemon mb-3.5 uppercase tracking-wider">Account &amp; Support</h4>
                <Link :href="profile.url()" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">My Profile</Link>
                <Link :href="tasks.url()" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">Task Center</Link>
                <Link :href="winners.url()" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">Winning History</Link>
                <a href="#" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">Privacy Policy</a>
                <a href="#" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">Terms of Service</a>
                <a href="#" class="block text-[#4a6a40] text-sm mb-2 hover:text-lemon transition-colors">Contact Support</a>
            </div>
        </div>
        <div class="border-t border-[#0f2010] p-4 max-w-[1300px] mx-auto flex flex-col md:flex-row justify-between items-center text-xs text-[#2a4030] gap-4">
            <span>© {{ year }} CarryGo Executive. All rights reserved.</span>
            <div class="flex gap-4">
                <a href="#" class="text-[#2a4030] hover:text-lemon transition-colors">Cookie Settings</a>
                <a href="#" class="text-[#2a4030] hover:text-lemon transition-colors">Security</a>
                <a href="#" class="text-[#2a4030] hover:text-lemon transition-colors">Privacy</a>
            </div>
        </div>
    </footer>
</template>
