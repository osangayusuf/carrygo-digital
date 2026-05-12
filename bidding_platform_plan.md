# Blueprint: Carrygo Digital E-Auction Platform

## 1. Project Overview
A high-concurrency "Forward Auction" platform where users purchase points via Paystack and bid on items.
- **Conversion:** All Naira payments are automatically converted to points at a configurable rate (see `config/points.php`).
- **Bidding Phase 1 (Accumulation):** Bidding continues until the `opening_points` threshold is met.
- **Bidding Phase 2 (Countdown):** Once the threshold is met, a real-time timer triggers. The user with the highest **cumulative** bid total when the countdown reaches zero wins the item.
- **No Refunds:** Bid points are consumed on placement. The winner receives the item; no points are returned to any bidder.
- **Minimum Bid Per Placement:** Each individual bid must be at least **10 points** (configurable via `config/points.php`).
- **Bid Rate Limit:** A user may place at most **1 bid per 5 seconds** per auction (enforced at the HTTP layer via throttle middleware).
- **Winning Determination:** After every bid, each user's bids for the auction are summed. The user with the highest cumulative total is marked as winning. If two users share the same cumulative total, no one is marked as winning (all `is_winning = false`) until the tie is broken.
- **Tie-Breaking (bid-level):** If two bids are placed with the exact same `amount` at the same microsecond, the one with the earlier `created_at` (microsecond precision) is recorded first.

## 2. Technical Stack
- **Backend:** Laravel 13 (PHP 8.4+), Laravel Reverb (WebSockets), Spatie Permissions.
- **Frontend:** Vue 3 (Composition API), Pinia, Tailwind CSS.
- **Database:** MySQL 8.0+ (Primary), Redis (Caching & Session).
- **Queue Driver:** `database` (Laravel queued jobs via `jobs` table).
- **Payment:** Paystack Integration (webhook signature verification mandatory).

## 3. System Architecture & Model Schemas

### User Model
- `id`: bigIncremental
- `name`: string
- `email`: string (unique)
- `phone`: string (11-13 chars, unique)
- `points_balance`: decimal(15,2) (Default: 0.00)
- `bonus_points`: decimal(15,2) (Unclaimed/unactivated pool — not spendable until claimed)
- `email_verified_at`: timestamp
- `password`: string
- `remember_token`: string
- `timestamps`

### Auction Model
- `id`: bigIncremental
- `category`: string (Category name — no separate Category model)
- `name`: string (Item title)
- `description`: text
- `opening_points`: integer (Threshold to trigger countdown — compared against `current_points`)
- `current_points`: integer (**Sum of all bid amounts placed on this auction** — incremented by each bid's `amount`; triggers countdown when it crosses `opening_points`)
- `status`: enum (`draft`, `active`, `triggered`, `closed`)
- `image`: string (Storage URL — single image per auction, stored on server via Laravel storage)
- `bid_count`: integer (Atomic counter — reconciled periodically against actual `Bid` count)
- `countdown_duration_seconds`: integer (Duration of the triggered countdown, in seconds)
- `triggered_at`: datetime (Nullable — set when `current_points` first crosses `opening_points`)
- `expires_at`: datetime (Nullable — set at trigger time: `triggered_at + countdown_duration_seconds`)
- `winner_id`: foreignId (Nullable — references `users.id`, set on auction close)
- `timestamps`

### Bid Model
- `id`: bigIncremental
- `auction_id`: foreignId (constrained)
- `user_id`: foreignId (constrained)
- `amount`: integer (Points placed in this single bid placement)
- `is_winning`: boolean (Default: `false` — recalculated after every bid. Set to `true` on the **most recent bid** of the user whose cumulative total across all their bids on this auction is strictly highest. If two users share the same cumulative total, all `is_winning` remain `false` until the tie is broken.)
- `created_at`: timestamp(6) (Microsecond precision — used as tie-breaker for simultaneous placements)

### PointTransaction Model
- `id`: bigIncremental
- `user_id`: foreignId (constrained)
- `type`: enum (`deposit`, `bid_debit`, `bonus_award`, `bonus_claim`)
- `amount`: decimal(15,2)
- `naira_amount`: decimal(15,2) (Nullable — populated for `deposit` type only)
- `exchange_rate`: decimal(10,4) (Snapshot of rate at time of transaction)
- `provider_reference`: string (Nullable — Paystack transaction reference)
- `status`: enum (`pending`, `completed`, `failed`)
- `metadata`: json
- `timestamps`

### Notification Model (Laravel Default)
Use Laravel's built-in `notifications` table (polymorphic). All user-facing events are delivered via **both** the `database` and `mail` channels.

**Notifiable events:**

| Class | Trigger | Recipient | Channels |
| :--- | :--- | :--- | :--- |
| `AuctionWon` | Auction closes with a winner | Winner | `database`, `mail` |
| `AuctionTriggered` | `current_points` crosses `opening_points` | All bidders on the auction | `database`, `mail` |
| `BidPlaced` | A bid is successfully placed | Bidding user | `database` only |
| `PaymentConfirmed` | Paystack webhook verified, points credited | Depositing user | `database`, `mail` |
| `BonusPointsAwarded` | Bonus points added to account | Recipient user | `database` only |

## 4. Points & Bonus Points System

### Points (`points_balance`)
- Purchased via Paystack. Rate defined in `config/points.php` and `.env`.
- Spent on bids (debited immediately on bid placement). **No refunds.**
- Exchange rate: `POINTS_PER_NAIRA` (e.g., `100` points per ₦1).

### Bonus Points (`bonus_points`)
- Awarded via platform activities (referrals, tasks, spin-to-win).
- **Not spendable directly.** Must be explicitly claimed by the user.
- On claim, bonus points are converted to spendable `points_balance` at a configurable conversion rate.
- Default conversion: **1 bonus point = 1 spendable point** (configurable via `config/points.php`).
- A `bonus_claim` `PointTransaction` is recorded on each claim.

### `config/points.php`
```php
return [
    'points_per_naira'        => env('POINTS_PER_NAIRA', 100),
    'bonus_conversion_rate'   => env('BONUS_CONVERSION_RATE', 1.0), // bonus → spendable
    'min_bid_increment'       => env('MIN_BID_INCREMENT', 10),
];
```

### `.env` variables
```dotenv
POINTS_PER_NAIRA=100
BONUS_CONVERSION_RATE=1.0
MIN_BID_INCREMENT=10
```

## 5. Concurrency & Bid Placement Strategy

All bid placements run inside a **DB transaction with pessimistic locking** to prevent race conditions:

```
DB::transaction(function () use ($auctionId, $userId, $amount) {
    $auction = Auction::lockForUpdate()->findOrFail($auctionId);

    // 1. Validate auction status is 'active' or 'triggered'
    // 2. Validate $amount >= config('points.min_bid_increment')
    // 3. Validate user points_balance >= $amount
    // 4. Debit user points (UPDATE users SET points_balance = points_balance - $amount)
    // 5. Create Bid record with is_winning = false (recalculated next)
    // 6. Recalculate is_winning across all bids for this auction:
    //    a. GROUP BY user_id, SUM(amount) → get cumulative totals
    //    b. Find the MAX cumulative total
    //    c. Count how many users share that MAX
    //    d. If exactly one user → set is_winning = true on their most recent bid;
    //       set is_winning = false on all other bids
    //    e. If tie (count > 1) → set all is_winning = false
    // 7. Increment auction current_points += $amount; increment bid_count
    // 8. If current_points crosses opening_points for the first time:
    //    → Set status = 'triggered', triggered_at = now(), expires_at = now() + countdown_duration_seconds
    //    → Dispatch CloseAuctionJob with delay = countdown_duration_seconds
    // 9. Record PointTransaction (type = 'bid_debit')
    // 10. Dispatch BidPlaced notification (queued)
    // 11. If auction just triggered: dispatch AuctionTriggered notification to all bidders (queued)
});
```

**No anti-sniping / timer extension.** The countdown is fixed once triggered.

## 6. Auction Lifecycle & Closure

### Closure Strategy (Dual-layer)
1. **Primary:** When a bid triggers the countdown (`status = triggered`), a `CloseAuctionJob` is dispatched with a delay equal to `countdown_duration_seconds`. This closes the auction precisely on time.
2. **Fallback:** A scheduled task (`php artisan auctions:reconcile`) runs every minute to catch any missed closures and to reconcile `bid_count` against actual `Bid` count.

### `CloseAuctionJob` responsibilities:
- Set `auction.status = closed`.
- Set `auction.winner_id` to the `user_id` of the current `is_winning = true` bid.
- Send `AuctionWon` notification to the winner.
- Broadcast closure event on the public auction channel.

## 7. Authentication & Authorization

- **Auth Scaffold:** Laravel Fortify (default Vue + Laravel setup).
- **Package:** Spatie Laravel Permission.
- **Roles:** Three roles are defined:
  - `user` — Standard bidder. Can purchase points, place bids, claim bonus points.
  - `admin` — Full platform management. Can create/publish/close auctions, view all transactions, manage users.
  - `agent` — Customer support role. Scope to be defined separately (see Roles & Permissions Skill).

## 8. Paystack Webhook Security (Mandatory)

Every incoming Paystack webhook **must** be verified before processing:
1. Retrieve the raw request body (before JSON decoding).
2. Compute `hash_hmac('sha512', $rawBody, env('PAYSTACK_SECRET_KEY'))`.
3. Compare with the `x-paystack-signature` header.
4. If mismatch → return `403` immediately, do not process.

This logic belongs in `WalletService` (or a dedicated `PaystackWebhookMiddleware`).

## 9. WebSocket Broadcasting

- **Driver:** Laravel Reverb.
- **Auction channels:** Public channels (`auction.{id}`). No per-user auth required for bid/timer events.
- **Per-user events:** Delivered via the **`database` notifications channel** (not private broadcast channels). Vue frontend polls or listens to the notification endpoint.
- **Key broadcast events:**
  - `BidPlaced` — Broadcast on `auction.{id}` with new `current_points` and `bid_count`.
  - `AuctionTriggered` — Broadcast on `auction.{id}` with `expires_at`.
  - `AuctionClosed` — Broadcast on `auction.{id}` with `winner_id`.

## 10. UI Design DNA (Carrygo Industrial)

### Color Palette (Tailwind Configuration)
AI Agents must use these specific hex codes for all styling:

| Variable     | Hex Code  | Usage                                         |
| :----------- | :-------- | :-------------------------------------------- |
| Navy (Base)  | `#0D1B2A` | Primary Backgrounds / Dark Sections           |
| Ink          | `#0A0A0A` | Deepest Contrast / Card Backgrounds           |
| Lemon        | `#C8E000` | Primary CTA / Highlights / Active Bids        |
| Amber        | `#F5E642` | Secondary CTA / Warnings / Timer Accents      |
| Forest       | `#1A472A` | Success / Won Status / Active Indicators      |
| Forest Mid   | `#1A5C2A` | Secondary Success / Buttons                   |
| Sage Light   | `#EEF5E0` | Surface Elements / Secondary Text             |
| Sage Border  | `#DDE8CC` | Border Accents / Dividers                     |
| Sage Dark    | `#5A7A4A` | Muted Text / Icons                            |

### UI Philosophy
- **Industrial Look:** Use thick borders (`border-2` or `border-4`), high-contrast buttons, and heavy sans-serif fonts.
- **Micro-Interactions:** "Flash" effect on price updates using Lemon or Amber.

### Pinia Store Shape (`useAuctionStore`)
```js
{
  auction: {
    id, name, category, image,
    status,           // 'active' | 'triggered' | 'closed'
    current_points,
    bid_count,
    expires_at,       // ISO string from server — never calculated client-side
    winner_id,
  },
  myBids: [],         // Current user's bids on this auction
  lastBidAt: null,    // Timestamp of user's last bid (for 5s rate limit UX feedback)
  timerRemaining: 0,  // Derived: computed from expires_at in AuctionTimer.vue
}
```

## 11. AI Agent "Skill Kits" (.cursorrules)

**Persona 1: The Database Architect**
Specialized in Laravel 13 and MySQL. Ensure all point fields are `decimal(15,2)`. Prioritize strict foreign keys and index `status`, `expires_at`, and `user_id`. Use PHP 8.4 property hooks. The `image` field on Auction stores a **single storage URL string**. Use `timestamp(6)` (microsecond precision) for `Bid.created_at`.

**Persona 2: The Real-Time Specialist**
Specialist in Laravel Reverb and Vue 3 Echo. Auction channels are **public**. Sync the `expires_at` timestamp with the client's `AuctionTimer.vue` — never calculate countdown client-side. Handle connection drops gracefully. Per-user events use the `database` notification channel, not private broadcast channels.

**Persona 3: The Industrial UI Designer**
Master of Tailwind. Use the defined palette (Navy for backgrounds, Lemon for highlights). Reference Carrygo screenshots for the "weight" of buttons. Use Sage Border for card outlines. Ensure a mobile-first, high-stakes bidding UI.

**Persona 4: The Logic Architect (Service Specialist)**
Specialization: Clean Code & Service Pattern.
Focus: Keep Controllers ultra-thin (DRY). All business logic lives in dedicated Service classes (`BiddingService`, `WalletService`, `AuctionService`, `NotificationService`). Use DB transactions with `lockForUpdate()` for all bid placements. Enforce the 5-second bid rate limit via throttle middleware on the bid route. Validate Paystack webhooks with HMAC-SHA512 before processing.

**Persona 5: The Roles & Permissions Specialist**
Specialization: Spatie Laravel Permission.
Roles: `user`, `admin`, `agent`. Define granular permissions per role. Assign roles on registration (`user`) and via admin panel (`admin`, `agent`). Agent scope to be defined — do not implement agent-exclusive features until specified.

**Persona 6: The Notification Specialist**
Specialization: Laravel Notifications (`database` + `mail` channels).
Events: `AuctionWon`, `AuctionTriggered`, `BidPlaced`, `PaymentConfirmed`, `BonusPointsAwarded`. High-value events (`AuctionWon`, `AuctionTriggered`, `PaymentConfirmed`) use both `database` and `mail` channels. Low-noise events (`BidPlaced`, `BonusPointsAwarded`) use `database` only. All notifications implement `ShouldQueue`. Never dispatch synchronously in the request lifecycle.

## 12. Progressive Execution Plan

> Each step must be fully complete before the next begins. No step introduces service logic, notifications, or frontend work that belongs to a later step.

---

**Step 1: Infrastructure & All Model Scaffolding**
*Goal: Every migration, model class, and relationship exists before any logic is written.*
- Setup Laravel 13 + Vue 3 (if not already done).
- Configure Tailwind with the Carrygo Industrial Palette.
- Create `config/points.php` and register all `.env` variables (`POINTS_PER_NAIRA`, `BONUS_CONVERSION_RATE`, `MIN_BID_INCREMENT`).
- Set `QUEUE_CONNECTION=database`. Run `php artisan queue:table && php artisan migrate`.
- Create and run migrations for: `users` (with `points_balance`, `bonus_points`), `auctions`, `bids`, `point_transactions`, and the Spatie permissions tables.
- Create Eloquent model classes with fillable, casts, and relationships — no business logic yet:
  - `User` (with `HasRoles` trait from Spatie)
  - `Auction` (relationships: `bids`, `winner`, `pointTransactions`)
  - `Bid` (relationships: `auction`, `user`)
  - `PointTransaction` (relationship: `user`)
- Seed the three Spatie roles: `user`, `admin`, `agent` (via `RoleSeeder`).
- Auto-assign the `user` role on registration (via `RegisteredUser` event listener).

---

**Step 2: Points Engine & Wallet**
*Goal: Users can purchase points via Paystack. No auction or bid logic yet.*
- Build `WalletService` with:
  - HMAC-SHA512 Paystack webhook signature verification (mandatory — reject with `403` on failure).
  - Point credit logic: convert Naira → points using `config('points.points_per_naira')`, update `users.points_balance`, record a `deposit` `PointTransaction`.
  - Bonus point award logic: increment `users.bonus_points`, record a `bonus_award` `PointTransaction`.
  - Bonus claim logic: convert `bonus_points` → `points_balance` at `config('points.bonus_conversion_rate')`, record a `bonus_claim` `PointTransaction`.
- Wire Paystack webhook route (unprotected, raw body middleware) → `WalletService`.

---

**Step 3: Auction Core & Bidding Engine**
*Goal: Auctions can be created and bids can be placed with full data integrity.*
- Build `AuctionService` (admin-only):
  - Create, update, publish (`draft` → `active`), and manually close auctions.
  - Validate auction before publish (image present, `opening_points` > 0, `countdown_duration_seconds` > 0).
- Build `BiddingService` with the full DB transaction flow (see §5):
  - `lockForUpdate()` on auction.
  - Validate amount ≥ `config('points.min_bid_increment')`.
  - Debit `points_balance`, create `Bid`, recalculate `is_winning` across all bids.
  - Increment `auction.current_points` and `auction.bid_count`.
  - Trigger countdown if `current_points` crosses `opening_points`.
  - Dispatch `CloseAuctionJob` (delayed by `countdown_duration_seconds`).
  - Record `bid_debit` `PointTransaction`.
- Apply `throttle:1,5` middleware to the bid route (1 bid per 5 seconds per user).
- Implement `CloseAuctionJob`:
  - Set `auction.status = closed`.
  - Resolve winner from `is_winning = true` bid → set `auction.winner_id`.
- Register `php artisan auctions:reconcile` scheduler command (every minute):
  - Catch missed closures (auctions past `expires_at` still in `triggered` status).
  - Reconcile `bid_count` against actual `Bid` count.

---

**Step 4: Notifications**
*Goal: All user-facing events trigger properly formatted, queued notifications.*
- Create all Notification classes (all implement `ShouldQueue`, dispatched from services — never from controllers):
  - `BidPlaced` → `database` only (dispatched in `BiddingService`)
  - `AuctionTriggered` → `database` + `mail` (dispatched in `BiddingService` when countdown triggers)
  - `AuctionWon` → `database` + `mail` (dispatched in `CloseAuctionJob`)
  - `PaymentConfirmed` → `database` + `mail` (dispatched in `WalletService`)
  - `BonusPointsAwarded` → `database` only (dispatched in `WalletService`)
- Build API endpoints for the Vue frontend:
  - `GET /api/notifications` — list unread notifications.
  - `PATCH /api/notifications/{id}/read` — mark one as read.
  - `PATCH /api/notifications/read-all` — mark all as read.

---

**Step 5: Real-Time Bidding Interface**
*Goal: Auction state updates live in the browser without page reloads.*
- Install and configure Laravel Reverb.
- Create broadcast events (public `auction.{id}` channel):
  - `BidPlacedEvent` — payload: `current_points`, `bid_count`, `winning_user_id`.
  - `AuctionTriggeredEvent` — payload: `expires_at`.
  - `AuctionClosedEvent` — payload: `winner_id`.
- Dispatch broadcast events from `BiddingService` and `CloseAuctionJob` (after DB transaction commits).
- Build Vue components:
  - `AuctionTimer.vue` — Amber countdown driven by `expires_at` from server; never calculated client-side.
  - `BidInterface.vue` — Lemon bid button with 5-second cooldown UX feedback.
- Build `useAuctionStore` (Pinia) — syncs with Echo events, holds the auction state shape defined in §10.
- Build `useNotificationStore` (Pinia) — fetches and holds unread notifications; refreshed on relevant Echo events.

---

**Step 6: UI/UX Polish**
*Goal: The platform looks and feels premium on all devices.*
- Apply the full Carrygo Industrial theme across all pages and components.
- Add Lemon/Amber flash animation on `current_points` update.
- Implement in-app notification bell with unread badge from `useNotificationStore`.
- Ensure all views are mobile-first and responsive.

---

**Step 7: End-to-End Verification**
*Goal: Confirm the complete flow works under realistic conditions.*
- Full flow test: deposit → bid → trigger → countdown → close → winner notification.
- Verify HMAC webhook rejection on tampered payload.
- Verify `is_winning` recalculation correctly handles cumulative totals and tie scenarios.
- Verify `CloseAuctionJob` fires on time and scheduler fallback catches missed closures.
- Verify email + database notifications are delivered for all high-value events.
