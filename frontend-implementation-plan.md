# Step 6: Frontend UI/UX — Implementation Plan

## Overview

Build the complete user-facing frontend for the Carrygo Digital E-Auction Platform. Stack: Vue 3 + Inertia.js v3 + Tailwind CSS v4. Design follows the **light-mode** reference screenshots — Forest/Navy top bar, white content area, Lemon (`#C8E000`) CTAs. Points always displayed as `pts`.

---

## Confirmed Decisions

- **Categories** — Queried via `SELECT DISTINCT category FROM auctions` (no separate model)
- **`event` field** — Migration adds `event boolean default false` to `auctions`; Event Items page will be built as a shell now, full mechanics later
- **Tasks page** — UI shell only; backend mechanics (streaks, spin, referrals) deferred

---

## Design System Reference

| Token | Value | Usage |
|---|---|---|
| Nav background | `#1A472A` Forest | Top bar + nav bar |
| Page background | `#F5F5F5` | All content areas |
| Card background | `#FFFFFF` | Auction cards |
| Primary CTA | `#C8E000` Lemon (black text) | Bid buttons, "Start Bidding" |
| Secondary CTA | `#1A472A` Forest (white text) | Outline buttons |
| LIVE badge | `#C8E000` Lemon | Active auction label |
| Countdown accent | `#F5E642` Amber | Timer, triggered state |
| Card border | `#DDE8CC` Sage Border | Card outlines |
| Progress fill | `#1A472A` Forest | Threshold progress bar |
| Points format | `20,000 pts` | All point values |

---

## Navigation

### Top Bar
- Logo (left) → links to `/`
- Search bar (center) → opens `SearchModal.vue`
- Notification bell with unread badge (auth only)
- `Log In` button / user avatar + balance (authenticated)

### Nav Bar
**Desktop:** `Home | Trending | Open Bids | Event Items | Winners | Wallet | Leaderboard | How to Play`
**Mobile:** Two-row layout (already in `AppNavbar.vue`), hidden second row by default

### Category Sidebar
- Desktop homepage only; hidden on all other pages and all mobile viewports
- Populated by polling `DISTINCT category` from active auctions (60s interval)
- Clicking a category filters the homepage grid

---

## Step-by-Step Execution Plan

---

### Step 1 — Schema Migration

**Goal:** Add `event` boolean to `auctions` table.

- Create migration: `add_event_to_auctions_table`
  - `$table->boolean('event')->default(false)->after('status');`
- Update `Auction` model: add `event` to `$fillable` and `$casts`
- Run migration
- **Test:** Feature test confirming `auctions` table has `event` column

---

### Step 2 — Eloquent API Resources

**Goal:** Typed, consistent data shapes for all Inertia props.

- `AuctionResource` — `id, name, category, image, status, event, current_points, bid_count, opening_points, expires_at, winner_id, created_at`
- `BidResource` — `id, amount, user_name, created_at`
- `PointTransactionResource` — `id, type, amount, naira_amount, exchange_rate, status, created_at`
- `LeaderboardEntryResource` — `rank, user_name, total_pts_spent, wins_count`
- `WinnerAuctionResource` — extends `AuctionResource` + `winner_name, winning_pts, total_pts_bid, won_at`

---

### Step 3 — Controllers & Routes

**Goal:** All page routes registered and controllers scaffolded (thin — no business logic).

**Public routes:**
```
GET /                        HomeController@index         home
GET /trending                TrendingController@index     trending
GET /open-bids               OpenBidsController@index     open-bids
GET /event-items             EventItemsController@index   event-items
GET /winners                 WinnersController@index      winners
GET /leaderboard             LeaderboardController@index  leaderboard
GET /how-to-play             HowToPlayController@index    how-to-play
GET /auctions/{auction}      AuctionController@show       auctions.show
GET /search                  SearchController@index       search (API)
```

**Auth-required routes:**
```
GET  /wallet                 WalletController@index       wallet
POST /wallet/deposit         WalletController@deposit     wallet.deposit
POST /wallet/claim-bonus     WalletController@claimBonus  wallet.claim-bonus
GET  /tasks                  TaskController@index         tasks
GET  /profile                ProfileController@index      profile
```

- All controllers created via `php artisan make:controller`
- **Test:** Feature tests asserting each public route returns 200, each auth route redirects unauthenticated users to login

---

### Step 4 — Public Layout & Navbar

**Goal:** Shell that wraps every public-facing page.

**Files:**
- **[NEW]** `layouts/PublicLayout.vue` — slots for `<AppNavbar>` + `<AppFooter>` + `<LiveTicker>`
- **[MODIFY]** `components/AppNavbar.vue` — update nav links to match final list, add `NotificationBell`, points balance display for auth users
- **[NEW]** `components/AppFooter.vue` — links: Auctions section, Account & Support section (matching screenshot footer)
- **[NEW]** `components/LiveTicker.vue` — scrolling top bar showing triggered auctions; polls `/search?status=triggered` every 15s

---

### Step 5 — Shared Auction Components

**Goal:** Reusable components used across multiple pages.

- **[NEW]** `components/auction/AuctionCard.vue`
  - Image, title, category, current pts, bid count, progress bar, status badge, "Place Bid" button
  - Shows `CountdownBadge` when `status === 'triggered'`
  - Lemon "Place Bid" CTA → navigates to `/auctions/{id}`

- **[NEW]** `components/auction/AuctionGrid.vue`
  - Responsive grid: 2 cols (mobile) → 3 cols (tablet) → 4 cols (desktop)
  - Accepts `auctions` prop + pagination
  - Renders `AuctionCard` per item

- **[NEW]** `components/auction/StatusBadge.vue` — LIVE / TRIGGERED / CLOSED pill

- **[NEW]** `components/auction/ProgressBar.vue` — Threshold fill bar (`current_points / opening_points`)

- **[NEW]** `components/auction/CountdownBadge.vue` — Amber pill showing live countdown (uses `expires_at`)

- **[NEW]** `components/PointsBadge.vue` — Formats number as `"20,000 pts"`

- **[NEW]** `components/EmptyState.vue` — Reusable empty state with icon + message slot

- **[NEW]** `components/SearchModal.vue` — Full-screen overlay, debounced search input, polls results

- **[NEW]** `components/NotificationBell.vue` — Bell icon with unread count; polls `/api/notifications` every 30s

---

### Step 6 — Home Page

**Route:** `GET /`
**File:** `pages/Home/Index.vue`
**Layout:** `PublicLayout`

**Sections:**
1. **Hero** — Forest dark card: headline, "Start Bidding" + "How It Works" CTAs, 3 stats
2. **Promo Carousel** — Auto-slide with arrows + dots
3. **Category Sidebar** (desktop) + **Auction Grid** — side-by-side layout

**Backend props:**
- `liveAuctions` (deferred) — `active` + `triggered`, paginated 20
- `categories` (deferred) — `DISTINCT category` from active auctions
- `stats` — winners count, total bids

**Test:** Confirms page loads, returns `liveAuctions` and `categories` in props

---

### Step 7 — Trending Page

**Route:** `GET /trending`
**File:** `pages/Trending/Index.vue`
**Layout:** `PublicLayout`

- Full-width `AuctionGrid` (no sidebar)
- Ordered by `bid_count DESC`
- Sort dropdown (Most Bids, Most Recent Activity, Highest Points)
- Result count display: "895 results"
- Paginated

**Test:** Returns auctions ordered by `bid_count DESC`

---

### Step 8 — Open Bids Page

**Route:** `GET /open-bids`
**File:** `pages/OpenBids/Index.vue`
**Layout:** `PublicLayout`

- Only `triggered` auctions
- `CountdownBadge` shown prominently on each card
- Empty state: "No auctions are currently in countdown phase."

**Test:** Only returns auctions with `status = triggered`

---

### Step 9 — Event Items Page (Shell)

**Route:** `GET /event-items`
**File:** `pages/EventItems/Index.vue`
**Layout:** `PublicLayout`

- Filters auctions where `event = true`
- Same `AuctionGrid` component
- Empty state: "No event items are currently live."
- *(Full event management mechanics deferred to a later step)*

**Test:** Only returns auctions where `event = true`

---

### Step 10 — Winners Page

**Route:** `GET /winners`
**File:** `pages/Winners/Index.vue`
**Layout:** `PublicLayout`

Winner cards show:
- "WON BID" badge on image
- Item name + winning pts
- Winner's real name
- Winning pts, total pts bid on auction, bid count
- Days ago won
- "View Auction" link

**Backend props:** `winners` (deferred) — `closed` auctions with `winner` relation loaded, paginated 20

**Test:** Only returns auctions with `status = closed` and a `winner_id`

---

### Step 11 — Auction Detail Page

**Route:** `GET /auctions/{auction}`
**File:** `pages/Auctions/Show.vue`
**Layout:** `PublicLayout`

Two-column layout (desktop): details left, bid interface right.

**Sections:**
1. Full-size item image + `StatusBadge`
2. Name, category, description
3. Stats row: current pts, bid count, opening pts threshold
4. `ProgressBar` (hidden once `triggered`)
5. `AuctionTimer` — Amber countdown from `expires_at` (Step 5 component)
6. **Current Winning User** — Name of user on the `is_winning = true` bid
7. **Live Bid Feed** — scrollable list, updated via `useAuctionStore` Echo events
8. `BidInterface` — Points input + Lemon "Place Bid" + 5s cooldown UX (Step 5 component)
9. `ShareButton` — copy URL / native share API

**Backend props:**
- `auction` — `AuctionResource`
- `currentWinner` — name of user with `is_winning = true` bid
- `recentBids` (deferred) — last 20 bids via `BidResource`
- `myBids` — auth user's bids on this auction (empty array for guests)

**Test:** Returns correct auction; guests see page; unauthenticated bid attempt redirects to login

---

### Step 12 — Leaderboard Page

**Route:** `GET /leaderboard`
**File:** `pages/Leaderboard/Index.vue`
**Layout:** `PublicLayout`

Ranked table of top users by total `bid_debit` points spent.

| Rank | User | Pts Spent | Items Won |
|---|---|---|---|
| 🥇 1 | John D. | 450,000 pts | 12 |

- Top 3 get gold/silver/bronze treatment
- Paginated (top 50 shown)

**Backend:** Query `point_transactions` grouped by `user_id` where `type = bid_debit`, SUM amount, JOIN users

**Test:** Returns users ordered by total pts spent DESC

---

### Step 13 — Wallet Page

**Route:** `GET /wallet`
**File:** `pages/Wallet/Index.vue`
**Layout:** `AppLayout` (auth)

**Sections:**
1. **Balance Card** — `points_balance` + `bonus_points` claimable
2. **Claim Bonus** — Button → `POST /wallet/claim-bonus`; disabled if `bonus_points = 0`
3. **Buy Points** — Naira input → live pts preview (using `POINTS_PER_NAIRA` config) → "Pay with Paystack" button
4. **Transaction History** — `PointTransactionResource` table with type badge (deposit/bid_debit/bonus_award/bonus_claim), amount, date, status pill

**Test:** Auth required; balance and transactions returned in props

---

### Step 14 — Tasks Page (Shell)

**Route:** `GET /tasks`
**File:** `pages/Tasks/Index.vue`
**Layout:** `AppLayout` (auth)

UI shells with placeholder states for:
1. **Daily Check-In** — Calendar/streak UI, "Claim Today" CTA (disabled — backend TBD)
2. **Spin to Win** — Spin wheel illustration + "Spin" CTA (disabled — backend TBD)
3. **Referral Program** — Referral link copy input, stats (signups: 0, pts earned: 0 — backend TBD)

Each section shows a "Coming Soon" badge until mechanics are wired.

**Test:** Auth required; page loads without errors

---

### Step 15 — Profile Page

**Route:** `GET /profile`
**File:** `pages/Profile/Index.vue`
**Layout:** `AppLayout` (auth)

**Sections:**
1. **Header** — Avatar initials, name, email, phone, member since
2. **Points Summary** — `points_balance` + `bonus_points`, link to Wallet
3. **Bid History Tab** — All user bids grouped by auction; shows auction name, pts spent, date
4. **Win History Tab** — Closed auctions user won; image, name, pts, date
5. **Transactions Tab** — Full `PointTransaction` list (same data as Wallet page)

**Test:** Auth required; correct user data in props; no cross-user data leakage

---

### Step 16 — How to Play Page

**Route:** `GET /how-to-play`
**File:** `pages/HowToPlay/Index.vue`
**Layout:** `PublicLayout`

Fully static. Four illustrated steps:
1. **Subscribe** — Create a free account
2. **Fund Wallet** — Buy points via Paystack
3. **Bid** — Place bids on live auctions (pts consumed, no refunds)
4. **Win** — Highest cumulative bid total when countdown hits 0 wins the item

No backend queries. No test required beyond route returning 200.

---

## Files Summary

### New Pages (11)
```
pages/Home/Index.vue
pages/Trending/Index.vue
pages/OpenBids/Index.vue
pages/EventItems/Index.vue
pages/Winners/Index.vue
pages/Auctions/Show.vue
pages/Leaderboard/Index.vue
pages/Wallet/Index.vue
pages/Tasks/Index.vue
pages/Profile/Index.vue
pages/HowToPlay/Index.vue
```

### New Layouts (1)
```
layouts/PublicLayout.vue
```

### New Components (14)
```
components/AppFooter.vue
components/LiveTicker.vue
components/NotificationBell.vue
components/SearchModal.vue
components/PointsBadge.vue
components/EmptyState.vue
components/ShareButton.vue
components/auction/AuctionCard.vue
components/auction/AuctionGrid.vue
components/auction/StatusBadge.vue
components/auction/ProgressBar.vue
components/auction/CountdownBadge.vue
```

### New Controllers (11)
```
HomeController, TrendingController, OpenBidsController,
EventItemsController, WinnersController, LeaderboardController,
HowToPlayController, AuctionController, SearchController,
WalletController, TaskController, ProfileController
```

### New Resources (5)
```
AuctionResource, BidResource, PointTransactionResource,
LeaderboardEntryResource, WinnerAuctionResource
```

### New Migration (1)
```
add_event_to_auctions_table
```
