---
name: plugin-developer
description: Build modular Laravel plugins for ERP using composer-merge-plugin, spatie/laravel-package-tools, and conditional loading.
license: MIT
compatibility: opencode
metadata:
    audience: backend-engineers
---

# Core References

- **Full guide**: `concept-plans.md` (all plugin mechanics)
- **Full architecture**: `project.md` (26 plugins, Aureus ERP)
- **Database schema**: `db.md` (180+ tables across plugins)

---

# Plugin System Overview

```
plugins/{author}/{name}/
├── composer.json          ← PSR-4 autoload + Laravel providers
├── src/
│   ├── {Name}ServiceProvider.php
│   │   └── configureCustomPackage()
│   └── Models/ Controllers/ Services/ Events/ Policies/ Enums/
├── database/migrations/
├── routes/api.php
└── resources/lang/
```

Plugin is auto-discovered via `wikimedia/composer-merge-plugin` (configured in `composer.json` `extra.merge-plugin`).

---

# Required Packages

| Package | Purpose |
|---------|---------|
| `wikimedia/composer-merge-plugin` | Auto-merge `plugins/*/*/composer.json` |
| `spatie/laravel-package-tools` | Base Package and PackageServiceProvider |

---

# Plugin Mechanics

1. Each plugin has a `composer.json` with `extra.laravel.providers`
2. `composer-merge-plugin` merges them into root composer
3. Laravel auto-discovers the Service Provider
4. `PackageServiceProvider::boot()` checks `isPluginInstalled()` before loading routes/migrations
5. Plugin state tracked in `plugins` DB table (not filesystem)

---

# Key Classes

| Class | Function |
|-------|----------|
| `Webkul\PluginManager\Package` | Base package with `isCore()`, `hasDependency()`, `isPluginInstalled()` |
| `Webkul\PluginManager\PackageServiceProvider` | Extends Spatie's `PackageServiceProvider` |
| `InstallCommand` | `{name}:install` — runs migrations, seeds, marks installed |
| `UninstallCommand` | `{name}:uninstall` — reverses install |

---

# Route Convention

```
# Auth routes
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/auth/me

# Plugin Manager
GET    /api/v1/plugins
POST   /api/v1/plugins/install
POST   /api/v1/plugins/uninstall

# Per-plugin (loaded only if installed)
GET    /api/v1/{plugin}/...
```

---

# Plugin Types

| Type | Always Loaded | Examples |
|------|:---:|----------|
| **Core** | Yes | Support, Security, Chatter, PluginManager, Fields, Analytics |
| **Optional** | No | Accounting, Sales, Purchases, Inventories, Products, Employees, Projects, Website, Blogs |

---

# Event-Driven Integration

Plugins communicate via Laravel Events (not hard dependencies):
- `OperationDone` (Inventories) → `ComputeSaleOrderListener` (Sales)
- `MovePaid` (Payments) → `SaleMovePaidListener` (Sales)
- `OrderConfirmed/Canceled/Drafted` (Sales) → internal listeners

---

# After Every Task

- Update `progress.txt` (append, never overwrite)