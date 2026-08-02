<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscoveryMeeting extends CentralModel
{
    use HasPublicId;

    protected $table = 'prospect_workspace_tasks';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'prospect_workspace_id',
        'discovery_session_id',
        'lead_id',
        'task_type',
        'title',
        'description',
        'task_payload',
        'status',
        'meeting_format',
        'timezone',
        'preferred_start_date',
        'preferred_end_date',
        'availability_notes',
        'duration_minutes',
        'scheduled_for',
        'scheduler_reference',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'task_payload' => 'array',
            'preferred_start_date' => 'date',
            'preferred_end_date' => 'date',
            'scheduled_for' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Prospect, $this>
     */
    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class, 'lead_id');
    }

    /**
     * @return BelongsTo<OnboardingSession, $this>
     */
    public function onboardingSession(): BelongsTo
    {
        return $this->belongsTo(OnboardingSession::class, 'discovery_session_id');
    }

    /**
     * @return HasMany<DiscoveryNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(DiscoveryNote::class);
    }
}
