<?php

declare(strict_types=1);

namespace Tests\Unit\Demo;

use App\Demo\Support\TenantDatabaseName;
use InvalidArgumentException;
use Tests\TestCase;

class TenantDatabaseNameTest extends TestCase
{
    public function test_it_normalizes_safe_database_names(): void
    {
        $name = TenantDatabaseName::from('WBYT_LOCAL_COASTAL_COMFORT_PLUMBING');

        $this->assertSame('wbyt_local_coastal_comfort_plumbing', $name->value);
    }

    public function test_it_rejects_invalid_database_names(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TenantDatabaseName::from('invalid-name-with-dash');
    }
}
