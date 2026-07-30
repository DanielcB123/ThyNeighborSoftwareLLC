<?php

declare(strict_types=1);

namespace App\Demo\Commands;

use App\Demo\Services\DemoEnvironmentGuard;
use App\Demo\Verification\DemoDatasetVerifier;
use Throwable;

final class DemoVerifyCommand extends BaseDemoCommand
{
    protected $signature = 'demo:verify';

    protected $description = 'Verify core demo dataset integrity and scope';

    public function __construct(
        private readonly DemoEnvironmentGuard $environmentGuard,
        private readonly DemoDatasetVerifier $datasetVerifier,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->renderDemoWarningBanner();

        try {
            $this->environmentGuard->assertCredentialsDisplayAllowed();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        try {
            $result = $this->datasetVerifier->verify();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $rows = [];

        foreach ($result['checks'] as $check) {
            $rows[] = [
                $check['name'],
                $check['ok'] ? 'PASS' : 'FAIL',
                (string) count($check['issues']),
            ];
        }

        $this->table(['Check', 'Status', 'Issue count'], $rows);

        foreach ($result['checks'] as $check) {
            if ($check['issues'] === []) {
                continue;
            }

            $this->line(sprintf('Issues for %s:', $check['name']));
            foreach ($check['issues'] as $issue) {
                $this->line(' - '.$issue);
            }
        }

        if (! $result['ok']) {
            return self::FAILURE;
        }

        $this->info('Demo verification passed.');

        return self::SUCCESS;
    }
}
