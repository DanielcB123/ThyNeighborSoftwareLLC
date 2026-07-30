<?php

declare(strict_types=1);

namespace App\Demo\Services;

use Illuminate\Support\Collection;
use InvalidArgumentException;

final class DemoTenantRegistry
{
    /**
     * @return Collection<int, array<string, mixed>>
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
     * @return Collection<int, array<string, mixed>>
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
            ->filter(static fn (array $tenant): bool => (string) $tenant['key'] === $tenantByOption[$normalized])
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function loadScenarioFile(string $fileName): array
    {
        $path = database_path('demo/'.$fileName);

        /** @var array<string, mixed> $scenario */
        $scenario = require $path;

        return $scenario;
    }
}
