---
name: backend-architect
description: Build scalable Laravel 13 backend for modular ERP with Filament 5 admin panels, Sanctum API, and plugin architecture.
license: MIT
compatibility: opencode
metadata:
    audience: backend-engineers
---

# Project Context

- **Repo**: `project-erp/erp-be` — Laravel 13 API starter (will evolve into Aureus ERP)
- **Pair**: `project-erp/erp-fe` (Next.js 16 frontend)
- **Docs**: `concept-plans.md` (plugin system), `project.md` (Aureus ERP architecture), `db.md` (full schema)
- **Progress**: `progress.txt`

---

# Tech Stack

| Layer | Tech |
|-------|------|
| Framework | Laravel 13 |
| PHP | ^8.3 |
| Admin Panel | Filament 5 |
| Reactive UI | Livewire 4 + Alpine.js |
| Database | SQLite (dev) / MySQL 8.0 (prod) |
| API Auth | Sanctum (Bearer tokens) |
| API Docs | Scramble (`/docs/api`) |
| Query Builder | spatie/laravel-query-builder |
| RBAC | Spatie Permission + Filament Shield |
| Testing | Pest 4 |
| Code Quality | PHPStan max, Rector, Pint |

---

# Dev Commands

```bash
composer test      # lint → types → unit (run before push)
composer lint      # rector + pint auto-fix
composer test:lint # pint --test + rector --dry-run
composer test:types# phpstan (level max)
composer test:unit # config:clear + artisan test
```

With Docker: `docker compose run --rm app <cmd>`

---

# Architecture Rules

1. **API-only** — no Blade/Vite for frontend; routes in `routes/api/v{version}.php`
2. **API versioning** via `laravel-apiroute` — supports URI/header/query/Accept detection
3. **All controllers** use `ApiResponse` trait (`app/Traits/ApiResponse.php`) for JSON responses
4. **Rate limiters** in `AppServiceProvider`: `api` (60/min), `auth` (5/min), `authenticated` (120/min)
5. **`declare(strict_types=1)`** on every PHP file, all concrete classes `final`
6. **PHP 8 attributes** for `#[Fillable]`, `#[Hidden]` — never `$fillable`/`$hidden`
7. **Sanctum tokens** for auth — no JWT, no sessions

---

# After Every Task

- Update `progress.txt` (append, never overwrite)