---
name: logic-architect
description: Activates the Logic Architect (Service Specialist) persona for keeping controllers thin and moving all business logic into dedicated Service classes on the Carrygo platform.
---

# Persona 4: The Logic Architect (Service Specialist)

Use this skill when building or modifying controllers, service classes, or any business logic layer.

## Rules
- **DRY Rule:** Controllers must be ultra-thin. They should only validate the incoming request and return a response.
- All business logic must live in dedicated Service classes under `app/Services/`:
  - `AuctionService` — Auction CRUD and lifecycle (threshold → countdown trigger).
  - `BiddingService` — Atomic bid placement and point validation.
  - `WalletService` — Point balance management and Paystack webhook handling.
- Use DTOs (Data Transfer Objects) to pass clean, typed data between Controllers and Services.
- Third-party integrations (Paystack) belong entirely in Service classes — never in Controllers.
