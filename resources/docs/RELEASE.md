# OfRoot Frontend — Production Release 2025-10-13

This document captures the state, configuration, and checklist to safely ship the OfRoot frontend to production.

## Summary
- App Router, Next.js 15.5.3 with Turbopack
- Hardened SSR auth, token cookie, and admin guards
- Observability: Sentry for client and server with source maps
- Admin dashboard: Overview, Activity (scaffold), Users, Tenants, Subscribers, Billing
- API: Laravel 12 backend with admin endpoints for metrics, users, tenants, subscribers

## Stability Assessment
- Build: passes locally with `next build` (Turbopack)
- TypeScript: strict, no current errors in modified modules
- Runtime: SSR pages guard by token and `api.me` check
- Pages do not use mock data; zero states are explicit
- Error handling: API client returns structured errors; UI uses honest fallbacks

Risks:
- Subscribers and Activity are scaffolded; data may be empty until backend integration expands
- Global search is a placeholder (non-blocking)

Mitigations:
- Honest zero states and clear messaging
- Feature flags possible via environment variables

## Environments / Config
Set in Vercel project or `.env.production`:

- NEXT_PUBLIC_API_BASE_URL=https://api.ofroot.com
- SENTRY_DSN=your-dsn
- SENTRY_ORG=ofroot
- SENTRY_PROJECT=web-frontend
- SENTRY_AUTH_TOKEN=token-with-release-permissions

Cookies:
- Production token cookie name: `__Host-ofroot_token` (secure, httpOnly, sameSite=lax)

## Build & Deploy
1. Run tests and type-check
   - npm ci
   - npm run test
   - npm run build
2. Upload Sentry sourcemaps (CI)
   - npm run sentry:sourcemaps
3. Deploy to Vercel with required env vars

## Release Checklist
- [ ] API Base URL points to production
- [ ] Admin endpoints available and authenticated
- [ ] Sentry DSN configured and issues visible
- [ ] Source maps uploading on release
- [ ] Login flow works end-to-end on prod
- [ ] Dashboard pages render with real or zero-state data
- [ ] Robots: no sensitive pages indexed (handled by app auth)
- [ ] Rollback plan: redeploy previous Vercel build

## Rollback
- Revert to prior Vercel deployment using Vercel dashboard
- Or redeploy the previous git commit/tag

## Post-Release Monitoring
- Sentry: errors and performance
- Vercel analytics: traffic and latency
- API logs/metrics: auth failures, 5xx rate

---

If all checklist items are satisfied, this frontend is safe to release to production.
