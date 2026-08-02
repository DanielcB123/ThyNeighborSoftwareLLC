<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum DiscoverySessionStatus: string
{
    case InProgress = 'in_progress';
    case MeetingRequested = 'meeting_requested';
    case DiscoveryComplete = 'discovery_complete';
    case Archived = 'archived';
}
