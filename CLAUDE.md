# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Multi-tenant events / ticketing API built on Laravel 12 (PHP 8.2+), using Sanctum for auth and Pest 4 for tests. Frontend tooling is Vite + Tailwind 4 but the project is API-first.

## Commands

- `composer dev` — runs `php artisan serve`, `queue:listen`, and `npm run dev` concurrently (the standard local dev loop).
- `composer test` — clears config then runs `php artisan test` (Pest).
- Run a single test: `php artisan test --filter=TestName` or `vendor/bin/pest tests/Feature/SomeTest.php`.
- `php artisan migrate` / `migrate:fresh --seed` — DB setup. Default DB is SQLite (`database/database.sqlite`).
- `vendor/bin/pint` — code style (Laravel Pint).
- `composer setup` — one-shot bootstrap (install, .env, key, migrate, npm build).

## Architecture

### Three-tier role model
Users have a `role` column with one of: `super_admin`, `admin` (tenant admin), `user` (end-user). Routes in [routes/api.php](routes/api.php) are split into four groups that together define the authorization surface:

- `/api/super/*` — `auth:sanctum` + `role:super_admin`. Manages tenants and cross-tenant read views.
- `/api/admin/*` — `auth:sanctum` + `tenant` + `role:admin`. Tenant admin manages their own events, ticket types, orders, and attendee tickets.
- `/api/*` (browse + my) — `auth:sanctum` + `tenant`. End-user browsing and purchasing.
- `/api/auth/*` — login/logout/me.

The two custom middleware aliases live in [bootstrap/app.php](bootstrap/app.php): `tenant` → `EnsureTenantContext`, `role` → `CheckRole`.

### Tenant scoping (critical to understand before touching models or queries)
Tenant isolation is enforced by a **global Eloquent scope**, not by controller-level filtering:

- [app/Models/Scopes/TenantScope.php](app/Models/Scopes/TenantScope.php) reads the authenticated user. If the user has a `tenant_id`, it adds `where {table}.tenant_id = ?` to every query. If the user has no `tenant_id` (super admin or guest), **no filter is applied** — they see across tenants.
- Tenant-owned models opt in via the [HasTenantScope](app/Traits/HasTenantScope.php) trait, which boots the scope and exposes `Model::withoutTenantScope()` for explicit bypass.
- [User](app/Models/User.php) attaches `TenantScope` directly in `booted()` rather than via the trait.
- [EnsureTenantContext](app/Http/Middleware/EnsureTenantContext.php) lets super admins and users with `tenant_id == null` through unconditionally; otherwise it verifies the tenant exists and is active. It does **not** itself set any "current tenant" — the scope derives tenant from the auth user.

Implications:
- Never write raw queries that bypass Eloquent on tenant-owned tables without re-applying the tenant filter manually.
- Super admin code paths must use `withoutTenantScope()` only when intentional; by default super admins already see everything because they have `tenant_id = null`.
- Cross-tenant guards still need to be enforced explicitly in services that mutate data — see [TicketPurchaseService](app/Services/TicketPurchaseService.php) for the pattern (compares `ticket_type->tenant_id` against `user->tenant_id` even though the scope already filtered the query).

### Ticket purchase flow
[TicketPurchaseService::purchase()](app/Services/TicketPurchaseService.php) is the single source of truth for buying tickets. It runs inside a `DB::transaction`, uses `lockForUpdate()` on `TicketType` rows to prevent oversell, validates active/sale-window/inventory, increments `quantity_sold`, then creates a `TicketOrder` plus one `AttendeeTicket` per seat with a UUID `reference_code`. Any new purchase entry points should call this service rather than re-implementing the logic.

### Controllers are split by audience
[app/Http/Controllers/](app/Http/Controllers/) is organized by role rather than by resource: `Super/`, `Admin/`, `Public/`, `User/`, `Auth/`. The same underlying resource (e.g. events) often has separate controllers per audience because each audience has different fields, filters, and policies. When adding endpoints, place them in the audience namespace that matches the route group.

### Policies
Policies in [app/Policies/](app/Policies/) (Event, TicketType, AttendeeTicket, Tenant) handle per-record authorization on top of the role middleware. Role middleware gates the *route*; policies gate the *record*.
