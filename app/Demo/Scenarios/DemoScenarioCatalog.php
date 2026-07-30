<?php

declare(strict_types=1);

namespace App\Demo\Scenarios;

use App\Demo\Data\DemoTenantScenarioData;

final class DemoScenarioCatalog
{
    /**
     * @return list<DemoTenantScenarioData>
     */
    public function tenants(): array
    {
        return [
            $this->loadTenantScenario('small-business.php'),
            $this->loadTenantScenario('regional-business.php'),
            $this->loadTenantScenario('enterprise-business.php'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function permissions(): array
    {
        /** @var array<string, mixed> $permissions */
        $permissions = require database_path('demo/permissions.php');

        return $permissions;
    }

    /**
     * @return array<string, mixed>
     */
    public function financial(): array
    {
        /** @var array<string, mixed> $financial */
        $financial = require database_path('demo/financial-scenarios.php');

        return $financial;
    }

    /**
     * @return array<string, mixed>
     */
    public function kpi(): array
    {
        /** @var array<string, mixed> $kpi */
        $kpi = require database_path('demo/kpi-scenarios.php');

        return $kpi;
    }

    /**
     * @return array<string, mixed>
     */
    public function content(): array
    {
        /** @var array<string, mixed> $content */
        $content = require database_path('demo/content-scenarios.php');

        return $content;
    }

    private function loadTenantScenario(string $fileName): DemoTenantScenarioData
    {
        /** @var array<string, mixed> $payload */
        $payload = require database_path('demo/'.$fileName);

        return DemoTenantScenarioData::fromArray($payload);
    }
}
