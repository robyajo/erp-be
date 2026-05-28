# Aureus ERP — Project Analysis

> **Version:** 1.4.0 | **License:** MIT | **Author:** Webkul

---

## 1. Ringkasan (Executive Summary)

**Aureus ERP** adalah sistem ERP (*Enterprise Resource Planning*) *open-source* berbasis **Laravel 13** + **Filament 5** yang dirancang untuk UKM hingga perusahaan skala besar. Mengusung arsitektur plugin modular, Aureus ERP memungkinkan pengguna mengaktifkan hanya modul yang diperlukan.

| Atribut | Detail |
|---------|--------|
| PHP | ^8.3 |
| Laravel | ^13.0 |
| Filament | ^5.0 |
| Livewire | ^4.0 |
| Tailwind CSS | ^4.x |
| Database | SQLite (dev) / MySQL 8.0 (prod) |
| Testing | Pest 4 (PHP) + Playwright (E2E) |
| API | RESTful (Sanctum) + Scribe docs |

---

## 2. Arsitektur Aplikasi

### 2.1. Struktur Direktori Utama

```
aureuserp/
├── app/                    # Aplikasi inti Laravel
│   ├── Http/
│   │   ├── Controllers/    # Base Controller
│   │   └── Middleware/      # SetLocale middleware
│   ├── Models/
│   │   └── User.php        # User model default
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── Filament/
│           ├── AdminPanelProvider.php
│           └── CustomerPanelProvider.php
├── bootstrap/              # Konfigurasi boot Laravel
│   ├── app.php             # Middleware, exception handling
│   └── providers.php       # Daftar Service Provider (31 provider)
├── config/                 # 20 file konfigurasi
├── database/
│   ├── migrations/         # 11 migrasi inti
│   └── seeders/            # 3 seeder inti
├── plugins/webkul/         # 26 plugin modular
├── resources/              # CSS, JS, Blade views, SVG
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── tests/                  # Pest + Playwright
└── docker/                 # Produksi Docker (Nginx + PHP-FPM)
```

### 2.2. Bootstrap & Service Container

Bootstrap `bootstrap/app.php` mengonfigurasi:

- **Routing:** web, api, commands, health check (`/up`)
- **Middleware global:** `SetLocale` (menentukan bahasa berdasarkan query parameter, user authenticated, session, atau fallback)
- **Exception Handling:** Semua response JSON terstruktur untuk ValidationException (422), AuthenticationException (401), AuthorizationException (403), ModelNotFoundException (404), NotFoundHttpException (404), dan Throwable generic (500)

`bootstrap/providers.php` mendaftarkan **31 service provider** yang mencakup semua plugin inti dan opsional.

### 2.3. Panel Filament

Dua panel terpisah:

#### Admin Panel (`/admin`)
- Panel ID: `admin` (default)
- Fitur: Login, reset password, verifikasi email, profil, two-factor authentication (recoverable)
- 15 grup navigasi
- Middleware: EncryptCookies, StartSession, AuthenticateSession, ShareErrorsFromSession, PreventRequestForgery, SubstituteBindings, dll.
- Global Search: `GlobalSearchProvider`

#### Customer Panel (`/`)
- Panel ID: `customer`
- Path: `/` (root)
- Dark mode: disabled
- Auth guard: `customer`
- Digunakan untuk portal pelanggan (contoh: plugin `purchases` memiliki cluster customer)

### 2.4. Middleware & Lokalisasi

**SetLocale middleware** menentukan locale dengan prioritas:
1. Query parameter `?lang=`
2. Properti `language` user yang terautentikasi
3. Session locale (guest)
4. `config('app.locale')` / `config('app.fallback_locale')`

Bahasa yang didukung: **English (EN)** dan **Arabic (AR)** dengan dukungan RTL penuh.

---

## 3. Plugin System (Arsitektur Modular)

### 3.1. Konsep Dasar

Aureus ERP memiliki sistem plugin berbasis **Spatie Laravel Package Tools** dengan lapisan orkestrasi kustom dari **Plugin Manager**.

Setiap plugin adalah _self-contained Laravel package_ yang terdeteksi otomatis melalui `wikimedia/composer-merge-plugin` (menggabungkan `plugins/*/*/composer.json`).

### 3.2. Hirarki Plugin

| Tipe | Deskripsi | Daftar Plugin |
|------|-----------|---------------|
| **Core** | Selalu dimuat, menyediakan infrastruktur dasar | `support`, `security`, `chatter`, `plugin-manager`, `fields`, `table-views`, `analytics` |
| **Opsional** | Dapat diinstal sesuai kebutuhan | `accounting`, `accounts`, `blogs`, `contacts`, `employees`, `full-calendar`, `inventories`, `invoices`, `manufacturing`, `partners`, `payments`, `products`, `projects`, `purchases`, `recruitments`, `sales`, `time-off`, `timesheets`, `website` |

### 3.3. Struktur Internal Plugin

Setiap plugin mengikuti struktur konsisten:

```
plugins/webkul/{nama}/
├── src/
│   ├── {Nama}Plugin.php              # Filament Plugin (implements Filament\Contracts\Plugin)
│   ├── {Nama}ServiceProvider.php     # Service Provider (extends PackageServiceProvider)
│   ├── Models/                       # Eloquent models
│   ├── Filament/
│   │   ├── Clusters/                 # Grup navigasi (Configuration, Orders, Products, dll.)
│   │   │   └── {Cluster}/
│   │   │       └── Resources/        # CRUD resources
│   │   ├── Resources/                # Top-level resources
│   │   ├── Pages/                    # Standalone pages
│   │   ├── Widgets/                  # Dashboard widgets
│   │   ├── Actions/                  # Custom actions
│   │   ├── Forms/                    # Custom form components
│   │   ├── Infolists/                # Custom infolist components
│   │   └── Tables/                   # Custom table components
│   ├── Http/
│   │   ├── Controllers/API/V1/      # REST API controllers (versioned)
│   │   ├── Requests/                 # Form requests
│   │   └── Resources/V1/            # API resource transformers
│   ├── Livewire/                     # Livewire components
│   ├── Policies/                     # Authorization policies
│   ├── Services/                     # Business logic services
│   ├── Enums/                        # PHP Enums
│   ├── Events/                       # Custom events
│   └── Traits/                       # Shared traits
├── routes/
│   ├── web.php                       # Web routes
│   └── api.php                       # API routes (auth:sanctum)
├── database/migrations/              # Migrasi plugin
├── resources/views/                  # Blade views
├── resources/lang/                   # Plugin translations
└── config/
    └── filament-shield.php           # RBAC configuration
```

### 3.4. Plugin State Management

Plugin Manager menggunakan tabel database `plugins` untuk melacak status instalasi. Mekanisme:

1. **Install:** `php artisan {plugin}:install` — menjalankan migrasi, seeder, dan mencatat di tabel `plugins`
2. **Uninstall:** `php artisan {plugin}:uninstall` — menghapus tabel dan data (dengan peringatan backup)
3. **Dependency Check:** Plugin dapat mendeklarasikan dependensi via `hasDependencies()`; instalasi otomatis memeriksa dan meminta persetujuan
4. **Core check:** `Package::isCore()` menentukan apakah plugin harus selalu dimuat

### 3.5. Komunikasi Antar Plugin

Plugin berkomunikasi melalui **Laravel Events**:

| Event | Listener (Plugin) |
|-------|-------------------|
| `OperationDone` (inventories) | `ComputeSaleOrderListener` (sales) |
| `OperationDone` (inventories) | Purchase order listeners (purchases) |
| `OperationBackOrdered` (inventories) | `ComputeSaleOrderListener` (sales) |
| `MovePaid` | `SaleMovePaidListener` (sales) |
| `OrderConfirmed`, `OrderCanceled`, `OrderDrafted` (sales) | Internal listeners |

### 3.6. Alur Registrasi Plugin

```
1. Composer merge-plugin menggabungkan plugins/*/*/composer.json
2. bootstrap/providers.php mendaftarkan service provider plugin
3. {Nama}ServiceProvider extends PackageServiceProvider
4. packageRegistered() memanggil Panel::configureUsing() untuk mendaftarkan Filament Plugin
5. {Nama}Plugin memeriksa Package::isPluginInstalled() sebelum register resources
6. PackageServiceProvider boot() memuat migrasi, routes, views, config sesuai status
```

---

## 4. Daftar Lengkap Plugin & Fungsionalitas

### 4.1. Core Plugins (Infrastruktur)

| Plugin | Namespace | Model Count | Deskripsi |
|--------|-----------|-------------|-----------|
| **Support** | `Webkul\Support` | 21 | Core infrastruktur: negara, mata uang, bank, UOM, calendar, activity plan, email template, company, state, UTM tracking. Menyediakan helpers, Filament defaults, RTL support, router macros. |
| **Security** | `Webkul\Security` | 7 | RBAC: user, role, permission, team, company, invitation. Bouncer authorization (global/group/individual). Two-factor authentication. User scoping. |
| **Chatter** | `Webkul\Chatter` | 3 | Activity stream & messaging: message, follower, attachment. Chatter widget di semua halaman. Log aktivitas otomatis. |
| **Plugin Manager** | `Webkul\PluginManager` | 1 | Orchestrasi plugin: install, uninstall, dependency management. GUI Plugin Manager. |
| **Fields** | `Webkul\Field` | — | Custom field management untuk data structures. |
| **Table Views** | `Webkul\TableViews` | — | Customizable data presentation framework untuk tampilan tabel. |
| **Analytics** | `Webkul\Analytic` | — | Business intelligence & reporting tools bawaan. |

### 4.2. Financial Management

| Plugin | Model Count | Deskripsi |
|--------|-------------|-----------|
| **Accounting** | 24 | Full accounting: journal entries, chart of accounts (parent-child), tax management, fiscal positions, cash rounding, credit notes, refunds. Reporting: Balance Sheet, Profit & Loss, Trial Balance, General Ledger, Aged Payable/Receivable, Partner Ledger. |
| **Accounts** | — | Core accounting operations (dependensi Accounting). |
| **Invoices** | — | Invoice generation & management (AP/AR). |
| **Payments** | — | Payment processing & tracking. |

### 4.3. Operations

| Plugin | Model Count | Deskripsi |
|--------|-------------|-----------|
| **Products** | 13 | Product catalog: categories, attributes (customizable), variants/combinations, packagings, price lists & rules, supplier info. |
| **Inventories** | 29 | Warehouse management: locations, operation types, routes & rules, lots/serial numbers, packages, storage categories, replenishment (order points), scrapping, procurement groups. |
| **Manufacturing** | — | Bill of Materials (BOM), Manufacturing Orders, Work Centers & Operations. |
| **Purchases** | 17 | Procurement: RFQ, purchase orders, purchase agreements (blanket orders), requisitions, vendor price lists, receipt/bill integration. **Dual-panel** (admin + customer portal). |
| **Sales** | 21 | Sales pipeline: quotations, orders (confirmed/done/cancel), order templates, advanced payment invoices, upsell offers. Integration dengan inventory moves & invoice. |

### 4.4. Human Resources

| Plugin | Model Count | Deskripsi |
|--------|-------------|-----------|
| **Employees** | 19 | Employee management: departments, job positions, skills/skill levels, calendars & attendance, work locations, departure reasons, resume/experience tracking. |
| **Recruitments** | — | Applicant tracking & hiring. |
| **Time Off** | — | Leave management & tracking. |
| **Timesheets** | — | Employee work hour tracking. |

### 4.5. Customer & Partner

| Plugin | Model Count | Deskripsi |
|--------|-------------|-----------|
| **Contacts** | — | Contact management untuk customers & vendors. |
| **Partners** | — | Partner relationship management. |

### 4.6. Content & Project

| Plugin | Model Count | Deskripsi |
|--------|-------------|-----------|
| **Projects** | — | Project planning & management. |
| **Blogs** | — | Content management & blogging. |
| **Website** | — | Customer-facing website module. |
| **Full Calendar** | — | Calendar integration & event management. |

---

## 5. Database Schema

### 5.1. Tabel Inti (app/database/migrations/)

| Tabel | Deskripsi |
|-------|-----------|
| `users` | User default Laravel (authenticatable) |
| `cache`, `cache_locks` | Cache system |
| `jobs`, `job_batches`, `failed_jobs` | Queue system |
| `settings` | Spatie settings |
| `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` | Spatie Permission (RBAC) |
| `imports`, `exports`, `failed_import_rows` | Filament Import/Export |
| `notifications` | Filament database notifications |
| `personal_access_tokens` | Sanctum API tokens |
| `plugins` | Plugin installation state (Plugin Manager) |

### 5.2. Ringkasan Migrasi Plugin

| Plugin | Jumlah Migrasi | Tabel Utama |
|--------|---------------|-------------|
| Support | 30 | `plugins`, `currencies`, `countries`, `companies`, `states`, `banks`, `activity_plans`, `activity_types`, `email_logs`, `email_templates`, `uom_categories`, `unit_of_measures`, `utm_*`, `currency_rates`, `calendars` |
| Security | 10 | `user_invitations`, `teams`, `user_team`, MFA columns |
| Products | 16 | `products_categories`, `products_products`, `products_tags`, `products_attributes`, `products_attribute_options`, `products_product_attribute_values`, `products_packagings`, `products_price_rules`, `products_product_suppliers`, `products_product_price_lists`, `products_product_combinations` |
| Sales | 24 | `sales_teams`, `sales_team_members`, `sales_orders`, `sales_order_lines`, `sales_order_line_taxes`, `sales_tag`, `sales_advance_payment_invoices`, `sales_order_templates` |
| Purchases | 18 | `purchases_order_groups`, `purchases_requisitions`, `purchases_orders`, `purchases_order_lines`, `purchases_order_line_taxes` |
| Inventories | 49 | `inventories_tags`, `inventories_warehouses`, `inventories_storage_categories`, `inventories_locations`, `inventories_operation_types`, `inventories_routes`, `inventories_rules`, `inventories_package_types`, `inventories_packages`, `inventories_lots`, `inventories_product_quantities`, `inventories_operations`, `inventories_moves`, `inventories_move_lines`, `inventories_scraps`, `inventories_procurement_groups` |
| Employees | 21 | `employees_work_locations`, `employees_departments`, `employees_categories`, `employees_employment_types`, `employees_skill_types`, `employees_skills`, `employees_job_positions`, `employees_calendars`, `employees_departure_reasons`, `employees_employees` |
| Chatter | 4 | `chatter_followers`, `chatter_messages`, `chatter_attachments` |
| Plugin Manager | 1 | `plugins` |

**Total: ~190+ migrasi** di seluruh plugin.

---

## 6. API & Routing

### 6.1. API Architecture

- **Auth:** Laravel Sanctum (Bearer token via `Authorization` header)
- **Versioning:** API v1 (`admin/api/v1/*`)
- **Docs:** Scribe API documentation (endpoints terdefinisi di `.scribe/endpoints/`)
- **Login endpoint:** `POST /admin/api/v1/login`

### 6.2. Routes per Plugin

| Plugin | Tipe Routes | Prefix |
|--------|-------------|--------|
| Sales | API | `admin/api/v1/sales/*` |
| Products | API | `admin/api/v1/products/*` |
| Security | Web + API | Web: invitation accept; API: `admin/api/v1/auth/*` |
| Inventories | API | `admin/api/v1/inventories/*` (20 controllers) |
| Purchases | Web + API | Web: quotation respond; API: `admin/api/v1/purchases/*` |
| Support | API | `admin/api/v1/support/*` |

### 6.3. Web Routes

| Route | Method | Deskripsi |
|-------|--------|-----------|
| `/login` | GET | Redirect ke `/admin/login` |
| `/inspire` | (console) | Menampilkan kutipan inspiratif (cron hourly) |

---

## 7. Authentication & Authorization

### 7.1. Authentication

- **Guard:** `web` (admin panel), `customer` (customer portal)
- **Driver:** Sanctum (API tokens) + Session (web)
- **Multi-Factor Authentication:** Authenticator App (recoverable)
- **Password Reset:** Via email (admin panel + customer portal)
- **Email Verification:** Ada untuk admin panel

### 7.2. Authorization (RBAC)

- **Package:** Spatie Permission (`laravel-permission`) via **Filament Shield** (`bezhansalleh/filament-shield`)
- **Custom Models:** `Webkul\Security\Models\Permission`, `Webkul\Security\Models\Role`
- **Scope-based:** `Bouncer` class — global, group, dan individual level
- **Permission Generation:** Setiap plugin mendefinisikan resources/pages/widgets yang di-shield di `config/filament-shield.php`
- **User Scoping:** `UserPermissionScope` untuk membatasi akses data per user

### 7.3. Policy Pattern

Setiap plugin memiliki direktori `Policies/` dengan policy per-model. Contoh:
- `Webkul\Sale\Policies\OrderPolicy`
- `Webkul\Product\Policies\ProductPolicy`
- `Webkul\Inventory\Policies\OperationPolicy`

---

## 8. Frontend & UI

### 8.1. Stack

| Komponen | Teknologi |
|----------|-----------|
| Admin Panel | Filament 5 |
| CSS Framework | Tailwind CSS v4 |
| JavaScript | Alpine.js (bawaan Filament) + Axios |
| Build Tools | Vite 5 + PostCSS |
| Icons | Blade Icons + Custom SVG (18 icons) |

### 8.2. Filament Resources & Clusters

UI diorganisir dalam **Clusters** (grup navigasi). Contoh cluster umum:

| Cluster | Contoh Resources |
|---------|------------------|
| Configuration | ActivityPlan, Currency, Packaging, ProductAttribute, Tag |
| Orders | Customer, Order, Quotation |
| Products | Product |
| Settings | ManageInvoice, ManagePricing, ManageProducts |

Widget kustom: JournalCharts (Accounting), Chatter (semua halaman), RecordNavigationTabs (Support).

### 8.3. Fitur Frontend Spesifik

- **Language Switcher:** Dua komponen Blade (`auth-language-switcher`, `language-switcher`) dengan SVG flags EN/AR
- **RTL Support:** CSS penuh RTL dengan `HasRtlSupport` trait
- **Custom Form Components:** Repeater dengan Column Manager, State Flow (Alpine.js wire)
- **Custom Table Columns:** ProgressBarEntry
- **PDF Views:** Print actions untuk invoices, delivery slips, picking operations, barcode labels, lot labels

### 8.4. Asset Structure

```
resources/
├── css/
│   └── app.css              # Tailwind v4 + RTL support CSS
├── js/
│   ├── app.js               # Entry point
│   └── bootstrap.js          # Axios setup
├── svg/                      # 18 custom SVG icons
└── views/
    ├── filament/components/  # Language switchers
    ├── forms/components/     # State flow
    ├── scribe/               # API docs (Scalar)
    └── vendor/               # Vendor overrides
```

---

## 9. Dependency Overview

### 9.1. PHP Dependencies (Production)

| Package | Versi | Kegunaan |
|---------|-------|----------|
| `filament/filament` | ^5.0 | Admin panel framework |
| `livewire/livewire` | ^4.0 | Reactive UI components |
| `laravel/framework` | ^13.0 | Framework inti |
| `laravel/sanctum` | ^4.0 | API authentication |
| `spatie/laravel-permission` | ^6.0 | RBAC (via Filament Shield) |
| `spatie/laravel-query-builder` | ^7.0 | API query filtering/sorting |
| `spatie/eloquent-sortable` | ^5.0 | Drag-drop sorting |
| `bezhansalleh/filament-shield` | ^4.0 | Filament Shield permissions |
| `maatwebsite/excel` | ^3.1 | Import/Export Excel |
| `barryvdh/laravel-dompdf` | ^3.1 | PDF generation |
| `milon/barcode` | ^13.0 | Barcode generation |
| `knuckleswtf/scribe` | ^5.6 | API documentation |
| `flowframe/laravel-trend` | ^0.5 | Trend calculations |
| `guava/filament-icon-picker` | ^4.0 | Icon picker field |
| `wikimedia/composer-merge-plugin` | ^2.1 | Auto-discover plugins |

### 9.2. PHP Dependencies (Dev)

| Package | Kegunaan |
|---------|----------|
| `pestphp/pest` | PHP testing framework |
| `laravel/sail` | Docker development |
| `laravel/pint` | Code style fixer |
| `filament/upgrade` | Filament upgrade helper |
| `laravel/boost` | MCP server for Laravel |

### 9.3. Node Dependencies

| Package | Kegunaan |
|---------|----------|
| `tailwindcss` v4 | Utility-first CSS |
| `vite` v5 | Build tool |
| `laravel-vite-plugin` | Laravel Vite integration |
| `axios` | HTTP client |

---

## 10. Testing Strategy

### 10.1. PHP Testing (Pest 4)

```
tests/
├── Pest.php                 # Config + DatabaseTransactions trait
├── TestCase.php             # Base TestCase (abstract)
├── Feature/                 # Feature tests (belum ada file)
└── e2e-pw/                  # Playwright E2E tests
```

**Test Suites (phpunit.xml):**
1. `AccountFeature` — plugins/webkul/accounts/tests/Feature
2. `PartnerFeature` — plugins/webkul/partners/tests/Feature
3. `PurchaseFeature` — plugins/webkul/purchases/tests/Feature
4. `InventoryFeature` — plugins/webkul/inventories/tests/Feature
5. `SaleFeature` — plugins/webkul/sales/tests/Feature
6. `ProjectFeature` — plugins/webkul/projects/tests/Feature
7. `SupportFeature` — plugins/webkul/support/tests/Feature

**Plugin dengan test directories:** accounts, inventories, partners, products, projects, purchases, sales, support

### 10.2. E2E Testing (Playwright)

- **Location:** `tests/e2e-pw/`
- **Framework:** Playwright (TypeScript)
- **Structure:** Page Objects (`pages/`), Locators (`locator/`), Setup (`setup.ts`)
- **Shards:** GitHub Actions menjalankan dalam 4 shard untuk parallel execution

### 10.3. CI/CD (GitHub Actions)

| Workflow | Trigger | Detail |
|----------|---------|--------|
| `pest_tests.yml` | push/PR | PHP 8.3 + MySQL 8.0, composer install, migrate, pest |
| `playwright_tests.yml` | push/PR | 4 shards parallel, HTML report merge |
| `translations_check.yml` | push/PR | Cek konsistensi terjemahan AR/EN |
| `docker_publish.yml` | tag version | Build & publish Docker image (amd64 + arm64) |

---

## 11. Development Tools & Configuration

### 11.1. Code Style (Laravel Pint)

```json
{
    "preset": "laravel",
    "rules": {
        "concat_space": { "spacing": "none" },
        "binary_operator_spaces": { "operators": { "=>": "align" } }
    }
}
```

### 11.2. Dev Server Script

```bash
php artisan serve           # Server HTTP
php artisan queue:listen    # Queue worker
php artisan pail            # Log viewer
npm run dev                 # Vite HMR
```

Semua berjalan paralel via `composer run dev` (concurrently).

### 11.3. Database

- **Development:** SQLite (default, zero-config)
- **Production:** MySQL 8.0 (via Docker)
- **Queue:** Database driver (default)
- **Cache:** Database driver (default)

### 11.4. Environment Variables (.env.example)

| Variable | Default | Deskripsi |
|----------|---------|-----------|
| `APP_NAME` | YourERP | Nama aplikasi |
| `APP_LOCALE` | en | Bahasa default |
| `APP_CURRENCY` | USD | Mata uang default |
| `DB_CONNECTION` | sqlite | Database driver |
| `SESSION_DRIVER` | database | Session storage |
| `CACHE_STORE` | database | Cache backend |
| `QUEUE_CONNECTION` | database | Queue backend |
| `FILESYSTEM_DISK` | public | File storage disk |

---

## 12. Deployment (Docker Production)

### 12.1. Infrastruktur

- **Base Image:** Ubuntu 24.04
- **PHP:** 8.4 (FPM) dengan ekstensi: bcmath, cli, curl, exif, fpm, gd, gmp, intl, mbstring, mysql, soap, xml, zip
- **Web Server:** Nginx
- **Database:** MySQL 8.0
- **Cache:** Redis (Alpine)
- **Mail:** Mailpit (dev) / SMTP (prod)
- **Process Manager:** Supervisor (PHP-FPM + queues)

### 12.2. Build Process (Dockerfile Multi-stage)

1. Clone repository
2. `composer install --no-dev`
3. Build frontend assets (Vite)
4. `php artisan erp:install` (migrate + seed + role generation)
5. Konfigurasi Nginx + PHP-FPM + Supervisor
6. Health check: `/health`

### 12.3. Port Mapping

| Port | Service |
|------|---------|
| 80 | Web server (Nginx) |
| 3306 | MySQL |
| 6379 | Redis |
| 5173 | Vite HMR |
| 1025/8025 | Mailpit |

---

## 13. Model Architecture Highlights

### 13.1. User Model

`App\Models\User` hanyalah model default Laravel. Model user sesungguhnya adalah **`Webkul\Security\Models\User`** yang di-binding via:

```php
// AppServiceProvider
$this->app->bind(Authenticatable::class, User::class);
```

### 13.2. Key Model Patterns

- **Enums:** Semua status menggunakan PHP 8.1+ Enums (contoh: `OrderState`, `ProductType`, `OperationState`)
- **Traits:** `HasChatter`, `HasLogActivity`, `HasModifyState`, `HasPermissionScope`, `HasScopedPermissions` — cross-cutting concerns
- **Soft Deletes:** Beberapa model menggunakan soft deletes
- **Sortable:** `spatie/eloquent-sortable` untuk drag-drop ordering

### 13.3. Cross-Plugin Model Integration

Plugin dapat menambahkan kolom ke tabel milik plugin lain via migrasi (contoh: Sales menambahkan kolom ke `inventories_operations`, `inventories_moves`).

---

## 14. Enums Registry

Berikut adalah enum yang teridentifikasi di berbagai plugin:

| Plugin | Enum |
|--------|------|
| Sales | `AdvancedPayment`, `InvoiceStatus`, `OrderDeliveryStatus`, `OrderDisplayType`, `OrderState`, `QtyDeliveredMethod` |
| Products | `AttributeType`, `PriceRuleApplyTo`, `PriceRuleBase`, `PriceRuleType`, `ProductRemoval`, `ProductType` |
| Inventories | `AllowNewProduct`, `CreateBackorder`, `DeliveryStep`, `GroupPropagation`, `LocationType`, `ManufactureStep`, `MoveState`, `MoveType`, `OperationState`, `OperationType`, `OrderPointTrigger`, `PackageUse`, `ProcureMethod`, `ProductTracking`, `ReceptionStep`, `ReservationMethod`, `RuleAction`, `RuleAuto`, `ScrapState` |
| Employees | `CalendarDisplayType`, `Colors`, `DayOfWeek`, `DayPeriod`, `DistanceUnit`, `Gender`, `MaritalStatus`, `ResumeDisplayType`, `WeekType`, `WorkLocation` |
| Purchases | `OrderInvoiceStatus`, `OrderReceiptStatus`, `OrderState`, `QtyReceivedMethod`, `RequisitionState`, `RequisitionType` |

---

## 15. Events & Listeners

Sistem event-driven untuk integrasi lintas plugin:

| Event | Source Plugin | Listeners |
|-------|---------------|-----------|
| `OrderConfirmed` | Sales | Internal sales listeners |
| `OrderCanceled` | Sales | Internal sales listeners |
| `OrderDrafted` | Sales | Internal sales listeners |
| `OperationDone` | Inventories | `ComputeSaleOrderListener` (Sales), Purchase order listeners |
| `OperationBackOrdered` | Inventories | `ComputeSaleOrderListener` (Sales) |
| `OperationAssigned` | Inventories | Internal |
| `OperationConfirmed` | Inventories | Internal |
| `OperationCanceled` | Inventories | Internal |
| `OperationReturned` | Inventories | Internal |
| `MovePaid` | Payments | `SaleMovePaidListener` (Sales) |

---

## 16. File & Storage

### 16.1. Struktur Storage

```
storage/
├── app/public/               # File publik (profile pics, attachments)
├── debugbar/                 # Debugbar data
├── framework/                # Cache, sessions, views
└── logs/                     # Laravel log files
```

### 16.2. Public Assets

```
public/
├── adminer.php               # Database management tool
├── css/                      # Compiled CSS
├── flags/                    # Country flag SVGs
├── fonts/                    # Custom fonts
├── images/                   # Logo & images
├── js/                       # Compiled JS
├── svg/                      # Custom SVG icons
├── index.php                 # Laravel front controller
├── .htaccess                 # Apache config
└── robots.txt                # SEO
```

---

## 17. Services & Managers

Beberapa plugin memiliki service/manager class untuk bisnis logic kompleks:

| Plugin | Class | Baris | Fungsi Utama |
|--------|-------|-------|--------------|
| Sales | `SaleManager` | ~785 | Confirm, cancel, compute, email, invoice, inventory rules |
| Inventories | `InventoryManager` | ~1400+ | Transfer confirmation, assignment, validation, cancellation, returns, back orders, procurement |
| Purchases | `PurchaseOrder` | — | Purchase order management |
| Security | `Bouncer` | — | Scope-based authorization (global/group/individual) |
| Support | `EmailService` | — | Email sending |
| Support | `EmailTemplateService` | — | Email template rendering |
| Support | `SchemaRegistry` | — | Dynamic schema registration |

---

## 18. Changelog Highlights (v1.0.0 — v1.4.0)

| Versi | Highlights |
|-------|------------|
| **v1.4.0** | Manufacturing module (BOM, Orders, Work Centers); Multi-language support; Calendar refinements; Parent-child accounts; Chart of Accounts grouping; Partial Return support; **Laravel 13 upgrade**; Playwright E2E tests; Column manager refactor |
| **v1.3.1** | RTL support refactor; Filament defaults traits; Router macros; Table views fixes |
| **v1.3.0** | REST API + Scribe docs; **Filament v5 upgrade**; Filament Shield integration; Arabic translations; Two-Factor Authentication |
| **v1.2.0** | Filament v4.1 upgrade; Plugin Manager GUI |
| **v1.1.0** | Bug fixes & performance optimizations |
| **v1.0.0** | Initial stable release with Filament v4 |

---

## 19. Tech Stack Summary

```
LARAVEL 13 ──── FILAMENT 5 ──── LIVEWIRE 4
    │               │                │
    │        ┌──────┴──────┐         │
    │   Spatie Permission    ┌───────┘
    │   (Filament Shield)    │
    │        │          Alpine.js
    │   Sanctum ──── REST API
    │        │
    ┌────────┴────────┐
    │                  │
 Tailwind v4       Vite 5
 (PostCSS)        (Build)
    │                  │
    └────── Blade Views
```

---

## 20. Catatan Arsitektur Penting

1. **Plugin as Full Package:** Setiap plugin adalah Laravel package mandiri yang bisa dikembangkan secara independen.
2. **Dual Panel:** Admin (`/admin`) + Customer (`/`) — memungkinkan portal pelanggan terpisah.
3. **Event-Driven Integration:** Lintas plugin menggunakan event system, bukan hard dependency.
4. **API-first:** REST API v1 di sebagian besar plugin dengan dokumentasi Scribe otomatis.
5. **RBAC Granular:** Setiap resource, page, dan widget memiliki permission sendiri via Filament Shield.
6. **RTL Support:** Full bidirectional layout (Arab/Inggris) dengan CSS native.
7. **Plugin State Tracking:** Tabel `plugins` di database mencegah double-install & mengelola dependensi.
8. **User Scoping:** Data dibatasi per user/group/global via `Bouncer`.
9. **Composer Merge:** Auto-discovery plugin tanpa registrasi manual.
