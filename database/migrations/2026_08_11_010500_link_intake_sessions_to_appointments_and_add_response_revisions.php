<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarding_sessions', function (Blueprint $table): void {
            $table->foreignId('onboarding_appointment_id')
                ->nullable()
                ->after('prospect_id')
                ->constrained('onboarding_appointments')
                ->nullOnDelete()
                ->restrictOnUpdate();

            $table->unique('onboarding_appointment_id', 'uq_onboarding_sessions_appointment');
        });

        Schema::create('onboarding_response_revisions', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique('uq_onboarding_response_revisions_public_id');
            $table->foreignId('onboarding_response_id')
                ->constrained('onboarding_responses')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->unsignedSmallInteger('revision_number');
            $table->json('response_payload');
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['onboarding_response_id', 'revision_number'],
                'uq_onboarding_response_revisions_number'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_response_revisions');

        Schema::table('onboarding_sessions', function (Blueprint $table): void {
            $table->dropUnique('uq_onboarding_sessions_appointment');
            $table->dropConstrainedForeignId('onboarding_appointment_id');
        });
    }
};
