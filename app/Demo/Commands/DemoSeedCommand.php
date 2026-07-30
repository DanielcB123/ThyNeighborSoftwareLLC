<?php

declare(strict_types=1);

namespace App\Demo\Commands;

use App\Demo\Services\DemoDatasetManager;
use App\Demo\Services\DemoEnvironmentGuard;
use Throwable;

final class DemoSeedCommand extends BaseDemoCommand
{
    protected $signature = 'demo:seed
        {--profile= : minimal|standard|large}
        {--reference-date= : YYYY-MM-DD}
        {--tenant=all : all|small|regional|enterprise}
        {--skip-platform : Skip platform user seeding}
        {--skip-finance : Skip finance scenario data}
        {--skip-content : Skip content scenario data}
        {--skip-analytics : Skip analytics scenario data}
        {--force : Skip interactive confirmation}';

    protected $description = 'Seed deterministic demo tenants and credentials';

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
            $this->environmentGuard->assertMutationAllowed('demo:seed', explicitCommand: true);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $confirmed = (bool) $this->option('force') || $this->confirm(
            'This command seeds deterministic demo data in central and demo tenant metadata. Continue?',
            false
        );

        if (! $confirmed) {
            $this->comment('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $summary = $this->datasetManager->seed(
                tenantOption: (string) $this->option('tenant'),
                referenceDate: $this->option('reference-date') !== null ? (string) $this->option('reference-date') : null,
                profileOverride: $this->resolveProfileOption($this->option('profile') !== null ? (string) $this->option('profile') : null),
                seedPlatform: ! (bool) $this->option('skip-platform'),
                skipFlags: [
                    'skip-platform' => (bool) $this->option('skip-platform'),
                    'skip-finance' => (bool) $this->option('skip-finance'),
                    'skip-content' => (bool) $this->option('skip-content'),
                    'skip-analytics' => (bool) $this->option('skip-analytics'),
                ],
                explicitCommand: true
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['dataset_version', (string) $summary['dataset_version']],
                ['profile', (string) $summary['profile']],
                ['reference_date', (string) $summary['reference_date']],
                ['tenant_option', (string) $summary['tenant_option']],
                ['seeded_tenants', (string) $summary['seeded_tenants']],
                ['seeded_users', (string) $summary['seeded_users']],
                ['tenant_databases', implode(', ', $summary['tenant_databases']) ?: 'none'],
                ['skip_flags', implode(', ', $summary['skip_flags']) ?: 'none'],
            ]
        );

        $this->info('Demo seed completed.');

        return self::SUCCESS;
    }
}
