<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Tests\TestCase;

class AuditEnvironmentCommandTest extends TestCase
{
    public function test_audit_environment_command_reports_incident_identifier(): void
    {
        $this->artisan('app:audit-environment')
            ->expectsOutputToContain('Incident: TENANCY-LOCAL-REGISTRY-001')
            ->expectsOutputToContain('Tracked key diagnostics:')
            ->assertExitCode(1);
    }

    public function test_audit_environment_command_flags_deprecated_tenant_database_keys(): void
    {
        $envLocalPath = base_path('.env.local');
        file_put_contents($envLocalPath, implode(PHP_EOL, [
            'TENANT_DB_DATABASE=legacy_static_tenant_db',
            'TENANT_DB_USERNAME=legacy_tenant_user',
            'TENANT_DB_PASSWORD=legacy_tenant_password',
            '',
        ]));

        try {
            $this->artisan('app:audit-environment')
                ->expectsOutputToContain('TENANT_DB_DATABASE:')
                ->expectsOutputToContain('status: deprecated')
                ->assertExitCode(1);
        } finally {
            @unlink($envLocalPath);
        }
    }
}
