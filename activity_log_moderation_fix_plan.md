# Plan: Fix Activity Log Moderation Bug

## Root cause (recap)

`ActivityLogController::index()` eager-loads the main table's activity author with `with('user:id,name,email')` — `is_active` is never selected. The two alert queries a few lines below correctly select it (`select('id','name','email','is_active')`). The Vue page renders the Moderate button's label/icon/color off `user.is_active`, so for the main table that value is always `undefined` → every row always shows "Enable" / unlock icon, regardless of the user's real status. An `act.user as any` cast in the template papers over the resulting TypeScript error instead of fixing the underlying data gap.

One thing I over-called in the initial diagnosis: the "silent failure on self-toggle" isn't actually a gap — `AdminLayout.vue` (lines 254-266) already renders `$page.props.flash.success` and `$page.props.errors.error` globally for every admin page, so the self-disable error message does surface. No fix needed there.

## 1. Backend — select `is_active` for the main table's user relation

File: `app/Http/Controllers/Admin/ActivityLogController.php`, line 36.

Change:
```php
$query = UserActivity::query()
    ->with('user:id,name,email')
    ->orderBy('created_at', 'desc');
```
to:
```php
$query = UserActivity::query()
    ->with('user:id,name,email,is_active')
    ->orderBy('created_at', 'desc');
```

## 2. Frontend — widen the type and drop the unsafe cast

File: `resources/js/pages/Admin/ActivityLog/Index.vue`.

- Line 35: change
  ```ts
  user: { id: number; name: string; email: string } | null;
  ```
  to
  ```ts
  user: User | null;
  ```
  (reusing the existing `User` type at lines 19-24, which already declares `is_active: boolean`). This removes the need for a separate inline type entirely.
- Lines 521-526: drop the `as any` cast now that `act.user` is a real `User`:
  ```ts
  @click="toggleUserActive(act.user)"
  ```
  TypeScript will now correctly flag any future regression if the backend ever stops sending `is_active` again.

## 3. Test coverage

File: `tests/Feature/AdminMetricsAndActivityTest.php` (same file that already covers the two alert paths at lines 125 and 154).

Add a test asserting the main table actually returns `is_active` on the nested user and that it reflects real state for both an active and a disabled user, e.g.:
- Create one active user and one disabled user, each with a `UserActivity` entry.
- Hit `route('admin.activity-log.index')`.
- Assert `activities.data.*.user.is_active` matches each user's real `is_active` value (not just present/truthy).

This is the test that would have caught the original bug — it should fail against the current code (before step 1) and pass after.

## 4. Manual verification after the fix

- Toggle a currently-active user from the main table: button should read "Disable" before the click, flip to "Enable" after, and the row should reflect the new state without a manual page refresh (Inertia's default full reload on `router.post` already re-fetches `activities`, so this should just work once the query is fixed).
- Toggle a currently-disabled user: button should read "Enable" beforehand.
- Confirm the two alert-panel buttons still behave correctly (they were already fine — this is a regression-safety check, not a fix).
- Confirm self-toggle attempt still shows the existing global error banner via `AdminLayout`.

## Rollout order

Backend eager-load fix → frontend type fix (remove cast) → test → manual click-through verification on the running dev server.
