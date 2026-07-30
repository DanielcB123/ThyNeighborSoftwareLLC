# Remediation Log — TENANCY-LOCAL-REGISTRY-001

## Actions Completed

1. Created incident workspace and baseline command-output capture.
2. Implemented strict unknown-host default handling in tenancy middleware.
3. Added runtime-surface attribution on request context and frontend runtime payload.
4. Added negative-cache support and configurable TTL controls in central resolver.
5. Normalized tenancy configuration keys:
   - resolution cache TTL
   - negative cache TTL
   - unknown-host behavior
   - local domain handling
6. Added local environment audit command:
   - `php artisan app:audit-environment`
7. Removed raw `env()` calls from runtime tenancy/demo services.
8. Added/updated feature and unit tests for middleware and resolver behavior.
9. Added demo dataset auto-provisioning during `db:seed` (guarded by config) so tenant-domain records and tenant DBs are created through the canonical seeding path.
10. Added cache invalidation after demo seed to remove stale negative domain-resolution entries.

## Files Updated

- `app/Http/Middleware/ResolveTenantFromDomain.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Tenancy/CentralTenantDatabaseResolver.php`
- `app/Console/Commands/AuditEnvironmentCommand.php`
- `app/Demo/Services/DemoTenantProvisioner.php`
- `app/Demo/Services/DemoDatasetManager.php`
- `config/tenancy.php`
- `config/demo.php`
- `config/app.php`
- `database/seeders/Central/DemoTenantDatasetSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `routes/console.php`
- `routes/web.php`
- `.env.example`
- `.env.testing`
- `phpunit.xml`
- `resources/js/core/runtime/frontendContext.ts`
- `tests/Feature/Tenancy/ResolveTenantFromDomainMiddlewareTest.php`
- `tests/Unit/Tenancy/CentralTenantDatabaseResolverTest.php`
- `tests/Unit/Demo/DemoTenantDatasetSeederTest.php`
- `tests/Feature/Console/AuditEnvironmentCommandTest.php`
