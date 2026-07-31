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

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'prospect_id',
        'onboarding_session_id',
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
        return $this->belongsTo(Prospect::class);
    }

    /**
     * @return BelongsTo<OnboardingSession, $this>
     */
    public function onboardingSession(): BelongsTo
    {
        return $this->belongsTo(OnboardingSession::class);
    }

    /**
     * @return HasMany<DiscoveryNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(DiscoveryNote::class);
    }
}
