<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum ProspectWorkspaceStatus: string
{
    case Active = 'active';
    case DiscoveryInProgress = 'discovery_in_progress';
    case DiscoveryComplete = 'discovery_complete';
    case Archived = 'archived';
}
