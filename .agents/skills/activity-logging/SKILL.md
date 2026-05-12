---
name: activity-logging
description: >
  Activates the Activity Logging specialist persona for adding user activity
  tracking to new controller methods and service class actions on the Carrygo
  platform. Use whenever a new controller method, service action, or domain
  event is being introduced that should produce an audit trail.
  Trigger words: "log activity", "user activity", "audit", "track action",
  "activity history", "analytics", ActivityService, UserActivity.
---

# Persona: Activity Logging Specialist

Use this skill when:
- Adding or modifying a controller method that performs a user action.
- Adding or modifying a service class method that represents a domain event.
- The user asks to "log", "track", "audit", or "record" a user action.

---

## Core Concepts

| Class | Responsibility |
|---|---|
| `App\Enums\ActivityType` | The canonical list of every loggable event — **always extend this enum first** when adding a new action. |
| `App\Services\ActivityService` | The only way to create a log entry. Inject it and call `->log()`. |
| `App\Jobs\LogUserActivity` | The queued job that writes the `UserActivity` row. Never dispatch this directly — let `ActivityService` handle it. |
| `App\Models\UserActivity` | The Eloquent model. Never instantiate it directly in controllers or services. |

---

## ActivityType Enum Values

```php
ActivityType::LOGIN_SUCCESS      // 'login_success'
ActivityType::LOGIN_FAILED       // 'login_failed'
ActivityType::PROFILE_UPDATED    // 'profile_updated'
ActivityType::PASSWORD_CHANGED   // 'password_changed'
ActivityType::TWO_FACTOR_ENABLED // 'two_factor_enabled'
ActivityType::TWO_FACTOR_DISABLED// 'two_factor_disabled'
ActivityType::AUCTION_VIEWED     // 'auction_viewed'
ActivityType::BID_PLACED         // 'bid_placed'
ActivityType::AUCTION_WON        // 'auction_won'
ActivityType::POINTS_DEPOSITED   // 'points_deposited'
ActivityType::POINTS_SPENT       // 'points_spent'
```

**When adding a new loggable action**, add a new case to `ActivityType` first,
then use it in the call below.

---

## The Logging Call

```php
$this->activityService->log(
    type: ActivityType::BID_PLACED,      // required
    user: $user,                         // nullable — pass null for pre-auth events
    subject: $auction,                   // nullable Eloquent model (polymorphic)
    metadata: ['amount' => $bid->amount],// nullable — any extra key/value context
);
```

**`user_agent` and `ip_address` are captured automatically** — never pass them
in `metadata`.

---

## Where to Log: Decision Table

| Action type | Log from | Reason |
|---|---|---|
| Auth (login success/failure) | Controller | No service exists; controller is the boundary |
| Settings (profile, password, 2FA) | Controller | Direct user intent; thin action |
| Bid placement | `BiddingService` | Business logic lives in the service |
| Auction win | `AuctionService` | Domain event fired from service |
| Points deposit / webhook | `WalletService` | Paystack callback handled in service |
| Bonus points | `WalletService` | Wallet mutations always go through the service |

---

## Rules

1. **Inject `ActivityService` via the constructor** — never use `app()` or `resolve()`.
   ```php
   public function __construct(private readonly ActivityService $activityService) {}
   ```

2. **Log after the primary action succeeds** — never log before, never inside a
   `try/catch` that would swallow the failure.

3. **Never log inside a database transaction** — if the transaction rolls back,
   the log row will be lost anyway. Place the log call after `DB::transaction()`
   completes.

4. **Never log in middleware** — middleware runs on every request; targeted
   per-action logging is more useful and easier to test.

5. **`subject`** should be the most meaningful Eloquent model involved (e.g. the
   `Auction` when logging `BID_PLACED`), or `null` if none applies.

6. **Guest events** (no authenticated user) must pass `user: null` — this is
   valid and produces a row with `user_id = NULL`.

7. **Keep `metadata` small and serializable** — no Eloquent models, no closures.
   Scalar values and simple arrays only.

---

## Testing Pattern

Use `assertDatabaseHas` after triggering the action. The job runs synchronously
in the `testing` environment, so no queue faking is needed.

```php
test('login activity is logged on success', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'phone' => $user->phone,
        'password' => 'password',
    ]);

    assertDatabaseHas('user_activities', [
        'user_id' => $user->id,
        'type'    => ActivityType::LOGIN_SUCCESS->value,
    ]);
});
```

For failed-login tests where `user_id` is `NULL`:

```php
assertDatabaseHas('user_activities', [
    'user_id' => null,
    'type'    => ActivityType::LOGIN_FAILED->value,
]);
```
