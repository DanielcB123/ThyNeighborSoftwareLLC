<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Tenancy\Contracts\TenantDatabaseSecretProvider;
use App\Tenancy\Data\TenantDatabaseCredentials;
use App\Tenancy\Exceptions\TenantDatabaseSecretNotFoundException;

final class ConfigTenantDatabaseSecretProvider implements TenantDatabaseSecretProvider
{
    public function resolve(string $secretReference): TenantDatabaseCredentials
    {
        $referenceMap = config('tenancy.secret_provider.references', []);

        if (is_array($referenceMap) && isset($referenceMap[$secretReference])) {
            $candidate = $referenceMap[$secretReference];

            if (is_array($candidate)) {
                $username = trim((string) ($candidate['username'] ?? ''));
                $password = (string) ($candidate['password'] ?? '');

                if ($username !== '') {
                    return new TenantDatabaseCredentials($username, $password);
                }
            }
        }

        $fallbackUsername = trim((string) config('tenancy.secret_provider.fallback_username', ''));
        $fallbackPassword = (string) config('tenancy.secret_provider.fallback_password', '');

        if ($fallbackUsername !== '') {
            return new TenantDatabaseCredentials($fallbackUsername, $fallbackPassword);
        }

        throw TenantDatabaseSecretNotFoundException::forReference($secretReference);
    }
}
