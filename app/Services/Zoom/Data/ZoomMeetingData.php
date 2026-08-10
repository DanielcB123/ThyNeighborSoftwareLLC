<?php

declare(strict_types=1);

namespace App\Services\Zoom\Data;

final class ZoomMeetingData
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $joinUrl,
        public readonly ?string $startUrl,
        public readonly ?string $passcode,
        public readonly ?string $status,
    ) {
    }
}
