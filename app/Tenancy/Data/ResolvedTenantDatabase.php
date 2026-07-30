<?php

declare(strict_types=1);

namespace App\Tenancy\Data;

final readonly class ResolvedTenantDatabase
{
    public function __construct(
        public string $tenantPublicId,
        public string $tenantSlug,
        public string $tenantDisplayName,
        public string $tenantStatus,
        public string $tenantLocale,
        public string $tenantTimezone,
        /** @var list<string> */
        public array $tenantEnabledModules,
        /** @var list<string> */
        public array $tenantEnabledCapabilities,
        public string $domain,
        public string $databaseName,
        public string $databaseStatus,
        public string $secretReference,
        public string $clusterName,
        public string $clusterHost,
        public int $clusterPort,
        public string $clusterSslMode,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toCachePayload(): array
    {
        return [
            'tenantPublicId' => $this->tenantPublicId,
            'tenantSlug' => $this->tenantSlug,
            'tenantDisplayName' => $this->tenantDisplayName,
            'tenantStatus' => $this->tenantStatus,
            'tenantLocale' => $this->tenantLocale,
            'tenantTimezone' => $this->tenantTimezone,
            'tenantEnabledModules' => $this->tenantEnabledModules,
            'tenantEnabledCapabilities' => $this->tenantEnabledCapabilities,
            'domain' => $this->domain,
            'databaseName' => $this->databaseName,
            'databaseStatus' => $this->databaseStatus,
            'secretReference' => $this->secretReference,
            'clusterName' => $this->clusterName,
            'clusterHost' => $this->clusterHost,
            'clusterPort' => $this->clusterPort,
            'clusterSslMode' => $this->clusterSslMode,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromCachePayload(array $payload): ?self
    {
        $requiredFields = [
            'tenantPublicId',
            'tenantSlug',
            'tenantDisplayName',
            'tenantStatus',
            'tenantLocale',
            'tenantTimezone',
            'tenantEnabledModules',
            'tenantEnabledCapabilities',
            'domain',
            'databaseName',
            'databaseStatus',
            'secretReference',
            'clusterName',
            'clusterHost',
            'clusterPort',
            'clusterSslMode',
        ];

        foreach ($requiredFields as $field) {
            if (! array_key_exists($field, $payload)) {
                return null;
            }
        }

        return new self(
            (string) $payload['tenantPublicId'],
            (string) $payload['tenantSlug'],
            (string) $payload['tenantDisplayName'],
            (string) $payload['tenantStatus'],
            (string) $payload['tenantLocale'],
            (string) $payload['tenantTimezone'],
            array_values(array_map('strval', is_array($payload['tenantEnabledModules']) ? $payload['tenantEnabledModules'] : [])),
            array_values(array_map('strval', is_array($payload['tenantEnabledCapabilities']) ? $payload['tenantEnabledCapabilities'] : [])),
            (string) $payload['domain'],
            (string) $payload['databaseName'],
            (string) $payload['databaseStatus'],
            (string) $payload['secretReference'],
            (string) $payload['clusterName'],
            (string) $payload['clusterHost'],
            (int) $payload['clusterPort'],
            (string) $payload['clusterSslMode'],
        );
    }
}
