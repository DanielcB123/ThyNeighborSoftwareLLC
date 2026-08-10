<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum ProspectWorkspaceMemberStatus: string
{
    case Invited = 'invited';
    case Active = 'active';
    case Suspended = 'suspended';
    case Removed = 'removed';
}
