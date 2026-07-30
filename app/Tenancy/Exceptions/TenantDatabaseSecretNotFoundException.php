<?php

declare(strict_types=1);

namespace App\Tenancy\Exceptions;

use RuntimeException;

final class TenantDatabaseSecretNotFoundException extends RuntimeException
{
    public static function forReference(string $reference): self
    {
        return new self(
            sprintf('No tenant database secret could be resolved for reference "%s".', $reference)
        );
    }
}
