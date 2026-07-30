<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Demo\Data\DemoConfigurationData;
use App\Demo\Exceptions\DemoSeedingNotAllowedException;
use App\Demo\Exceptions\UnsafeDemoDatabaseException;

final class DemoEnvironmentGuard
{
    public function assertMutationAllowed(string $operationName, bool $explicitCommand): void
    {
        if (! $explicitCommand) {
            throw new DemoSeedingNotAllowedException(sprintf(
                'Demo command "%s" requires explicit operator invocation.',
                $operationName
            ));
        }

        $configuration = DemoConfigurationData::fromConfig();
        $environment = app()->environment();

        if (! $configuration->isEnvironmentAllowed($environment)) {
            throw new DemoSeedingNotAllowedException(sprintf(
                'Demo command "%s" is blocked in "%s" environment.',
                $operationName,
                $environment
            ));
        }

        if (! $configuration->enabled) {
            throw new DemoSeedingNotAllowedException(
                'Demo seeding is disabled. Set DEMO_SEEDING_ENABLED=true to continue.'
            );
        }

        $databaseName = $this->resolveCentralDatabaseName();

        if (! $configuration->isDatabaseNameAllowed($databaseName)) {
            throw new UnsafeDemoDatabaseException(sprintf(
                'Central database "%s" is not approved for demo operations.',
                $databaseName
            ));
        }
    }

    public function assertCredentialsDisplayAllowed(): void
    {
        $configuration = DemoConfigurationData::fromConfig();
        $environment = app()->environment();

        if (! $configuration->isEnvironmentAllowed($environment)) {
            throw new DemoSeedingNotAllowedException(sprintf(
                'Demo credentials are not available in "%s" environment.',
                $environment
            ));
        }

        $databaseName = $this->resolveCentralDatabaseName();

        if (! $configuration->isDatabaseNameAllowed($databaseName)) {
            throw new UnsafeDemoDatabaseException(sprintf(
                'Central database "%s" is not approved for demo credential display.',
                $databaseName
            ));
        }
    }

    private function resolveCentralDatabaseName(): string
    {
        $databaseName = (string) config('database.connections.central.database', '');

        if ($databaseName !== '') {
            return $databaseName;
        }

        $defaultConnection = (string) config('database.default', 'mysql');

        return (string) config('database.connections.'.$defaultConnection.'.database', '');
    }
}
