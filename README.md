<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

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
