<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Demo\Services\DemoTenantDataSeeder;
use App\Demo\Support\InteractsWithDemoTenantSeedContext;
use Illuminate\Database\Seeder;

final class TenantFormSeeder extends Seeder
{
    use InteractsWithDemoTenantSeedContext;

    public function run(): void
    {
        app(DemoTenantDataSeeder::class)->seedForms($this->demoSeedContext());
    }
}
