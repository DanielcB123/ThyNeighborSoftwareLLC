<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Demo\Data\DemoConfigurationData;
use Illuminate\Support\Collection;

final class DemoCredentialRegistry
{
    public function __construct(
        private readonly DemoTenantRegistry $tenantRegistry,
    ) {
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    public function credentials(string $tenantOption, ?string $role = null): Collection
    {
        $configuration = DemoConfigurationData::fromConfig();
        $normalizedRole = is_string($role) && trim($role) !== '' ? strtolower(trim($role)) : null;

        return $this->tenantRegistry
            ->resolveSelection($tenantOption)
            ->flatMap(function (array $tenant) use ($configuration): array {
                $domain = (string) ($tenant['domain'] ?? '');
                $roles = is_array($tenant['users'] ?? null) ? $tenant['users'] : [];

                return array_map(static function (array $entry) use ($configuration, $domain): array {
                    $email = (string) ($entry['email'] ?? '');
                    $name = (string) ($entry['name'] ?? '');
                    $role = (string) ($entry['role'] ?? 'user');
                    $scope = (string) ($entry['scope'] ?? 'tenant');

                    return [
                        'tenant' => (string) ($entry['tenant'] ?? ''),
                        'domain' => $domain,
                        'login_url' => sprintf('https://%s/login', $domain),
                        'name' => $name,
                        'email' => $email,
                        'role' => $role,
                        'scope' => $scope,
                        'password' => $configuration->password,
                        'mfa_status' => (string) ($entry['mfa_status'] ?? 'disabled'),
                        'access_limitations' => (string) ($entry['access_limitations'] ?? 'Demo data only'),
                    ];
                }, $roles);
            })
            ->filter(static function (array $entry) use ($normalizedRole): bool {
                if ($normalizedRole === null) {
                    return true;
                }

                return strtolower((string) ($entry['role'] ?? '')) === $normalizedRole;
            })
            ->values();
    }
}
