# STARTHERE.md

## Welcome / Purpose

Welcome to **ThyNeighborSoftwareLLC**.  
This repository is a Laravel + Inertia + Vue multi-tenant platform with central MySQL data, optional demo tenant datasets, and Redis-backed runtime services.

Follow this guide **top to bottom, in order**. When you finish, you should have:

- the app running locally;
- background worker + frontend dev server running;
- database schema + seed data installed;
- test commands working.

---

## 1) Technology Stack

| Component | Technology | Required / Project Constraint |
| --- | --- | --- |
| Backend framework | Laravel | `^13.8` (locked `v13.21.1`) |
| Backend language | PHP | `^8.3` |
| Auth stack | Laravel Breeze + session auth | Breeze `^2.4`, session guard |
| API/session package | Laravel Sanctum | Installed (`^4.0`, locked `v4.3.3`) |
| Server rendering bridge | Inertia (Laravel + Vue adapter) | `inertiajs/inertia-laravel ^2.0` (locked `v2.0.24`), `@inertiajs/vue3 ^2.0.0` (locked `2.3.27`) |
| Frontend | Vue 3 | `^3.4.0` (locked `3.5.40`) |
| Build tool | Vite | `^8.0.0` (locked `8.1.5`) |
| CSS tooling | Tailwind CSS | `^3.4.19` |
| DB engine (runtime default) | MySQL | MySQL driver + `utf8mb4_0900_ai_ci` defaults (MySQL 8+) |
| Cache/session/queue (runtime defaults) | Redis + phpredis client | `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis` |
| PHP dependency manager | Composer | Composer 2.x |
| JS package manager | npm (lockfile v3) | use `npm ci` |
| Test frameworks | PHPUnit + Vitest | PHPUnit `^12.5.12`, Vitest `^4.1.10` |

---

## 2) Software You Must Install

Install these before cloning/running:

1. **Git**
2. **PHP 8.3+**
3. **Composer 2.x**
4. **Node.js** matching Vite engine: `^20.19.0` or `>=22.12.0`
5. **npm** (lockfile v3; npm 9+ recommended)
6. **MySQL 8+**
7. **Redis server**
8. **PHP Redis extension (`phpredis`)**  
   (this repo defaults to `REDIS_CLIENT=phpredis`)

Verify installs:

```bash
git --version
php --version
composer --version
node --version
npm --version
mysql --version
redis-server --version
php -m | rg "redis|pdo_mysql"
```

### OS notes (short)

- **macOS**: Homebrew is the quickest path for PHP, Composer, Node, MySQL, Redis.
- **Linux**: use your distro packages + official Node distribution if needed.
- **Windows**: use WSL2 for best parity with project command examples.

---

## 3) Clone the Repository

HTTPS:

```bash
git clone https://github.com/DanielcB123/ThyNeighborSoftwareLLC.git
cd ThyNeighborSoftwareLLC
```

If your organization standard is SSH, use your SSH remote equivalent.

---

## 4) Verify Runtime Versions

Run:

```bash
php --version
composer --version
node --version
npm --version
```

Required constraints in this repo:

- PHP: `^8.3` (from `composer.json`)
- Node: must satisfy Vite engine `^20.19.0 || >=22.12.0` (from lockfile)

---

## 5) Install Backend Dependencies

```bash
composer install
```

Use `install`, not `update`, so versions match `composer.lock`.

---

## 6) Install Frontend Dependencies

This repository uses **npm** (`package-lock.json` present).

```bash
npm ci
```

Use `ci` for deterministic lockfile installs.

---

## 7) Create the Local Environment File

```bash
cp .env.example .env
```

PowerShell alternative:

```powershell
Copy-Item .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

### Configure required `.env` values

Only change what is needed for your machine/services:

#### Application

```dotenv
APP_ENV=local
APP_URL=http://localhost:8000
APP_DEBUG=true
```

#### Central database (required)

```dotenv
DB_CONNECTION=central
CENTRAL_DB_HOST=127.0.0.1
CENTRAL_DB_PORT=3306
CENTRAL_DB_DATABASE=webuildyouthrive_central
CENTRAL_DB_USERNAME=<your_local_mysql_user>
CENTRAL_DB_PASSWORD=<your_local_mysql_password>
```

> `.env.example` defaults `CENTRAL_DB_HOST=mysql`. If you are not on a container network with that hostname, use `127.0.0.1` (or your local DB host).

#### Tenant DB defaults (required for tenancy-aware behavior)

```dotenv
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306
TENANT_DB_FALLBACK_USERNAME=<your_local_mysql_user>
TENANT_DB_FALLBACK_PASSWORD=<your_local_mysql_password>
```

#### Redis + runtime drivers (required for default app behavior)

```dotenv
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### Frontend runtime URLs

Usually keep in sync with `APP_URL`:

```dotenv
FRONTEND_PLATFORM_PUBLIC_URL=${APP_URL}
FRONTEND_PLATFORM_AUTH_URL=${APP_URL}
FRONTEND_PLATFORM_ADMIN_URL=${APP_URL}/admin
PLATFORM_DOMAINS=localhost,127.0.0.1
```

#### Optional: Zoom integration (only needed if testing onboarding scheduling against Zoom)

```dotenv
ZOOM_ACCOUNT_ID=
ZOOM_CLIENT_ID=
ZOOM_CLIENT_SECRET=
ZOOM_HOST_USER=
ZOOM_VERIFY_SSL=true
```

#### Optional: Demo dataset controls

Default is safe/off:

```dotenv
DEMO_SEEDING_ENABLED=false
DEMO_AUTO_SEED_ON_DATABASE_SEEDER=true
DEMO_USER_PASSWORD=DemoPassword!2026
DEMO_DATA_PROFILE=standard
```

> Note: `.env.example` currently includes duplicate `ZOOM_*` key blocks. Keep a single effective set in your local `.env`.

---

## 8) Create Local Databases

Create the central DB:

```bash
mysql -u <your_local_mysql_user> -p -e "CREATE DATABASE IF NOT EXISTS webuildyouthrive_central CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
```

Create test DB (recommended now so tests run cleanly later):

```bash
mysql -u <your_local_mysql_user> -p -e "CREATE DATABASE IF NOT EXISTS wbyt_test_central CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
```

---

## 9) Run Migrations and Seeders

Run migrations + seed initial platform data and demo credential scaffolding:

```bash
php artisan migrate --seed
```

Why seed? `DatabaseSeeder` provisions platform permissions/roles/modules/plans/users and registry metadata needed for realistic local usage.

Create the public storage symlink:

```bash
php artisan storage:link
```

Optional but recommended environment sanity check:

```bash
php artisan app:audit-environment
```

---

## 10) Start the Application (All Services)

Use the repo’s built-in concurrent dev script:

```bash
composer run dev
```

This starts, together:

- Laravel HTTP server (`php artisan serve`)
- queue listener (`php artisan queue:listen`)
- log stream (`php artisan pail`)
- Vite dev server (`npm run dev`)

Open:

- App: `http://127.0.0.1:8000`

---

## 11) Default Local Accounts

After `migrate --seed`, central platform users are created by `PlatformUserSeeder`:

- `platform.owner@webuildyouthrive.test`
- `platform.support@webuildyouthrive.test`
- `platform.billing@webuildyouthrive.test`

Default password (development/demo only):  
`DemoPassword!2026` (override via `DEMO_USER_PASSWORD`)

You can print seeded demo credentials with:

```bash
php artisan demo:credentials
```

---

## 12) Run Tests

### Backend tests

```bash
php artisan test
```

If your local MySQL host/port/user differ from PHPUnit defaults, run with explicit overrides:

```bash
CENTRAL_DB_HOST=127.0.0.1 \
CENTRAL_DB_PORT=3306 \
CENTRAL_DB_USERNAME=<your_local_mysql_user> \
CENTRAL_DB_PASSWORD=<your_local_mysql_password> \
TEST_CENTRAL_DB_DATABASE=wbyt_test_central \
php artisan test
```

### Frontend tests

```bash
npm run test:unit
npm run test:component
npm run test:browser
npm run test:accessibility
```

---

## 13) Optional: Enable Full Demo Tenant Domains

Only do this if you need seeded multi-tenant demo data and domain resolution behavior.

1) Enable in `.env`:

```dotenv
DEMO_SEEDING_ENABLED=true
TENANT_DB_FALLBACK_USERNAME=<your_local_mysql_user>
TENANT_DB_FALLBACK_PASSWORD=<your_local_mysql_password>
```

2) Seed demo datasets:

```bash
php artisan demo:seed --profile=standard --tenant=all --force
```

3) Add host mappings:

```text
127.0.0.1 coastalcomfortplumbing.test
127.0.0.1 carolinabeautycollective.test
127.0.0.1 atlasfieldservices.test
```

4) Validate:

```bash
php artisan demo:verify
```

---

## 14) Troubleshooting

### `SQLSTATE[HY000] [2002] ... mysql`

Your DB hostname/port is wrong for your machine. Update:

- `CENTRAL_DB_HOST`, `CENTRAL_DB_PORT`
- `TENANT_DB_HOST`, `TENANT_DB_PORT`

### `Class "Redis" not found`

Install/enable the **phpredis** extension for your PHP runtime, then restart PHP processes.

### Queue jobs not being processed

Ensure `composer run dev` is running (it starts `queue:listen`) or run a worker manually:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

### Reset local DB (destructive, local only)

```bash
php artisan migrate:fresh --seed
```

### Reset demo tenants (destructive, demo only)

```bash
php artisan demo:reset --tenant=all --force
```

---

## 15) Daily Workflow Quick Reference

From repo root:

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
composer run dev
```

In another terminal:

```bash
php artisan test
```

