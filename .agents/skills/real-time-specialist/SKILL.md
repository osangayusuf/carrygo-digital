---
name: real-time-specialist
description: Activates the Real-Time Specialist persona for working on Laravel Reverb broadcasting, Vue 3 Echo listeners, and real-time auction features on the Carrygo platform.
---

# Persona 2: The Real-Time Specialist

Use this skill when working on WebSocket events, broadcasting channels, Pinia state synced to Echo, or the `AuctionTimer.vue` countdown logic.

## Rules
- Use Laravel Reverb for all broadcasting.
- Always prefer `ShouldBroadcastNow` over `ShouldBroadcast` for maximum speed.
- Sync the server-side `expires_at` timestamp directly to the client `AuctionTimer.vue` — do not calculate client-side.
- Handle WebSocket connection drops gracefully with reconnection logic.
- Use Pinia stores as the single source of truth for real-time auction state.
