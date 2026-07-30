<?php

declare(strict_types=1);

namespace App\Demo\Verification;

use App\Demo\Services\DemoTenantRegistry;
use App\Models\Central\Tenant;
use App\Models\User;

final class DemoDatasetVerifier
{
    public function __construct(
        private readonly DemoTenantRegistry $tenantRegistry,
        private readonly DemoFinancialVerifier $financialVerifier,
        private readonly DemoTenantIsolationVerifier $isolationVerifier,
    ) {
    }

    /**
     * @return array{
     *   ok:bool,
     *   checks:list<array{name:string,ok:bool,issues:list<string>}>
     * }
     */
    public function verify(): array
    {
        $checks = [
            $this->verifyCentralTenantRecords(),
            $this->verifySeededUsers(),
            $this->isolationCheck(),
            $this->financeCheck(),
        ];

        $overallOk = ! collect($checks)->contains(static fn (array $check): bool => $check['ok'] === false);

        return [
            'ok' => $overallOk,
            'checks' => $checks,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyCentralTenantRecords(): array
    {
        $issues = [];

        foreach ($this->tenantRegistry->all() as $scenario) {
            $slug = (string) ($scenario['slug'] ?? '');
            $tenant = Tenant::query()->where('slug', $slug)->first();

            if ($tenant === null) {
                $issues[] = sprintf('Tenant "%s" is missing.', $slug);
                continue;
            }

            if (! $tenant->is_demo) {
                $issues[] = sprintf('Tenant "%s" is not marked as demo.', $slug);
            }

            $expectedDomain = strtolower((string) ($scenario['domain'] ?? ''));
            $hasDomain = $tenant->domains()->where('domain', $expectedDomain)->exists();
            if (! $hasDomain) {
                $issues[] = sprintf('Tenant "%s" is missing domain mapping "%s".', $slug, $expectedDomain);
            }
        }

        return [
            'name' => 'central_demo_tenant_records',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifySeededUsers(): array
    {
        $issues = [];

        foreach ($this->tenantRegistry->all() as $scenario) {
            foreach (($scenario['users'] ?? []) as $userScenario) {
                $email = strtolower((string) ($userScenario['email'] ?? ''));

                if ($email === '') {
                    continue;
                }

                if (! User::query()->where('email', $email)->exists()) {
                    $issues[] = sprintf('Expected demo user missing: %s', $email);
                }
            }
        }

        return [
            'name' => 'seeded_users',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function isolationCheck(): array
    {
        $result = $this->isolationVerifier->verify();

        return [
            'name' => 'tenant_isolation',
            'ok' => $result['ok'],
            'issues' => $result['issues'],
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function financeCheck(): array
    {
        $result = $this->financialVerifier->verify();

        return [
            'name' => 'financial_consistency',
            'ok' => $result['ok'],
            'issues' => $result['issues'],
        ];
    }
}
