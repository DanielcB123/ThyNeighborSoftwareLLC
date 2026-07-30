<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_clusters', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_database_clusters_public_id');
            $table->string('name')->unique('uq_database_clusters_name');
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(3306);
            $table->string('ssl_mode', 32)->default('preferred');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('tenants', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_tenants_public_id');
            $table->string('slug')->unique('uq_tenants_slug');
            $table->string('display_name');
            $table->string('status', 32)->default('active');
            $table->string('locale', 12)->default('en');
            $table->string('timezone', 64)->default('UTC');
            $table->json('enabled_modules')->nullable();
            $table->json('enabled_capabilities')->nullable();
            $table->string('provisioning_state', 32)->default('ready');
            $table->string('migration_state', 32)->default('current');
            $table->string('health_state', 32)->default('healthy');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('tenant_domains', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('tenant_id')
                ->constrained('tenants', 'id', 'fk_tenant_domains_tenant_id')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('domain')->unique('uq_tenant_domains_domain');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['tenant_id', 'is_primary'], 'idx_tenant_domains_tenant_primary');
            $table->index(['domain', 'is_active'], 'idx_tenant_domains_domain_active');
        });

        Schema::create('tenant_databases', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('tenant_id')
                ->constrained('tenants', 'id', 'fk_tenant_databases_tenant_id')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('database_cluster_id')
                ->constrained('database_clusters', 'id', 'fk_tenant_databases_cluster_id')
                ->restrictOnDelete()
                ->restrictOnUpdate();
            $table->string('database_name')->unique('uq_tenant_databases_database_name');
            $table->string('status', 32)->default('active');
            $table->string('secret_reference');
            $table->string('schema_version', 64)->nullable();
            $table->string('provisioning_state', 32)->default('ready');
            $table->string('migration_state', 32)->default('current');
            $table->string('health_state', 32)->default('healthy');
            $table->boolean('is_current')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['tenant_id', 'is_current'], 'uq_tenant_databases_tenant_current');
            $table->index(['database_cluster_id', 'status'], 'idx_tenant_databases_cluster_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_databases');
        Schema::dropIfExists('tenant_domains');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('database_clusters');
    }
};
