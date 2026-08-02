<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum ProspectTaskType: string
{
    case General = 'general';
    case DiscoveryMeeting = 'discovery_meeting';
    case Clarification = 'clarification';
}
