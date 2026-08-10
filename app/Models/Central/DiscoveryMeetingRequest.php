<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscoveryMeetingRequest extends CentralModel
{
    use HasPublicId;

    protected $table = 'discovery_meeting_requests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'prospect_workspace_id',
        'discovery_session_id',
        'lead_id',
        'status',
        'meeting_format',
        'timezone',
        'preferred_start_date',
        'preferred_end_date',
        'availability_notes',
        'duration_minutes',
        'scheduled_for',
        'scheduler_reference',
        'request_payload',
        'requested_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'preferred_start_date' => 'date',
            'preferred_end_date' => 'date',
            'scheduled_for' => 'datetime',
            'requested_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    /**
     * @return BelongsTo<OnboardingSession, $this>
     */
    public function onboardingSession(): BelongsTo
    {
        return $this->belongsTo(OnboardingSession::class, 'discovery_session_id');
    }

    /**
     * @return BelongsTo<ProspectWorkspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(ProspectWorkspace::class, 'prospect_workspace_id');
    }
}
