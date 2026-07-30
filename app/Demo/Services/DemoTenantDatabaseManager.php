<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Models\Central\DatabaseCluster;
use App\Tenancy\Contracts\TenantDatabaseSecretProvider;
use App\Tenancy\Exceptions\TenantDatabaseSecretNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class DemoTenantDatabaseManager
{
    public function __construct(
        private readonly TenantDatabaseSecretProvider $secretProvider,
    ) {
    }

    public function ensureDatabase(string $databaseName, string $secretReference, DatabaseCluster $cluster): string
    {
        return $this->connectToDatabase(
            databaseName: $databaseName,
            secretReference: $secretReference,
            cluster: $cluster,
            createIfMissing: true
        );
    }

    public function connectToDatabase(
        string $databaseName,
        string $secretReference,
        DatabaseCluster $cluster,
        bool $createIfMissing = false,
    ): string {
        $adminConnectionName = $this->adminConnectionName($databaseName);
        $tenantConnectionName = $this->tenantConnectionName($databaseName);
        $credentials = $this->resolveCredentials($secretReference);

        config()->set('database.connections.'.$adminConnectionName, [
            'driver' => 'mysql',
            'host' => $cluster->host,
            'port' => (string) $cluster->port,
            'database' => 'information_schema',
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'unix_socket' => '',
            'charset' => (string) config('database.connections.central.charset', 'utf8mb4'),
            'collation' => (string) config('database.connections.central.collation', 'utf8mb4_0900_ai_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'timezone' => (string) config('database.connections.central.timezone', '+00:00'),
            'engine' => 'InnoDB',
            'modes' => config('database.connections.central.modes', []),
            'sslmode' => $cluster->ssl_mode,
            'options' => config('database.connections.central.options', []),
        ]);

        DB::purge($adminConnectionName);
        if ($createIfMissing) {
            DB::connection($adminConnectionName)->statement(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci',
                $databaseName
            ));
        } else {
            $exists = DB::connection($adminConnectionName)
                ->table('SCHEMATA')
                ->where('SCHEMA_NAME', '=', $databaseName)
                ->exists();

            if (! $exists) {
                throw new RuntimeException(sprintf(
                    'Tenant database "%s" does not exist.',
                    $databaseName
                ));
            }
        }

        $template = config('database.connections.tenant');
        if (! is_array($template)) {
            throw new RuntimeException('Tenant database connection template is missing.');
        }

        $template['host'] = $cluster->host;
        $template['port'] = (string) $cluster->port;
        $template['database'] = $databaseName;
        $template['username'] = $credentials['username'];
        $template['password'] = $credentials['password'];
        $template['sslmode'] = $cluster->ssl_mode;

        config()->set('database.connections.'.$tenantConnectionName, $template);
        DB::purge($tenantConnectionName);
        DB::reconnect($tenantConnectionName);
        DB::connection($tenantConnectionName)->getPdo();

        return $tenantConnectionName;
    }

    public function dropDatabase(string $databaseName, string $secretReference, DatabaseCluster $cluster): void
    {
        $adminConnectionName = $this->adminConnectionName($databaseName);
        $credentials = $this->resolveCredentials($secretReference);

        config()->set('database.connections.'.$adminConnectionName, [
            'driver' => 'mysql',
            'host' => $cluster->host,
            'port' => (string) $cluster->port,
            'database' => 'information_schema',
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'unix_socket' => '',
            'charset' => (string) config('database.connections.central.charset', 'utf8mb4'),
            'collation' => (string) config('database.connections.central.collation', 'utf8mb4_0900_ai_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'timezone' => (string) config('database.connections.central.timezone', '+00:00'),
            'engine' => 'InnoDB',
            'modes' => config('database.connections.central.modes', []),
            'sslmode' => $cluster->ssl_mode,
            'options' => config('database.connections.central.options', []),
        ]);

        DB::purge($adminConnectionName);
        DB::connection($adminConnectionName)->statement(sprintf('DROP DATABASE IF EXISTS `%s`', $databaseName));
        DB::disconnect($adminConnectionName);
        DB::purge($adminConnectionName);
    }

    public function purgeTenantConnection(string $databaseName): void
    {
        $connectionName = $this->tenantConnectionName($databaseName);

        DB::disconnect($connectionName);
        DB::purge($connectionName);
        config()->set('database.connections.'.$connectionName, null);
    }

    private function adminConnectionName(string $databaseName): string
    {
        return 'demo_admin_'.md5($databaseName);
    }

    private function tenantConnectionName(string $databaseName): string
    {
        return 'demo_tenant_'.md5($databaseName);
    }

    /**
     * @return array{username:string,password:string}
     */
    private function resolveCredentials(string $secretReference): array
    {
        try {
            $credentials = $this->secretProvider->resolve($secretReference);

            return [
                'username' => $credentials->username,
                'password' => $credentials->password,
            ];
        } catch (TenantDatabaseSecretNotFoundException) {
            $centralUsername = trim((string) config('database.connections.central.username', ''));
            $centralPassword = (string) config('database.connections.central.password', '');

            if ($centralUsername !== '') {
                return [
                    'username' => $centralUsername,
                    'password' => $centralPassword,
                ];
            }

            throw new RuntimeException(sprintf(
                'Unable to resolve tenant database credentials for secret reference "%s".',
                $secretReference
            ));
        }
    }
}
