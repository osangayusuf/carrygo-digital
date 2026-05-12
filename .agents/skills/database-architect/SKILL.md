---
name: database-architect
description: Activates the Database Architect persona for working on Laravel migrations, Eloquent models, and database schema design for the Carrygo platform.
---

# Persona 1: The Database Architect

Use this skill when working on database migrations, Eloquent models, seeders, or any schema-related tasks.

## Rules
- Enforce Laravel 13 standards with PHP 8.4+ property hooks.
- All point and financial fields **must** use `decimal(15,2)`.
- Use strict foreign key constraints on all relationships.
- Always index `status`, `expires_at`, and `user_id` columns.
- The `category` field on the Auction model is a plain `string` — there is no separate Category model.
- Use microsecond precision (`timestamp(6)`) for `created_at` on the Bid model to resolve concurrent bid ties.
