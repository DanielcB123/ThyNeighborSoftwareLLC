<?php

declare(strict_types=1);

namespace App\Demo\Scenarios;

final class DemoScenarioCatalog
{
    /**
     * @return list<string>
     */
    public function available(): array
    {
        return [
            'small-business',
            'regional-business',
            'enterprise-business',
            'permissions',
            'financial-scenarios',
            'kpi-scenarios',
            'content-scenarios',
        ];
    }
}
