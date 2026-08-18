# AGENTS.md

## Cursor Cloud specific instructions

This is a Laravel 13 web app using the Inertia.js + Vue 3 stack (Laravel Breeze scaffolding). It implements auth (register/login/password reset/email verification), a dashboard, and profile management. Backend is PHP; frontend assets are built with Vite. Data store defaults to a local SQLite file (`database/database.sqlite`); sessions, cache, and queue all use the `database` driver, so no external DB/Redis/broker is needed. Mail and broadcasting use the `log` driver (no external SMTP/websocket service).

### Runtime requirement (non-obvious)
- Requires **PHP 8.4+**. Although `composer.json` declares `"php": "^8.3"`, `composer.lock` pins Symfony 8.1 packages that require `php >=8.4.1`. Installing only PHP 8.3 will make `composer install` fail. The update script assumes PHP 8.4 is already present (installed during environment setup via the `ondrej/php` PPA and set as the default `php`).

### Standard commands (see `composer.json` scripts)
- Run all dev services: `composer run dev` — runs `php artisan serve` (port 8000), `php artisan queue:listen`, `php artisan pail` (logs), and `npm run dev` (Vite, port 5173) concurrently via `concurrently --kill-others`. Run it under a long-lived process (e.g. tmux); do not use one-shot backgrounding.
- Run tests: `php artisan test` (or `composer test`).
- Lint: `./vendor/bin/pint` (add `--test` to check without writing). Note: the repo currently has a pre-existing Pint style deviation in `bootstrap/app.php`; `pint --test` exits non-zero because of it (not caused by setup).
- Build assets: `npm run build`.

### Gotchas
- **Tests require built frontend assets.** Feature tests render Inertia pages, which need `public/build/manifest.json`. If the manifest is missing, tests fail with `ViteManifestNotFoundException` and return HTTP 500. Run `npm run build` (or have `npm run dev` running) before `php artisan test`. `public/build/` is gitignored, so rebuild it if it's absent.
- **`php artisan test` / `pint` emit machine-readable JSON** (single-line) to stdout in this repo rather than the usual pretty PHPUnit output. Parse the JSON `result`/`passed`/`failed` fields; do not expect `PASS`/`FAIL` text lines.
- The Vite dev server binds to `localhost` only. Use `http://localhost:5173`, not `127.0.0.1:5173`, when checking it directly.
- First setup needs `.env` (copied from `.env.example`), an app key (`php artisan key:generate`), the SQLite file (`touch database/database.sqlite`), and migrations (`php artisan migrate`). These persist in the VM snapshot; the update script does not repeat them.
