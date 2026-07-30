<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->boolean('is_demo')->default(false)->after('health_state');
            $table->string('demo_dataset_version', 30)->nullable()->after('is_demo');
            $table->timestamp('demo_seeded_at')->nullable()->after('demo_dataset_version');
            $table->index(['is_demo', 'status'], 'idx_tenants_demo');
        });

        Schema::create('central_settings', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('key')->unique('uq_central_settings_key');
            $table->text('value')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropIndex('idx_tenants_demo');
            $table->dropColumn([
                'is_demo',
                'demo_dataset_version',
                'demo_seeded_at',
            ]);
        });

        Schema::dropIfExists('central_settings');
    }
};
