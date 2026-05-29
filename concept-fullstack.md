# Laravel Fullstack + React + Plugin System

> Panduan membangun aplikasi fullstack modular — Laravel Breeze (React + Inertia) sebagai starter, ditambah plugin architecture untuk modularitas

---

## Daftar Isi

- [1. Konsep Dasar](#1-konsep-dasar)
- [2. Perbedaan dengan API-only](#2-perbedaan-dengan-api-only)
- [3. Struktur Direktori](#3-struktur-direktori)
- [4. Stack Teknologi](#4-stack-teknologi)
- [5. Komponen Wajib](#5-komponen-wajib)
- [6. Plugin dengan React](#6-plugin-dengan-react)
- [7. Langkah Implementasi](#7-langkah-implementasi)
- [8. Cara Kerja](#8-cara-kerja)
- [9. Contoh Plugin Fullstack](#9-contoh-plugin-fullstack)
- [10. Checklist Build](#10-checklist-build)

---

## 1. Konsep Dasar

### 1.1. Stack: Laravel + Inertia + React

Stack ini menggunakan **Laravel Breeze** dengan opsi React + Inertia:

- **Laravel** — Backend API + routing + auth session
- **Inertia.js** — Jembatan antara Laravel & React (tanpa perlu REST API)
- **React** — Frontend SPA-like component
- **Plugin system** — Sama seperti API-only: Composer Merge + conditional loading + tabel `plugins`

Perbedaan utama: plugin tidak hanya menyediakan endpoint API, tapi juga React page components yang dirender oleh Inertia.

### 1.2. Alur Request

```
Browser → URL → Laravel Route (web.php) → Inertia render → React component → HTML
```

Plugin mendaftarkan route di `web.php` yang mengembalikan Inertia response dengan component React dari plugin.

### 1.3. Stateful Installation

Sama seperti versi API: tabel `plugins` mencatat status instalasi. Migrasi, routes, dan React components hanya dimuat jika plugin terinstal.

---

## 2. Perbedaan dengan API-only

| Aspek | API-only | Fullstack (Inertia + React) |
|-------|----------|----------------------------|
| Auth | Sanctum token | Session + CSRF |
| Routes | `routes/api.php` | `routes/web.php` |
| Frontend | Pisah repo (React/Vue/mobile) | Satu repo, React via Inertia |
| Response | JSON | JSON + Inertia page render |
| Plugin UI | Tidak ada | React components + halaman |
| Starter | `laravel/laravel` + `install:api` | `laravel/breeze` --react |

---

## 3. Struktur Direktori

```
my-app/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   └── Providers/
│
├── bootstrap/
│   └── providers.php
│
├── database/migrations/
│   └── 2024_01_01_000001_create_plugins_table.php    ← WAJIB
│
├── plugins/
│   ├── core/plugin-manager/                          ← WAJIB
│   │   ├── composer.json
│   │   └── src/
│   │       ├── PluginManagerServiceProvider.php
│   │       ├── Package.php
│   │       ├── PackageServiceProvider.php
│   │       ├── Console/InstallCommand.php
│   │       ├── Console/UninstallCommand.php
│   │       └── Models/Plugin.php
│   │
│   └── mitunierp/blog/                                    ← Contoh plugin
│       ├── composer.json
│       ├── src/
│       │   ├── BlogServiceProvider.php
│       │   ├── Http/Controllers/
│       │   │   └── PostController.php       ← Inertia controller
│       │   ├── Models/Post.php
│       │   ├── Policies/PostPolicy.php
│       │   └── Database/Seeders/
│       ├── database/migrations/
│       ├── routes/
│       │   └── web.php                     ← Inertia routes
│       └── resources/js/                   ← ✨ REACT COMPONENTS
│           ├── Pages/
│           │   ├── Posts/Index.jsx
│           │   ├── Posts/Create.jsx
│           │   └── Posts/Edit.jsx
│           └── Components/
│               └── PostCard.jsx
│
├── resources/js/
│   ├── Pages/                              ← Halaman aplikasi utama
│   └── Components/                         ← Komponen global
│
├── routes/
│   ├── web.php                             ← Route utama (auth, dashboard)
│   └── api.php
│
├── vendor/wikimedia/composer-merge-plugin/
├── composer.json
├── package.json
└── vite.config.js
```

---

## 4. Stack Teknologi

### 4.1. Package

| Tipe | Package | Fungsi |
|------|---------|--------|
| **WAJIB** | `laravel/framework` | Framework inti |
| **WAJIB** | `laravel/breeze` | Starter auth + React stack |
| **WAJIB** | `wikimedia/composer-merge-plugin` | Auto-merge composer.json plugin |
| **WAJIB** | `spatie/laravel-package-tools` | Base class untuk membuat package |
| WAJIB | `laravel/sanctum` | API auth (untuk SPA) |
| Opsional | `spatie/laravel-permission` | RBAC |
| Dev | `laravel/pint` | Code style fixer |
| Dev | `pestphp/pest` | Testing |

### 4.2. Node Dependencies (dari Breeze)

| Package | Fungsi |
|---------|--------|
| `react` + `react-dom` | Frontend library |
| `@inertiajs/react` | Inertia adapter untuk React |
| `tailwindcss` | CSS framework |
| `vite` + `laravel-vite-plugin` | Build tool |

### 4.3. `composer.json` Lengkap

```json
{
    "require": {
        "php": "^8.3",
        "laravel/framework": "^11.0",
        "laravel/breeze": "^2.0",
        "laravel/sanctum": "^4.0",
        "wikimedia/composer-merge-plugin": "^2.1",
        "spatie/laravel-package-tools": "^1.16",
        "spatie/laravel-permission": "^6.0"
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

## 5. Komponen Wajib

### 5.1. Setup Breeze + React

```bash
composer require laravel/breeze
php artisan breeze:install react
php artisan migrate
npm install && npm run dev
```

Ini menghasilkan: auth scaffolding (login, register, dashboard), Tailwind config, Inertia setup, dan React page templates.

### 5.2. Tabel Database

Sama dengan API-only version — lihat `concept.md` section 4.2.

### 5.3. Base Package Class

Sama dengan API-only version — lihat `concept.md` section 4.3.

### 5.4. Base Service Provider

Sama dengan API-only, tapi load `web.php` bukan `api.php`:

```php
public function boot()
{
    if ($this->package->isCore || $this->package->isInstalled()) {
        $this->loadMigrationsFrom($path);
        $this->loadRoutesFrom($path);            // ← web.php
        $this->loadViewsFrom($path, 'plugin');    // ← React components path
    }
}
```

### 5.5. Install Command

Sama dengan API-only — lihat `concept.md` section 4.5.

---

## 6. Plugin dengan React

### 6.1. Publikasi React Components

Plugin perlu mempublikasikan file JSX-nya ke `resources/js/` agar di-build oleh Vite.

**Cara 1: Vite alias + symlink**

Di `vite.config.js`:

```js
resolve: {
    alias: {
        '@blog': path.resolve(__dirname, 'plugins/mitunierp/blog/resources/js'),
    }
}
```

**Cara 2: Plugin publish ke `resources/js/vendor/`**

Di `InstallCommand::handle()`:

```php
$this->call('vendor:publish', [
    '--tag' => 'blog-react',
]);
```

Konfigurasi di Service Provider:

```php
$package->name('blog')
    ->hasViews()
    ->hasInstallCommand(function ($cmd) {
        $cmd->runsMigrations();
        $cmd->publish('react-assets'); // publish JS ke resources/js/vendor/blog/
    });
```

### 6.2. Registrasi Route + Inertia Page

**`plugins/mitunierp/blog/routes/web.php`**:

```php
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->prefix('blog')->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('blog.posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('blog.posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('blog.posts.store');
});
```

**`plugins/mitunierp/blog/src/Http/Controllers/PostController.php`**:

```php
class PostController extends Controller
{
    public function index()
    {
        return Inertia::render('Blog/Posts/Index', [
            'posts' => Post::all(),
        ]);
    }
}
```

### 6.3. React Component Plugin Side

**`plugins/mitunierp/blog/resources/js/Pages/Posts/Index.jsx`**:

```jsx
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function PostIndex({ posts }) {
    return (
        <AuthenticatedLayout>
            <Head title="Blog Posts" />
            <div className="p-6">
                <h1 className="text-2xl font-bold mb-4">Blog Posts</h1>
                {posts.map(post => (
                    <div key={post.id} className="p-4 border rounded mb-2">
                        <h2>{post.title}</h2>
                        <p>{post.content}</p>
                    </div>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
```

### 6.4. Dinamis Component Loading via Inertia

React components plugin harus terdaftar di Inertia agar bisa dirender. Plugin perlu menambahkan file mapping:

**`resources/js/plugin-pages.js`** (dibuat otomatis saat install):

```js
// Auto-generated saat plugin diinstall
export default {
    'Blog/Posts/Index': () => import('./vendor/blog/Pages/Posts/Index'),
    'Blog/Posts/Create': () => import('./vendor/blog/Pages/Posts/Create'),
    // ditambah plugin lain...
};
```

**`app/Providers/AppServiceProvider.php`** — load plugin pages:

```php
public function boot()
{
    if (Package::isPluginInstalled('blog')) {
        // Plugin components sudah dipublish ke resources/js/vendor/blog/
    }
}
```

### 6.5. Alternatif Plugin tanpa React Component (Hybrid)

Plugin bisa tetap menyediakan **API endpoint** untuk frontend yang memanggil dari manapun:

```php
// routes/api.php (dalam plugin blog)
Route::prefix('api/v1/blog')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
});
```

Frontend utama cukup fetch data dari API plugin — tidak perlu registrasi React components.

---

## 7. Langkah Implementasi

| Step | Perintah | Hasil |
|------|----------|-------|
| 1 | `composer create-project laravel/laravel my-app` | Project baru |
| 2 | `composer require laravel/breeze` | Breeze package |
| 3 | `php artisan breeze:install react` | Auth + React scaffold |
| 4 | `php artisan migrate` | Tabel users & auth |
| 5 | `npm install && npm run dev` | Frontend build |
| 6 | `composer require wikimedia/composer-merge-plugin` | Merge plugin |
| 7 | `composer require spatie/laravel-package-tools` | Package tools |
| 8 | Tambah `extra.merge-plugin` di `composer.json` | |
| 9 | `php artisan make:migration create_plugins_table` | |
| 10 | Buat `plugins/core/plugin-manager/` | Core base classes |
| 11 | Buat `Package.php`, `PackageServiceProvider.php` | |
| 12 | Buat `InstallCommand.php`, `UninstallCommand.php` | |
| 13 | Buat `Models/Plugin.php` | |
| 14 | Daftarkan core provider di `bootstrap/providers.php` | |
| 15 | Buat plugin contoh `plugins/mitunierp/blog/` | |
| 16 | `composer dump-autoload` | |
| 17 | `php artisan blog:install` | Test instalasi |

---

## 8. Cara Kerja

### 8.1. Install Plugin

```
php artisan blog:install
  └── InstallCommand::handle()
      ├── runMigrations()               ← buat tabel posts
      └── updateOrCreate()              ← INSERT ke plugins
```

Setelah itu, `PackageServiceProvider::boot()` akan memuat routes/web.php plugin dan React components sudah bisa diakses via Inertia.

### 8.2. Runtime Request

```
GET /blog/posts
  └── Route: PostController@index (dari plugin)
      └── Inertia::render('Blog/Posts/Index', { posts: [...] })
          └── React component Blog/Posts/Index.jsx
              └── Render HTML
```

### 8.3. Uninstall

```
php artisan blog:uninstall
  └── DELETE FROM plugins WHERE name = 'blog'
      → routes berhenti dimuat
      → /blog/posts → 404
```

---

## 9. Contoh Plugin Fullstack

### 9.1. Struktur Plugin Blog

```
plugins/mitunierp/blog/
├── composer.json
├── src/
│   ├── BlogServiceProvider.php
│   ├── Http/Controllers/PostController.php
│   ├── Models/Post.php
│   ├── Policies/PostPolicy.php
│   └── Database/Seeders/DatabaseSeeder.php
├── database/migrations/
│   └── 2024_01_01_000001_create_posts_table.php
├── routes/
│   └── web.php                         ← Inertia routes
└── resources/
    ├── js/
    │   └── Pages/
    │       └── Posts/
    │           ├── Index.jsx
    │           └── Create.jsx
    └── lang/
        └── en/blog.php
```

### 9.2. Service Provider

```php
class BlogServiceProvider extends PackageServiceProvider
{
    public function configureCustomPackage(Package $package): void
    {
        $package->name('blog')
            ->hasMigrations(['2024_01_01_000001_create_posts_table'])
            ->runsMigrations()
            ->hasRoutes(['web'])
            ->hasInstallCommand(fn($cmd) => $cmd->runsMigrations());
    }
}
```

### 9.3. Web Routes (Inertia)

```php
Route::middleware(['auth', 'verified'])->prefix('blog')->name('blog.')->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
});
```

### 9.4. Controller

```php
class PostController extends Controller
{
    public function index()
    {
        return Inertia::render('Blog/Posts/Index', [
            'posts' => Post::latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Blog/Posts/Create');
    }

    public function store(StorePostRequest $request)
    {
        Post::create($request->validated());
        return redirect()->route('blog.posts.index');
    }
}
```

---

## 10. Checklist Build

- [ ] `composer create-project laravel/laravel`
- [ ] `composer require laravel/breeze`
- [ ] `php artisan breeze:install react`
- [ ] `php artisan migrate && npm install && npm run dev`
- [ ] `composer require wikimedia/composer-merge-plugin`
- [ ] `composer require spatie/laravel-package-tools`
- [ ] Konfigurasi `extra.merge-plugin` di `composer.json`
- [ ] Buat migrasi `plugins` & `plugin_dependencies`
- [ ] Buat model `Plugin`
- [ ] Buat class `Package`, `PackageServiceProvider`
- [ ] Buat `InstallCommand`, `UninstallCommand`
- [ ] Buat `plugins/core/plugin-manager/`
- [ ] Daftarkan core provider di `bootstrap/providers.php`
- [ ] Buat plugin contoh (`plugins/mitunierp/blog/`)
- [ ] Buat React components di `resources/js/Pages/Blog/`
- [ ] Setup Vite alias untuk plugin path (opsional)
- [ ] `composer dump-autoload`
- [ ] `php artisan blog:install` → sukses
- [ ] Buka `/blog/posts` di browser → halaman React tampil
- [ ] `php artisan blog:uninstall` → `/blog/posts` → 404
