<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OnboardingSession extends CentralModel
{
    use HasPublicId;

    protected $table = 'discovery_sessions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'prospect_workspace_id',
        'lead_id',
        'discovery_template_version_id',
        'access_token',
        'status',
        'current_step',
        'last_saved_at',
        'completed_at',
        'last_activity_ip',
        'last_activity_user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_step' => 'integer',
            'last_saved_at' => 'datetime',
            'completed_at' => 'datetime',
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
     * @return BelongsTo<ProspectWorkspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(ProspectWorkspace::class, 'prospect_workspace_id');
    }

    /**
     * @return HasMany<OnboardingResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(OnboardingResponse::class, 'discovery_session_id');
    }

    /**
     * @return HasOne<DiscoveryMeetingRequest, $this>
     */
    public function discoveryMeeting(): HasOne
    {
        return $this->hasOne(DiscoveryMeetingRequest::class, 'discovery_session_id');
    }

    /**
     * @deprecated Prefer {@see self::lead()}.
     *
     * @return BelongsTo<Lead, $this>
     */
    public function prospect(): BelongsTo
    {
        return $this->lead();
    }
}
