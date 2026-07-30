<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class AuditEnvironmentCommand extends Command
{
    private const INCIDENT_ID = 'TENANCY-LOCAL-REGISTRY-001';

    /**
     * @var list<string>
     */
    private const TRACKED_KEYS = [
        'APP_ENV',
        'APP_URL',
        'DB_CONNECTION',
        'CENTRAL_DATABASE_URL',
        'CENTRAL_DB_HOST',
        'CENTRAL_DB_PORT',
        'CENTRAL_DB_DATABASE',
        'CENTRAL_DB_USERNAME',
        'CENTRAL_DB_PASSWORD',
        'TENANT_DB_DRIVER',
        'TENANT_DB_HOST',
        'TENANT_DB_PORT',
        'TENANT_DB_DATABASE',
        'TENANT_DB_USERNAME',
        'TENANT_DB_PASSWORD',
        'CACHE_STORE',
        'REDIS_HOST',
        'REDIS_PORT',
        'REDIS_PASSWORD',
        'DEMO_SEEDING_ENABLED',
        'DEMO_DATA_PROFILE',
        'PLATFORM_DOMAINS',
        'TENANCY_RESOLUTION_CACHE_STORE',
        'TENANCY_RESOLUTION_CACHE_PREFIX',
        'TENANCY_RESOLUTION_CACHE_TTL',
        'TENANCY_RESOLUTION_CACHE_TTL_SECONDS',
        'TENANCY_NEGATIVE_CACHE_TTL',
        'TENANCY_UNKNOWN_HOST_BEHAVIOR',
        'TENANCY_LOCAL_DOMAINS_ENABLED',
    ];

    /**
     * @var list<string>
     */
    private const REQUIRED_KEYS = [
        'APP_ENV',
        'APP_URL',
        'DB_CONNECTION',
        'CENTRAL_DB_HOST',
        'CENTRAL_DB_PORT',
        'CENTRAL_DB_DATABASE',
        'CENTRAL_DB_USERNAME',
        'CACHE_STORE',
        'REDIS_HOST',
        'REDIS_PORT',
        'DEMO_SEEDING_ENABLED',
        'DEMO_DATA_PROFILE',
    ];

    /**
     * @var array<string, string>
     */
    private const DEPRECATED_KEYS = [
        'TENANT_DB_DATABASE' => 'Tenant database name is resolved from central tenant registry metadata.',
        'TENANT_DB_USERNAME' => 'Tenant credentials are resolved by the tenant secret provider.',
        'TENANT_DB_PASSWORD' => 'Tenant credentials are resolved by the tenant secret provider.',
    ];

    /**
     * @var array<string, string>
     */
    private const IGNORED_KEYS = [
        'TENANT_DB_DRIVER' => 'Tenant connection driver is fixed in config/database.php and cannot be overridden by env.',
    ];

    protected $signature = 'app:audit-environment';

    protected $description = 'Audit local environment sources for tenancy misconfiguration risks';

    /**
     * @var array<string, string>
     */
    private array $effectiveConfigPaths = [
        'APP_ENV' => 'app.env',
        'APP_URL' => 'app.url',
        'DB_CONNECTION' => 'database.default',
        'CENTRAL_DATABASE_URL' => 'database.connections.central.url',
        'CENTRAL_DB_HOST' => 'database.connections.central.host',
        'CENTRAL_DB_PORT' => 'database.connections.central.port',
        'CENTRAL_DB_DATABASE' => 'database.connections.central.database',
        'CENTRAL_DB_USERNAME' => 'database.connections.central.username',
        'CENTRAL_DB_PASSWORD' => 'database.connections.central.password',
        'TENANT_DB_HOST' => 'tenancy.secret_provider.local_cluster_defaults.host',
        'TENANT_DB_PORT' => 'tenancy.secret_provider.local_cluster_defaults.port',
        'CACHE_STORE' => 'cache.default',
        'REDIS_HOST' => 'database.redis.default.host',
        'REDIS_PORT' => 'database.redis.default.port',
        'REDIS_PASSWORD' => 'database.redis.default.password',
        'DEMO_SEEDING_ENABLED' => 'demo.enabled',
        'DEMO_DATA_PROFILE' => 'demo.profile',
        'PLATFORM_DOMAINS' => 'tenancy.platform_domains',
        'TENANCY_RESOLUTION_CACHE_STORE' => 'tenancy.resolution_cache_store',
        'TENANCY_RESOLUTION_CACHE_PREFIX' => 'tenancy.resolution_cache_prefix',
        'TENANCY_RESOLUTION_CACHE_TTL' => 'tenancy.resolution_cache_ttl',
        'TENANCY_RESOLUTION_CACHE_TTL_SECONDS' => 'tenancy.resolution_cache_ttl_seconds',
        'TENANCY_NEGATIVE_CACHE_TTL' => 'tenancy.negative_cache_ttl',
        'TENANCY_UNKNOWN_HOST_BEHAVIOR' => 'tenancy.unknown_host_behavior',
        'TENANCY_LOCAL_DOMAINS_ENABLED' => 'tenancy.local_domains_enabled',
    ];

    public function handle(): int
    {
        $sources = $this->collectSources();
        $keys = $this->trackedKeysIndex();
        $hasFailures = false;

        $this->line(sprintf('Incident: %s', self::INCIDENT_ID));
        $this->line(sprintf('Application environment: %s', app()->environment()));
        $this->newLine();

        $this->line('Scanned local configuration sources:');
        foreach ($sources as $source) {
            $this->line(sprintf(
                '- %s (%s, %s)',
                $source['name'],
                $source['type'],
                $source['active'] ? 'active' : 'reference-only'
            ));
        }
        $this->newLine();

        $this->line('Duplicate keys within individual sources:');
        $duplicateRows = [];
        foreach ($sources as $source) {
            $duplicateRows[] = [
                $source['name'],
                $source['duplicates'] === []
                    ? 'none'
                    : implode(', ', $source['duplicates']),
            ];
        }
        $this->table(['Source', 'Duplicate keys'], $duplicateRows);

        $this->line('Tracked key diagnostics:');
        foreach (array_keys($keys) as $key) {
            $records = $this->recordsForKey($sources, $key);
            $activeRecords = array_values(array_filter(
                $records,
                static fn (array $record): bool => $record['active']
            ));
            $effective = $this->effectiveValueForKey($key);
            $isSecret = $this->isSecretKey($key);
            $isRequired = in_array($key, self::REQUIRED_KEYS, true);
            $status = 'ok';
            $notes = [];

            $this->line($key.':');

            if ($records === []) {
                $this->line('    sources: missing');
            } else {
                foreach ($records as $record) {
                    $this->line(sprintf(
                        '    %s %s: %s%s',
                        $record['source'],
                        $record['line'],
                        $this->describeRecordValue($record['value'], $isSecret),
                        $record['active'] ? '' : ' [reference-only]'
                    ));
                }
            }

            if ($activeRecords === []) {
                if ($isRequired) {
                    $status = 'missing';
                    $notes[] = 'required key is missing from active local sources';
                }
            } elseif ($isRequired && $this->allRecordValuesEmpty($activeRecords)) {
                $status = 'missing';
                $notes[] = 'required key is present but empty across active local sources';
            }

            if ($this->hasValueConflict($activeRecords, $isSecret)) {
                $status = $status === 'ok' ? 'conflict' : $status;
                $notes[] = 'conflicting values across active local configuration sources';
            }

            if (array_key_exists($key, self::DEPRECATED_KEYS) && $records !== []) {
                $status = $status === 'ok' ? 'deprecated' : $status;
                $notes[] = self::DEPRECATED_KEYS[$key];
            }

            if (array_key_exists($key, self::IGNORED_KEYS) && $records !== []) {
                $status = $status === 'ok' ? 'ignored' : $status;
                $notes[] = self::IGNORED_KEYS[$key];
            }

            $this->line(sprintf(
                '    effective config%s: %s',
                $this->effectiveConfigLabel($key),
                $this->describeEffectiveValue($effective, $isSecret)
            ));
            $this->line(sprintf('    status: %s', $status));

            foreach ($notes as $note) {
                $this->line('    note: '.$note);
            }

            if (in_array($status, ['missing', 'conflict'], true)) {
                $hasFailures = true;
            }

            $this->newLine();
        }

        $sqliteWarnings = $this->sqliteWarnings();
        if ($sqliteWarnings !== []) {
            $hasFailures = true;
            $this->line('SQLite configuration warnings:');
            foreach ($sqliteWarnings as $warning) {
                $this->line('- '.$warning);
            }
            $this->newLine();
        }

        $dbConnection = strtolower((string) config('database.default', ''));
        if ($dbConnection !== 'central') {
            $hasFailures = true;
            $this->error(sprintf(
                'Expected database.default=central but found "%s".',
                $dbConnection === '' ? '[empty]' : $dbConnection
            ));
        }

        if ($hasFailures) {
            $this->warn('Environment audit completed with issues.');

            return self::FAILURE;
        }

        $this->info('Environment audit completed with no blocking issues.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{
     *   name:string,
     *   type:string,
     *   active:bool,
     *   duplicates:list<string>,
     *   records:list<array{key:string,line:string,value:string}>
     * }>
     */
    private function collectSources(): array
    {
        $sources = [];

        foreach ([
            '.env',
            '.env.local',
            '.env.testing',
            '.env.example',
        ] as $envFile) {
            $path = base_path($envFile);
            if (! is_file($path)) {
                continue;
            }

            $isActive = match ($envFile) {
                '.env', '.env.local' => true,
                '.env.testing' => app()->environment('testing'),
                default => false,
            };

            $sources[] = $this->parseDotEnvSource($path, $envFile, $isActive);
        }

        foreach (['phpunit.xml', 'phpunit.xml.dist'] as $xmlFile) {
            $path = base_path($xmlFile);
            if (! is_file($path)) {
                continue;
            }

            $sources[] = $this->parsePhpUnitXmlSource(
                $path,
                $xmlFile,
                app()->environment('testing')
            );
        }

        foreach ($this->discoverYamlConfigSources() as $yamlPath) {
            $sources[] = $this->parseYamlLikeSource(
                $yamlPath,
                $this->relativePath($yamlPath),
                true
            );
        }

        $sources[] = $this->shellEnvironmentSource(true);

        return $sources;
    }

    /**
     * @return list<string>
     */
    private function discoverYamlConfigSources(): array
    {
        $patterns = [
            base_path('docker-compose.yml'),
            base_path('docker-compose.yaml'),
            base_path('compose.yml'),
            base_path('compose.yaml'),
        ];

        $workflowPaths = glob(base_path('.github/workflows/*.yml')) ?: [];
        $workflowPaths = array_merge($workflowPaths, glob(base_path('.github/workflows/*.yaml')) ?: []);

        $paths = array_values(array_filter(array_unique(array_merge(
            $patterns,
            $workflowPaths
        )), static fn (string $path): bool => is_file($path)));

        return $paths;
    }

    /**
     * @return array{
     *   name:string,
     *   type:string,
     *   active:bool,
     *   duplicates:list<string>,
     *   records:list<array{key:string,line:string,value:string}>
     * }
     */
    private function parseDotEnvSource(string $path, string $sourceName, bool $active): array
    {
        $records = [];
        $counts = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $lineNumber => $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $normalized = str_starts_with($trimmed, 'export ')
                ? substr($trimmed, 7)
                : $trimmed;

            if (! preg_match('/^([A-Z0-9_]+)\s*=\s*(.*)$/', $normalized, $matches)) {
                continue;
            }

            $key = (string) $matches[1];
            $value = $this->normalizeEnvValue((string) $matches[2]);

            $counts[$key] = ($counts[$key] ?? 0) + 1;

            if (! in_array($key, self::TRACKED_KEYS, true)) {
                continue;
            }

            $records[] = [
                'key' => $key,
                'line' => sprintf('line %d', $lineNumber + 1),
                'value' => $value,
            ];
        }

        return [
            'name' => $sourceName,
            'type' => 'dotenv',
            'active' => $active,
            'duplicates' => $this->duplicateKeysFromCounts($counts),
            'records' => $records,
        ];
    }

    /**
     * @return array{
     *   name:string,
     *   type:string,
     *   active:bool,
     *   duplicates:list<string>,
     *   records:list<array{key:string,line:string,value:string}>
     * }
     */
    private function parsePhpUnitXmlSource(string $path, string $sourceName, bool $active): array
    {
        $records = [];
        $counts = [];

        if (! function_exists('simplexml_load_file')) {
            return [
                'name' => $sourceName,
                'type' => 'phpunit-xml',
                'active' => $active,
                'duplicates' => [],
                'records' => [],
            ];
        }

        $xml = simplexml_load_file($path);

        if ($xml === false) {
            return [
                'name' => $sourceName,
                'type' => 'phpunit-xml',
                'active' => $active,
                'duplicates' => [],
                'records' => [],
            ];
        }

        $envNodes = $xml->xpath('//php/env');
        if (! is_array($envNodes)) {
            $envNodes = [];
        }

        foreach (array_values($envNodes) as $index => $node) {
            $key = trim((string) ($node['name'] ?? ''));
            if ($key === '') {
                continue;
            }

            $counts[$key] = ($counts[$key] ?? 0) + 1;

            if (! in_array($key, self::TRACKED_KEYS, true)) {
                continue;
            }

            $records[] = [
                'key' => $key,
                'line' => sprintf('env[%d]', $index + 1),
                'value' => trim((string) ($node['value'] ?? '')),
            ];
        }

        return [
            'name' => $sourceName,
            'type' => 'phpunit-xml',
            'active' => $active,
            'duplicates' => $this->duplicateKeysFromCounts($counts),
            'records' => $records,
        ];
    }

    /**
     * @return array{
     *   name:string,
     *   type:string,
     *   active:bool,
     *   duplicates:list<string>,
     *   records:list<array{key:string,line:string,value:string}>
     * }
     */
    private function parseYamlLikeSource(string $path, string $sourceName, bool $active): array
    {
        $records = [];
        $counts = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $lineNumber => $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $key = null;
            $value = null;

            if (preg_match('/^-?\s*([A-Z0-9_]+)\s*=\s*(.+)$/', $line, $matches) === 1) {
                $key = trim((string) $matches[1]);
                $value = trim((string) $matches[2], " \t\n\r\0\x0B\"'");
            } elseif (preg_match('/^\s*([A-Z0-9_]+)\s*:\s*(.+)$/', $line, $matches) === 1) {
                $key = trim((string) $matches[1]);
                $value = trim((string) $matches[2], " \t\n\r\0\x0B\"'");
            }

            if (! is_string($key) || ! is_string($value) || $key === '') {
                continue;
            }

            if (! in_array($key, self::TRACKED_KEYS, true)) {
                continue;
            }

            $counts[$key] = ($counts[$key] ?? 0) + 1;
            $records[] = [
                'key' => $key,
                'line' => sprintf('line %d', $lineNumber + 1),
                'value' => $value,
            ];
        }

        return [
            'name' => $sourceName,
            'type' => 'yaml',
            'active' => $active,
            'duplicates' => $this->duplicateKeysFromCounts($counts),
            'records' => $records,
        ];
    }

    /**
     * @return array{
     *   name:string,
     *   type:string,
     *   active:bool,
     *   duplicates:list<string>,
     *   records:list<array{key:string,line:string,value:string}>
     * }
     */
    private function shellEnvironmentSource(bool $active): array
    {
        $records = [];
        foreach (self::TRACKED_KEYS as $key) {
            $value = getenv($key);
            if ($value === false) {
                continue;
            }

            $records[] = [
                'key' => $key,
                'line' => 'exported',
                'value' => (string) $value,
            ];
        }

        return [
            'name' => 'shell-environment',
            'type' => 'runtime',
            'active' => $active,
            'duplicates' => [],
            'records' => $records,
        ];
    }

    /**
     * @param  list<array{
     *   name:string,
     *   type:string,
     *   active:bool,
     *   duplicates:list<string>,
     *   records:list<array{key:string,line:string,value:string}>
     * }>  $sources
     * @return list<array{source:string,line:string,value:string,active:bool}>
     */
    private function recordsForKey(array $sources, string $key): array
    {
        $records = [];

        foreach ($sources as $source) {
            foreach ($source['records'] as $record) {
                if ($record['key'] !== $key) {
                    continue;
                }

                $records[] = [
                    'source' => $source['name'],
                    'line' => $record['line'],
                    'value' => $record['value'],
                    'active' => $source['active'],
                ];
            }
        }

        return $records;
    }

    private function effectiveValueForKey(string $key): mixed
    {
        if (! array_key_exists($key, $this->effectiveConfigPaths)) {
            return null;
        }

        return config($this->effectiveConfigPaths[$key]);
    }

    private function effectiveConfigLabel(string $key): string
    {
        if (! array_key_exists($key, $this->effectiveConfigPaths)) {
            return '';
        }

        return sprintf(' (%s)', $this->effectiveConfigPaths[$key]);
    }

    /**
     * @param  list<array{source:string,line:string,value:string,active:bool}>  $records
     */
    private function hasValueConflict(array $records, bool $isSecret): bool
    {
        if ($records === []) {
            return false;
        }

        $distinct = [];
        foreach ($records as $record) {
            $value = trim($record['value']);
            $distinct[] = $isSecret
                ? hash('sha256', $value)
                : $value;
        }

        return count(array_unique($distinct)) > 1;
    }

    /**
     * @param  list<array{source:string,line:string,value:string,active:bool}>  $records
     */
    private function allRecordValuesEmpty(array $records): bool
    {
        if ($records === []) {
            return true;
        }

        foreach ($records as $record) {
            if (trim($record['value']) !== '') {
                return false;
            }
        }

        return true;
    }

    private function describeRecordValue(string $value, bool $isSecret): string
    {
        $trimmed = trim($value);
        if ($isSecret) {
            if ($trimmed === '') {
                return 'empty';
            }

            return 'present';
        }

        return $trimmed === ''
            ? '[empty]'
            : $trimmed;
    }

    private function describeEffectiveValue(mixed $value, bool $isSecret): string
    {
        if ($isSecret) {
            if ($value === null) {
                return 'missing';
            }

            if (is_string($value) && trim($value) === '') {
                return 'empty';
            }

            return 'present';
        }

        if ($value === null) {
            return '[not configured]';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES) ?: '[invalid-json]';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $stringValue = trim((string) $value);

        return $stringValue === ''
            ? '[empty]'
            : $stringValue;
    }

    private function isSecretKey(string $key): bool
    {
        return str_contains($key, 'PASSWORD')
            || str_contains($key, 'SECRET')
            || str_contains($key, '_KEY')
            || str_contains($key, 'DATABASE_URL');
    }

    private function normalizeEnvValue(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        if (
            (str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"')) ||
            (str_starts_with($trimmed, "'") && str_ends_with($trimmed, "'"))
        ) {
            return substr($trimmed, 1, -1);
        }

        return $trimmed;
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<string>
     */
    private function duplicateKeysFromCounts(array $counts): array
    {
        $duplicates = [];
        foreach ($counts as $key => $count) {
            if ($count > 1) {
                $duplicates[] = $key;
            }
        }

        sort($duplicates);

        return $duplicates;
    }

    /**
     * @return array<string, true>
     */
    private function trackedKeysIndex(): array
    {
        $index = [];
        foreach (self::TRACKED_KEYS as $key) {
            $index[$key] = true;
        }

        ksort($index);

        return $index;
    }

    /**
     * @return list<string>
     */
    private function sqliteWarnings(): array
    {
        $warnings = [];

        $defaultConnection = strtolower((string) config('database.default', ''));
        if ($defaultConnection === 'sqlite') {
            $warnings[] = 'database.default resolves to sqlite.';
        }

        $centralDriver = strtolower((string) config('database.connections.central.driver', ''));
        if ($centralDriver === 'sqlite') {
            $warnings[] = 'database.connections.central.driver resolves to sqlite.';
        }

        return $warnings;
    }

    private function relativePath(string $absolutePath): string
    {
        $base = rtrim(base_path(), '/').'/';

        if (str_starts_with($absolutePath, $base)) {
            return substr($absolutePath, strlen($base));
        }

        return $absolutePath;
    }
}
