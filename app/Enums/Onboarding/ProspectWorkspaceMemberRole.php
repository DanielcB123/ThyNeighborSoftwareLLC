<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum ProspectWorkspaceMemberRole: string
{
    case Owner = 'owner';
    case Collaborator = 'collaborator';
    case Reviewer = 'reviewer';
}
