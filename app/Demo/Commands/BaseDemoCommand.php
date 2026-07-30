<?php

declare(strict_types=1);

namespace App\Demo\Commands;

use App\Demo\Enums\DemoDataProfile;
use Illuminate\Console\Command;
use InvalidArgumentException;

abstract class BaseDemoCommand extends Command
{
    protected function renderDemoWarningBanner(): void
    {
        $this->newLine();
        $this->warn('DEMO DATA ONLY');
        $this->warn('DO NOT USE THESE CREDENTIALS IN PRODUCTION');
        $this->newLine();
    }

    protected function resolveProfileOption(?string $profile): ?DemoDataProfile
    {
        if (! is_string($profile) || trim($profile) === '') {
            return null;
        }

        $resolved = DemoDataProfile::tryFrom(strtolower(trim($profile)));

        if ($resolved === null) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported profile "%s". Expected minimal|standard|large.',
                $profile
            ));
        }

        return $resolved;
    }
}
