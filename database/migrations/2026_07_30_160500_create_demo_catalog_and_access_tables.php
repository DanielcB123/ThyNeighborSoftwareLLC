<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_permissions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->string('permission_key')->unique('uq_platform_permissions_key');
            $table->string('display_name');
            $table->string('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('platform_roles', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->string('role_key')->unique('uq_platform_roles_key');
            $table->string('display_name');
            $table->string('scope', 32);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('platform_role_permissions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->string('role_key');
            $table->string('permission_key');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['role_key', 'permission_key'], 'uq_platform_role_permissions_role_permission');
            $table->index(['permission_key'], 'idx_platform_role_permissions_permission');
        });

        Schema::create('platform_user_roles', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->foreignId('user_id')
                ->constrained('users', 'id', 'fk_platform_user_roles_user_id')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('role_key');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['user_id', 'role_key'], 'uq_platform_user_roles_user_role');
        });

        Schema::create('platform_modules', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->string('module_key')->unique('uq_platform_modules_key');
            $table->string('display_name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('platform_features', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->string('feature_key')->unique('uq_platform_features_key');
            $table->string('display_name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('platform_module_features', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->string('module_key');
            $table->string('feature_key');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['module_key', 'feature_key'], 'uq_platform_module_features_module_feature');
        });

        Schema::create('platform_plans', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->string('plan_key')->unique('uq_platform_plans_key');
            $table->string('display_name');
            $table->unsignedInteger('monthly_price_cents')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('platform_plan_features', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->string('plan_key');
            $table->string('feature_key');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['plan_key', 'feature_key'], 'uq_platform_plan_features_plan_feature');
        });

        Schema::create('demo_audit_events', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->id();
            $table->string('event_type');
            $table->string('tenant_scope', 32)->default('all');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['event_type', 'created_at'], 'idx_demo_audit_events_type_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_audit_events');
        Schema::dropIfExists('platform_plan_features');
        Schema::dropIfExists('platform_plans');
        Schema::dropIfExists('platform_module_features');
        Schema::dropIfExists('platform_features');
        Schema::dropIfExists('platform_modules');
        Schema::dropIfExists('platform_user_roles');
        Schema::dropIfExists('platform_role_permissions');
        Schema::dropIfExists('platform_roles');
        Schema::dropIfExists('platform_permissions');
    }
};
