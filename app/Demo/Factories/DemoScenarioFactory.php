<?php

declare(strict_types=1);

namespace App\Demo\Factories;

use App\Demo\Enums\DemoDataProfile;
use App\Demo\Scenarios\DemoScenarioCatalog;
use InvalidArgumentException;

final class DemoScenarioFactory
{
    public function __construct(
        private readonly DemoScenarioCatalog $catalog,
    ) {
    }

    /**
     * @return array{
     *   tenant_key:string,
     *   profile:string,
     *   tenant_count:int,
     *   tenant_weight:int,
     *   tenant_display_name:string,
     *   feature_count:int,
     *   permission_count:int
     * }
     */
    public function makeScenario(string $tenantKey, DemoDataProfile $profile): array
    {
        $tenantScenario = collect($this->catalog->tenants())
            ->first(fn ($scenario): bool => $scenario->key === $tenantKey);

        if ($tenantScenario === null) {
            throw new InvalidArgumentException(sprintf(
                'Unknown tenant key "%s". Expected one of small|regional|enterprise.',
                $tenantKey
            ));
        }

        $permissions = $this->catalog->permissions();
        $features = $this->catalog->kpi()['kpi_metrics'] ?? [];

        return [
            'tenant_key' => $tenantScenario->key,
            'profile' => $profile->value,
            'tenant_count' => count($this->catalog->tenants()),
            'tenant_weight' => $tenantScenario->weight,
            'tenant_display_name' => $tenantScenario->displayName,
            'feature_count' => count(is_array($features) ? $features : []),
            'permission_count' => count(is_array($permissions['tenant_permissions'] ?? null) ? $permissions['tenant_permissions'] : []),
        ];
    }
}
