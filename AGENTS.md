# AGENTS.md

## Cursor Cloud specific instructions

This is a Laravel 13 + Inertia + Vue 3 app scaffolded with Laravel Breeze. It uses a
SQLite database (`database/database.sqlite`) and Vite for the frontend.

### Runtime / gotchas

- **PHP 8.4 is required**, not 8.3. `composer.json` declares `php: ^8.3`, but
  `composer.lock` pins Symfony 8.1 packages that require `php >=8.4.1`. Installing PHP
  8.3 makes `composer install` fail. The VM snapshot has PHP 8.4 + Composer preinstalled.
- **Feature tests need built frontend assets.** Many `tests/Feature` tests render Inertia
  pages and fail with `ViteManifestNotFoundException` (HTTP 500) if
  `public/build/manifest.json` is missing. Run `npm run build` once before running the
  test suite (the update script does this).
- The `.env` file and the SQLite DB are gitignored; they are created during environment
  setup and persist in the VM snapshot.

### Running the app

- `composer dev` starts everything concurrently: `php artisan serve` (http://127.0.0.1:8000),
  `queue:listen`, `pail` (logs), and `npm run dev` (Vite dev server on port 5173, bound to
  localhost only). Vite HMR proxies through the Laravel server; browse the app at
  port 8000, not 5173.

### Standard commands (see `composer.json` / `package.json`)

- Lint: `./vendor/bin/pint` (add `--test` to check without fixing). Note the base scaffold
  ships with one pre-existing Pint style finding in `bootstrap/app.php`.
- Test: `php artisan test` or `composer test` (config in `phpunit.xml`, uses in-memory SQLite).
- Build: `npm run build`.
