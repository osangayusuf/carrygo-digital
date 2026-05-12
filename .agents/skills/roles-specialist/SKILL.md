---
name: roles-specialist
description: Activates the Roles & Permissions Specialist persona for managing Spatie Laravel Permission roles and gates on the Carrygo platform.
---

# Persona 5: The Roles & Permissions Specialist

Use this skill when working on role definitions, permission gates, middleware guards, or any access-control logic on the Carrygo platform.

## Roles

Three roles are defined on this platform. Use Spatie Laravel Permission for all role/permission work.

| Role    | Description                                                                 |
| :------ | :-------------------------------------------------------------------------- |
| `user`  | Standard bidder. Assigned automatically on registration.                    |
| `admin` | Full platform management. Assigned manually by an existing admin.           |
| `agent` | Customer support. Scope is not yet fully defined — do NOT implement agent-exclusive features until explicitly specified. |

## Rules

- **Auto-assignment:** Every newly registered user must be assigned the `user` role automatically (hook into Fortify's registration flow or a `RegisteredUser` event listener).
- **Role seeding:** Always seed all three roles (`user`, `admin`, `agent`) in a dedicated `RoleSeeder`. Never hardcode role names as bare strings in business logic — use constants or config.
- **Permission granularity:** Define permissions at the action level (e.g., `create auctions`, `close auctions`, `view all transactions`, `manage users`) and assign them to roles. Do not use `role:admin` middleware alone — use named permissions via `can()` / `->middleware('can:...')`.
- **Admin-only routes:** Protect admin routes with `->middleware(['auth', 'can:access admin panel'])` or a dedicated `AdminMiddleware`.
- **Agent scope:** Agent features are not yet defined. Register the role but assign no permissions until the agent feature set is specified.
- **No permission checks in Blade/Vue directly:** Always go through Gates or Policies. Expose `$page.props.auth.permissions` via Inertia shared data for frontend conditional rendering.
- Use `Spatie\Permission\Traits\HasRoles` on the `User` model.
