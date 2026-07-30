<?php

declare(strict_types=1);

namespace App\Demo\Verification;

use App\Models\Central\Tenant;

final class DemoFinancialVerifier
{
    /**
     * @return array{ok:bool,issues:list<string>}
     */
    public function verify(): array
    {
        $issues = [];

        $demoTenantCount = Tenant::query()->where('is_demo', true)->count();

        if ($demoTenantCount === 0) {
            $issues[] = 'No demo tenants are present, so finance verification cannot run.';
        }

        return [
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }
}
