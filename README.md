# Events System — Multi-Tenant Ticketing API

A multi-tenant events and ticketing REST API built on Laravel 12. Tenants (organizations) own events and ticket types; end-users browse events scoped to their tenant and purchase tickets through a transactional service that prevents oversell. A super admin tier sits above tenants for platform-wide management.

## Summary

Production-style multi-tenant SaaS backend implementing strict tenant isolation via a global Eloquent scope, three-role authorization (super admin / tenant admin / end-user), and a transactional ticket-purchase service that uses row-level locking to prevent overselling under concurrency. Authentication is handled by Laravel Sanctum personal access tokens. Routes are segmented by audience (Super, Admin, User, Public) and protected by a two-layer authorization model — middleware gates routes, policies gate records.

## Features

- **Multi-tenant data isolation** — every tenant-owned model is filtered automatically by a global Eloquent scope ([TenantScope](app/Models/Scopes/TenantScope.php)) attached via the [HasTenantScope](app/Traits/HasTenantScope.php) trait. Super admins (users with `tenant_id = null`) bypass the scope and can read across tenants.
- **Three-tier role model** — `super_admin`, `admin` (tenant admin), `user` (end-user), enforced by the [CheckRole](app/Http/Middleware/CheckRole.php) middleware (`role:` alias).
- **Tenant-context middleware** — [EnsureTenantContext](app/Http/Middleware/EnsureTenantContext.php) (`tenant` alias) verifies the authenticated user's tenant exists and is active before allowing requests through.
- **Sanctum token authentication** — login issues a personal access token via `createToken()`; logout revokes the current token. See [AuthController](app/Http/Controllers/Auth/AuthController.php).
- **Transactional ticket purchase with oversell protection** — [TicketPurchaseService](app/Services/TicketPurchaseService.php) wraps the entire purchase in a DB transaction, calls `lockForUpdate()` on `ticket_types`, validates active flag, sale window, and remaining inventory, then atomically increments `quantity_sold`, creates the `TicketOrder`, and issues one `AttendeeTicket` per seat with a UUID `reference_code`.
- **Cross-tenant purchase guard** — even after the global scope filters queries, the purchase service re-verifies that every ticket type's `tenant_id` matches the buyer's `tenant_id`.
- **Per-record authorization policies** — [EventPolicy](app/Policies/EventPolicy.php), [TicketTypePolicy](app/Policies/TicketTypePolicy.php), [AttendeeTicketPolicy](app/Policies/AttendeeTicketPolicy.php), [TenantPolicy](app/Policies/TenantPolicy.php) layered on top of role middleware.
- **Audience-segmented controllers** — Super, Admin, Public, User, and Auth controller namespaces, each with its own resource shape.
- **Pest 4 test suite** — runnable via `composer test`.
- **Demo seeder** — bootstraps two tenants, a super admin, tenant admins, end-users, an event, and a ticket type for immediate manual testing.

## Tech Stack

- PHP 8.2+
- Laravel 12
- Laravel Sanctum 4 (API token auth)
- Pest 4 + Pest Laravel plugin
- SQLite (default; configurable via `DB_CONNECTION`)
- Vite 7 + Tailwind CSS 4 (asset pipeline only — project is API-first)
- Laravel Pint (code style)

## Architecture

### Three-tier role model

| Role | `tenant_id` | Access |
| --- | --- | --- |
| `super_admin` | `null` | Cross-tenant; manages tenants and reads all tenant data |
| `admin` | set | Tenant admin; manages own tenant's events, ticket types, orders, attendees |
| `user` | set | End-user; browses tenant events and purchases tickets |

### Tenant scoping strategy

Tenant isolation is enforced at the **model layer**, not at the controller layer. The [TenantScope](app/Models/Scopes/TenantScope.php) global scope reads `Auth::user()` and:

1. If no user is authenticated, no filter is applied.
2. If the user has `tenant_id = null` (super admin), no filter is applied — they see across tenants.
3. Otherwise, every query gains `where {table}.tenant_id = {user.tenant_id}`.

Tenant-owned models opt in by using the [HasTenantScope](app/Traits/HasTenantScope.php) trait, which also exposes `Model::withoutTenantScope()` for explicit bypass. The [User](app/Models/User.php) model attaches the scope directly in its `booted()` method.

### Two-layer authorization

- **Route layer:** the `tenant` and `role:{name}` middleware (registered in [bootstrap/app.php](bootstrap/app.php)) gate which audience can hit which route group.
- **Record layer:** policies in [app/Policies/](app/Policies/) gate individual records inside controllers.

### Audience-segmented controllers

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/        # login / logout / me
│   │   ├── Super/       # super admin: tenants + cross-tenant reads
│   │   ├── Admin/       # tenant admin: own events, ticket types, orders, attendees
│   │   ├── Public/      # browse events + ticket types (auth required, tenant-scoped)
│   │   └── User/        # my orders, my tickets, place order
│   ├── Middleware/
│   │   ├── CheckRole.php
│   │   └── EnsureTenantContext.php
│   └── Requests/
├── Models/
│   ├── Scopes/TenantScope.php
│   ├── User.php  Tenant.php  Event.php  TicketType.php
│   ├── TicketOrder.php  AttendeeTicket.php
├── Policies/
├── Services/
│   └── TicketPurchaseService.php
└── Traits/
    └── HasTenantScope.php
```

## API Surface

All routes are defined in [routes/api.php](routes/api.php).

### Auth

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/api/auth/login` | Issue a Sanctum token for valid credentials |
| POST | `/api/auth/logout` | Revoke the current access token |
| GET | `/api/me` | Return the authenticated user |

### Super Admin (`auth:sanctum` + `role:super_admin`)

| Method | Path | Purpose |
| --- | --- | --- |
| GET/POST/PUT/PATCH/DELETE | `/api/super/tenants` (+ `{tenant}`) | Full tenant CRUD |
| GET | `/api/super/tenants/{tenant}/events` | List events for a tenant |
| GET | `/api/super/tenants/{tenant}/ticket-orders` | List ticket orders for a tenant |
| GET | `/api/super/tenants/{tenant}/attendee-tickets` | List attendee tickets for a tenant |

### Tenant Admin (`auth:sanctum` + `tenant` + `role:admin`)

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/api/admin/tenant` | View own tenant profile |
| PATCH | `/api/admin/tenant` | Update own tenant profile |
| GET/POST/PUT/PATCH/DELETE | `/api/admin/events` (+ `{event}`) | Manage own events |
| GET/POST/PUT/PATCH/DELETE | `/api/admin/events/{event}/ticket-types` (+ `{ticket_type}`) | Manage ticket types under an event |
| GET | `/api/admin/ticket-orders` | List orders for own tenant |
| GET | `/api/admin/ticket-orders/{ticketOrder}` | Show a single order |
| GET | `/api/admin/attendee-tickets` | List attendee tickets for own tenant |
| GET | `/api/admin/attendee-tickets/{attendeeTicket}` | Show a single attendee ticket |
| PATCH | `/api/admin/attendee-tickets/{attendeeTicket}` | Update an attendee ticket (e.g. mark scanned) |

### Authenticated User (`auth:sanctum` + `tenant`)

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/api/events` | Browse events (tenant-scoped) |
| GET | `/api/events/{event}` | Show a single event |
| GET | `/api/events/{event}/ticket-types` | List ticket types for an event |
| GET | `/api/my/orders` | List the authenticated user's orders |
| GET | `/api/my/orders/{ticketOrder}` | Show one of the user's orders |
| POST | `/api/orders` | Place a new order (calls `TicketPurchaseService`) |
| GET | `/api/my/tickets` | List the authenticated user's attendee tickets |

## Setup

### Prerequisites

- PHP 8.2 or newer (with `pdo_sqlite` extension enabled)
- Composer 2.x
- Node.js 18+ and npm
- Git

### Steps

```bash
git clone https://github.com/Mazoda/events-system.git
cd events-system
```

```bash
composer install
```

Copy the environment file and generate an app key:

```bash
# macOS / Linux
cp .env.example .env

# Windows (cmd)
copy .env.example .env
```

```bash
php artisan key:generate
```

The default DB connection in [config/database.php](config/database.php) is `sqlite`. Create the database file:

```bash
# macOS / Linux
touch database/database.sqlite

# Windows (PowerShell)
New-Item database/database.sqlite -ItemType File

# Windows (cmd)
type nul > database\database.sqlite
```

Run migrations and seed the demo data (two tenants, users for each role, a sample event and ticket type):

```bash
php artisan migrate --seed
```

Install and build frontend assets:

```bash
npm install
npm run build
```

Start the application. The simplest option is the standalone server:

```bash
php artisan serve
```

For a full local dev loop (HTTP server, queue listener, and Vite dev server in parallel), use the bundled Composer script:

```bash
composer dev
```

The API is now reachable at `http://127.0.0.1:8000`.

### Demo accounts (created by [DemoSeeder](database/seeders/DemoSeeder.php))

| Role | Email | Password |
| --- | --- | --- |
| Super admin | `super@admin.com` | `password` |
| Tenant A admin | `admin@tenant-a.com` | `password` |
| Tenant A user | `user@tenant-a.com` | `password` |
| Tenant B admin | `admin@tenant-b.com` | `password` |
| Tenant B user | `user@tenant-b.com` | `password` |

### Creating a super admin manually (without seeding)

If you skipped `--seed`, create one through Tinker:

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'tenant_id' => null,
    'role' => 'super_admin',
    'name' => 'Super Admin',
    'email' => 'super@admin.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password'),
]);
```

## Authentication Example

Log in to obtain a token:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@tenant-a.com","password":"password"}'
```

Response:

```json
{
  "token": "1|abcdef...",
  "user": { "id": 3, "name": "Tenant A User", "...": "..." }
}
```

Use the token to call a protected endpoint:

```bash
curl http://127.0.0.1:8000/api/events \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef..."
```

Place an order:

```bash
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abcdef..." \
  -d '{"items":[{"ticket_type_id":1,"quantity":2}]}'
```

## Running Tests

```bash
composer test
```

This clears the config cache and runs the Pest 4 suite via `php artisan test`.

Run a single test file or filter by name:

```bash
php artisan test --filter=TicketPurchase
vendor/bin/pest tests/Feature/SomeTest.php
```

## Code Style

```bash
vendor/bin/pint
```

## License

MIT — see [composer.json](composer.json).
