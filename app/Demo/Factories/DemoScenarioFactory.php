<?php

declare(strict_types=1);

namespace App\Demo\Factories;

use App\Demo\Enums\DemoDataProfile;

final class DemoScenarioFactory
{
    /**
     * @return array<string, mixed>
     */
    public function makeScenario(string $tenantKey, DemoDataProfile $profile): array
    {
        return [
            'tenant_key' => $tenantKey,
            'profile' => $profile->value,
        ];
    }
}
