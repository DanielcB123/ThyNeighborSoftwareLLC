<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum DiscoveryTemplateStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Retired = 'retired';
}
