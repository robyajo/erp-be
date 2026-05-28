# Laravel API + Plugin System

> Panduan membangun REST API modular dengan plugin architecture — Laravel sebagai backend API murni (tanpa Blade, tanpa Filament, JSON only)

---

## Daftar Isi

- [1. Konsep Dasar](#1-konsep-dasar)
- [2. Struktur Direktori](#2-struktur-direktori)
- [3. Stack Teknologi](#3-stack-teknologi)
- [4. Komponen Wajib](#4-komponen-wajib)
- [5. Langkah Implementasi](#5-langkah-implementasi)
- [6. Cara Kerja](#6-cara-kerja)
- [7. Contoh Plugin](#7-contoh-plugin)
- [8. Testing](#8-testing)
- [9. Checklist Build](#9-checklist-build)

---

## 1. Konsep Dasar

### 1.1. Composer Merge Plugin

Composer hanya membaca package dari `vendor/`. Agar package di `plugins/` terbaca, gunakan `wikimedia/composer-merge-plugin` yang otomatis menggabungkan file `composer.json` dari folder manapun.

### 1.2. Laravel Package Auto-Discovery

Laravel membaca `extra.laravel.providers` dari setiap package yang terdaftar. Setelah composer merge menggabungkan plugin, Laravel otomatis mendeteksi Service Provider-nya.

### 1.3. Stateful Installation

Plugin dilacak via tabel database (`plugins`), bukan filesystem. Keuntungannya:

- Install/uninstall tanpa menghapus folder
- Conditional loading — migrasi & routes hanya dimuat jika plugin terinstal
- Dependency tracking antar plugin

---

## 2. Struktur Direktori

### 2.1. Gambaran Umum

```
my-app/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/API/V1/AuthController.php
│   │   └── Middleware/
│   └── Providers/AppServiceProvider.php
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/
│   ├── sanctum.php
│   └── cors.php
│
├── database/migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000002_create_personal_access_tokens_table.php
│   └── 2024_01_01_000001_create_plugins_table.php    ← WAJIB
│
├── plugins/
│   ├── core/plugin-manager/                          ← WAJIB (core)
│   │   ├── composer.json
│   │   └── src/
│   │       ├── PluginManagerServiceProvider.php
│   │       ├── Package.php
│   │       ├── PackageServiceProvider.php
│   │       ├── Console/InstallCommand.php
│   │       ├── Console/UninstallCommand.php
│   │       ├── Models/Plugin.php
│   │       └── Http/Controllers/API/V1/PluginController.php
│   │
│   ├── acme/blog/                                    ← Contoh plugin
│   │   ├── composer.json
│   │   └── src/
│   │       ├── BlogServiceProvider.php
│   │       ├── Models/ (Post, Category)
│   │       ├── Http/Controllers/API/V1/ (PostController, CategoryController)
│   │       ├── Http/Requests/
│   │       ├── Http/Resources/V1/
│   │       ├── Services/BlogService.php
│   │       ├── Events/PostCreated.php
│   │       ├── Listeners/
│   │       ├── Policies/PostPolicy.php
│   │       ├── Enums/PostStatus.php
│   │       └── Database/Seeders/
│   │   ├── database/migrations/
│   │   ├── resources/lang/
│   │   └── routes/api.php
│   │
│   └── acme/inventory/                               ← Contoh plugin
│       ├── composer.json
│       └── src/
│           ├── InventoryServiceProvider.php
│           ├── Models/ (Product, Warehouse)
│           ├── Http/Controllers/API/V1/
│           └── Services/StockService.php
│       ├── database/migrations/
│       └── routes/api.php
│
├── routes/api.php                                    ← Route API utama
├── vendor/wikimedia/composer-merge-plugin/           ← KUNCI
├── composer.json                                      ← + extra.merge-plugin
└── .env
```

### 2.2. Anatomi Plugin

```
plugins/acme/nama/
├── composer.json              ← Autoload PSR-4 + Laravel providers
├── src/
│   └── NamaServiceProvider.php
│       └── configureCustomPackage()
│           ├── ->name('nama')
│           ├── ->hasMigrations([...])
│           ├── ->runsMigrations()
│           ├── ->hasRoutes(['api'])
│           ├── ->hasDependencies([...])
│           └── ->hasInstallCommand(...)
├── database/migrations/
├── routes/api.php
└── resources/lang/
```

### 2.3. Route Convention

```
# Auth
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/auth/me

# Plugin Manager
GET    /api/v1/plugins
POST   /api/v1/plugins/install
POST   /api/v1/plugins/uninstall

# Plugin (dimuat otomatis jika terinstal)
GET    /api/v1/{plugin}/...
```

---

## 3. Stack Teknologi

### 3.1. Package yang Dibutuhkan

| Tipe      | Package                           | Fungsi                           |
| --------- | --------------------------------- | -------------------------------- |
| **WAJIB** | `laravel/framework`               | Framework inti                   |
| **WAJIB** | `laravel/sanctum`                 | API token auth                   |
| **WAJIB** | `wikimedia/composer-merge-plugin` | Auto-merge composer.json plugin  |
| **WAJIB** | `spatie/laravel-package-tools`    | Base class untuk membuat package |
| Opsional  | `spatie/laravel-permission`       | RBAC role & permission           |
| Opsional  | `spatie/laravel-query-builder`    | API filter, sort, include        |
| Opsional  | `knuckleswtf/scribe`              | Auto-generate API docs           |
| Dev       | `laravel/pint`                    | Code style fixer                 |
| Dev       | `pestphp/pest`                    | Testing                          |

### 3.2. `composer.json` Lengkap

```json
{
    "require": {
        "php": "^8.3",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "wikimedia/composer-merge-plugin": "^2.1",
        "spatie/laravel-package-tools": "^1.16",
        "spatie/laravel-permission": "^6.0",
        "spatie/laravel-query-builder": "^6.0"
    },
    "require-dev": {
        "laravel/pint": "^1.0",
        "pestphp/pest": "^3.0"
    },
    "extra": {
        "merge-plugin": {
            "include": ["plugins/*/*/composer.json"]
        }
    },
    "config": {
        "allow-plugins": {
            "wikimedia/composer-merge-plugin": true
        }
    }
}
```

---

## 4. Komponen Wajib

### 4.1. API Auth (Sanctum)

```bash
composer require laravel/sanctum
php artisan install:api
```

### 4.2. Tabel Database

```php
Schema::create('plugins', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('author')->nullable();
    $table->text('summary')->nullable();
    $table->string('version')->nullable();
    $table->string('icon')->nullable();
    $table->boolean('is_core')->default(false);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_installed')->default(false);
    $table->integer('sort')->default(0);
    $table->timestamps();
});

Schema::create('plugin_dependencies', function (Blueprint $table) {
    $table->foreignId('plugin_id')->constrained('plugins')->cascadeOnDelete();
    $table->foreignId('dependency_id')->constrained('plugins')->cascadeOnDelete();
    $table->unique(['plugin_id', 'dependency_id']);
});
```

### 4.3. Base Package Class

Extends `Spatie\LaravelPackageTools\Package`:

```php
class Package extends BasePackage
{
    public static array $plugins = [];
    public bool $isCore = false;
    public bool $runsMigrations = false;
    public bool $runsSeeders = false;
    public array $dependencies = [];

    public function isCore(bool $v = true): static { $this->isCore = $v; return $this; }
    public function hasDependency(string $dep): static { $this->dependencies[] = $dep; return $this; }

    public static function isPluginInstalled(string $name): bool
    {
        if (! static::$plugins) {
            static::$plugins = Plugin::all()->keyBy('name');
        }
        return static::$plugins[$name]->is_installed ?? false;
    }

    public function updateOrCreate(): Plugin { /* INSERT/UPDATE ke tabel plugins */ }
}
```

### 4.4. Base Service Provider

```php
abstract class PackageServiceProvider extends BasePackageServiceProvider
{
    abstract public function configureCustomPackage(Package $package): void;

    public function register()
    {
        $this->package = new Package;
        $this->configureCustomPackage($this->package);
        $this->packageRegistered();
    }

    public function boot()
    {
        if ($this->package->isCore || $this->package->isInstalled()) {
            $this->loadMigrationsFrom($path);
            $this->loadRoutesFrom($path);
        }
        $this->packageBooted();
    }
}
```

### 4.5. Install Command (Nama Dinamis)

```php
class InstallCommand extends Command
{
    public function __construct(Package $package)
    {
        $this->signature = $package->shortName().':install';
        $this->package = $package;
        parent::__construct();
    }

    public function handle()
    {
        foreach ($this->package->dependencies as $dep) {
            $this->call($dep.':install');
        }
        $this->runMigrations();
        $this->package->updateOrCreate();
        $this->info("{$this->package->shortName()} installed!");
    }
}
```

---

## 5. Langkah Implementasi

| Step | Perintah                                           | Keterangan        |
| ---- | -------------------------------------------------- | ----------------- |
| 1    | `composer create-project laravel/laravel my-app`   | Buat project baru |
| 2    | `php artisan install:api`                          | Install Sanctum   |
| 3    | `composer require wikimedia/composer-merge-plugin` | Merge plugin      |
| 4    | `composer require spatie/laravel-package-tools`    | Package tools     |
| 5    | Tambah `extra.merge-plugin` ke `composer.json`     | Lihat section 3.2 |
| 6    | `php artisan make:migration create_plugins_table`  | Buat migrasi      |
| 7    | Isi migrasi dengan schema di 4.2                   |                   |
| 8    | Buat folder `plugins/core/plugin-manager/src/`     |                   |
| 9    | Buat `Package.php`, `PackageServiceProvider.php`   | Lihat 4.3, 4.4    |
| 10   | Buat `InstallCommand.php`, `UninstallCommand.php`  | Lihat 4.5         |
| 11   | Buat `Models/Plugin.php`                           | Eloquent model    |
| 12   | Daftarkan ke `bootstrap/providers.php`             |                   |
| 13   | Buat plugin contoh `plugins/acme/blog/`            | Lihat 7.1         |
| 14   | `composer dump-autoload`                           |                   |
| 15   | `php artisan blog:install`                         | Test instalasi    |

---

## 6. Cara Kerja

### 6.1. Install via Artisan

```
composer dump-autoload
  └── Composer scan plugins/*/*/composer.json
      └── Merge ke composer root
          └── Laravel autodiscover BlogServiceProvider

php artisan blog:install
  └── InstallCommand::handle()
      ├── Install dependencies (jika ada)
      ├── Run migrations
      ├── Run seeders
      ├── INSERT INTO plugins (name, is_installed=1)
      └── Output: "blog installed!"
```

### 6.2. Install via API (dari Frontend)

```
POST /api/v1/plugins/install  { "name": "inventory" }
  │
  ├── Cek: sudah terinstal? → 400
  ├── Cek: dependencies terpenuhi? → 400
  ├── Artisan::call('inventory:install')
  │   ├── Migrasi database
  │   └── INSERT INTO plugins
  └── Response: { success: true, message: "..." }
```

```php
class PluginController extends Controller
{
    public function install(Request $request)
    {
        $name = $request->input('name');

        if (Package::isPluginInstalled($name)) {
            return response()->json(['message' => 'Already installed'], 400);
        }

        // Cek dependencies
        foreach ($this->getDependencies($name) as $dep) {
            if (! Package::isPluginInstalled($dep)) {
                return response()->json(['message' => "Install {$dep} first"], 400);
            }
        }

        Artisan::call("{$name}:install");

        return response()->json(['success' => true, 'message' => "{$name} installed"]);
    }
}
```

### 6.3. Runtime (setiap Request API)

```
GET /api/v1/blog/posts
  └── Laravel boot providers
      └── BlogServiceProvider::boot()
          └── if isPluginInstalled('blog')
              ├── loadMigrationsFrom()
              └── loadRoutesFrom()
      └── Route: PostController@index
          ├── auth:sanctum
          └── return JSON
```

### 6.4. Uninstall

```
php artisan blog:uninstall
  └── DELETE FROM plugins WHERE name = 'blog'
      → routes & migrasi berhenti dimuat otomatis
      → API /api/v1/blog/* → 404
```

---

## 7. Contoh Plugin

### 7.1. Plugin Blog (Sederhana)

**`plugins/acme/blog/composer.json`**:

```json
{
    "name": "acme/blog",
    "autoload": { "psr-4": { "Acme\\Blog\\": "src/" } },
    "extra": {
        "laravel": { "providers": ["Acme\\Blog\\BlogServiceProvider"] }
    }
}
```

**`plugins/acme/blog/src/BlogServiceProvider.php`**:

```php
class BlogServiceProvider extends PackageServiceProvider
{
    public function configureCustomPackage(Package $package): void
    {
        $package->name('blog')
            ->hasMigrations(['2024_01_01_000001_create_posts_table'])
            ->runsMigrations()
            ->hasRoutes(['api'])
            ->hasInstallCommand(fn($cmd) => $cmd->runsMigrations());
    }
}
```

**`plugins/acme/blog/routes/api.php`**:

```php
Route::prefix('api/v1/blog')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
});
```

### 7.2. Plugin dengan Dependency

```php
$package->name('sales')
    ->hasDependencies(['invoices', 'payments'])
    ->hasRoutes(['api'])
    ->hasInstallCommand(fn($cmd) => $cmd->installDependencies()->runsMigrations());
```

Saat `php artisan sales:install`, otomatis menjalankan `invoices:install` dan `payments:install` terlebih dahulu.

### 7.3. Response Format

```json
{
    "data": [
        {
            "id": 1,
            "title": "Hello World",
            "status": "published",
            "author": { "id": 1, "name": "Admin" },
            "created_at": "2024-01-01T00:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 1
    }
}
```

---

## 8. Testing

### 8.1. Install Plugin

```bash
php artisan list | grep :install
php artisan blog:install

# Cek database
php artisan tinker
>>> Plugin::where('name', 'blog')->first()->is_installed
=> true
```

### 8.2. API Test

```bash
# Login
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}' | jq -r '.token')

# Sebelum install → 404
curl -s -o /dev/null -w "%{http_code}" -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/blog/posts
# → 404

php artisan blog:install

# Setelah install → 200
curl -s -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/blog/posts
# → 200 + JSON
```

### 8.3. Uninstall

```bash
php artisan blog:uninstall
# → API /api/v1/blog/posts → 404
```

---

## 9. Checklist Build

- [ ] `composer create-project laravel/laravel`
- [ ] `php artisan install:api`
- [ ] `composer require wikimedia/composer-merge-plugin`
- [ ] `composer require spatie/laravel-package-tools`
- [ ] Konfigurasi `extra.merge-plugin` di `composer.json`
- [ ] Buat migrasi `plugins` & `plugin_dependencies`
- [ ] Buat model `Plugin`
- [ ] Buat class `Package`, `PackageServiceProvider`
- [ ] Buat `InstallCommand`, `UninstallCommand`
- [ ] Buat `plugins/core/plugin-manager/`
- [ ] Daftarkan core provider di `bootstrap/providers.php`
- [ ] Buat plugin contoh (`plugins/acme/blog/`)
- [ ] `composer dump-autoload`
- [ ] `php artisan blog:install` → sukses
- [ ] `GET /api/v1/blog/posts` → 200
- [ ] `php artisan blog:uninstall` → 404
