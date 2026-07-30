<?php

declare(strict_types=1);

namespace App\Demo\Support;

use App\Demo\Data\DemoConfigurationData;
use Carbon\CarbonImmutable;

final class DemoReferenceClock
{
    public function resolveReferenceDate(?string $overrideDate = null): CarbonImmutable
    {
        if (is_string($overrideDate) && trim($overrideDate) !== '') {
            return CarbonImmutable::parse($overrideDate)->startOfDay();
        }

        return DemoConfigurationData::fromConfig()->referenceDate;
    }
}
