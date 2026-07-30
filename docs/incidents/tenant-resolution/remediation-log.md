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

## Files Updated

- `app/Http/Middleware/ResolveTenantFromDomain.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Tenancy/CentralTenantDatabaseResolver.php`
- `app/Console/Commands/AuditEnvironmentCommand.php`
- `app/Demo/Services/DemoTenantProvisioner.php`
- `app/Demo/Services/DemoDatasetManager.php`
- `config/tenancy.php`
- `config/app.php`
- `routes/console.php`
- `.env.example`
- `.env.testing`
- `resources/js/core/runtime/frontendContext.ts`
- `tests/Feature/Tenancy/ResolveTenantFromDomainMiddlewareTest.php`
- `tests/Unit/Tenancy/CentralTenantDatabaseResolverTest.php`
- `tests/Feature/Console/AuditEnvironmentCommandTest.php`
