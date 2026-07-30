<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ImportSqliteToMysqlCommand extends Command
{
    protected $signature = 'app:import-sqlite-to-mysql
        {--source= : Path to the source SQLite file}
        {--target-env= : Required APP_ENV value for this execution}
        {--allow-production : Permit imports when APP_ENV=production}
        {--dry-run : Inspect and validate without writing rows}';

    protected $description = 'Import SQLite data into central MySQL with safety checks';

    /**
     * @var list<string>
     */
    private array $tableOrder = [
        'users',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'database_clusters',
        'tenants',
        'tenant_domains',
        'tenant_databases',
    ];

    public function handle(): int
    {
        $sourceOption = trim((string) $this->option('source'));
        $targetEnv = trim((string) $this->option('target-env'));
        $dryRun = (bool) $this->option('dry-run');

        if ($sourceOption === '') {
            $this->error('The --source option is required.');

            return self::FAILURE;
        }

        if ($targetEnv === '') {
            $this->error('The --target-env option is required.');

            return self::FAILURE;
        }

        if ($targetEnv !== app()->environment()) {
            $this->error(sprintf(
                'Target environment mismatch. Expected "%s", current environment is "%s".',
                $targetEnv,
                app()->environment()
            ));

            return self::FAILURE;
        }

        if (app()->environment('production') && ! (bool) $this->option('allow-production')) {
            $this->error(
                'Production imports are blocked unless --allow-production is explicitly provided.'
            );

            return self::FAILURE;
        }

        $sourcePath = $this->resolveSourcePath($sourceOption);

        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            $this->error(sprintf('SQLite source file is not readable: %s', $sourcePath));

            return self::FAILURE;
        }

        config()->set('database.connections.sqlite_import', [
            'driver' => 'sqlite',
            'database' => $sourcePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite_import');
        DB::disconnect('sqlite_import');

        $source = DB::connection('sqlite_import');
        $target = DB::connection('central');

        $report = [
            'generated_at_utc' => now()->toIso8601String(),
            'source_sqlite_file' => $sourcePath,
            'target_connection' => 'central',
            'target_environment' => app()->environment(),
            'dry_run' => $dryRun,
            'tables' => [],
            'failed_rows' => [],
            'notes' => [],
        ];

        $targetOccupancy = $this->countTargetRows($target);

        if (! $dryRun) {
            $nonEmptyTargetTables = array_filter(
                $targetOccupancy,
                static fn (int $count): bool => $count > 0
            );

            if ($nonEmptyTargetTables !== []) {
                $this->error('Import aborted: target MySQL tables already contain data.');
                foreach ($nonEmptyTargetTables as $table => $count) {
                    $this->line(sprintf(' - %s: %d rows', $table, $count));
                }

                $report['notes'][] = 'Import aborted because target tables are non-empty.';
                $report['target_table_occupancy'] = $targetOccupancy;
                $this->writeReport($report);

                return self::FAILURE;
            }
        } elseif (array_filter($targetOccupancy, static fn (int $count): bool => $count > 0) !== []) {
            $report['notes'][] = 'Dry-run only: target tables are non-empty and a live import would be rejected.';
        }

        $hasFailures = false;
        $summaryRows = [];

        foreach ($this->tableOrder as $table) {
            if (! $source->getSchemaBuilder()->hasTable($table)) {
                $report['tables'][$table] = [
                    'source_rows' => 0,
                    'imported_rows' => 0,
                    'failed_rows' => 0,
                    'target_rows_after' => (int) $target->table($table)->count(),
                    'status' => 'skipped_missing_source_table',
                ];

                $summaryRows[] = [$table, 'skipped', '0', '0', '0'];

                continue;
            }

            $sourceRows = $source->table($table)->get();
            $sourceCount = $sourceRows->count();
            $importedRows = 0;
            $failedRows = 0;

            if (! $dryRun && $sourceCount > 0) {
                foreach ($sourceRows as $sourceRow) {
                    $payload = (array) $sourceRow;

                    try {
                        $target->table($table)->insert($payload);
                        $importedRows++;
                    } catch (Throwable $exception) {
                        $failedRows++;
                        $hasFailures = true;

                        $report['failed_rows'][] = [
                            'table' => $table,
                            'row_identity' => $this->deriveRowIdentity($payload),
                            'error' => $exception->getMessage(),
                        ];
                    }
                }
            }

            if ($dryRun) {
                $importedRows = $sourceCount;
            }

            $status = $failedRows > 0
                ? 'completed_with_row_failures'
                : 'completed';

            $targetRowsAfter = (int) $target->table($table)->count();

            if (! $dryRun && $failedRows === 0 && $sourceCount !== $targetRowsAfter) {
                $status = 'row_count_mismatch';
                $hasFailures = true;
            }

            $report['tables'][$table] = [
                'source_rows' => $sourceCount,
                'imported_rows' => $importedRows,
                'failed_rows' => $failedRows,
                'target_rows_after' => $targetRowsAfter,
                'status' => $status,
            ];

            $summaryRows[] = [
                $table,
                $status,
                (string) $sourceCount,
                (string) $importedRows,
                (string) $failedRows,
            ];
        }

        $this->table(
            ['table', 'status', 'source rows', 'imported rows', 'failed rows'],
            $summaryRows
        );

        $reportPath = $this->writeReport($report);
        $this->info(sprintf('Import report written to %s', $reportPath));

        DB::disconnect('sqlite_import');
        DB::purge('sqlite_import');

        if ($hasFailures) {
            $this->error('Import finished with failures. Review the report before retrying.');

            return self::FAILURE;
        }

        $this->info($dryRun ? 'Dry-run completed successfully.' : 'Import completed successfully.');

        return self::SUCCESS;
    }

    private function resolveSourcePath(string $optionValue): string
    {
        if (str_starts_with($optionValue, '/')) {
            return $optionValue;
        }

        return base_path($optionValue);
    }

    /**
     * @return array<string, int>
     */
    private function countTargetRows($targetConnection): array
    {
        $counts = [];

        foreach ($this->tableOrder as $table) {
            $counts[$table] = $targetConnection->getSchemaBuilder()->hasTable($table)
                ? (int) $targetConnection->table($table)->count()
                : 0;
        }

        return $counts;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function deriveRowIdentity(array $payload): string
    {
        foreach (['id', 'public_id', 'uuid', 'email', 'key'] as $identityColumn) {
            if (array_key_exists($identityColumn, $payload)) {
                return sprintf('%s=%s', $identityColumn, (string) $payload[$identityColumn]);
            }
        }

        return '[unidentified-row]';
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function writeReport(array $report): string
    {
        $reportDirectory = storage_path('app/reports');

        if (! is_dir($reportDirectory)) {
            mkdir($reportDirectory, 0755, true);
        }

        $reportPath = sprintf(
            '%s/sqlite-import-to-mysql-%s.json',
            $reportDirectory,
            now()->format('Ymd-His')
        );

        file_put_contents(
            $reportPath,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
        );

        return $reportPath;
    }
}
