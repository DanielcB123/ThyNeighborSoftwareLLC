<?php

declare(strict_types=1);

namespace App\Demo\Commands;

use App\Demo\Services\DemoDatasetManager;
use App\Demo\Services\DemoEnvironmentGuard;
use Throwable;

final class DemoResetCommand extends BaseDemoCommand
{
    protected $signature = 'demo:reset
        {--tenant=all : all|small|regional|enterprise}
        {--keep-databases : Keep physical tenant databases}
        {--force : Skip interactive confirmation}';

    protected $description = 'Reset demo tenants and related records safely';

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
            $this->environmentGuard->assertMutationAllowed('demo:reset', explicitCommand: true);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (! (bool) $this->option('force')) {
            $confirmed = $this->confirm(
                'This will remove demo tenant records and may drop demo databases. Continue?',
                false
            );

            if (! $confirmed) {
                $this->comment('Cancelled.');

                return self::SUCCESS;
            }
        }

        try {
            $summary = $this->datasetManager->reset(
                tenantOption: (string) $this->option('tenant'),
                keepDatabases: (bool) $this->option('keep-databases'),
                explicitCommand: true
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['tenant_option', (string) $summary['tenant_option']],
                ['deleted_tenants', (string) $summary['deleted_tenants']],
                ['dropped_databases', (string) $summary['dropped_databases']],
                ['keep_databases', ((bool) $summary['kept_databases']) ? 'yes' : 'no'],
            ]
        );

        $this->info('Demo reset completed.');

        return self::SUCCESS;
    }
}
