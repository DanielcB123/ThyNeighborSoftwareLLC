<?php

declare(strict_types=1);

namespace App\Demo\Support;

use InvalidArgumentException;

final readonly class TenantDatabaseName
{
    private function __construct(public string $value)
    {
    }

    public static function from(string $databaseName): self
    {
        $normalized = strtolower(trim($databaseName));

        if ($normalized === '' || preg_match('/^[a-z0-9_]+$/', $normalized) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid tenant database name: %s', $databaseName));
        }

        if (strlen($normalized) > 64) {
            throw new InvalidArgumentException('Tenant database names may not exceed 64 characters.');
        }

        return new self($normalized);
    }
}
