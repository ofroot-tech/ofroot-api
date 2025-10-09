# Ofroot API

A Laravel 12 API service that exposes multi-tenant primitives (Tenants, Leads) and a pragmatic deployment shape for Render.

## TL;DR

- Production: container starts, runs `php artisan migrate --force`, serves on port 10000. No seeding at boot.
- Development: optional seeders behind `APP_SEED_ALLOWED=true` generate demo tenants and leads. Nothing seeds by default.

## Concepts

- Tenants own users and leads. Lead belongs to an optional tenant (`tenant_id` nullable, `ON DELETE SET NULL`).
- Migrations are ordered so foreign keys resolve (tenants before leads).
- Seeds and factories are dev-only unless explicitly enabled.

## Local Setup

1. Clone and install
    - PHP >= 8.2, Composer
    - composer install

2. Environment
    - cp .env.example .env
    - php artisan key:generate
    - For SQLite dev: touch database/database.sqlite and set in `.env`:
      - DB_CONNECTION=sqlite
      - DB_DATABASE=/absolute/path/to/database/database.sqlite

3. Database
    - php artisan migrate:fresh
    - Optionally enable dev seeds: set `APP_SEED_ALLOWED=true` in `.env`, then:
      - php artisan db:seed --class=TenantAndLeadSeeder

## Seeding Strategy (Production-Safe)

- `TenantAndLeadSeeder` refuses to run in production and when `APP_SEED_ALLOWED` is not `true`.
- Guard logic:
  - If `App::environment('production')` → skip
  - If `APP_SEED_ALLOWED !== 'true'` → skip
- CLI feedback is printed so you see whether it ran.

## Factories

- `database/factories/TenantFactory.php`: realistic names/domains.
- `database/factories/LeadFactory.php`: realistic contact/services; `tenant_id` null by default. Attach explicitly when needed.

## Models & Relations

- `Tenant` hasMany `users`, hasMany `leads`.
- `Lead` belongsTo `tenant`.
- `Lead` casts: `meta` as array. Scopes: `status($value)`, `zip($value)`.

## Render Deployment Behavior

- Dockerfile runs `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000`.
- No automatic seeding in production images.
- Verify DB connectivity with `php artisan migrate:status` in Render Shell.

## Testing

- Use Pest/PhpUnit.
- Suggested tests:
  - Seeder aborts in production.
  - Seeder runs with `APP_SEED_ALLOWED=true` in non-prod.
  - Lead↔Tenant relations and scopes.

## Operational Notes

- Removing a tenant sets `tenant_id` on related leads to NULL (no cascade delete).
- If you must seed in CI, set `APP_SEED_ALLOWED=true` in CI env.

## Change Log (high level)

- feat: add TenantFactory and LeadFactory
- feat: add TenantAndLeadSeeder with production guard
- chore: update DatabaseSeeder to call dev seeder conditionally
- chore: ensure leads.tenant_id is nullable with `nullOnDelete`

## Public Leads Ingestion API
- Endpoint: POST /api/leads
- Purpose: Accept inbound leads from landing pages/forms without auth.
- Validation rules:
  - tenant_id: nullable, exists:tenants,id
  - name: nullable string
  - email: nullable email
  - phone: required string
  - service: required string
  - zip: required string
  - source: nullable string
  - status: optional (defaults to 'new')
  - meta: nullable object

Example
```
POST /api/leads
{
  "zip": "90210",
  "service": "plumbing",
  "phone": "555-1111",
  "source": "landing-page"
}
```
Response: 201 Created with the lead JSON.

## Admin Tenants API (authenticated + admin)
Guarding
- Requires auth via Sanctum and admin middleware.
- Admin users are matched by email against ADMIN_EMAILS (comma-separated) env var.

Endpoints
- POST /api/tenants — create tenant
- GET /api/tenants — list current user’s tenant (example impl) or all tenants (adjust as needed)
- PUT /api/tenants/{id} — update tenant by id (admin-only helper)

Enable admin
- In `.env`, set: ADMIN_EMAILS=admin@example.com,ops@example.com

Examples
- curl -H "Authorization: Bearer <token>" -X POST /api/tenants -d '{"name":"Acme"}'
- curl -H "Authorization: Bearer <token>" /api/tenants
- curl -H "Authorization: Bearer <token>" -X PUT /api/tenants/1 -d '{"plan":"pro"}'

## Lead Assignment
Purpose
- Attach/detach leads to tenants as an admin-only operation.

Service
- App\Services\LeadAssignmentService with assign() and unassign().

Endpoints (admin-only)
- POST /api/leads/assign { lead_id, tenant_id } → attach
- POST /api/leads/unassign { lead_id } → detach

Security
- Requires auth via Sanctum and ADMIN_EMAILS allowlist.

## Frontend integration (Task 4.1)
- The Next.js app on Vercel should set `NEXT_PUBLIC_API_BASE_URL=https://ofroot-leads.onrender.com/api` so it knows where to call this API in production.
- If your Vercel app uses cookies/session with Sanctum, set `SANCTUM_STATEFUL_DOMAINS` to include your frontend hosts (e.g., `your-app.vercel.app,www.yourdomain.com`).
- Ensure CORS allows your frontend origins. Laravel's `HandleCors` middleware reads from `config/cors.php` if present; otherwise the default is permissive. If you tighten CORS, whitelist:
  - `https://<your-project>.vercel.app`
  - Any custom domains you add later.

Local dev pairings
- Frontend: `NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api`
- API: run `php artisan serve` (port 8000) and `php artisan migrate:fresh` as needed.
