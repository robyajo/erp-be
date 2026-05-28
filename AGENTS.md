# AGENTS.md — erp-be (Laravel API Kit)

Part of the `project-erp` monorepo. Paired with `erp-fe/` (Next.js).

## Dev commands

```bash
composer test          # lint → types → unit (run before push)
composer lint          # auto-fix: rector + pint
composer test:lint     # pint --test && rector --dry-run
composer test:types    # phpstan (level max)
composer test:unit     # config:clear && artisan test (Pest)
php artisan serve      # dev server
```

With Docker: `docker compose run --rm app <cmd>` or `docker compose exec app <cmd>`.

## Code conventions

- `declare(strict_types=1)` on **every** PHP file
- All concrete classes are `final`
- PHP 8 attributes for `#[Fillable]`, `#[Hidden]` — never `$fillable`/`$hidden` properties
- `global_namespace_import` is on (no leading `\` needed for built-in classes)
- `ordered_class_elements`: trait → constant → property → construct → magic → methods (public static → public → protected static → protected → private static → private)

## Architecture

- **API-only** — no Blade/Vite/frontend; `routes/web.php` is disabled
- **API versioning** via `laravel-apiroute` — routes live in `routes/api/v1.php`, *not* `routes/api.php`. Supports URI, header, query, and Accept-header detection (see `config/apiroute.php`)
- **Sanctum** for auth (token-based, not JWT/session)
- **SQLite by default** — `phpunit.xml` uses `:memory:` for tests
- **Scramble** for OpenAPI docs at `/docs/api` — zero annotations needed
- **Rate limiters** defined in `AppServiceProvider`: `api` (60/min), `auth` (5/min for login/register), `authenticated` (120/min)

## Response format

All controllers use `ApiResponse` trait from `app/Traits/ApiResponse.php`:

```php
$this->success($data, $message);           // 200  {success, message, data}
$this->created($data, $message);           // 201
$this->noContent();                         // 204
$this->error($message, $code, $errors);     // {success: false, message, errors?}
$this->notFound($message);                  // 404
$this->unauthorized($message);              // 401
$this->forbidden($message);                 // 403
$this->validationError($errors, $message);  // 422
```

## Models

- `User` model has both `id` (auto-increment PK) and `ulid` (auto-generated via `str()->ulid()` in `booted()` — do **not** use `HasUlids` trait as it overrides the PK)
- Implements `MustVerifyEmail`
- Uses `HasApiTokens` (Sanctum)

## Testing

- **Pest** for feature tests (`tests/Feature/`)
- Feature tests use `uses(RefreshDatabase::class)` (applied in `Pest.php`), but it's commented out — each test file should declare it
- Test files: `tests/Feature/Api/V1/AuthTest.php`, `EmailVerificationTest.php`, `PasswordResetTest.php`
- CI runs on PHP 8.3 + 8.4 via GitHub Actions (`composer test`)

## Tool quirks

- `phpstan.neon`: `level: max`, `tmpDir: /tmp/phpstan` (set `PHPSTAN_CACHE_DIR` on Windows)
- `rector.php`: caches to `/tmp/rector` (set `RECTOR_CACHE_DIR` on Windows)
- `pint.json`: excludes `tests/TestCase.php` and `config/database.php`

## Middleware aliases

| Alias | Class |
|-------|-------|
| `force.json` | ForceJsonResponse |
| `log.api` | LogApiRequests |
| `verified` | EnsureEmailVerified |
