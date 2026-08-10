<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('discovery_meeting_requests')) {
            Schema::create('discovery_meeting_requests', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';

                $table->id();
                $table->char('public_id', 26)->unique('uq_discovery_meeting_requests_public_id');
                $table->foreignId('prospect_workspace_id')
                    ->constrained('prospect_workspaces')
                    ->cascadeOnDelete()
                    ->restrictOnUpdate();
                $table->foreignId('discovery_session_id')
                    ->nullable()
                    ->constrained('discovery_sessions')
                    ->nullOnDelete()
                    ->restrictOnUpdate();
                $table->foreignId('lead_id')
                    ->constrained('leads')
                    ->cascadeOnDelete()
                    ->restrictOnUpdate();
                $table->string('status', 32)->default('requested');
                $table->string('meeting_format', 40)->default('video');
                $table->string('timezone', 64)->default('UTC');
                $table->date('preferred_start_date')->nullable();
                $table->date('preferred_end_date')->nullable();
                $table->text('availability_notes')->nullable();
                $table->unsignedSmallInteger('duration_minutes')->default(45);
                $table->timestamp('scheduled_for')->nullable();
                $table->string('scheduler_reference')->nullable();
                $table->json('request_payload')->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique(['discovery_session_id'], 'uq_discovery_meeting_request_session');
                $table->index(['prospect_workspace_id', 'status'], 'idx_discovery_meeting_request_status');
            });
        }

        if (
            Schema::hasTable('prospect_workspace_tasks') &&
            Schema::hasTable('discovery_meeting_requests')
        ) {
            $legacyMeetingTasks = DB::table('prospect_workspace_tasks')
                ->where('task_type', 'discovery_meeting')
                ->whereNotNull('discovery_session_id')
                ->orderBy('id')
                ->get();

            foreach ($legacyMeetingTasks as $task) {
                DB::table('discovery_meeting_requests')->updateOrInsert(
                    [
                        'discovery_session_id' => $task->discovery_session_id,
                    ],
                    [
                        'public_id' => (string) ($task->public_id ?? Str::ulid()),
                        'prospect_workspace_id' => $task->prospect_workspace_id,
                        'lead_id' => $task->lead_id,
                        'status' => $task->status ?? 'requested',
                        'meeting_format' => $task->meeting_format ?? 'video',
                        'timezone' => $task->timezone ?? 'UTC',
                        'preferred_start_date' => $task->preferred_start_date,
                        'preferred_end_date' => $task->preferred_end_date,
                        'availability_notes' => $task->availability_notes,
                        'duration_minutes' => $task->duration_minutes ?? 45,
                        'scheduled_for' => $task->scheduled_for,
                        'scheduler_reference' => $task->scheduler_reference,
                        'request_payload' => $task->task_payload,
                        'requested_at' => $task->created_at,
                        'created_at' => $task->created_at ?? now(),
                        'updated_at' => $task->updated_at ?? now(),
                    ]
                );
            }
        }

        if (Schema::hasTable('discovery_sessions')) {
            DB::table('discovery_sessions')
                ->where('status', 'in_progress')
                ->where(function ($query): void {
                    $query
                        ->where('current_step', '>', 1)
                        ->orWhereExists(function ($subquery): void {
                            $subquery->selectRaw('1')
                                ->from('discovery_responses')
                                ->whereColumn('discovery_responses.discovery_session_id', 'discovery_sessions.id');
                        });
                })
                ->update([
                    'status' => 'project_discovery_in_progress',
                    'updated_at' => now(),
                ]);

            DB::table('discovery_sessions')
                ->where('status', 'in_progress')
                ->update([
                    'status' => 'draft',
                    'updated_at' => now(),
                ]);

            DB::table('discovery_sessions')
                ->where('status', 'discovery_complete')
                ->update([
                    'status' => 'submitted',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('discovery_sessions')) {
            DB::table('discovery_sessions')
                ->where('status', 'submitted')
                ->update([
                    'status' => 'discovery_complete',
                    'updated_at' => now(),
                ]);

            DB::table('discovery_sessions')
                ->whereIn('status', ['draft', 'project_discovery_in_progress'])
                ->update([
                    'status' => 'in_progress',
                    'updated_at' => now(),
                ]);
        }

        Schema::dropIfExists('discovery_meeting_requests');
    }
};
