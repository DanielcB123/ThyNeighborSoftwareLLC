<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Demo\Data\DemoTenantScenarioData;
use Illuminate\Support\Collection;
use InvalidArgumentException;

final class DemoTenantRegistry
{
    /**
     * @return Collection<int, DemoTenantScenarioData>
     */
    public function all(): Collection
    {
        return collect([
            $this->loadScenarioFile('small-business.php'),
            $this->loadScenarioFile('regional-business.php'),
            $this->loadScenarioFile('enterprise-business.php'),
        ]);
    }

    /**
     * @return Collection<int, DemoTenantScenarioData>
     */
    public function resolveSelection(string $tenantOption): Collection
    {
        $normalized = strtolower(trim($tenantOption));

        if ($normalized === '' || $normalized === 'all') {
            return $this->all();
        }

        $tenantByOption = [
            'small' => 'small',
            'regional' => 'regional',
            'enterprise' => 'enterprise',
        ];

        if (! array_key_exists($normalized, $tenantByOption)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported tenant option "%s". Expected all|small|regional|enterprise.',
                $tenantOption
            ));
        }

        return $this->all()
            ->filter(static fn (DemoTenantScenarioData $tenant): bool => $tenant->key === $tenantByOption[$normalized])
            ->values();
    }

    /**
     * @return DemoTenantScenarioData
     */
    private function loadScenarioFile(string $fileName): DemoTenantScenarioData
    {
        $path = database_path('demo/'.$fileName);

        /** @var array<string, mixed> $scenarioPayload */
        $scenarioPayload = require $path;

        return DemoTenantScenarioData::fromArray($scenarioPayload);
    }
}
