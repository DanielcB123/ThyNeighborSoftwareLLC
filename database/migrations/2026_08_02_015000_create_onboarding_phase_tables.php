<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_sources', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_lead_sources_public_id');
            $table->string('name');
            $table->string('slug')->unique('uq_lead_sources_slug');
            $table->string('status', 32)->default('active');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('inquiry_submissions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_inquiry_submissions_public_id');
            $table->foreignId('lead_source_id')
                ->nullable()
                ->constrained('lead_sources')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('status', 32)->default('submitted');
            $table->string('spam_disposition', 32)->default('not_checked');
            $table->string('contact_name', 120)->nullable();
            $table->string('business_name', 160)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('normalized_email', 191)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('website', 255)->nullable();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['status', 'submitted_at'], 'idx_inquiry_submission_status');
            $table->index('normalized_email', 'idx_inquiry_submission_email');
        });

        Schema::create('leads', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_leads_public_id');
            $table->foreignId('inquiry_submission_id')
                ->nullable()
                ->constrained('inquiry_submissions')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('status', 32)->default('new');
            $table->string('priority', 32)->default('normal');
            $table->string('business_stage', 32)->nullable();
            $table->string('business_name', 160)->nullable();
            $table->string('primary_contact_name', 120)->nullable();
            $table->string('primary_contact_role', 80)->nullable();
            $table->string('primary_email', 191)->nullable();
            $table->string('normalized_primary_email', 191)->nullable();
            $table->string('primary_phone', 40)->nullable();
            $table->string('industry', 80)->nullable();
            $table->string('industry_other', 120)->nullable();
            $table->string('business_location')->nullable();
            $table->unsignedSmallInteger('location_count')->nullable();
            $table->string('employee_range', 32)->nullable();
            $table->string('project_direction', 80)->nullable();
            $table->string('budget_range', 32)->nullable();
            $table->string('desired_start_range', 32)->nullable();
            $table->string('decision_stage', 32)->nullable();
            $table->timestamp('converted_to_workspace_at')->nullable();
            $table->timestamp('discovery_completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['status', 'priority'], 'idx_leads_status_priority');
            $table->index('normalized_primary_email', 'idx_leads_email');
        });

        Schema::create('lead_contacts', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_lead_contacts_public_id');
            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('name', 120);
            $table->string('email', 191)->nullable();
            $table->string('normalized_email', 191)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('role', 80)->nullable();
            $table->string('contact_method', 32)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('invite_later')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['lead_id', 'normalized_email'], 'uq_lead_contact_email');
            $table->index(['lead_id', 'is_primary'], 'idx_lead_contact_primary');
        });

        Schema::create('lead_project_interests', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_lead_project_interest_public_id');
            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('project_type', 80);
            $table->text('summary')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['lead_id', 'project_type'], 'uq_lead_project_interest');
        });

        Schema::create('lead_status_events', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_lead_status_events_public_id');
            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('previous_status', 32)->nullable();
            $table->string('next_status', 32);
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('lead_activities', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_lead_activities_public_id');
            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('activity_type', 40);
            $table->string('visibility', 24)->default('internal');
            $table->string('summary');
            $table->text('details')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['lead_id', 'activity_type'], 'idx_lead_activities_type');
        });

        Schema::create('lead_qualification_reviews', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_lead_qualification_reviews_public_id');
            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('outcome', 40);
            $table->string('assessment_rating', 24)->nullable();
            $table->string('disqualification_reason', 40)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('prospect_workspaces', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospect_workspaces_public_id');
            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('status', 40)->default('active');
            $table->string('access_scope', 32)->default('invited_only');
            $table->unsignedTinyInteger('active_workspace_key')->nullable()->default(1);
            $table->string('workspace_name', 160)->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('discovery_completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['lead_id', 'active_workspace_key'], 'uq_prospect_workspace_active');
        });

        Schema::create('prospect_workspace_members', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospect_workspace_members_public_id');
            $table->foreignId('prospect_workspace_id')
                ->constrained('prospect_workspaces')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('role', 40)->default('owner');
            $table->string('access_scope', 32)->default('full');
            $table->string('status', 32)->default('active');
            $table->string('name', 120);
            $table->string('email', 191);
            $table->string('normalized_email', 191);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['prospect_workspace_id', 'normalized_email'], 'uq_workspace_member_email');
        });

        Schema::create('prospect_invitations', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospect_invitations_public_id');
            $table->foreignId('prospect_workspace_id')
                ->constrained('prospect_workspaces')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('invited_by_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('status', 32)->default('pending');
            $table->string('email', 191);
            $table->string('normalized_email', 191);
            $table->string('role', 40)->default('collaborator');
            $table->string('invitation_token_hash', 128)->unique('uq_prospect_invitation_token');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('prospect_workspace_tasks', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospect_workspace_tasks_public_id');
            $table->foreignId('prospect_workspace_id')
                ->constrained('prospect_workspaces')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->unsignedBigInteger('discovery_session_id')->nullable();
            $table->string('task_type', 40)->default('general');
            $table->string('status', 32)->default('open');
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->string('meeting_format', 40)->default('video');
            $table->string('timezone', 64)->default('UTC');
            $table->date('preferred_start_date')->nullable();
            $table->date('preferred_end_date')->nullable();
            $table->text('availability_notes')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(45);
            $table->timestamp('scheduled_for')->nullable();
            $table->string('scheduler_reference')->nullable();
            $table->json('task_payload')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['prospect_workspace_id', 'task_type'], 'idx_workspace_tasks_type');
            $table->index('discovery_session_id', 'idx_workspace_tasks_session');
        });

        Schema::create('prospect_message_threads', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospect_message_threads_public_id');
            $table->foreignId('prospect_workspace_id')
                ->constrained('prospect_workspaces')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('started_by_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('status', 32)->default('open');
            $table->string('subject', 160)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('prospect_messages', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospect_messages_public_id');
            $table->foreignId('prospect_message_thread_id')
                ->constrained('prospect_message_threads')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('author_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('visibility', 24)->default('workspace');
            $table->longText('body');
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('prospect_files', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospect_files_public_id');
            $table->foreignId('prospect_workspace_id')
                ->constrained('prospect_workspaces')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('uploaded_by_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('status', 32)->default('active');
            $table->string('file_scan_status', 32)->default('not_scanned');
            $table->string('category', 32)->default('supporting_material');
            $table->string('visibility', 32)->default('workspace');
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['prospect_workspace_id', 'status'], 'idx_prospect_files_status');
        });

        Schema::create('prospect_workspace_events', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospect_workspace_events_public_id');
            $table->foreignId('prospect_workspace_id')
                ->constrained('prospect_workspaces')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('actor_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('event_type', 48);
            $table->json('event_payload')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('discovery_templates', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_templates_public_id');
            $table->string('name', 160);
            $table->string('slug', 160)->unique('uq_discovery_templates_slug');
            $table->string('status', 32)->default('draft');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('discovery_template_versions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_template_versions_public_id');
            $table->foreignId('discovery_template_id')
                ->constrained('discovery_templates')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->unsignedInteger('version_number');
            $table->string('status', 32)->default('draft');
            $table->string('created_by', 120)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['discovery_template_id', 'version_number'], 'uq_discovery_template_version');
        });

        Schema::create('discovery_section_definitions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_section_definitions_public_id');
            $table->foreignId('discovery_template_version_id')
                ->constrained('discovery_template_versions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('section_key', 80);
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('visibility_rules')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['discovery_template_version_id', 'section_key'], 'uq_discovery_section_key');
        });

        Schema::create('discovery_question_definitions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_question_definitions_public_id');
            $table->foreignId('discovery_template_version_id')
                ->constrained('discovery_template_versions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('section_key', 80);
            $table->string('question_key', 80);
            $table->string('response_type', 32);
            $table->text('prompt');
            $table->boolean('is_required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('validation_rules')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['discovery_template_version_id', 'question_key'], 'uq_discovery_question_key');
            $table->index(['discovery_template_version_id', 'section_key'], 'idx_discovery_questions_section');
        });

        Schema::create('discovery_question_options', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_question_options_public_id');
            $table->foreignId('discovery_question_definition_id')
                ->constrained('discovery_question_definitions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('option_key', 80);
            $table->string('option_label', 160);
            $table->string('option_value', 160)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['discovery_question_definition_id', 'option_key'], 'uq_discovery_question_option');
        });

        Schema::create('discovery_logic_rules', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_logic_rules_public_id');
            $table->foreignId('discovery_template_version_id')
                ->constrained('discovery_template_versions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('rule_key', 80);
            $table->string('trigger_question_key', 80);
            $table->string('condition_operator', 32);
            $table->string('condition_value')->nullable();
            $table->string('action_type', 40);
            $table->string('action_target', 80);
            $table->json('action_payload')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['discovery_template_version_id', 'rule_key'], 'uq_discovery_logic_rule');
        });

        Schema::create('discovery_sessions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_sessions_public_id');
            $table->foreignId('prospect_workspace_id')
                ->constrained('prospect_workspaces')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('discovery_template_version_id')
                ->nullable()
                ->constrained('discovery_template_versions')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('access_token', 80)->unique('uq_discovery_sessions_access_token');
            $table->string('status', 32)->default('in_progress');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('last_activity_ip', 64)->nullable();
            $table->string('last_activity_user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['lead_id', 'status'], 'idx_discovery_sessions_status');
        });

        Schema::create('discovery_section_sessions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_section_sessions_public_id');
            $table->foreignId('discovery_session_id')
                ->constrained('discovery_sessions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('section_key', 80);
            $table->string('status', 32)->default('in_progress');
            $table->timestamp('entered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['discovery_session_id', 'section_key'], 'uq_discovery_section_session');
        });

        Schema::table('prospect_workspace_tasks', function (Blueprint $table): void {
            $table->foreign('discovery_session_id', 'fk_workspace_tasks_discovery_session')
                ->references('id')
                ->on('discovery_sessions')
                ->nullOnDelete()
                ->restrictOnUpdate();
        });

        Schema::create('discovery_responses', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_responses_public_id');
            $table->foreignId('discovery_session_id')
                ->constrained('discovery_sessions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->string('question_key', 80);
            $table->json('response_payload');
            $table->foreignId('answered_by_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->unsignedTinyInteger('current_response_key')->nullable()->default(1);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(
                ['discovery_session_id', 'question_key', 'current_response_key'],
                'uq_discovery_response_current'
            );
        });

        Schema::create('discovery_response_revisions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_response_revisions_public_id');
            $table->foreignId('discovery_response_id')
                ->constrained('discovery_responses')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->unsignedSmallInteger('revision_number');
            $table->foreignId('changed_by_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->json('response_payload');
            $table->timestamp('changed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['discovery_response_id', 'revision_number'], 'uq_discovery_response_revision');
        });

        Schema::create('clarification_requests', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_clarification_requests_public_id');
            $table->foreignId('discovery_session_id')
                ->constrained('discovery_sessions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('requested_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('resolved_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('status', 32)->default('open');
            $table->string('question_key', 80)->nullable();
            $table->text('prompt');
            $table->text('response')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('inspiration_references', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_inspiration_references_public_id');
            $table->foreignId('discovery_session_id')
                ->constrained('discovery_sessions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('submitted_by_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('reference_type', 40)->default('link');
            $table->string('title', 160)->nullable();
            $table->string('url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('prospect_stakeholders', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_prospect_stakeholders_public_id');
            $table->foreignId('prospect_workspace_id')
                ->constrained('prospect_workspaces')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('invited_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('name', 120);
            $table->string('email', 191)->nullable();
            $table->string('normalized_email', 191)->nullable();
            $table->string('role', 80)->nullable();
            $table->string('influence_level', 32)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['prospect_workspace_id', 'normalized_email'], 'uq_workspace_stakeholder_email');
        });

        Schema::create('requirement_candidates', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_requirement_candidates_public_id');
            $table->foreignId('prospect_workspace_id')
                ->constrained('prospect_workspaces')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('discovery_session_id')
                ->constrained('discovery_sessions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('source_response_id')
                ->nullable()
                ->constrained('discovery_responses')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->string('priority', 24)->default('normal');
            $table->string('status', 32)->default('candidate');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('discovery_submission_versions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_submission_versions_public_id');
            $table->foreignId('discovery_session_id')
                ->constrained('discovery_sessions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('submitted_by_member_id')
                ->nullable()
                ->constrained('prospect_workspace_members')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->unsignedSmallInteger('version_number');
            $table->json('submitted_payload');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['discovery_session_id', 'version_number'], 'uq_discovery_submission_version');
        });

        Schema::create('discovery_review_assessments', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->char('public_id', 26)->unique('uq_discovery_review_assessments_public_id');
            $table->foreignId('discovery_submission_version_id')
                ->constrained('discovery_submission_versions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('assessed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();
            $table->string('rating', 24)->nullable();
            $table->string('outcome', 40)->default('needs_clarification');
            $table->text('notes')->nullable();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_review_assessments');
        Schema::dropIfExists('discovery_submission_versions');
        Schema::dropIfExists('requirement_candidates');
        Schema::dropIfExists('prospect_stakeholders');
        Schema::dropIfExists('inspiration_references');
        Schema::dropIfExists('clarification_requests');
        Schema::dropIfExists('discovery_response_revisions');
        Schema::dropIfExists('discovery_responses');
        Schema::dropIfExists('discovery_section_sessions');
        Schema::dropIfExists('discovery_sessions');
        Schema::dropIfExists('discovery_logic_rules');
        Schema::dropIfExists('discovery_question_options');
        Schema::dropIfExists('discovery_question_definitions');
        Schema::dropIfExists('discovery_section_definitions');
        Schema::dropIfExists('discovery_template_versions');
        Schema::dropIfExists('discovery_templates');
        Schema::dropIfExists('prospect_workspace_events');
        Schema::dropIfExists('prospect_files');
        Schema::dropIfExists('prospect_messages');
        Schema::dropIfExists('prospect_message_threads');
        Schema::dropIfExists('prospect_workspace_tasks');
        Schema::dropIfExists('prospect_invitations');
        Schema::dropIfExists('prospect_workspace_members');
        Schema::dropIfExists('prospect_workspaces');
        Schema::dropIfExists('lead_qualification_reviews');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('lead_status_events');
        Schema::dropIfExists('lead_project_interests');
        Schema::dropIfExists('lead_contacts');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('inquiry_submissions');
        Schema::dropIfExists('lead_sources');
    }
};
