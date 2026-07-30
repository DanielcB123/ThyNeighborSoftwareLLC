<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Demo\Services\DemoDatasetManager;
use Illuminate\Database\Seeder;

final class DemoTenantDatasetSeeder extends Seeder
{
    public function __construct(
        private readonly DemoDatasetManager $datasetManager,
    ) {
    }

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        if (! (bool) config('demo.enabled', false)) {
            return;
        }

        if (! (bool) config('demo.auto_seed_on_database_seeder', true)) {
            return;
        }

        $this->datasetManager->seed(
            tenantOption: 'all',
            referenceDate: null,
            profileOverride: null,
            seedPlatform: true,
            skipFlags: [],
            explicitCommand: true
        );
    }
}
