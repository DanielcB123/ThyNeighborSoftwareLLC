<?php

declare(strict_types=1);

namespace Tests\Unit\Demo;

use App\Demo\Data\DemoTenantScenarioData;
use InvalidArgumentException;
use Tests\TestCase;

class DemoTenantScenarioDataTest extends TestCase
{
    public function test_it_builds_typed_tenant_scenario_from_payload(): void
    {
        $scenario = DemoTenantScenarioData::fromArray([
            'key' => 'small',
            'weight' => 20,
            'slug' => 'coastal-comfort-plumbing',
            'display_name' => 'Coastal Comfort Plumbing',
            'public_id' => '01K1A4SMALLBIZTENANT000001',
            'domain' => 'coastalcomfortplumbing.test',
            'database_name' => 'wbyt_local_coastal_comfort_plumbing',
            'organization_units' => [
                ['key' => 'hq', 'name' => 'Headquarters', 'scope' => 'company', 'parent' => null],
            ],
            'users' => [
                ['email' => 'owner@coastalcomfortplumbing.test', 'role' => 'owner'],
            ],
        ]);

        $this->assertSame('small', $scenario->key);
        $this->assertSame(20, $scenario->weight);
        $this->assertSame('coastal-comfort-plumbing', $scenario->slug);
        $this->assertSame('wbyt_local_coastal_comfort_plumbing', $scenario->databaseName);
        $this->assertCount(1, $scenario->organizationUnits);
        $this->assertCount(1, $scenario->users);
    }

    public function test_it_rejects_missing_required_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DemoTenantScenarioData::fromArray([
            'key' => 'small',
            'slug' => 'coastal-comfort-plumbing',
        ]);
    }
}
