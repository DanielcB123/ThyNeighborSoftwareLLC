<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Demo\Services\DemoTenantRegistry;
use App\Models\Central\CentralSetting;
use Illuminate\Database\Seeder;

final class DemoTenantRegistrySeeder extends Seeder
{
    public function __construct(
        private readonly DemoTenantRegistry $tenantRegistry,
    ) {
    }

    public function run(): void
    {
        $tenantSummaries = $this->tenantRegistry
            ->all()
            ->map(static fn ($scenario): array => [
                'key' => $scenario->key,
                'slug' => $scenario->slug,
                'domain' => $scenario->domain,
                'database_name' => $scenario->databaseName,
                'vertical' => $scenario->vertical,
                'weight' => $scenario->weight,
            ])
            ->all();

        CentralSetting::query()->updateOrCreate(
            ['key' => 'demo.registry.tenants'],
            ['value' => json_encode($tenantSummaries, JSON_UNESCAPED_SLASHES)]
        );
    }
}
