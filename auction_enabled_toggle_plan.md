# Plan: Auction Enabled/Disabled Toggle

## Goal

Add an `enabled` boolean to auctions, independent of `status` (draft/active/triggered/closed). Disabled auctions are fully hidden from regular users — listings, direct URLs, bidding, and the Winners page — but remain fully visible and manageable in the admin panel.

## Decisions locked in

- Disabling blocks bidding immediately, even via direct API calls.
- Direct auction URLs (show page, timeline) 404 for non-admins when disabled.
- Disabled auctions are excluded from the public Winners page too — `enabled` is a global on/off switch for the user-facing site.
- Admin UI gets a single switch per row (one-click toggle), not separate Enable/Disable buttons.

## 1. Database

- New migration: add `enabled` boolean, `default(true)`, after `status`. Existing auctions are unaffected.
- Add a composite index on `(status, enabled)` since nearly every user-facing query will filter on both.

## 2. Model — `app/Models/Auction.php`

- Add `enabled` to `$fillable` and cast as boolean.
- Add `scopeEnabled(Builder $query)` → `$query->where('enabled', true)`.
- This scope is the one thing every read path below needs to adopt.

## 3. Service layer — `app/Services/AuctionService.php`

- `create()`: default `enabled` to `true` (or accept it from admin input, defaulting true).
- Add `enable(Auction $auction)` and `disable(Auction $auction)` methods (simple, no status restriction — disabling works in any status per the requirement). Each just flips the flag and saves; no need to touch `status`.
- `BiddingService::placeBid()` (or wherever bids are validated): add a guard that rejects the bid if `!$auction->enabled`, alongside existing status checks.

## 4. Admin routes/controller

- `routes/web.php`: add `Route::patch('auctions/{auction}/toggle-enabled', ...)->name('auctions.toggleEnabled')` next to the existing `publish`/`close` routes.
- `Admin/AuctionController.php`: add a `toggleEnabled(Auction $auction)` method calling `$auctionService->enable()`/`disable()` based on current state, redirect back with a success message (same pattern as `publish`/`close`).
- `Admin/AuctionController::index` stays unfiltered by `enabled` — admins must see every auction regardless of state.

## 5. User-facing read paths to update (add `->enabled()` scope)

- `AuctionListingService`: `activeAuctionsQuery()`, `paginateOpenBids()`, `paginateEventItems()`.
- `HomeController`: `activeAuctionsQuery()` (feeds trending/recently-added/luxury/featured/category bids), the `openBids` block, the `winners` block, `resolveWinnerPopup()`, `resolveEventPopupBid()`.
- `RecommendedController`: its own inline `Auction::query()->whereIn('status', ...)` needs `->enabled()` added directly.
- `WinnerListingService::` main query (feeds the Winners page — per the decision above, add `->enabled()` here).
- `LeaderboardService::paginateAuctionLeaderboards()`.

## 6. Direct-access paths (need explicit enabled + admin check, not just a scope)

- `AuctionController::show`: route-model-bound, currently unfiltered. Add `abort_if(!$auction->enabled && !$request->user()?->hasRole('admin'), 404)`.
- `AuctionTimelineController::index`: same guard.
- `BidController::store`: same guard (belt-and-suspenders alongside the `BiddingService` check in step 3, since this is the entry point).

## 7. Paths that should NOT filter on enabled

- `ReconcileAuctionsCommand` and `CloseAuctionJob`: leave unfiltered. A disabled auction that was already triggered should still auto-close and reconcile bid counts correctly in the background; disabling shouldn't leave it stuck in limbo.

## 8. Frontend

- `resources/js/types/auction.ts` and the local `Auction` type in `Admin/Auctions/Index.vue`: add `enabled: boolean`.
- `Admin/Auctions/Index.vue`: add an "Enabled" column with a switch per row (reuse/extend the existing `Checkbox` component styled as a switch, or pull in a proper Switch primitive since none exists yet). On toggle, `router.patch(auctionsToggleEnabled.url(id))` — same fire-and-confirm pattern as `closeAuction`. Consider an optional All/Enabled/Disabled filter tab alongside the existing status filter.
- Regenerate the Wayfinder route helpers (`php artisan wayfinder:generate` or project's equivalent) so `@/routes/admin/auctions` exposes the new `toggleEnabled` route.

## 9. Tests

- `AuctionFactory`: add a `disabled()` state (`fn () => ['enabled' => false]`); `enabled` defaults to `true` in `definition()`.
- `AuctionServiceTest`: cover `enable()`/`disable()` in every status.
- `AdminAuctionControllerTest`: toggle route flips the flag; admin `index` still lists disabled auctions.
- `BiddingServiceTest`: bid on a disabled active/triggered auction is rejected.
- `AuctionShowTest`: disabled auction show/timeline 404s for a regular user, 200s for admin.
- Visibility regression tests (follow the existing `TrendingControllerTest` pattern of creating named auctions per state and asserting Inertia props): add a disabled-but-active auction to `TrendingControllerTest`, `OpenBidsControllerTest`, `EventItemsControllerTest`, `RecommendedControllerTest`, `WinnersControllerTest`, `LeaderboardControllerTest`, `HomeControllerTest` — assert it's excluded everywhere.

## 10. Rollout order

Migration → model → service (`enable`/`disable` + bidding guard) → admin route/controller → user-facing query scopes → direct-access guards → frontend (types, column, switch, route regen) → tests.
