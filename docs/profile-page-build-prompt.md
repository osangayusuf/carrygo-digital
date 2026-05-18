# Build the Carrygo Profile page (`GET /profile`)

## Project context

**Carrygo Digital** is a Laravel 13 + Inertia v3 + Vue 3 e-auction platform at `/Users/osanga/Herd/carrygo-digital`. Follow `AGENTS.md`, `.cursorrules`, and `frontend-implementation-plan.md` (Step 15). Activate skills: `laravel-best-practices`, `logic-architect`, `industrial-ui-designer`, `inertia-vue-development`, `pest-testing`, `wayfinder-development`, `activity-logging` where relevant.

**Already built (reference patterns):**

- Public listing pages: `Trending`, `OpenBids`, `EventItems`, `Winners`, `Leaderboard` — thin controllers + `*ListingService` classes, `PublicLayout`, industrial UI (Navy `#0D1B2A`, Lemon `#C8E000`, Amber `#F5E642`, Ink `#0A0A0A`, `border-2`, bold typography)
- Shared types: `resources/js/types/auction.ts`
- MSISDN masking: `maskedMsisdnParts()` and `formatMsisdn()` in `resources/js/lib/utils.ts`
- Navbar/footer link to `route('profile')` via Wayfinder `@/routes/index`

**Not built yet (stubs only):**

- `app/Http/Controllers/ProfileController.php` — empty
- `app/Http/Controllers/WalletController.php` — empty
- No `resources/js/pages/Profile/Index.vue`

**Important: two different “profile” concepts**

| Route | Controller | Purpose |
|-------|------------|---------|
| `GET /profile` | `App\Http\Controllers\ProfileController` | **User dashboard** (this task) — bid/win/transaction history |
| `GET /settings/profile` | `App\Http\Controllers\Settings\ProfileController` | **Account settings** (Fortify) — edit name/email, delete account |

Do **not** conflate these. Link to settings only if needed (e.g. “Edit account” → `profile.edit`).

**Navbar note:** The logged-in navbar button (wallet icon + points) currently links to `profile.url()`. Treat `/profile` as the authenticated account hub; include a clear CTA to `/wallet` once wallet exists.

---

## Requirements (from `frontend-implementation-plan.md` Step 15)

**Route:** `GET /profile` — `ProfileController@index`, name `profile`  
**Middleware:** `auth`, `verified` (already in `routes/web.php`)  
**Page:** `resources/js/pages/Profile/Index.vue`  
**Layout:** Prefer **`PublicLayout`** (matches other marketplace pages and navbar) unless you have a strong reason for `AppLayout`/`AppSidebarLayout` — if you use sidebar layout, ensure navbar/footer still feel consistent.

### UI sections

1. **Header**
   - Avatar initials from user name
   - Name, email, phone (MSISDN)
   - “Member since” (`created_at`)
   - Phone displayed **masked** (same pattern as winners/homepage: `maskedMsisdnParts()` + `***` middle)

2. **Points summary**
   - `points_balance`, `bonus_points` from authenticated user
   - Link/button to `route('wallet')` (wallet page may still be a stub — link is fine)

3. **Tabbed content** (client-side tabs in Vue; optional `?tab=` query sync)
   - **Bid history** — all bids for the auth user, grouped by auction. Per auction: name, image, total pts spent on that item, bid count, last bid date. Link to `auctions.show`.
   - **Win history** — closed auctions where `winner_id = auth user`. Card style similar to `WinnersSection` (image, name, winning pts, date).
   - **Transactions** — paginated `PointTransaction` list for auth user (same shape as planned Wallet page: type badge, amount, date, status). Reuse or mirror `PointTransactionResource` when you create it.

### Backend architecture

- **Thin controller:** `ProfileController@index` only loads Inertia props.
- **Service:** `app/Services/ProfileService.php` (or split into focused methods) for:
  - User summary DTO/array
  - Paginated/grouped bid history
  - Paginated win history
  - Paginated transactions
- **No business logic in the controller.**
- Eager-load relationships; avoid N+1.
- **Authorization:** only ever query `auth()->id()` / `$request->user()` — no user id from request input.

### Data model reference

- `User`: `name`, `email`, `phone`, `points_balance`, `bonus_points`, `created_at`
- `Bid`: `auction_id`, `user_id`, `amount`, `is_winning`, `created_at` → `auction`, `user`
- `Auction`: `winner_id`, `status` (`App\Enums\AuctionStatus`), `name`, `image`, `price`, etc.
- `PointTransaction`: `type` (`App\Enums\TransactionType`), `amount`, `status`, `created_at`; bid debits may have `metadata['auction_id']`

### Frontend conventions

- Vue 3 Composition API, single root element per page
- Wayfinder for routes (`@/routes/index`, `@/actions/...`)
- Section component: `resources/js/components/profile/ProfileSection.vue` (or split tab components)
- Match industrial styling of `WinnersSection.vue` / `LeaderboardSection.vue`
- Types in `resources/js/types/` (e.g. `profile.ts` or extend existing types)

### Out of scope (unless explicitly extended)

- Referral program UI (deferred to Tasks page / Step 14 — no `referral_code` on `User` yet)
- Profile **settings** edits (already at `/settings/profile`)
- Wallet deposit / Paystack (Wallet controller stub)
- Real-time balance updates (optional later via Reverb)

### Tests (Pest feature tests)

Create `tests/Feature/ProfileControllerTest.php`:

- Guest → redirect to login
- Authenticated user → `Profile/Index` with correct summary props
- Bid history only includes auth user’s bids
- Win history only includes auctions where auth user is `winner_id` and status is `CLOSED`
- Transactions only for auth user
- **No cross-user data leakage** (second user with bids/wins must not appear)

Run: `php artisan test --compact tests/Feature/ProfileControllerTest.php`  
Run: `vendor/bin/pint --dirty`  
Run: `npm run build`

### Template reference (visual inspiration only)

`template-resources/resources/js/pages/Profile.vue` — header card, points card, referral/share (referral is **out of scope** for Step 15; adapt layout to Carrygo industrial palette, not Material surface tokens).

### Deliverables checklist

- [ ] `ProfileService` + implemented `ProfileController@index`
- [ ] `pages/Profile/Index.vue` + profile section/tab components
- [ ] Types for Inertia props
- [ ] Pest tests passing
- [ ] Pint + build green
