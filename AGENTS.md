# AGENTS.md

## Cursor Cloud specific instructions

WeBuildYouThrive is a single-product multi-tenant SaaS: **Laravel 13 (PHP 8.4) + Vue 3 / Inertia + Vite**, backed by **MySQL 8** (central registry DB + per-tenant DBs) and **Redis** (cache, sessions, queue, tenant-domain resolution cache). Standard commands live in `composer.json` (`setup`, `dev`, `test`) and `package.json` (`lint`, `typecheck`, `test:*`); the demo-data workflow is documented in `docs/demo-testing-guide.md`.

The startup update script only refreshes dependencies (`composer install`, `npm install`). Everything below is runtime state that does NOT persist and is NOT handled by the update script, so a fresh session must do it manually.

### PHP version
This repo requires **PHP 8.4** even though `composer.json` says `^8.3` — `composer.lock` pins Symfony 8.1 packages that need `php >= 8.4.1`. PHP 8.4 is the default `php` here; PHP 8.3 is also installed but will fail `composer install`.

### Start services before doing anything (they are not auto-started)
```bash
sudo service mysql start
sudo service redis-server start
```
MySQL is reachable over TCP as `root` with an empty password. The committed configs (`.env.example`, `phpunit.xml`, `.env.testing`) reference DB/Redis hosts named `mysql` and `redis`; these are mapped to `127.0.0.1` in `/etc/hosts`. The local `.env` points at `127.0.0.1` directly. Databases `webuildyouthrive_central`, `wbyt_local_dev`, and `wbyt_test_central` already exist.

### Run the app (dev mode)
```bash
composer dev
```
This runs, concurrently: `php artisan serve` (app on `http://localhost:8000`), `php artisan queue:listen`, `php artisan pail` (log tail), and `npm run dev` (Vite on `:5173`). Blade uses the `@vite` directive, so the Vite dev server must be running for assets/HMR. Health check: `curl http://localhost:8000/up`.

### Running tests — export APP_KEY first
`.env.testing` (committed) intentionally has no `APP_KEY`, and the testing env loads `.env.testing` instead of `.env`, so feature tests fail with "No application encryption key has been specified" unless you provide one at runtime:
```bash
export APP_KEY=$(grep '^APP_KEY=' .env | cut -d= -f2-)
composer test          # or: php artisan test
```
PHP tests require a running MySQL (they create/drop `wbyt_test_*` databases). They do NOT need Redis or Vite (cache/session/queue use array/sync drivers in tests).

Known pre-existing test failures (NOT environment issues, present on any machine): several tests in `tests/Unit/Demo`, `tests/Unit/Tenancy`, and `tests/Feature/Tenancy` try to `Mockery::mock()` classes declared `final` (`DemoDatasetManager`, `CentralTenantDatabaseResolver`) and error out. Do not treat these as setup problems.

### Frontend checks (no services needed)
```bash
npm run lint
npm run typecheck
npm run test:unit
npm run test:component
npm run test:browser
npm run test:accessibility
```

### Demo multi-tenant data (optional)
`.env` has `DEMO_SEEDING_ENABLED=true`. Seed the three demo tenants (each gets an isolated MySQL DB) with:
```bash
php artisan demo:seed --profile=standard --tenant=all --force
php artisan demo:verify
```
Tenant domains (`coastalcomfortplumbing.test`, `carolinabeautycollective.test`, `atlasfieldservices.test`, `webuildyouthrive.test`) are already mapped to `127.0.0.1` in `/etc/hosts`; access them via `http://<domain>:8000`.
