<?php

declare(strict_types=1);

namespace App\Demo\Commands;

use App\Demo\Services\DemoDatasetManager;
use App\Demo\Services\DemoEnvironmentGuard;
use Throwable;

final class DemoRefreshOperationsCommand extends BaseDemoCommand
{
    protected $signature = 'demo:refresh-operations
        {--reference-date= : YYYY-MM-DD}
        {--tenant=all : all|small|regional|enterprise}
        {--profile= : minimal|standard|large}
        {--force : Skip interactive confirmation}';

    protected $description = 'Rebuild operational demo data using a new reference date';

    public function __construct(
        private readonly DemoEnvironmentGuard $environmentGuard,
        private readonly DemoDatasetManager $datasetManager,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->renderDemoWarningBanner();

        try {
            $this->environmentGuard->assertMutationAllowed('demo:refresh-operations', explicitCommand: true);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (! (bool) $this->option('force')) {
            $confirmed = $this->confirm(
                'Refresh operational demo data while preserving tenant identity and domains?',
                false
            );

            if (! $confirmed) {
                $this->comment('Cancelled.');

                return self::SUCCESS;
            }
        }

        try {
            $summary = $this->datasetManager->refreshOperations(
                tenantOption: (string) $this->option('tenant'),
                profileOverride: $this->resolveProfileOption($this->option('profile') !== null ? (string) $this->option('profile') : null),
                referenceDate: $this->option('reference-date') !== null ? (string) $this->option('reference-date') : null,
                explicitCommand: true,
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['profile', (string) $summary['profile']],
                ['reference_date', (string) $summary['reference_date']],
                ['tenant_option', (string) $summary['tenant_option']],
                ['refreshed', ((bool) $summary['refreshed']) ? 'yes' : 'no'],
            ]
        );

        $this->info('Demo operational refresh completed.');

        return self::SUCCESS;
    }
}
