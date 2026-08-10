<?php

declare(strict_types=1);

namespace App\Services\Zoom;

use App\Services\Zoom\Exceptions\ZoomIntegrationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class ZoomClient
{
    private const TOKEN_CACHE_KEY = 'zoom:s2s:access-token';

    /**
     * @param  array{account_id: string|null, client_id: string|null, client_secret: string|null, host_user: string|null}  $zoomConfig
     */
    public function __construct(
        private readonly array $zoomConfig,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createMeetingForHost(string $hostUser, array $payload): array
    {
        $response = $this->request('POST', sprintf('/users/%s/meetings', rawurlencode($hostUser)), $payload);

        /** @var array<string, mixed> $decoded */
        $decoded = $response->json();

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateMeeting(string $meetingId, array $payload): void
    {
        $this->request('PATCH', sprintf('/meetings/%s', rawurlencode($meetingId)), $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeeting(string $meetingId): array
    {
        $response = $this->request('GET', sprintf('/meetings/%s', rawurlencode($meetingId)));

        /** @var array<string, mixed> $decoded */
        $decoded = $response->json();

        return $decoded;
    }

    public function deleteMeeting(string $meetingId): void
    {
        $response = $this->request(
            'DELETE',
            sprintf('/meetings/%s', rawurlencode($meetingId)),
            [],
            ['schedule_for_reminder' => 'false'],
            suppressNotFound: true,
        );

        if ($response->status() === 404) {
            return;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $query
     */
    private function request(
        string $method,
        string $uri,
        array $payload = [],
        array $query = [],
        bool $suppressNotFound = false,
    ): Response {
        $this->ensureConfigured();

        $attemptedTokenRefresh = false;

        do {
            $response = $this->zoomApiRequest()
                ->send($method, sprintf('https://api.zoom.us/v2%s', $uri), array_filter([
                    'query' => $query !== [] ? $query : null,
                    'json' => $payload !== [] ? $payload : null,
                ]));

            if ($response->status() === 401 && ! $attemptedTokenRefresh) {
                Cache::forget(self::TOKEN_CACHE_KEY);
                $attemptedTokenRefresh = true;
                continue;
            }

            if ($suppressNotFound && $response->status() === 404) {
                return $response;
            }

            if ($response->failed()) {
                throw $this->exceptionFromResponse($response);
            }

            return $response;
        } while (true);
    }

    private function zoomApiRequest(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout(20)
            ->withToken($this->accessToken());
    }

    private function accessToken(): string
    {
        /** @var string|null $cachedToken */
        $cachedToken = Cache::get(self::TOKEN_CACHE_KEY);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $response = Http::asForm()
            ->acceptJson()
            ->withBasicAuth(
                (string) $this->zoomConfig['client_id'],
                (string) $this->zoomConfig['client_secret'],
            )
            ->timeout(15)
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => (string) $this->zoomConfig['account_id'],
            ]);

        if ($response->failed()) {
            throw new ZoomIntegrationException(
                'Zoom OAuth token request failed.',
                'Zoom authentication failed. Please try again shortly.',
            );
        }

        $token = (string) $response->json('access_token', '');
        $expiresIn = max(60, (int) $response->json('expires_in', 3600) - 60);

        if ($token === '') {
            throw new ZoomIntegrationException(
                'Zoom OAuth token payload missing access_token.',
                'Zoom authentication failed. Please try again shortly.',
            );
        }

        Cache::put(self::TOKEN_CACHE_KEY, $token, now()->addSeconds($expiresIn));

        return $token;
    }

    private function ensureConfigured(): void
    {
        foreach (['account_id', 'client_id', 'client_secret'] as $requiredKey) {
            if (! is_string($this->zoomConfig[$requiredKey] ?? null) || trim((string) $this->zoomConfig[$requiredKey]) === '') {
                throw new ZoomIntegrationException(
                    sprintf('Zoom configuration missing [%s].', $requiredKey),
                    'Zoom integration is not configured yet. Please contact support.',
                );
            }
        }
    }

    private function exceptionFromResponse(Response $response): ZoomIntegrationException
    {
        $statusCode = $response->status();
        $zoomMessage = (string) ($response->json('message') ?? '');

        $userMessage = match (true) {
            $statusCode === 400 => 'Zoom rejected the meeting details. Please review your selected date and time.',
            $statusCode === 401 || $statusCode === 403 => 'Zoom authentication failed. Please try again shortly.',
            $statusCode === 404 => 'The Zoom meeting could not be found. Please try scheduling again.',
            $statusCode >= 500 => 'Zoom is currently unavailable. Please retry in a moment.',
            default => 'We could not complete the Zoom request. Please try again.',
        };

        return new ZoomIntegrationException(
            sprintf(
                'Zoom API request failed with status [%d] and message [%s].',
                $statusCode,
                $zoomMessage,
            ),
            $userMessage,
        );
    }
}
