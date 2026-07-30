<?php

declare(strict_types=1);

namespace Tests\Unit\Demo;

use App\Demo\Data\DemoConfigurationData;
use App\Demo\Enums\DemoDataProfile;
use Tests\TestCase;

class DemoConfigurationDataTest extends TestCase
{
    public function test_it_builds_typed_demo_configuration_data(): void
    {
        $data = DemoConfigurationData::fromConfig([
            'enabled' => true,
            'password' => 'DemoPassword!2026',
            'reference_date' => '2026-07-01',
            'dataset_version' => '1.0.0',
            'profile' => 'minimal',
            'allowed_environments' => ['local', 'testing'],
            'allowed_database_patterns' => ['/^wbyt_test_[a-z0-9_]+$/'],
            'domains' => [
                'small_business' => 'coastalcomfortplumbing.test',
            ],
        ]);

        $this->assertTrue($data->enabled);
        $this->assertSame('DemoPassword!2026', $data->password);
        $this->assertSame('2026-07-01', $data->referenceDate->toDateString());
        $this->assertSame('1.0.0', $data->datasetVersion);
        $this->assertSame(DemoDataProfile::Minimal, $data->profile);
        $this->assertTrue($data->isEnvironmentAllowed('testing'));
        $this->assertTrue($data->isDatabaseNameAllowed('wbyt_test_central'));
        $this->assertSame('coastalcomfortplumbing.test', $data->domains['small_business']);
    }
}
