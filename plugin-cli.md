# Plugin CLI — Artisan Commands

## Daftar Perintah

| Perintah | Fungsi |
|----------|--------|
| `make:plugin {name}` | Membuat plugin baru |
| `make:plugin-controller {plugin} {name}` | Membuat API controller di plugin |
| `make:plugin-model {plugin} {name}` | Membuat model di plugin |
| `make:plugin-migration {plugin} {name}` | Membuat migration di plugin |

---

## `make:plugin`

Membuat struktur direktori dan file dasar untuk plugin baru.

```bash
php artisan make:plugin inventory
```

**Output:**
```
Created: plugins/mitunierp/inventory/composer.json
Created: plugins/mitunierp/inventory/src/InventoryServiceProvider.php
Created: plugins/mitunierp/inventory/routes/api.php
Plugin 'inventory' created successfully at plugins/mitunierp/inventory
Next steps:
  1. Register in bootstrap/providers.php
  2. Add to PluginController::AVAILABLE array
  3. Add to PluginController::menus() method
  4. Run: composer dump-autoload
  5. Install: php artisan inventory:install
```

**Struktur yang dibuat:**
```
plugins/mitunierp/{name}/
├── composer.json              # PSR-4 autoload + extra.laravel.providers
├── src/
│   └── {Name}ServiceProvider.php  # extends PackageServiceProvider
├── database/
│   └── migrations/            # Folder migrasi (kosong)
└── routes/
    └── api.php                # Route prefix: /api/v1/{name}
```

**Setelah generate, lakukan:**
1. Buka `bootstrap/providers.php`, tambahkan:
   ```php
   use Mitunierp\Inventory\InventoryServiceProvider;
   
   return [
       // ...
       InventoryServiceProvider::class,
   ];
   ```
2. Buka `PluginController.php`, tambahkan ke `AVAILABLE`:
   ```php
   private const AVAILABLE = [
       // ...
       'inventory' => [
           'label' => 'Inventory',
           'description' => '...',
           'icon' => 'package',
       ],
   ];
   ```
3. Buka `PluginController.php`, tambahkan ke `menus()`:
   ```php
   'inventory' => [
       'headerNav' => [...],
       'sidebar' => [...],
   ],
   ```
4. Jalankan:
   ```bash
   composer dump-autoload
   php artisan inventory:install
   ```

---

## `make:plugin-model`

Membuat model Eloquent untuk plugin tertentu.

```bash
php artisan make:plugin-model inventory Product
```

**Output:**
```
Model Product created for plugin 'inventory'
```

**File dibuat:**
`plugins/mitunierp/inventory/src/Models/Product.php`

```php
<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([])]
final class Product extends Model
{
    protected $table = 'inventory_products';

    protected function casts(): array
    {
        return [];
    }
}
```

**Fitur:**
- Nama tabel otomatis: `{plugin}_{snake_plural_model}`
- Namespace: `Mitunierp\{Plugin}\Models`
- Final class dengan Fillable attribute
- Siap ditambahkan relasi dan casting

---

## `make:plugin-migration`

Membuat file migration untuk plugin tertentu.

```bash
php artisan make:plugin-migration inventory products
```

**Output:**
```
Migration created: 2026_05_29_183326_create_inventory_products_table.php
Remember to add '2026_05_29_183326_create_inventory_products_table.php' to the hasMigrations() array in the ServiceProvider
```

**File dibuat:**
`plugins/mitunierp/inventory/database/migrations/2026_05_29_183326_create_inventory_products_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_products', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_products');
    }
};
```

**Setelah generate:**
Buka ServiceProvider plugin, tambahkan nama file ke array `hasMigrations()`:
```php
$package
    ->name('inventory')
    ->hasMigrations([
        '2026_05_29_183326_create_inventory_products_table',
    ])
    ->runsMigrations();
```

---

---

## `make:plugin-controller`

Membuat API controller untuk plugin tertentu dengan CRUD lengkap.

```bash
php artisan make:plugin-controller inventory Product
```

**Output:**
```
Controller ProductController created for plugin 'inventory'
Don't forget to add the route in routes/api.php:
    Route::apiResource('products', ProductController::class);
```

**File dibuat:**
`plugins/mitunierp/inventory/src/Http/Controllers/API/V1/ProductController.php`

**Method yang dihasilkan:**
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `index()` | `GET /products` | List paginated dengan QueryBuilder |
| `show()` | `GET /products/{id}` | Detail single record |
| `store()` | `POST /products` | Create dengan validasi |
| `update()` | `PUT /products/{id}` | Update dengan validasi |
| `destroy()` | `DELETE /products/{id}` | Soft delete |

**Struktur controller:**
- Extends `App\Http\Controllers\Api\ApiController` — mewarisi `ApiResponse` trait
- Route model binding otomatis dari Laravel
- QueryBuilder untuk filter + sort
- Response menggunakan `$this->success()`, `$this->created()`, `$this->noContent()`

**Setelah generate, daftarkan route di `routes/api.php`:**
```php
use Mitunierp\Inventory\Http\Controllers\API\V1\ProductController;

Route::apiResource('products', ProductController::class);
```

---

## Contoh Lengkap: Plugin Sales

```bash
# 1. Buat plugin
php artisan make:plugin sales

# 2. Daftarkan di bootstrap/providers.php
# 3. Tambah ke AVAILABLE + menus() di PluginController

# 4. Buat model
php artisan make:plugin-model sales Order
php artisan make:plugin-model sales OrderLine
php artisan make:plugin-model sales Customer

# 5. Buat migrasi
php artisan make:plugin-migration sales orders
php artisan make:plugin-migration sales order_lines
php artisan make:plugin-migration sales customers

# 6. Tambah migrasi ke ServiceProvider
# 7. composer dump-autoload
# 8. php artisan sales:install
```

---

## Catatan

- Nama plugin menggunakan **snake_case** (e.g., `plugin-manager`, `order-line`)
- Nama model menggunakan **StudlyCase** (e.g., `OrderLine`, `ProductCategory`)
- Migration akan otomatis diberi prefix `{plugin}_` pada nama tabel
- Semua file PHP menggunakan `declare(strict_types=1)` dan final class
- Setiap perintah hanya membuat file — tidak mendaftarkan ke database atau autoload
