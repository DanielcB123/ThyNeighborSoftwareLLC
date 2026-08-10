<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_appointments', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique('uq_onboarding_appointments_public_id');
            $table->char('tenant_public_id', 26)->nullable();
            $table->string('business_name');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('timezone', 64)->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(45);
            $table->string('zoom_meeting_id', 64)->nullable();
            $table->text('zoom_join_url')->nullable();
            $table->text('zoom_start_url')->nullable();
            $table->string('zoom_passcode', 64)->nullable();
            $table->string('zoom_status', 32)->nullable();
            $table->char('access_token_hash', 64);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('last_zoom_synced_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_public_id', 'status'], 'idx_onboarding_appointments_tenant_status');
            $table->index(['status', 'scheduled_at'], 'idx_onboarding_appointments_status_scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_appointments');
    }
};
