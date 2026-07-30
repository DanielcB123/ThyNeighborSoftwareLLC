<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Tenancy\Contracts\TenantDatabaseSecretProvider;
use App\Tenancy\Data\ResolvedTenantDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class TenantDatabaseConnectionManager
{
    /**
     * @var array<string, mixed>
     */
    private array $tenantConnectionTemplate;

    private string $tenantConnectionName;

    public function __construct(
        private readonly TenantDatabaseSecretProvider $secretProvider
    ) {
        $this->tenantConnectionName = (string) config('tenancy.tenant_connection_name', 'tenant');

        $template = config('database.connections.'.$this->tenantConnectionName);

        if (! is_array($template)) {
            throw new RuntimeException(
                sprintf('The "%s" tenant database connection template is not configured.', $this->tenantConnectionName)
            );
        }

        $this->tenantConnectionTemplate = $template;
    }

    public function activate(ResolvedTenantDatabase $resolved): void
    {
        $credentials = $this->secretProvider->resolve($resolved->secretReference);
        $connectionConfig = $this->tenantConnectionTemplate;

        $connectionConfig['driver'] = 'mysql';
        $connectionConfig['host'] = $resolved->clusterHost;
        $connectionConfig['port'] = (string) $resolved->clusterPort;
        $connectionConfig['database'] = $resolved->databaseName;
        $connectionConfig['username'] = $credentials->username;
        $connectionConfig['password'] = $credentials->password;
        $connectionConfig['sslmode'] = $resolved->clusterSslMode;

        config()->set('database.connections.'.$this->tenantConnectionName, $connectionConfig);

        DB::purge($this->tenantConnectionName);
        DB::reconnect($this->tenantConnectionName);
        DB::connection($this->tenantConnectionName)->getPdo();
    }

    public function reset(): void
    {
        DB::disconnect($this->tenantConnectionName);
        DB::purge($this->tenantConnectionName);

        config()->set(
            'database.connections.'.$this->tenantConnectionName,
            $this->tenantConnectionTemplate
        );
    }
}
