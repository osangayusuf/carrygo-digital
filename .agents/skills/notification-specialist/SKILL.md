---
name: notification-specialist
description: Activates the Notification Specialist persona for building Laravel database-channel notifications and in-app notification UX on the Carrygo platform.
---

# Persona 6: The Notification Specialist

Use this skill when creating, modifying, or dispatching Laravel Notifications, building the in-app notification feed, or wiring notification triggers in service classes.

## Notification Events

The following notification classes must exist under `app/Notifications/`:

| Class                   | Trigger                                                         | Recipient       |
| :---------------------- | :-------------------------------------------------------------- | :-------------- |
| `AuctionWon`            | Auction closes with a winner                                    | Winner (`user`) |
| `AuctionTriggered`      | `opening_points` threshold is crossed, countdown begins          | All active bidders on the auction |
| `BidPlaced`             | A bid is successfully placed                                    | Bidding user    |
| `PaymentConfirmed`      | Paystack webhook verified and points credited to wallet         | Depositing user |
| `BonusPointsAwarded`    | Bonus points are added to a user's `bonus_points` balance       | Recipient user  |

## Rules

- **Channels:** Use `database` and `mail` channels. Do **not** add `broadcast` unless explicitly requested.
  - `AuctionWon` → `database` + `mail`
  - `AuctionTriggered` → `database` + `mail`
  - `BidPlaced` → `database` only (high-frequency; email would be noise)
  - `PaymentConfirmed` → `database` + `mail`
  - `BonusPointsAwarded` → `database` only
- **Always queued:** Every notification class must implement `ShouldQueue`. Never dispatch notifications synchronously within the HTTP request lifecycle.
- **Queue driver:** `database` (configured in `.env` as `QUEUE_CONNECTION=database`).
- **Dispatch from services, never controllers:** Notifications are dispatched inside the relevant Service class (`BiddingService`, `WalletService`, `AuctionService`) — never from controllers.
- **Notification data shape:** The `toArray()` / `toDatabase()` method must return a typed, predictable array:
  ```php
  [
      'type'       => 'auction_won',   // snake_case event name
      'auction_id' => $this->auction->id,
      'message'    => 'You won the auction for ...',
      'url'        => '/auctions/' . $this->auction->id,
  ]
  ```
- **Frontend consumption:** The Vue frontend fetches notifications via a dedicated API endpoint (`/api/notifications`). Mark as read via `PATCH /api/notifications/{id}/read`. The `useNotificationStore` Pinia store holds unread notifications and a count badge.
- **No notification polling:** Trigger a notification refresh from Echo events (e.g., after a `BidPlaced` broadcast) rather than polling on a timer.
