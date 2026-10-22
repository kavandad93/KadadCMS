# Kadad CMS

**A Persian-first, general-purpose CMS with commerce at its core.**

Kadad CMS is an independent project aiming to bring content publishing, ecommerce, and extensible website tools into one cohesive platform—with a simple admin experience and no mandatory page-builder ecosystem.

## Version 0.0.1 — Foundation prototype

This repository is being initialized. This release is an early prototype, not production-ready.

### Initial principles
- Persian-first, RTL-friendly administration
- PHP + MySQL/MariaDB compatibility for ordinary shared hosting
- Modular architecture for content, commerce, themes, and extensions
- Server-side validation, prepared SQL statements, CSRF protection, and password hashing
- Responsive, accessible UI with minimal dependencies
- Clear installation, upgrade, and security documentation

### Planned milestones
- **0.0.1:** repository foundation and initial admin prototype
- **0.0.2:** authentication hardening, permissions, migrations, tests
- **0.1.0:** publishing workflow, slugs, categories, SEO, media library
- **0.2.0:** product variants, inventory, carts, orders, checkout, payment adapter
- **0.3.0:** plugin API, themes, visual editor, scoped REST API

## Security notice

Do not deploy this prototype for production commerce. Payment processing, order management, permission granularity, rate limiting, backup/restore, automated tests, and a formal security review are not yet complete. Never commit credentials, API keys, customer data, or production database dumps.

## License

No license has been selected yet. Until one is added, do not assume the project is licensed for reuse.
