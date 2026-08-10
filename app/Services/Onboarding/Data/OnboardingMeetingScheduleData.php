<?php

declare(strict_types=1);

namespace App\Services\Onboarding\Data;

final class OnboardingMeetingScheduleData
{
    public function __construct(
        public readonly string $businessName,
        public readonly string $contactName,
        public readonly string $contactEmail,
        public readonly string $meetingDate,
        public readonly string $meetingTime,
        public readonly string $timezone,
        public readonly int $durationMinutes,
    ) {
    }
}
