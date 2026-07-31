<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospects_public_id');
            $table->string('intake_status', 32)->default('in_progress');
            $table->string('project_direction', 80)->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_email')->nullable();
            $table->string('business_phone', 40)->nullable();
            $table->string('industry', 80)->nullable();
            $table->string('industry_other', 120)->nullable();
            $table->string('business_location')->nullable();
            $table->unsignedSmallInteger('location_count')->nullable();
            $table->string('team_size', 40)->nullable();
            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_role', 80)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('prospect_contacts', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('prospect_id')
                ->constrained('prospects')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('role', 80)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('invite_later')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['prospect_id', 'is_primary'], 'idx_prospect_contacts_primary');
        });

        Schema::create('onboarding_sessions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_onboarding_sessions_public_id');
            $table->foreignId('prospect_id')
                ->constrained('prospects')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('access_token', 80)->unique('uq_onboarding_sessions_access_token');
            $table->string('status', 32)->default('in_progress');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('last_activity_ip', 64)->nullable();
            $table->string('last_activity_user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('onboarding_responses', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('onboarding_session_id')
                ->constrained('onboarding_sessions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('step_key', 40);
            $table->json('response_payload');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(
                ['onboarding_session_id', 'step_key'],
                'uq_onboarding_responses_session_step'
            );
        });

        Schema::create('discovery_meetings', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_meetings_public_id');
            $table->foreignId('prospect_id')
                ->constrained('prospects')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('onboarding_session_id')
                ->nullable()
                ->constrained('onboarding_sessions')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('status', 32)->default('requested');
            $table->string('meeting_format', 40)->default('video');
            $table->string('timezone', 64);
            $table->date('preferred_start_date')->nullable();
            $table->date('preferred_end_date')->nullable();
            $table->text('availability_notes')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(45);
            $table->timestamp('scheduled_for')->nullable();
            $table->string('scheduler_reference')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('discovery_notes', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('discovery_meeting_id')
                ->constrained('discovery_meetings')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('author_name', 120)->nullable();
            $table->text('note_text');
            $table->json('note_context')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('project_requirements', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('prospect_id')
                ->constrained('prospects')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('discovery_meeting_id')
                ->nullable()
                ->constrained('discovery_meetings')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 32)->default('draft');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('proposed_deliverables', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('prospect_id')
                ->constrained('prospects')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('discovery_meeting_id')
                ->nullable()
                ->constrained('discovery_meetings')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('phase', 40)->nullable();
            $table->text('effort_notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('client_reviews', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('prospect_id')
                ->constrained('prospects')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('discovery_meeting_id')
                ->nullable()
                ->constrained('discovery_meetings')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('status', 32)->default('pending');
            $table->text('summary')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_reviews');
        Schema::dropIfExists('proposed_deliverables');
        Schema::dropIfExists('project_requirements');
        Schema::dropIfExists('discovery_notes');
        Schema::dropIfExists('discovery_meetings');
        Schema::dropIfExists('onboarding_responses');
        Schema::dropIfExists('onboarding_sessions');
        Schema::dropIfExists('prospect_contacts');
        Schema::dropIfExists('prospects');
    }
};
