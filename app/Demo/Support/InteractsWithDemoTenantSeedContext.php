<?php

declare(strict_types=1);

namespace App\Demo\Support;

use App\Demo\Data\DemoTenantSeedContextData;
use RuntimeException;

trait InteractsWithDemoTenantSeedContext
{
    protected function demoSeedContext(): DemoTenantSeedContextData
    {
        if (! app()->bound(DemoTenantSeedContextData::class)) {
            throw new RuntimeException('Demo tenant seeding context has not been initialized.');
        }

        return app(DemoTenantSeedContextData::class);
    }
}
