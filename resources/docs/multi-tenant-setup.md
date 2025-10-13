# Multi-tenant Data Model and Access Roles

This document describes the canonical data model for a subscription-based, multi-tenant platform running across Helpr, OnTask, and Health modules. It defines the actors (User, Tenant, Subscriber), their relationships, and provides concise SQL examples, performance notes, and access-control guidance.


## Core definitions

- User — Any individual with an account in the system. Includes patients, clients, end-users, admins, physicians, or service providers.
- Tenant — An organization, business, or clinic using the platform. Each tenant owns an isolated workspace and its users.
- Subscriber — The paying account (often the tenant owner) that manages billing, plans, and renewals.

How they connect: subscriber → tenant → users

- A subscriber pays for a tenant’s access.
- A tenant is the organization that uses the product.
- A user is a person who logs in and acts inside a tenant.


## Examples by module

- Helpr: a home service business (tenant) with technicians and managers (users), paid by the owner (subscriber).
- OfRoot Health: a healthcare clinic (tenant) with physicians and admins (users), paid by the clinic’s billing owner (subscriber).
- CMS/OnTask: a marketing agency (tenant) with editors and clients (users), paid by the agency account owner (subscriber).


## A. ERD (text diagram)

```
[SUBSCRIBERS] 1 --- 1..* [TENANTS] 1 --- 1..* [USERS]
         \                         \
          \                        \--< [TENANT_FEATURES] >-- [FEATURES]
           \
            \--< [INVOICES]
```


## B. Alphabetical list of tables (one-sentence purpose)

- features — Catalog of features or plans.
- invoices — Billing records for subscribers.
- roles — Possible roles (admin, member, provider).
- subscriber_payment_methods — Stored payment method metadata.
- subscribers — The paying account (owner of billing).
- tenant_features — Which features a tenant has enabled.
- tenants — Organization-level container and settings.
- user_roles — Role assignments per user per tenant.
- users — Individual accounts that can log in.
- workspaces (optional) — Sub-division under tenant if needed.


## C. Short explanations (alphabetical)

- features — Defines capabilities (e.g., ECG-analysis, Helpr).
- invoices — Links money to a subscriber and time period.
- roles — Powers access control.
- subscriber_payment_methods — Helps with retries and refunds.
- subscribers — Pays, upgrades, and owns billing contact.
- tenant_features — Toggles features per tenant.
- tenants — Holds tenant-specific data and isolation keys.
- user_roles — Connects users to roles inside tenants.
- users — Stores auth, profile, and security fields.
- workspaces — Optional for multi-campus or multi-business tenants.


## D. Key relationships (alphabetical)

- invoices.subscriber_id → subscribers.id
- tenant_features.tenant_id → tenants.id
- tenants.subscriber_id → subscribers.id
- user_roles.tenant_id → tenants.id
- user_roles.user_id → users.id
- users.tenant_default_id → tenants.id (optional convenience)


## E. Example SQL schema (concise, core tables only)

```sql
-- subscribers: the paying account
CREATE TABLE subscribers (
  id                UUID PRIMARY KEY,
  name              TEXT NOT NULL,
  billing_email     TEXT NOT NULL,
  stripe_customer_id TEXT, -- or payment gateway id
  created_at        TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

-- tenants: organization using the app
CREATE TABLE tenants (
  id            UUID PRIMARY KEY,
  subscriber_id UUID NOT NULL REFERENCES subscribers(id) ON DELETE CASCADE,
  name          TEXT NOT NULL,
  slug          TEXT UNIQUE NOT NULL,
  settings      JSONB DEFAULT '{}'::jsonb,
  created_at    TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

-- users: people who log in
CREATE TABLE users (
  id            UUID PRIMARY KEY,
  email         TEXT UNIQUE NOT NULL,
  password_hash TEXT,
  full_name     TEXT,
  is_active     BOOLEAN DEFAULT TRUE,
  created_at    TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

-- roles: permission roles
CREATE TABLE roles (
  id          SERIAL PRIMARY KEY,
  name        TEXT UNIQUE NOT NULL,
  description TEXT
);

-- user_roles: map user to role inside tenant
CREATE TABLE user_roles (
  id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id    UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  tenant_id  UUID NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
  role_id    INTEGER NOT NULL REFERENCES roles(id),
  created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
  UNIQUE(user_id, tenant_id, role_id)
);

-- features
CREATE TABLE features (
  id          UUID PRIMARY KEY,
  key         TEXT UNIQUE NOT NULL,
  name        TEXT NOT NULL,
  description TEXT
);

-- tenant_features: feature toggles per tenant
CREATE TABLE tenant_features (
  id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id  UUID NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
  feature_id UUID NOT NULL REFERENCES features(id),
  enabled    BOOLEAN DEFAULT FALSE,
  config     JSONB DEFAULT '{}'::jsonb,
  UNIQUE(tenant_id, feature_id)
);

-- invoices
CREATE TABLE invoices (
  id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  subscriber_id UUID NOT NULL REFERENCES subscribers(id) ON DELETE CASCADE,
  amount_cents  INTEGER NOT NULL,
  currency      TEXT NOT NULL DEFAULT 'usd',
  status        TEXT NOT NULL, -- e.g., pending, paid, failed
  period_start  TIMESTAMP,
  period_end    TIMESTAMP,
  created_at    TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);
```


## F. Multi-tenant strategy suggestions (alphabetical)

- Row-level isolation — Put tenant_id on tenant-scoped tables. Simple to scale.
- Schema-per-tenant — Strong isolation. Harder to maintain migrations.
- Hybrid — Use row-level for most data. Use schema-per-tenant for sensitive clients.


## G. Index and performance tips (alphabetical)

- Add composite index on (tenant_id, created_at) for large tables.
- Index users.email for login.
- Index tenants.slug for fast lookup.
- Partition huge audit tables by tenant_id or time.


## H. Access control & auth short notes (alphabetical)

- Implement role-based access control (RBAC).
- Keep session tokens scoped to tenant_id when applicable.
- Verify tenant_id in every API request that touches tenant data.


## Alignment with current app

- Frontend types already include Tenant, User, Subscriber. Admin lists exist for tenants, users, and subscribers.
- This document is the source of truth for refining backend migrations/models to fully align with the ERD above.


## Slug and location

- File: docs/multi-tenant-setup.md
- Public URL: /docs/multi-tenant-setup
