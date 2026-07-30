<?php

declare(strict_types=1);

namespace Tests\Unit\Demo;

use App\Demo\Exceptions\DemoSeedingNotAllowedException;
use App\Demo\Exceptions\UnsafeDemoDatabaseException;
use App\Demo\Services\DemoEnvironmentGuard;
use Tests\TestCase;

class DemoEnvironmentGuardTest extends TestCase
{
    public function test_it_requires_explicit_command_invocation_for_mutations(): void
    {
        config()->set('demo.enabled', true);
        config()->set('database.connections.central.database', 'wbyt_test_central');

        $guard = new DemoEnvironmentGuard();

        $this->expectException(DemoSeedingNotAllowedException::class);
        $guard->assertMutationAllowed('demo:seed', explicitCommand: false);
    }

    public function test_it_rejects_unsafe_central_database_names(): void
    {
        config()->set('demo.enabled', true);
        config()->set('database.connections.central.database', 'production_central');

        $guard = new DemoEnvironmentGuard();

        $this->expectException(UnsafeDemoDatabaseException::class);
        $guard->assertMutationAllowed('demo:seed', explicitCommand: true);
    }

    public function test_it_allows_mutation_with_safe_configuration(): void
    {
        config()->set('demo.enabled', true);
        config()->set('database.connections.central.database', 'wbyt_test_central');

        $guard = new DemoEnvironmentGuard();

        $guard->assertMutationAllowed('demo:seed', explicitCommand: true);
        $this->addToAssertionCount(1);
    }
}
