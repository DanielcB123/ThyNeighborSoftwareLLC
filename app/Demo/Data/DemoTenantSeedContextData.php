<?php

declare(strict_types=1);

namespace App\Demo\Data;

use App\Demo\Enums\DemoDataProfile;
use App\Demo\Support\DemoRandomizer;
use Carbon\CarbonImmutable;

final readonly class DemoTenantSeedContextData
{
    /**
     * @param  array{
     *   locations:int,
     *   customers:int,
     *   leads:int,
     *   invoices:int,
     *   payments:int,
     *   expenses:int,
     *   kpi_days:int,
     *   users:int
     * }  $targets
     * @param  array<string, bool>  $skipFlags
     */
    public function __construct(
        public string $connectionName,
        public string $tenantPublicId,
        public string $tenantSlug,
        public string $tenantDisplayName,
        public string $tenantDomain,
        public string $datasetVersion,
        public DemoDataProfile $profile,
        public CarbonImmutable $referenceDate,
        public DemoTenantScenarioData $scenario,
        public array $targets,
        public array $skipFlags,
        public DemoRandomizer $randomizer,
        public string $password,
    ) {
    }
}
