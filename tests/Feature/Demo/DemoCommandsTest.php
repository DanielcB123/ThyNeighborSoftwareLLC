<?php

declare(strict_types=1);

namespace Tests\Feature\Demo;

use Tests\TestCase;

class DemoCommandsTest extends TestCase
{
    public function test_demo_seed_command_shows_warning_and_refuses_when_disabled(): void
    {
        config()->set('demo.enabled', false);
        config()->set('database.connections.central.database', 'wbyt_test_central');

        $this->artisan('demo:seed --force')
            ->expectsOutputToContain('DEMO DATA ONLY')
            ->expectsOutputToContain('DO NOT USE THESE CREDENTIALS IN PRODUCTION')
            ->expectsOutputToContain('Demo seeding is disabled. Set DEMO_SEEDING_ENABLED=true to continue.')
            ->assertExitCode(1);
    }
}
