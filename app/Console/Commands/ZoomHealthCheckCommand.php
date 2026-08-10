<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Zoom\Exceptions\ZoomIntegrationException;
use App\Services\Zoom\ZoomClient;
use Illuminate\Console\Command;
use Throwable;

final class ZoomHealthCheckCommand extends Command
{
    protected $signature = 'app:zoom-health-check';

    protected $description = 'Verify Zoom auth and host user availability for onboarding scheduling';

    public function handle(ZoomClient $zoomClient): int
    {
        $zoomConfig = (array) config('services.zoom', []);
        $hostUser = trim((string) ($zoomConfig['host_user'] ?? ''));

        $this->line('Running Zoom integration health check...');
        $this->newLine();

        $configRows = [
            ['ZOOM_ACCOUNT_ID', $this->presenceStatus($zoomConfig['account_id'] ?? null)],
            ['ZOOM_CLIENT_ID', $this->presenceStatus($zoomConfig['client_id'] ?? null)],
            ['ZOOM_CLIENT_SECRET', $this->presenceStatus($zoomConfig['client_secret'] ?? null)],
            ['ZOOM_HOST_USER', $hostUser !== '' ? $hostUser : '[missing]'],
            ['ZOOM_VERIFY_SSL', $this->boolDisplay($zoomConfig['verify_ssl'] ?? true)],
        ];

        $this->table(['Key', 'Resolved Value'], $configRows);

        if ($hostUser === '') {
            $this->error('ZOOM_HOST_USER is missing. Scheduler cannot create meetings without a host user.');

            return self::FAILURE;
        }

        try {
            $user = $zoomClient->getUser($hostUser);
        } catch (ZoomIntegrationException $exception) {
            $this->error('Zoom health check failed.');
            $this->line('Reason: '.$exception->userMessage());
            $this->line('Debug: '.$exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error('Zoom health check failed with an unexpected error.');
            $this->line('Debug: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Zoom health check passed.');
        $this->line(sprintf(
            'Host user resolved: %s (%s)',
            (string) ($user['email'] ?? $hostUser),
            (string) ($user['id'] ?? 'unknown-id')
        ));
        $this->line(sprintf(
            'User status: %s',
            (string) ($user['status'] ?? 'unknown')
        ));

        return self::SUCCESS;
    }

    private function presenceStatus(mixed $value): string
    {
        return is_string($value) && trim($value) !== '' ? 'present' : '[missing]';
    }

    private function boolDisplay(mixed $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) === false
            ? 'false'
            : 'true';
    }
}
