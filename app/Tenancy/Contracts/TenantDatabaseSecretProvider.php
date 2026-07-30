<?php

declare(strict_types=1);

namespace App\Tenancy\Contracts;

use App\Tenancy\Data\TenantDatabaseCredentials;

interface TenantDatabaseSecretProvider
{
    public function resolve(string $secretReference): TenantDatabaseCredentials;
}
