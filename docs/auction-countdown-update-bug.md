# Bug Report — Auction countdown does not reflect admin edits

**Status:** Fixed — see "Fix as implemented" at the bottom
**Area:** `AuctionService::update()`, `CloseAuctionJob`, countdown rendering

---

## Symptom

An admin edits `countdown_duration_seconds` on an auction from the admin panel. The save succeeds, but the countdown shown to users on the auction card, the auction detail page, the navbar banner and the featured carousel keeps ticking down to the *old* end time.

---

## Root cause

The UI never reads `countdown_duration_seconds`. Every timer surface reads `expires_at`:

- `resources/js/lib/utils.ts` → `getRemainingTime()`, `getRemainingTimeWithSeconds()`, `hasExpired()` all take `expiresAt`
- `AuctionCard.vue`, `Auction/Show.vue`, `AppNavbar.vue`, `AppSidebarHeader.vue`, `FeaturedAuctionsCarousel.vue`, `BidCard.vue`, `HomeEventPopup.vue` — all pass `auction.expires_at`
- Backend serializers (`AuctionResource`, `AuctionListingService`, `HomeController`, `HandleInertiaRequests`) expose `expires_at`, not the duration

`expires_at` is a materialised value, computed exactly once at trigger time:

`app/Services/BiddingService.php:83`
```php
$auction->expires_at = now()->addSeconds($auction->countdown_duration_seconds);
CloseAuctionJob::dispatch($auction->id)->delay($auction->countdown_duration_seconds);
```

`app/Services/AuctionService.php:72-88` (update path)
```php
$wasActive = $auction->status === AuctionStatus::ACTIVE;

$auction->fill($data)->save();          // countdown_duration_seconds written here

if ($wasActive && $auction->current_points >= $auction->opening_points) {
    // ...only here is expires_at recalculated
}
```

So on update the new duration is persisted to the `auctions` row, but `expires_at` is only recomputed inside a branch guarded by `$wasActive` — i.e. the auction must have been `active` *before* the save and must be crossing the trigger threshold. For an auction already in `triggered` status (the only status where a countdown is actually visible), that branch never runs. `expires_at` is left at its original value and the UI, correctly, keeps counting to it.

### Three distinct failure cases

| Auction status when edited | What happens | Visible effect |
|---|---|---|
| `draft` | Duration saved; no `expires_at` yet | Works correctly — new value applies at trigger |
| `active` (below threshold) | Duration saved; no `expires_at` yet | Works correctly |
| `triggered` | Duration saved; `expires_at` untouched; `CloseAuctionJob` already queued with the old delay | **Bug** — timer unchanged, auction still closes at the old moment |

### Secondary issue: the close job

Even if `expires_at` were recalculated, the auction would still close at the wrong time. `CloseAuctionJob` is dispatched with `->delay($auction->countdown_duration_seconds)` at trigger time and there is no cancellation or re-dispatch on update. The job carries only `auctionId` and re-checks status on run (`CloseAuctionJob.php`), so a stale job will happily close an auction whose `expires_at` has been pushed further out. Conversely, if the duration is shortened, the auction stays open past its displayed expiry until `ReconcileAuctionsCommand` catches it.

### Tertiary issue: no live push

`AuctionTriggeredEvent` is broadcast on trigger, but nothing on the frontend subscribes to it — `grep` for `Echo` in `resources/js` returns only the notification store and support chat. Clients only pick up a new `expires_at` on a full page load/Inertia visit. So even after a backend fix, users with the page already open would not see the new timer until they navigate or refresh.

---

## Recommended fix

1. **Recompute `expires_at` on update when the auction is triggered.** In `AuctionService::update()`, after `$auction->fill($data)->save()`, add a branch for `$auction->status === AuctionStatus::TRIGGERED && array_key_exists('countdown_duration_seconds', $data)`. Decide and document the semantics — the two sane options are:
   - *rebase from trigger:* `expires_at = triggered_at->addSeconds($new)` (preserves elapsed time; can land in the past if the duration is cut, so clamp to `now()`)
   - *restart from now:* `expires_at = now()->addSeconds($new)` (simpler, but effectively grants extra time)

   Rebasing from `triggered_at` is the more defensible behaviour for bidders.

2. **Make the close job expiry-aware rather than delay-aware.** Cheapest robust change: keep the delayed dispatch, but have `CloseAuctionJob::handle()` bail out and re-dispatch itself if `now() < $auction->expires_at`. That makes stale jobs harmless and removes the need to cancel queued jobs. Alternatively, drop the per-auction delayed job and rely on `ReconcileAuctionsCommand` at a tight schedule.

3. **Broadcast the change.** Emit `AuctionTriggeredEvent` (or a new `AuctionCountdownUpdatedEvent`) carrying the new `expires_at` after an update, and add an Echo subscription on the auction card/detail/navbar surfaces so open sessions re-sync. Without this, step 1 only fixes the next page load.

4. **Guard the edit.** Consider whether editing the countdown of a live triggered auction should be allowed at all — it changes the terms of an auction bidders have already committed points to. If it stays, log it via `AuctionTimelineService` so there is an audit trail.

---

## Test gaps

`tests/Feature/AuctionServiceTest.php` covers the trigger-on-update path but has no case for "update `countdown_duration_seconds` on an already-triggered auction". Add:

- updating the duration on a `triggered` auction shifts `expires_at` by the expected amount
- shortening the duration below elapsed time closes the auction (or clamps) rather than producing a past `expires_at`
- a stale `CloseAuctionJob` does not close an auction whose `expires_at` has been extended

---

## Fix as implemented

**Semantics chosen:** rebase from `triggered_at`. Elapsed time is preserved — a
60s → 90s change adds exactly 30s to the deadline no matter when the edit lands.
If the new duration has already fully elapsed, the expiry is clamped to `now()`
and the auction closes immediately.

### Backend

- `AuctionService::update()` — captures the pre-save duration and expiry, and when
  a **triggered** auction's `countdown_duration_seconds` changes, delegates to the
  new private `rebaseCountdown()`.
- `AuctionService::rebaseCountdown()` — sets
  `expires_at = triggered_at + new duration` (clamped to `now()` if past), saves,
  dispatches a fresh `CloseAuctionJob` for the remaining time, records a timeline
  entry, and broadcasts.
- `CloseAuctionJob` — now refuses to close a triggered auction whose `expires_at`
  is still in the future, so a job queued against the *old*, shorter delay can no
  longer close an extended auction early. The stale run is simply dropped;
  `AuctionService` has already queued a correct job, and
  `ReconcileAuctionsCommand` remains the backstop. (Deliberately *not* a
  self-re-dispatch, which would recurse infinitely on the `sync` queue driver.)
- `Auction` model — added integer casts for `countdown_duration_seconds`,
  `opening_points`, `current_points`, `bid_count`. Without these, a value arriving
  as a form-data string (`"90"`) would fail the strict comparison against the
  stored integer, both missing real changes and firing on non-changes.
- `AuctionCountdownUpdatedEvent` (new) — `ShouldBroadcastNow` on the existing
  `auction.{id}` channel, `broadcastAs('AuctionCountdownUpdated')`, payload
  `{ expires_at, status }`. `AuctionTriggeredEvent` also gained a
  `broadcastAs('AuctionTriggered')` so the client name is stable.
- `AuctionTimelineEntryType::CountdownAdjusted` (new) + 
  `AuctionTimelineService::recordCountdownAdjusted()` — audit trail recording the
  previous and new expiry and duration. The `type` column is a plain string, so no
  migration was needed.

### Frontend

- `resources/js/composables/useAuctionCountdown.ts` (new) — takes an auction id
  and the server-rendered `expires_at`, subscribes to `.AuctionCountdownUpdated`
  and `.AuctionTriggered` on `auction.{id}`, and returns a live `expiresAt` ref.
  Uses `stopListening` rather than `leave` on teardown so it does not pull the
  shared channel out from under `useAuctionTimeline`.
- Wired into every countdown surface: `AuctionCard.vue`, `Auction/Show.vue`,
  `AppNavbar.vue` (banner), `AppSidebarHeader.vue` (banner). Each now renders the
  composable's `expiresAt` instead of the raw prop, so an open tab re-anchors
  without a refresh.

### Tests

- `AuctionServiceTest` — extend, shorten, past-clamp, no-op (non-countdown update
  leaves `expires_at` alone and queues nothing), and timeline-entry cases.
- `CloseAuctionJobTest` — a stale job against a not-yet-expired triggered auction
  leaves it open.
- `BroadcastEventsTest` — channel, event name, and payload of the new event.
- `AuctionFactory::triggeredAndExpired()` (new) — `triggered()` produces a
  *future* `expires_at`, which the new guard correctly refuses to close. Existing
  `CloseAuctionJob` tests were moved onto this state, since they mean "a job
  running at or after expiry".

### Not verified locally

PHP is unavailable in this environment, so the PHP test suite was not executed.
Run `php artisan test` — particularly `AuctionServiceTest`, `CloseAuctionJobTest`,
`CloseAuctionJobNotificationTest`, and `BroadcastEventsTest`. `vue-tsc` and
`eslint` were run and report no errors in the touched frontend files (the repo has
pre-existing type errors elsewhere).
