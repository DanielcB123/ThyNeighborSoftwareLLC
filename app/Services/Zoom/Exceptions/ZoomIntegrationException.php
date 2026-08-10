<?php

declare(strict_types=1);

namespace App\Services\Zoom\Exceptions;

use RuntimeException;
use Throwable;

class ZoomIntegrationException extends RuntimeException
{
    public function __construct(
        string $message = 'Unable to complete Zoom operation.',
        private readonly string $userMessage = 'We could not schedule your Zoom meeting right now. Please try again.',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function userMessage(): string
    {
        return $this->userMessage;
    }
}
