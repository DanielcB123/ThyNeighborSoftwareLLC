<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum ProspectWorkspaceAccessScope: string
{
    case InvitedOnly = 'invited_only';
    case Full = 'full';
    case Limited = 'limited';
}
