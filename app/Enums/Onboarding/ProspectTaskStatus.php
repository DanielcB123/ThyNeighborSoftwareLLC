<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum ProspectTaskStatus: string
{
    case Open = 'open';
    case Requested = 'requested';
    case Scheduled = 'scheduled';
    case Complete = 'complete';
    case Cancelled = 'cancelled';
}
