<?php

declare(strict_types=1);

namespace App\Tenancy\Data;

final readonly class TenantDatabaseCredentials
{
    public function __construct(
        public string $username,
        public string $password,
    ) {
    }
}
