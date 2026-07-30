<?php

declare(strict_types=1);

namespace App\Demo\Data;

use App\Demo\Enums\DemoDataProfile;
use App\Demo\Enums\DemoDatasetVersion;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

final readonly class DemoConfigurationData
{
    /**
     * @param  list<string>  $allowedEnvironments
     * @param  list<string>  $allowedDatabasePatterns
     * @param  array<string, string>  $domains
     */
    public function __construct(
        public bool $enabled,
        public string $password,
        public CarbonImmutable $referenceDate,
        public string $datasetVersion,
        public DemoDataProfile $profile,
        public array $allowedEnvironments,
        public array $allowedDatabasePatterns,
        public array $domains,
    ) {
    }

    public static function fromConfig(?array $config = null): self
    {
        $resolvedConfig = is_array($config) ? $config : config('demo', []);
        $profile = DemoDataProfile::tryFrom((string) ($resolvedConfig['profile'] ?? ''))
            ?? DemoDataProfile::Standard;

        return new self(
            enabled: (bool) ($resolvedConfig['enabled'] ?? false),
            password: (string) ($resolvedConfig['password'] ?? 'DemoPassword!2026'),
            referenceDate: self::resolveReferenceDate($resolvedConfig['reference_date'] ?? null),
            datasetVersion: (string) ($resolvedConfig['dataset_version'] ?? DemoDatasetVersion::CURRENT),
            profile: $profile,
            allowedEnvironments: self::normalizeStringList($resolvedConfig['allowed_environments'] ?? []),
            allowedDatabasePatterns: self::normalizeStringList($resolvedConfig['allowed_database_patterns'] ?? []),
            domains: self::normalizeStringMap($resolvedConfig['domains'] ?? []),
        );
    }

    public function isEnvironmentAllowed(string $environment): bool
    {
        return in_array(strtolower($environment), $this->allowedEnvironments, true);
    }

    public function isDatabaseNameAllowed(string $databaseName): bool
    {
        foreach ($this->allowedDatabasePatterns as $pattern) {
            if (@preg_match($pattern, $databaseName) === 1) {
                return true;
            }
        }

        return false;
    }

    private static function resolveReferenceDate(mixed $value): CarbonImmutable
    {
        if (is_string($value) && trim($value) !== '') {
            return CarbonImmutable::parse($value)->startOfDay();
        }

        return Carbon::now()->startOfMonth()->toImmutable()->startOfDay();
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private static function normalizeStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => strtolower(trim((string) $item)),
            $value
        ), static fn (string $item): bool => $item !== ''));
    }

    /**
     * @param  mixed  $value
     * @return array<string, string>
     */
    private static function normalizeStringMap(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $key => $domain) {
            $normalized[(string) $key] = strtolower(trim((string) $domain));
        }

        return $normalized;
    }
}
