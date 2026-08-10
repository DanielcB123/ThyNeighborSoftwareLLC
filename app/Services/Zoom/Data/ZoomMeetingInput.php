<?php

declare(strict_types=1);

namespace App\Services\Zoom\Data;

use Carbon\CarbonImmutable;

final class ZoomMeetingInput
{
    public function __construct(
        public readonly string $topic,
        public readonly CarbonImmutable $scheduledAt,
        public readonly string $timezone,
        public readonly int $durationMinutes,
    ) {
    }
}
