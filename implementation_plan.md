# Implementation Plan: CarryGo/Bidora Standalone User Page Review

This updated implementation plan details the steps required to execute the remaining enhancements, UI/UX changes, and feature optimizations for the **Bidora** platform environment.

---

## Proposed Changes

### 1. Core Platform & Rebranding Copy
* **Files to Modify:**
  * [HomeController.php](file:///Users/osanga/Herd/carrygo-digital/app/Http/Controllers/HomeController.php)
* **Files to Create:**
  * [FeaturedAuctionsCarousel.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/home/FeaturedAuctionsCarousel.vue) [NEW]
* **Changes:**
  * Standardize the Bidora branding across copy (excluding the slogan which is already updated).
  * Build a new, dedicated [FeaturedAuctionsCarousel.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/home/FeaturedAuctionsCarousel.vue) component on the homepage to dynamically showcase premium items currently open for auction, featured auctions, and limited-time offers. Leave the hero section carousel as-is.

---

### 2. Homepage & Navigation Refinements
* **Files to Create:**
  * [MobileBottomNav.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/layout/MobileBottomNav.vue) [NEW]
* **Files to Modify:**
  * [PublicLayout.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/layouts/PublicLayout.vue)
  * [AppSidebarLayout.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/layouts/app/AppSidebarLayout.vue)
  * [AppNavbar.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/AppNavbar.vue)
  * [Home/Index.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/pages/Home/Index.vue)
  * [HomeWinnersSection.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/home/HomeWinnersSection.vue)
* **Changes:**
  * **Contact Support Link:** Add a highly visible "Contact Support" button to the top navbar header of `AppNavbar.vue` that opens the live chat widget on click by dispatching a custom window event (`open-support-chat`).
  * **Mobile bottom toolbar:** Integrate `MobileBottomNav.vue` rendering sticky options for: *Home, Trending, Support, and My Profile* on mobile screens. Add to both public and app layouts.
  * **Scroll-to-Top:** Add a floating scroll-to-top action button in `PublicLayout.vue` that fades in when scroll position is greater than 300px and scrolls smoothly to the top.
  * **Trending Section Restructure:** Change homepage trending bids display from 10 to a maximum of 8 items (`props.trendingBids.slice(0, 8)`). Add a prominent, styled "View More Trending Items" button beneath the card grid.
  * **Winners & Social Proof Carousel:** Restructure the horizontal track in `HomeWinnersSection.vue` into a responsive auto-sliding carousel that showcases won item thumbnails, masked phone numbers, points spent, and original market prices.
  * **Ad Slots:** Add e-commerce style advertising slots (Jumia/Konga style grids) on the homepage layout to feature promos like "Points Booster" and "Referral Program".

---

### 3. Product Discovery & Auction Dynamics
* **Files to Modify:**
  * [HandleInertiaRequests.php](file:///Users/osanga/Herd/carrygo-digital/app/Http/Middleware/HandleInertiaRequests.php)
  * [AppNavbar.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/AppNavbar.vue)
  * [TrendingBidsSection.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/trending/TrendingBidsSection.vue)
  * [AuctionListingService.php](file:///Users/osanga/Herd/carrygo-digital/app/Services/AuctionListingService.php)
  * [BidCard.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/cards/BidCard.vue)
  * [Show.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/pages/Auction/Show.vue)
* **Changes:**
  * **Countdown Ubiquity:** Share `triggeredAuctions` (auctions in the triggered countdown phase) globally via `HandleInertiaRequests.php`. If countdowns are active, display a persistent top notification banner on all layout headers showing the active stopwatch and a link to bid.
  * **Closing Soon Filters:** Add a `closing_soon` sorting algorithm to `AuctionListingService.php` that ranks triggered (countdown) auctions first. Expose this filter on the list page (`TrendingBidsSection.vue`) as both a sort dropdown item and a prominent quick-toggle button ("Closing Soon ⏳") next to the filters drawer.
  * **Excitement Indicators:**
    * Add a blinking `🚨 Closing Soon` badge for triggered items on `BidCard.vue` and `Show.vue`.
    * Display `🔥 Hot Bid` badges on items with high engagement.
    * Implement a live-looking `"X people bidding now"` counter with a pulsing green indicator on both card tiles and detail pages.

---

### 4. Feature Logic & Rules Optimization
* **Files to Modify:**
  * [ProfileController.php](file:///Users/osanga/Herd/carrygo-digital/app/Http/Controllers/ProfileController.php)
  * [Profile/Index.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/pages/Profile/Index.vue)
  * [SettingsShell.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/settings/SettingsShell.vue)
  * [WalletDepositForm.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/wallet/WalletDepositForm.vue)
  * [PlaceBidModal.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/modals/PlaceBidModal.vue)
  * [BidInterface.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/BidInterface.vue)
  * [RewardsService.php](file:///Users/osanga/Herd/carrygo-digital/app/Services/RewardsService.php)
  * [rewards.php](file:///Users/osanga/Herd/carrygo-digital/config/rewards.php)
* **Files to Create:**
  * [ProfileLeaderboard.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/pages/Profile/Leaderboard.vue) [NEW]
* **Changes:**
  * **Prize Claim Status:**
    * Unauthenticated: Display general prize claim/delivery workflow steps on the homepage.
    * Authenticated: Fetch user's won auctions in `ProfileController.php`. Display a tracking pipeline (Winner Verification -> Processing -> Shipped -> Delivered) on the Profile settings view and at the top of the homepage for active winners.
  * **Financial Policies:** Append the explicit policy text: *"Points bidden cannot be refunded and wallet credits are non-withdrawable"* on the purchase checkout form (`WalletDepositForm.vue`) and bidding forms (`PlaceBidModal.vue`, `BidInterface.vue`).
  * **Spin & Win Limit:** Modify `grantDailySpins` in `RewardsService.php` to set `spins_balance = 1` for users with less than 1 spin, rather than incrementing.
  * **Flexible Leaderboard Configuration:**
    * Change `weekly_leaderboard.top_ranks` to scale up to 10 in `config/rewards.php`. Map bonus points dynamically for ranks 1 through 10.
    * Add a weekly leaderboard tab to settings (`SettingsShell.vue`) that routes to a new sub-page `ProfileLeaderboard.vue` to show top bidders.

---

### 5. Visitor Firewall & Gated Access (Conversion Strategy)
* **Files to Modify:**
  * [web.php](file:///Users/osanga/Herd/carrygo-digital/routes/web.php)
  * [Register.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/pages/auth/Register.vue)
* **Changes:**
  * **Route Gating:** Move `winners`, `leaderboard`, and `recommended` routes into the `auth` middleware group in `web.php` to completely restrict guest views.
  * **Conversion CTA Banner:** When guests are redirected to the registration page, display a prominent marketing card at the top: *"Register to enjoy spectacular items! Bid small, win big."*

---

### 6. Accessibility & Semantic Fixes
* **Files to Modify:**
  * [AppNavbar.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/AppNavbar.vue)
  * [AppSidebar.vue](file:///Users/osanga/Herd/carrygo-digital/resources/js/components/AppSidebar.vue)
* **Changes:**
  * Add descriptive `aria-label` attributes to icons that lack textual sibling content (e.g. navigation links, buttons).
  * Enforce focus rings (`focus:ring-2 focus:ring-primary focus:outline-none`) on all buttons, forms, and interactive inputs.
  * Add custom tooltips (native HTML `title` attributes and title descriptions) to navigation and toolbar controls to state their actions upon hover or focus.

---

## Verification Plan

### Automated Tests
Run target feature tests for existing and modified routes to ensure zero regressions:
```bash
php artisan test --compact --filter=TrendingControllerTest
php artisan test --compact --filter=WinnersControllerTest
php artisan test --compact --filter=LeaderboardControllerTest
php artisan test --compact --filter=RewardsServiceTest
```

### Manual Verification
1. Verify the mobile bottom toolbar appears and clicks route correctly.
2. Confirm clicking the top-arrow smoothly scrolls to the top of the homepage.
3. Test that accessing `/winners` or `/recommended` as a guest redirects to `/register` with the highlighted registration CTA.
4. Perform daily spin wheel grant mock run to verify users do not accumulate more than 1 spin.
5. Check visual layouts for mobile and desktop screens to confirm proper spacing.
