# Kadad CMS roadmap

## 0.0.1 — Foundation prototype
- [x] PHP/MySQL project foundation
- [x] Initial database schema
- [x] RTL admin shell
- [x] Basic content CRUD
- [x] Basic product catalog CRUD
- [ ] Installer and authentication hardening (next patch)

## 0.0.2 — Reliability and security
- Automated tests and supported PHP/database matrix
- Schema migrations, backup/restore, rate limiting
- Capability-based permissions and audit events
- Pagination, search, validation summaries

## 0.1.0 — Publishing
- Draft/published workflow, public rendering and slugs
- Categories, tags, SEO metadata, revisions
- Safe media library

## 0.2.0 — Commerce
- Product variants, inventory ledger and order state machine
- Cart, checkout, shipping, taxes and coupons
- Payment gateway abstraction with secrets stored outside source control

## 0.3.0 — Extensibility
- Plugin lifecycle and capability model
- Theme API and visual site editor
- Scoped REST API and localization

Do not label a release production-ready until security review, install/upgrade tests, backup/restore tests, and automated regression tests pass.
