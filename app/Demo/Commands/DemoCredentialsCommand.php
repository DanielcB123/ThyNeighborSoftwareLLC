<?php

declare(strict_types=1);

namespace App\Demo\Commands;

use App\Demo\Services\DemoCredentialRegistry;
use App\Demo\Services\DemoEnvironmentGuard;
use Throwable;

final class DemoCredentialsCommand extends BaseDemoCommand
{
    protected $signature = 'demo:credentials
        {--tenant=all : all|small|regional|enterprise}
        {--role= : Filter to a specific role}
        {--json : Render credentials in JSON}';

    protected $description = 'Display demo login credentials';

    public function __construct(
        private readonly DemoEnvironmentGuard $environmentGuard,
        private readonly DemoCredentialRegistry $credentialRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->renderDemoWarningBanner();

        try {
            $this->environmentGuard->assertCredentialsDisplayAllowed();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $credentials = $this->credentialRegistry->credentials(
            tenantOption: (string) $this->option('tenant'),
            role: $this->option('role') !== null ? (string) $this->option('role') : null
        );

        if ((bool) $this->option('json')) {
            $this->line($credentials->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $rows = $credentials->map(static fn (array $entry): array => [
            $entry['tenant'],
            $entry['domain'],
            $entry['login_url'],
            $entry['name'],
            $entry['email'],
            $entry['role'],
            $entry['scope'],
            $entry['password'],
            $entry['mfa_status'],
            $entry['access_limitations'],
        ])->all();

        $this->table(
            [
                'Tenant',
                'Domain',
                'Login URL',
                'Name',
                'Email',
                'Role',
                'Scope',
                'Password',
                'MFA',
                'Access limitations',
            ],
            $rows
        );

        return self::SUCCESS;
    }
}
