<?php

declare(strict_types=1);

namespace App\Models\Central;

/**
 * @deprecated Use {@see Lead} as the canonical model for `leads`.
 *             Prospect remains as a compatibility alias while onboarding
 *             call sites are migrated incrementally.
 */
class Prospect extends Lead
{
    //
}
